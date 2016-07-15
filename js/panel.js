"use strict";

var currentUnemployment;

var fractions = new Array();
var iTotal = new Array();
var jobLimits;
// ?? TODO: firstTimeCorrection auf true zurücksetzen, falls anderer Slider ausgewählt wurde ??
var firstTimeCorrection = true;

$(function() {
	$("input[type='button']#populationallocation").click(function() {
		panelAnimation("div#panelcontextmenu", "panelmoveinmax");
		
		$.post(PATH_REQUEST_INFO,
		{
			intelType:		"getPopAlloc"
		},
		function(data) {
			data = filterPHPData(data);
			var result = $.parseJSON(data);
			
			$("div#panelcontextmenu input[type='range']").each(function() {
				var iVal = result[$(this).data("allocoption")];
				
				if (iVal > 0) {
					iTotal[$(this).attr('id')] = iVal;
					var fVal = 100 * iVal / resources['population'];
					
					$(this).val(fVal);
					$("input[type='text']#" + $(this).data("field")).val(fVal);
					$("span#" + $(this).data("labeltotal")).text(iVal);
				}
			});
			currentUnemployment = resources['population'] - getTotalEmployment();
			console.log ("resources population " + resources['population']);
			console.log ("total unemployment " + getTotalEmployment());
			$("span#labelUnemployment").text(currentUnemployment);
		});
		
		$.post(PATH_REQUEST_INFO,
		{
			intelType:		"getJobsAll"
		},
		function(data) {
			data = filterPHPData(data);
			jobLimits = $.parseJSON(data);
		});
	});
	/* 
	 * TODO: wait for internet explorer fixing this issue
	 * https://connect.microsoft.com/IE/Feedback/Details/856998
	 * WORKAROUND: use 'change' instead of 'input' for IE
	 */
	 // TODO: optimize PROPORTIONAL REPRESENTATION of percentage
	 // TODO: unify function
	 
	$("li.buildingtab input[type='range']").on('input', function() {
		var val = $(this).val();
		
		var fTotalEmployed = getEmployedPopulation($(this));
		
		var fMaxVal1 = 100 - fTotalEmployed;
		var fMaxVal2 = 100 * jobLimits[$(this).data("allocoption")] / resources['population'];
		var fLimit = Math.min(fMaxVal1, fMaxVal2);
		
		val = Math.min(val, fLimit);
		
		var field = $("input[type='text']#" + $(this).data("field"));
		field.val(val);
		$(this).val(val);
		
		var fValTotal = resources['population'] * val / 100;
		var iValTotal = roundWithFraction($(this).attr('id'), fValTotal);
		iTotal[$(this).attr('id')] = iValTotal;
		var totalEmployment = getTotalEmployment();
		
		var errorValue = totalEmployment - resources['population'];
		
		$("span#" + $(this).data("labeltotal")).text(iValTotal);
		if (errorValue > 0)
			correctAllocation($(this).attr('id'), errorValue);
		
		$("span#labelUnemployment").text(resources['population'] - getTotalEmployment());
	});
	$("li.buildingtab input[type='text']").on('input', function() {
		var val = $(this).val();
		var isNumber = parseInt(val);
		
		if (!isNumber)
			val = 0;
		if (isNaN(val))
			return;
		
		var sliderName = $(this).data("slider");
		var slider = $("input[type='range']#" + sliderName);
		
		var fTotalEmployed = getEmployedPopulation($("input[type='range']#" + sliderName));
		
		var fMaxVal1 = 100 - fTotalEmployed;
		var fMaxVal2 = 100 * jobLimits[slider.data("allocoption")] / resources['population'];
		var fLimit = Math.min(fMaxVal1, fMaxVal2);
		
		val = Math.min(val, fLimit);
		val = Math.max(val, 0);
		
		$(this).val(val);
		slider.val(val);
		
		var fValTotal = resources['population'] * val / 100;
		var iValTotal = roundWithFraction(sliderName, fValTotal);
		iTotal[sliderName] = iValTotal;
		var totalEmployment = getTotalEmployment();
		
		var errorValue = totalEmployment - resources['population'];
		
		$("span#" + slider.data("labeltotal")).text(iValTotal);
		if (errorValue > 0)
			correctAllocation(sliderName, errorValue);
		
		$("span#labelUnemployment").text(resources['population'] - getTotalEmployment());
	});
	$("li.buildingtab input[type='range']").change(function() {
		firstTimeCorrection = true;
	});
	$("input[type='button']#btnSubmitAllocation").click(function() {
		var arrOptions = {};
		
		for (var key in iTotal) {
			var allocOption = $("div#panelcontextmenu input[type='range']#" + key).data("allocoption");
			arrOptions[allocOption] = iTotal[key];
		}
		
		submitAllocation(arrOptions);
		panelAnimation("div#panelcontextmenu", "panelmoveoutmax");
	});
});
$(function() {
	$("div#panelcontextmenu .btnClose").click(function() {
		panelAnimation("div#panelcontextmenu", "panelmoveoutmax");
	});
});

function getEmployedPopulation(currentSlider) {
	var sum = 0;
	$("div#panelcontextmenu > div.panelcenter input[type='range']").each(function() {
		if (currentSlider[0] !== $(this)[0])
			sum += parseInt($(this).val());
	});
	
	return sum;
}

function submitAllocation(allocation) {
	allocation = JSON.stringify(allocation);
	$.post(PATH_INPUT_HANDLER,
	{
		action:	"allocateJobs",
		data:	allocation,
	},
	function(data) {
		data = filterPHPData(data);
		
		// TODO: update/get Resources
	});
}

function roundWithFraction(allocOption, fValTotal) {
	if (fValTotal <= 0.0) {
		delete fractions[allocOption];
		return 0.0;
	}
	
	var fFraction = fValTotal - Math.floor(fValTotal);
	var returnVal = Math.round(fValTotal);
	
	if (returnVal < fValTotal)
		fractions[allocOption] = fFraction;
	else
		fractions[allocOption] = -(1.0 - fFraction);
	
	return returnVal;
}

function getTotalEmployment() {
	var sum = 0;
	
	for (var key in iTotal)
		sum += parseInt(iTotal[key]);
	
	return sum;
}

function correctAllocation(currentSlider, errorValue) {
	var tuples = [];
	
	for (var key in fractions) tuples.push([key, fractions[key]]);
	
	tuples.sort(function(a, b) {
		a = a[1];
		b = b[1];
		
		return a < b ? -1 : (a > b ? 1 : 0);
	});
	
	var iterator = Math.min(tuples.length, errorValue);
	for (var i = 0; i < iterator; i++) {
		if (tuples[i][0] == currentSlider)
			continue;
		
		decreaseSliderValueBy(tuples[i][0], 1);
	}
}

function decreaseSliderValueBy(sliderName, val) {
	if (!firstTimeCorrection)
		return;
	
	var slider = $("input[type='range']#" + sliderName);
	var field = $("input[type='text']#" + slider.data("field"));
	var labelTotal = $("span#" + slider.data("labeltotal"));
	
	var iCurrentSiderTotal = parseInt(labelTotal.text());
	iCurrentSiderTotal -= val;
	var fNew = 100 * iCurrentSiderTotal / resources['population'];
	var iNew = Math.round(fNew);
	
	slider.val(fNew);
	field.val(fNew);
	
	labelTotal.text(iCurrentSiderTotal);
	iTotal[sliderName] = iCurrentSiderTotal;
	
	firstTimeCorrection = false;
}

$(function() {
	$("div#panelerror .btnClose").click(function() {
		panelAnimation("div#panelerror", "panelmoveout", 0.5);
	});
});

function panelAnimation(panelname, animation, dur) {
	var duration = "3s";
	
	if (dur != undefined) {
		duration = dur + "s";
  }
	
	$(panelname).css({
		animationName: 				animation,
		animationDuration: 		duration,
		animationFillMode: 		"forwards",

		oAnimationName: 			animation,
		oAnimationDuration: 	duration,
		oAnimationFillMode: 	"forwards",

		webkitAnimationName: 		animation,
		webkitAnimationDuration: 	duration,
		webkitAnimationFillMode: 	"forwards",

		mozAnimationName: 			animation,
		mozAnimationDuration: 		duration,
		mozAnimationFillMode: 		"forwards"
	});
}