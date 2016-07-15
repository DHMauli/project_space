"use strict";

var fieldTypeList;

$(function() {
	$.post(PATH_REQUEST_INFO,
	{
		intelType: "getFieldTypeList"
	},
	function(data) {
		data = filterPHPData(data);
		fieldTypeList = $.parseJSON(data);
	});
});

$(function() {
	$("div.tile").click(function() {
		var buildingName;
		
		var classes = $(this).attr('class').split(' ');
		
		var doubleBreak = false;
		for (var i = 0; i < fieldTypeList.length; i++) {
			for (var c = 0; c < classes.length; c++) {
				if (fieldTypeList[i] == classes[c]) {
					buildingName = classes[c];
					doubleBreak = true;
					break;
				}
			}
			if (doubleBreak)
				break;
		}
	});
});