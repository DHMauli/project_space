"use strict";

var PATH_INPUT_HANDLER = "../php/game/inputhandler.php";
var PATH_REQUEST_INFO = "../php/game/requestInfo.php";

var processItems = [];

var resources = [];

var slowIntervall;
var standardInterval;
var fastInterval;

var selectedX;
var selectedY;

var currentView;

var selectedPlanetId = "";

$(function() {
	loadNewMap(3, 4, undefined, addTileEventListener);
	
	$("div").click(function(event) {
		getClickmenuHandlingFunction(this, event);
	});
	$("footer").click(function(event) {
		getClickmenuHandlingFunction(this, event);
	});
	
	$("#resetAccount").click(resetAccountData);
	$("#systemView").click(getSystemSatsFunction());
	
	$("#btnLogout").click(function() {
		$.post(PATH_INPUT_HANDLER,
		{
			action: "logout"
		},
		function(data) {
			data = filterPHPData(data);
			
			if (data === 'success') {
				window.location = "../index.htm";
			}
		});
	});
	
	getResources();
	slowIntervall = window.setInterval(slowUpdate, 5000);
	standardInterval = window.setInterval(standardUpdate, 2000);
	// fastInterval = window.setInterval(fastUpdate, 1000);
	getCurrentPlanetId(getAndCreateActiveProcesses, "building");
});

function getClickmenuHandlingFunction(object, event) {
	var attrClass = $(object).attr('class');
	
	var isClickmenu = false;
	if (attrClass !== undefined) {
		var classes = attrClass.split(' ');
		for (var i = 0; i < classes.length; i++) {
			if ((classes[i] === "clickmenu") || (classes[i] === "tile")) {
				isClickmenu = true;
				break;
			}
		}
	}
	
	if (isClickmenu) {
		stopPropagation(event);
		return;
	}
	
	hideClickMenu();
}

function stopPropagation(event) {
	// cross-browser event
	var thisEvent = event || window.event;
	
	if (thisEvent === undefined) {
		return;
	}
	
	if (thisEvent.stopPropagation) {
		// W3C standard variant
		thisEvent.stopPropagation();
	} else {
		// IE variant
		thisEvent.cancelBubble = true;
	}
};

function createCallbackBuildClick(buildingId, action) {
	switch (action) {
		case "build":
		{
			return function() {
				$.post(PATH_INPUT_HANDLER,
				{
					action:	action,
					bdgId:	buildingId,
					pos_x:	selectedX,
					pos_y:	selectedY
				},
				function(data) {
					data = filterPHPData(data);
					if (processError()) {
						return;
					}
					
					var result = $.parseJSON(data);
					
					for (var key in result['costs']) {
						resources[key] -= result['costs'][key];
					}
					updateResourceLabels();
					
					var divObj = $("div#x" + result['process']['pos_x'] + "-y" + result['process']['pos_y']);
					divObj.addClass("image");
					divObj.addClass("constructionsite");
					
					createProcess(result['process']);
				});
			};
		}
		case "buildInSpace":
		{
			return function() {
				$.post(PATH_INPUT_HANDLER,
				{
					action:	action,
					bdgId:	buildingId
				},
				function(data) {
					data = filterPHPData(data);
					if (processError()) {
						return;
					}
					
					var result = $.parseJSON(data);
					
					for (var key in result['costs']) {
						resources[key] -= result['costs'][key];
					}
					updateResourceLabels();
					
					createProcess(result['process']);
				});
			};
		}
		default:
		{
			console.log("createCallbackBuildClick action " + action + " is not supported.");
			return;
		}
	}
}

function createProcess(data, initialSynch, planetId) {
	var domName = data['name'].replace(/:/g, "-") + "-dom";
	var objPQI = new ProcessQItem(data['duration'], domName, data['name'], planetId);
	objPQI.createDOM();
	
	if (processItems.length === 0) {
		objPQI.init(initialSynch);
	}
	
	processItems[processItems.length] = objPQI;
}

function openSysBuildMenu(planetid) {
	$.post(PATH_REQUEST_INFO,
	{
		intelType:	"getBuildOptionsSys",
		planetid: 	planetid
	},
	function(data) {
		data = filterPHPData(data);
		
		if (processError()) {
			return;
		}
		
		var result = $.parseJSON(data);
		applyBuildingOptions(result);
	});
}

var previousTile = undefined;
function addTileEventListener() {
	$("div.tile").click(function() {
		if (previousTile !== undefined) {
			previousTile.css({
				"filter":        	"saturate(1.0)",
				"-webkit-filter":	"saturate(1.0)",
				"-ms-filter":    	"saturate(1.0)",
				"-o-filter":     	"saturate(1.0)"
			});	
		}
		previousTile = $(this);
		$(this).css({
			"filter":        	"saturate(2.0)",
			"-webkit-filter":	"saturate(2.0)",
			"-ms-filter":    	"saturate(2.0)",
			"-o-filter":     	"saturate(2.0)"
		});
		
		var strPos = $(this).attr('id').split('-');
		selectedX = strPos[0].substr(1);
		selectedY = strPos[1].substr(1);

		$("#labelselected").text("X: " + selectedX + ", Y: " + selectedY);

		$.post(PATH_REQUEST_INFO,
		{
			intelType: 	"getBuildOptions",
			pos_x: 		selectedX,
			pos_y: 		selectedY
		},
		function(data) {
			data = filterPHPData(data);
			var buildingOptions = $.parseJSON(data);
			applyBuildingOptions(buildingOptions);
		});
	});
}

function applyBuildingOptions(options) {
  hideBuildButtons();
	if ((options === null) || (options[0] === "none")) {
		console.log ("no building options");
	} else {
		var buildCommand;
		switch (currentView) {
			case "planet":
			{
				buildCommand = "build";
				break;
			}
			case "system":
			{
				buildCommand = "buildInSpace";
				break;
			}
			default:
			{
				break;
			}
		}
		
		$("#sidemenu-right > .clickmenu").css("display", "block");
		var doubleBreak = false;
		for (var i = 0; i < options.length; i++) {
			switch (options[i]) {
				case "all":
				{
					// TODO fix 'all'
					/*
					for (var j = 0; j < spanIds.length; j++) {
						var icon = $("span#" + spanIds[j]);
						icon.css("display", "inline");
						icon.click(createCallbackBuildClick(spanIds[j], buildCommand));
					}
					
					doubleBreak = true;
					break;
					*/
				}
				default:
				{
					var option = options[i];
					
					var spanButton = $("span.buildbutton#" + option);
					var numButton = spanButton.length;
					
					if (numButton >= 1) {
						spanButton.css("display", "inline");
						break;
					}
					
					var icon = $("<span class='buildbutton' id='" + option + "'></span>");
					icon.css({
						"background-image":	"url(../img/processicons/" + option + ".jpg",
						"display":			"inline"
					});
					$("div#sidemenu-right .clickmenu").slice(0, 1).append(icon);
					icon.click(createCallbackBuildClick(option, buildCommand));
					break;
				}
			}
			
			if (doubleBreak) {
				break;
			}
		}
	}
}

function updateProcessItems() {
	if (processItems.length === 0) {
		return;
	}
	
	if ((processItems[0].getTime() <= 0) && (processItems[0].DomDestroyed)) {
		
		processItems.splice(0, 1);
		
		if (processItems.length > 0) {
			processItems[0].init(true);
		}
		
		if (currentView === "system") {
			getPlanetSats(selectedPlanetId);
		}
	}
	
	if (processItems.length > 0) {
		processItems[0].synchToServer();
	}
}

function queryEvents() {
	$.post(PATH_REQUEST_INFO,
	{
		intelType:	"queryEvents",
		qualifier:	"finished"
	},
	function(data) {
		data = filterPHPData(data);
		if (data.length === 0) {
			return;
		}
		
		var result = $.parseJSON(data);
		
		for (var i = 0; i < result.length; i++) {
			var curEvent = $.parseJSON(result[i]);
			var id = "x" + curEvent["pos_x"] + "-" + "y" + curEvent["pos_y"];
			var divObj = $("div#" + id);
			
			var buildingId = curEvent['name'].split(':')[1];
			divObj.removeClass("constructionsite").addClass(buildingId);
		}
	});
}

function slowUpdate() {
	updateProcessItems();
	getResources();
}
function standardUpdate() {
	updateProcesses("unit");
	updateProcesses("translate");
	queryEvents();
}
function fastUpdate() { }

function getResources() {
	$.post(PATH_REQUEST_INFO,
	{
		intelType: "getAllResources"
	},
	function(data) {
		data = filterPHPData(data);
		var result = $.parseJSON(data);
		// TODO: implement prediction
		
		Object.keys(result).forEach(
			function (key) {
				var selector = "span#label-" + key;
				$(selector).text(parseInt(result[key]));
		});
		
		resources = result;
	});
}

function hideClickMenu() {
	hideBuildButtons();
	
	$(".clickmenu#disappear").css("display", "none");
}
function hideBuildButtons() {
	$(".clickmenu#disappear > span.buildbutton").each(function() {
		$(this).css("display", "none");
	});
}

function updateResourceLabels() {
	for (var key in resources) {
		$("ul#resbar span#label-" + key).text(Math.round(resources[key]));
	}
}

function resetAccountData() {
	$.post(PATH_INPUT_HANDLER,
	{
		action: "resetAccount"
	},
	function(data) {
		filterPHPData(data);
	});
}

function getCurrentPlanetId(callback) {
   var callArguments = Array.prototype.slice.call(arguments, 1);
   
   $.post(PATH_REQUEST_INFO,
   {
      intelType: "getCurrentPlanetId",
      success: callCallback(callback, callArguments)
   },
   function(data) {
      data = filterPHPData(data);
      selectedPlanetId = data;
   });
}

function callCallback(callback) {
   if (callback === undefined) {
      return;
   }
   
   var param2 = undefined;
   if (arguments.length > 1) {
      param2 = arguments[1];
   }
   
   callback.apply(this, param2);
}

function getAndCreateActiveProcesses(category) {
   $.post(PATH_REQUEST_INFO,
	{
		intelType:			"getActiveProcesses",
		processCategory:	category
	},
	function(data) {
		data = filterPHPData(data);
		var result = $.parseJSON(data);
		
		for (var i = 0; i < result.length; i++) {
			var object = $.parseJSON(result[i]);
			createProcess(object, true);
		}
	});
}

function updateProcesses(category) {
	$.post(PATH_REQUEST_INFO,
	{
		intelType:			"getActiveProcesses",
		processCategory:	category
	},
	function(data) {
		data = filterPHPData(data);
		if (processError()) {
			return;
		}
	});
}