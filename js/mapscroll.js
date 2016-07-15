"use strict";

var PATH_DRAWMAP = "../php/classes/drawmap.php";

var viewportWidth = 8;
var viewPortHeight = 5;

var currentRowX = 3;
var currentRowY = 4;

var mapWidth = 0;
var mapHeight = 0;

var requesting = false;

$(function() {
	$.post(PATH_REQUEST_INFO,
	{
		intelType: "getMapDimensions"
	},
	function(data) {
		data = filterPHPData(data);
		var result = $.parseJSON(data);
		
		mapWidth = result[0];
		mapHeight = result[1];
	});
	
	$("input[type='button']#btnright").click(function() {
		if (requesting) {
			return;
		}
		if ((currentRowX + viewportWidth) < mapWidth) {
			currentRowX++;
			requesting = true;
			loadNewMap(currentRowX, currentRowY, undefined, setRequestingFalse);
		}
	});
	$("input[type='button']#btnleft").click(function() {
		if (requesting) {
			return;
		}
		if (currentRowX >= 1) {
			currentRowX--;
			requesting = true;
			loadNewMap(currentRowX, currentRowY, undefined, setRequestingFalse);
		}
	});
	$("input[type='button']#btnup").click(function() {
		if (requesting) {
			return;
		}
		if (currentRowY >= 1) {
			currentRowY--;
			requesting = true;
			loadNewMap(currentRowX, currentRowY, undefined, setRequestingFalse);
		}
	});
	$("input[type='button']#btndown").click(function() {
		if (requesting) {
			return;
		}
		if ((currentRowY + viewPortHeight) < mapHeight) {
			currentRowY++;
			requesting = true;
			loadNewMap(currentRowX, currentRowY, undefined, setRequestingFalse);
		}
	});
})

function setRequestingFalse() {
	requesting = false;
}