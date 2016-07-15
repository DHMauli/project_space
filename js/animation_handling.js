"use strict";

var strActivePanel = undefined;

$(function() {
	$("ul#navheader input[type='button']#register").click(function() {
		btnNavOnClick('panelregister', 'panelmoveinfar');
	});
	$("ul#navheader input[type='button']#login").click(function() {
		btnNavOnClick('panellogin', 'panelmovein');
	});
	$("div#panellogin input[type='button'].btnClose").click(function() {
		btnCancelOnClicK('panellogin', 'panelmoveout');
	});
	$("div#panelregister input[type='button'].btnClose").click(function() {
		btnCancelOnClicK('panelregister', 'panelmoveoutfar');
	});
});

function btnNavOnClick(strPanel, animation) {
	if (strActivePanel !== undefined && strActivePanel !== strPanel)
	{
		if (strActivePanel === 'panellogin') {
			btnCancelOnClicK(strActivePanel, 'panelmoveout');
      } else {
			btnCancelOnClicK(strActivePanel, 'panelmoveoutfar');
      }
	}

	strActivePanel = strPanel;
	$("#"+strPanel).css({
		animationName: 				animation,
		animationDuration: 			"2s",
		animationFillMode: 			"forwards",

		oAnimationName:            animation,
		oAnimationDuration:        "2s",
		oAnimationFillMode:        "forwards",

		webkitAnimationName: 		animation,
		webkitAnimationDuration: 	"2s",
		webkitAnimationFillMode: 	"forwards",

		mozAnimationName: 			animation,
		mozAnimationDuration: 		"2s",
		mozAnimationFillMode: 		"forwards"
	});
}

function btnCancelOnClicK(strPanel, animation) {
	$("#"+strPanel).css({
		animationName: 				animation,
		animationDuration: 			"2s",

		oAnimationName:            animation,
		oAnimationDuration:        "2s",

		webkitAnimationName: 		animation,
		webkitAnimationDuration: 	"2s",

		mozAnimationName: 			animation,
		mozAnimationDuration: 		"2s"
	});
}