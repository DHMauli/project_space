"use strict";

/* system scripts */

var uiClickPlanetContext;

function getButtonSendFunction() {
  return (function() {
    var arrUnits = {};
    
    var listUnits = $("div#transform > div.panelcenter input[type='text'].unitselector");
    for (var i = 0; i < listUnits.length; i++) {
      var key = $(listUnits[i]).attr('id').split("-")[1];
      var val = $(listUnits[i]).val();
      if (val !== "") {
        arrUnits[key] = val;
      }
    }
    
    var strUnits = JSON.stringify(arrUnits);
    
    var targetPlanet = $("div#transform > div.panelcenter " +
      "input[type='text']#planetselection-field").val();
    /*
    if ((targetPlanet === undefined) || (targetPlanet.length === 0)) {
      console.log ("Target planet missing");
      return;
    }
      
    if (targetPlanet.charAt(0).toLowerCase() !== 'p') {
      console.log ("Target planet-name not in correct format");
      return;
    }
    */
    $.post(PATH_INPUT_HANDLER,
    {
      action: "sendUnits",
      units: strUnits,
      start: uiClickPlanetContext,
      target: targetPlanet
    },
    function(data) {
      console.log ("data: " + data);
      data = filterPHPData(data);
			if (processError()) {
				return;
			}
    });
  });
}

function getSystemSatsFunction() {
	return (function() {
		hideClickMenu();
		$.post(PATH_REQUEST_INFO,
		{
			intelType:	"getSystemSats",
			// HARDCODED
			sys:		"s001"
		},
		function(data) {
			data = filterPHPData(data);
			if (processError()) {
				return;
			}
      
			var sats = $.parseJSON(data);
			
			currentView = "system";
			
			$("div#wrapper").html('');
			
			sats.sort(function(a, b) {
				var id_a = a['id'];
				var id_b = b['id'];
				
				if (id_a < id_b) return -1;
				if (id_a > id_b) return +1;
				
				return 0;
			});
         
			for (var pair in sats) {
				var tab = $("<div class='planettab' id=" + sats[pair]['id'] + "></div>");
				
				var label = $("<div class='planetcaption'><strong>" + sats[pair]['label'] + "</strong></div>");
				var caption = $("<div class='planetcaption'><em>" + sats[pair]['id'] + "</em></div>");
				
				var display = $("<div class='planetdisplay' id='" + sats[pair]['id'] + "'></div>");
				display.css({
					"background": "transparent url(../img/sysview/" + sats[pair]['image'] + ") 0 0 no-repeat",
					"background-size": "contain"
				});
				
				display.hover(getDisplayPlanetHoverFunction(), function() {
					$("div").remove(".planetdisplay .twobuttonmenu");
				});
				
				var buildingMatrix = $("<div class='clickmenu transparentBackground'></div>");
				buildingMatrix.css("display", "block");
				buildingMatrix.perfectScrollbar();
				// TEST
				//$(".ps-scrollbar-y-rail > .ps-scrollbar-y").css("background-color", "#ff0000");
				//-----
				buildingMatrix.click(function(event) {
					getClickmenuHandlingFunction(this, event);
				});
				
				tab.append(label);
				tab.append(caption);
				tab.append(display);
				tab.append(buildingMatrix);
				
				$("div#wrapper").append(tab);
            
				getPlanetSats(sats[pair]['id']);
			}
			
			$("div#gamescreen").css("display", "none");
			$("div#systemview").css("display", "inline-block");
			$("div#content input[type='button'].directionbutton").css("display", "none");
		});
	});
}

function onClickDelegate() {
	$("div").remove("div#systemview > div#wrapper .planettab");
	$("div#gamescreen").css("display", "inline-block");
	$("div#systemview").css("display", "none");
	$("div#content input[type='button'].directionbutton").css("display", "inline-block");
}

function getPlanetSats(planetId) {
	console.log ("gettingPlanetSats of " + planetId);
	$.post(PATH_REQUEST_INFO,
	{
		intelType: "getPlanetsSats",
		planetId: planetId
	},
	function(data) {
		data = filterPHPData(data);
		if (processError()) {
			return;
		}

		if (data.length === 0) {
			return;
		}

		data = $.parseJSON(data);
		var planetId = data['id'];
		var planetSats = data['sats'];

		var parent = $("div.planettab#" + planetId + " div.clickmenu");
		parent.html('');

		for (var i = 0; i < planetSats.length; i++) {
			var spanButton = $("<span class='buildbutton'></span>");
			spanButton.css({
				//"background-image": "url(../img/processicons/" + planetSats[i] + ".jpg)",
				"background-image": "url(../img/processicons/shipyard.jpg)",
				"display": "inline"
			});

			spanButton.click(buildUnitsMenuCallback("shipyard"));

			parent.append(spanButton);
		}
	});
}

function buildUnitsMenuCallback(buildingId) {
	return function() {
		console.log ("game.js openBuildUnitsMenu " + buildingId);
		$("div#sidemenu-right .clickmenu").css("display", "block");
		
		$.post(PATH_REQUEST_INFO,
		{
			intelType: "getStructureBuildOptions",
			// DEBUG
			type: "shipyard"
		},
		function(data) {
			data = filterPHPData(data);
			
			if (processError()) {
				return;
			}

			if (data.length === 0) {
				return;
			}
			console.log ("gamejs getStructureBuildOptions " + data);
			var result = $.parseJSON(data);
			
			var parent = $("div#sidemenu-right .clickmenu").slice(0, 1);
			hideBuildButtons();
			
			for (var i = 0; i < result.length; i++) {
				var name = result[i]['name'];
				var path = result[i]['path'];
				
				var spanButton = $("<span class='buildbutton'></span>");
				spanButton.css({
					"background-image": "url(../img/units/" + path + ")",
					"display": "inline"
				});
				spanButton.addClass("buildbutton");
				spanButton.click(createBuildUnitCallback(name));
				
				parent.append(spanButton);
			}
		});
	}
}
function createBuildUnitCallback(unitType) {
	return (function() {
		$.post(PATH_INPUT_HANDLER,
		{
			action: "buildUnit",
			unitType: unitType
		},
		function(data) {
			data = filterPHPData(data);

			if (processError()) {
				return;
			}
		});
	});
}

function getDisplayPlanetHoverFunction() {
	return (function() {
		var button1 = $("<input type='button' class='button' value='PV'>");
		button1.click(function() {
			var id = $(this).parent().parent().attr('id');
			getCurrentPlanetId(loadNewMap, 3, 4, id, onClickDelegate);
		});
		var spacer = $("<div class='spacer'></div>");
		var button2 = $("<input type='button' class='button' value='BM'>");
		
		button2.click(function() {
			var id = $(this).parent().parent().attr('id');
			openSysBuildMenu(id);
		});
		
		var button3 = $("<input type='button' class='button' value='SEND'>");
		button3.click(getOpenTransformPanelFunction());
		
		var container = $("<div class='twobuttonmenu'></div>");
		container.append(button1, button2, button3);
		
		$(this).append(container);
	});
}

function getOpenTransformPanelFunction() {
	return (function() {
    
    uiClickPlanetContext = $(this).parent().parent().attr('id');
    
    var transformPanelContainer = undefined;
    var panelMain = undefined;
    
    var panel = $("div#transform");
    
    var panelExists = false;
    
    if (panel[0] !== undefined) {
      transformPanelContainer = panel;
      panelMain = $("div#transform > div.panelcenter");
      panelMain.html("");
      panelExists = true;
    } else {
      transformPanelContainer = $("<div class='container' id='transform'></div>'");
      panelMain = $("<div class='panelcenter'></div>"); 
    }
		
    var divCloseButton = $("<div></div>").css({
      "width": "100%",
      "height": "3vh"
    });
    var closeButton = $("<input type='button' class='btnClose' value=''>");
    divCloseButton.append(closeButton);
    
    closeButton.click(function() {
      panelAnimation("div#transform", "panelmoveout", 1);
    });
    
    panelMain.append(divCloseButton);
    
		panelMain.html(getTransformPanelContent(appendSubmitButton));
		
    if (panelExists === false) {
      transformPanelContainer.append(panelMain);
      transformPanelContainer.css("top", "-45vh");
      
      $("body").append(transformPanelContainer);
      
      var left = ($(document).width() / 2) - (transformPanelContainer.width() / 2);
      
      // TODO: positioning is not correct
      transformPanelContainer.css("left", left + "px");
    }
    
    panelAnimation("div#transform", "panelmovein", 1);
	});
}

function appendSubmitButton() {
  var divSubmitButton = $("<div></div>").css({
    "width": "100%",
    "height": "3vh",
    "text-align": "center",
    "margin-top": "3vh"
  });
  
  var btnSendButton = $("<input type='button' value='Send' id='btnSend'>");
  btnSendButton.click(getButtonSendFunction());
  divSubmitButton.append(btnSendButton);
  
  $("div#transform > div.panelcenter").append(divSubmitButton);
}

function getTransformPanelContent(callback) {
	
	$.post(PATH_REQUEST_INFO,
	{
		intelType: "getPlanetUnits",
		planetId: uiClickPlanetContext
	},
	function(data) {
    
		data = filterPHPData(data);
		if (processError()) {
			return;
		}
		
		if (data.length == 0) {
			return;
		}
		
		var units = $.parseJSON(data);
		
		var unitCount = {};
		
		for (var i = 0; i < units.length; i++) {
			var unitType = units[i].split(":")[0];
			
			if (unitCount[unitType] == undefined) {
				unitCount[unitType] = 1;
			} else {
				unitCount[unitType]++;
			}
		}
    
    var listLeft = $("<ul id='list-left'></ul>").css({
      "list-style-type":"none",
      "margin": "0",
      "padding": "0",
      "width": "47.5%",
      "display": "inline-block",
      "background-color": "#ff0000"
    });
    var listRight = $("<ul id='list-right'></ul>").css({
      "list-style-type":"none",
      "margin": "0",
      "padding": "0",
      "width": "47.5%",
      "display": "inline-block",
      "background-color": "#ff0000"
    });
    
		for (var key in unitCount) {
      var elemList1 = $("<li></li>").css("width", "100%").
        html("<strong>" + key + ":</strong> " + unitCount[key] + "<br>");
        
      listLeft.append(elemList1);
      
      var inputField = $("<input type='text' class='unitselector' id='textfield-" + key + "'>").
        css({
          "width": "100%",
          "height": "100%",
          "padding": "0",
          "margin": "0",
          "border": "0"
        });
        
      var elemList2 = $("<li></li>").css({
        "width": "100%",
        "padding": "0",
        "margin": "0",
        "border": "0",
        "background-color": "#00ff00"
      }).
      append(inputField);
      
      listRight.append(elemList2);
		}
    
    var planetSelectionLabel = $("<br><span>Target</span>").css("display", "block");
    listLeft.append(planetSelectionLabel);
    
    var planetSelectionField = $("<br><input type='text' id='planetselection-field'>").
      css({
          "width": "100%",
          "height": "100%",
          "padding": "0",
          "margin": "0",
          "border": "0"
        });
    listRight.append(planetSelectionField);
    
    var panelParent = $("div#transform > div.panelcenter");
    panelParent.css("width", "20vw");
    
		panelParent.append(listLeft);
		panelParent.append(listRight);
    
    callback();
    
    return;
	});
}

function loadNewMap(offX, offY, id, callback) {
	$.post(PATH_INPUT_HANDLER,
	{
		action:			"openPlanetView",
		offsetX:		offX,
		offsetY:		offY,
		planetid:		id
	},
	function(data) {
		data = filterPHPData(data);
		if (processError()) {
			return;
		}
		
		currentRowX = offX;
		currentRowY = offY;
		
		currentView = "planet";
      
		$("div#gamescreen").html('').html(data);
		addTileEventListener();
		
		$("div#gamescreen div.tile").click(function(event) {
			getClickmenuHandlingFunction(this, event);
		});
		
		requesting = false;
		
		if (callback !== null) {
			callback();
		}
	});
}