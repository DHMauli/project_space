"use strict";

function ProcessQItem(duration, DOMName, processName, planetId) {
// private:
	var m_This 		= this;
	var m_duration = duration;
	var m_curTime	= duration;
	var m_DOMName 	= DOMName;
	var m_name		= processName;
	var m_interval	= undefined;
	
	this.DomDestroyed	= false;
	
// public:
	this.init			= function(initialSynch) {
		if (initialSynch) {
			m_This.synchToServer();
		}
		
		m_This.m_interval = window.setInterval(m_This.updateLabel, 1000);
	}
	
	this.getTime		= function() {
		return m_curTime;
	}
	
	this.getName		= function() {
		return m_name;
	}
	
	this.setPosition 	= function(pos_x, pos_y) {
		
	}
	
	this.createDOM 		= function() {
		var divContainer = $("<div></div>");
		divContainer.attr('id', m_DOMName);
		
		var buildingName = m_DOMName.split("-")[1];
		
		var processIcon = $("<img></img>");
		processIcon.attr('src', "../img/processicons/" + buildingName + ".jpg");
		processIcon.addClass('process');
		processIcon.attr('id', m_DOMName + "-img");
		
		var label = $("<span></span>");
		label.attr('id', m_DOMName + "-label");
		label.text("Time: pending");
		
		divContainer.append(processIcon);
		divContainer.append(label);
		$("#sidemenu-left").append(divContainer);
	}
	
	this.updateLabel 	= function() {
		m_curTime--;
		$("#" + m_DOMName + "-label").text("Time: " + m_curTime);
		
		if (m_curTime <= 0) {
			m_This.synchToServer();
		}
	}
	
// private:
	this.synchToServer	= function() {
      console.log("JS classes synchToServer process " + m_name);
		$.post("../php/game/requestinfo.php",
		{
			intelType: 		"checkProcessState",
			processId:		m_name
		},
		function(data) {
			data = filterPHPData(data);
			console.log ("JS: checkProcessState " + data);
			if (data === "finished") {
            console.log ("JS Process finished");
				$("#" + m_DOMName).remove();
				window.clearInterval(m_This.m_interval);
				m_This.DomDestroyed = true;
				$("#" + m_DOMName + "-label").text("Time: -");
            m_curTime = 0;
			} else if (data === "ongoing") {
				$.post("../php/game/requestinfo.php",
				{
					intelType: 		"getProcessTime",
					processId:		m_name
				},
				function(data) {
					data = filterPHPData(data);
					$("#" + m_DOMName + "-label").text("Time: " + data);
					m_curTime = data;
				});
			} else if (data === "pending") {
				$("#" + m_DOMName + "-label").text("Time: pending");
			}
		});
	}
}