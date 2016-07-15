"use strict";

var errorlog = [];

function filterPHPData(message) {
	var rgxResult = null;
	do {
		rgxResult = message.match(/(__A__.*?__Z__)/);
		if (rgxResult != null) {
			console.log ("php-info: " + rgxResult[0].replace(/(__A__|__Z__)?/g, ''));
			message = message.replace(rgxResult[0], '');	
		}
	} while (rgxResult != null);
	
	rgxResult = null;
	do {
		rgxResult = message.match(/(__E__.*?__F__)/);
		if (rgxResult != null) {
			var msg = rgxResult[0].replace(/(__E__|__F__)?/g, '');
			errorlog[errorlog.length] = msg;
			console.log ("PHP-ERROR: " + msg);
			message = message.replace(rgxResult[0], '');
		}
	} while (rgxResult != null);
	
	rgxResult = null;
	do {
		rgxResult = message.match(/(__IE__.*?__IF__)/);
		if (rgxResult != null) {
			var msg = rgxResult[0].replace(/(__IE__|__IF__)?/g, '');
			errorlog[errorlog.length] = msg;
			console.log ("PHP-INTERNAL-ERROR: " + msg);
			message = message.replace(rgxResult[0], '');
		}
	} while (rgxResult != null);
	
	return message;
}
// TODO different panel for internal errors
function getErrorLog() {
	var copy = errorlog; 
	errorlog = [];
	return copy;
}

function processError() {
	var error = getErrorLog();
	if (error.length > 0) {
		var errormessage = "";
		for (var i = 0; i < error.length; i++) {
			errormessage += "(" + (i+1) + "): " + error[i];
			if ((i + 1) < error.length) {
				errormessage += "<br>";
			}
		}
		
		$("div#panelerror #errormessage").html(errormessage);
		panelAnimation("div#panelerror", "panelmovein", 0.5);
		return true;
	}
	
	return false;
}