/**
 * Javascript file
 *
 * No description
 *
 * @package EDTB\Backend
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

 /*
 * ED ToolBox, a companion web app for the video game Elite Dangerous
 * (C) 1984 - 2016 Frontier Developments Plc.
 * ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @var zindexmax */
var zindexmax = 100000;

/** @var debug_mode */
var debug_mode = false;

/**
 * Send error messages to console if debug_mode = true
 *
 * @param string msg
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function log(msg)
{
	if (debug_mode !== false)
	{
		console.log(msg);
	}
}

/**
 * Slider for long system names
 */
function slide()
{
	var sliderOptions =
	{
		currentMargin: 0,
		marginSpeed: -10
	};

	var s = $("#ltitle");

	if (s.width() >= 288)
	{
		value = s.width() - 284;
		s.css("right", value + "px");
	}
}

/**
 * Slider for long system names
 */
function slideout()
{
	var sliderOptions =
	{
		currentMargin: 0,
		marginSpeed: -10
	};
	var s = $("#ltitle");

	if (s.width() >= 288)
	{
		s.css("right", "0px");
	}
}

/**
 * Retrieve URL variables
 * http://papermashup.com/read-url-get-variables-withjavascript/
 *
 * @author Ashley <ashley@papermashup.com>
 */
function getUrlVars()
{
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value)
	{
		vars[key] = value;
	});
	return vars;
}

/**
 * Update map_points.json
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function update_map()
{
	$.ajax(
	{
		url: "/get/getMapPoints.json.php",
		cache: false,
		dataType: "html",
		success: function()
		{
			log("Requested /get/getMapPoints.json.php succesfully");
		},
		error: function()
		{
			log("Error occured when requesting /get/getMapPoints.json.php");
		}
	});
}

var requestno = 0;
/**
 * Update current system and station data
 *
 * @param bool override
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_data(override)
{
	override = override || false;

	if (override === true)
	{
	   requestno = 0;
	}

	var time = 4000;
	if (requestno === 0)
	{
	   time = 200;
	}

	var system_id = getUrlVars().system_id;
	var system_name = getUrlVars().system_name;

	var slog_sort = getUrlVars().slog_sort;
	var glog_sort = getUrlVars().glog_sort;
	var page_sys = $("#system_title").html();

	/**
	 * fetch info for left panel, system.php and maps
	 */
	$.ajax(
	{
		url: "/get/getData.php?action=onlysystem",
		cache: false,
		success: function(onlysystem)
		{
			if (onlysystem != page_sys || override === true)
			{
				requestno = 0;
				$.ajax(
				{
					url: "/get/getData.php?request=" + requestno + "&system_id=" + system_id + "&system_name=" + system_name + "&slog_sort=" + slog_sort + "&glog_sort=" + glog_sort,
					cache: false,
					dataType: "json",
					success: function(result)
					{
						var returnedvalue = result;

						$("#nowplaying").html(result.now_playing);

						if (onlysystem != page_sys)
						{
							log("Refreshing data (system changed)");
						}
						else if (override === true)
						{
							log("Refreshing data (override)");
						}

						$("#t1").html(result.system_title);
						$("#systeminfo").html(result.system_info);
						$("#scrollable").html(result.log_data);
						$("#stations").html(result.station_data);

						if (result.notifications != "false")
						{
							$("#notifications").html(result.notifications);
							if (result.notifications_data != "false")
							{
								$("#notice_new").html(result.notifications_data);
							}
						}

						if (result.update_in_progress != "false")
						{
							$("#notifications").html(result.update_notification);
							if (result.update_notification_data != "false")
							{
								$("#notice").html(result.update_notification_data);
							}
						}

						// clear reference distances if we're in a new system
						if (result.new_sys != "false")
						{
							$("#ref_1_dist").val("");
							$("#ref_2_dist").val("");
							$("#ref_3_dist").val("");
							$("#ref_4_dist").val("");
						}

						// if we're on the system info page
						if ($("#system_page").length)
						{
							$("#si_name").html(result.si_name);
							$("#si_stations").html(result.si_stations);
							$("#si_detailed").html(result.si_detailed);

							//log(result.si_name);
							//log(result.si_stations);
							//log(result.si_detailed);
						}

						if ($("#container").length)
						{
							log("Updating Neighborhood Map");
							var chart = $("#container").highcharts();

							if (chart)
							{
								$("#container").highcharts().destroy();
							}
							var mode = getUrlVars().mode;
							var maxdistance = getUrlVars().maxdistance;
							var script = document.createElement("script");
							script.type = "text/javascript";
							script.src = "/get/getMapPoints.js.php?mode=" + mode + "&maxdistance=" + maxdistance;

							$("head").append(script);
						}

						if ($("#poi_bm").length)
						{
							log("Updating Poi & BM");
							update_poi_bm();
						}

						if (result.update_map != "false")
						{
							log("Calling update_map()");
							update_map();
						}

						if (override === true)
						{
							update_api(time, "false", "false", "true");
						}
						else
						{
							update_api(time, "false", "true");
						}
						requestno = 1;
						//log("Success: requesting /get/getData.php ok");
					},
					error: function()
					{
						log("Error: requesting /get/getData.php failed");
					}
				});
			}
			log("getData called but no need to refresh");
		}
	});
}

/**
 * Update data from FD API
 *
 * @param int wait
 * @param bool newsys
 * @param bool override
 * @param bool force_update
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function update_api(wait, newsys, override, force_update)
{
	wait = wait | 0;
	newsys = newsys | "false";
	override = override | "false";
	force_update = force_update | "false";
	setTimeout(function()
	{
		$.ajax(
		{
			url: "/get/getData_status.php?newsys=" + newsys + "&override=" + override + "&force_update=" + force_update,
			cache: false,
			dataType: "json",
			success: function(result)
			{
				if (result.cmdr_status != "false" && result.cmdr_ranks_update == "true")
				{
					log("CMDR status changed, refreshing");
					$("#cmdr_status").html(result.cmdr_status);
				}
				else
				{
					log("CMDR status not changed");
				}

				if (result.cmdr_balance_status != "false" && result.cmdr_balance_update == "true")
				{
					log("CMDR balance changed, refreshing");
					$("#balance_st").html(result.cmdr_balance_status);
				}
				else
				{
					log("CMDR balance not changed");
				}

				if (result.ship_status != "false" && result.ship_status_update == "true")
				{
					log("Ship status changed, refreshing");
					$("#ship_status").html(result.ship_status);
				}
				else
				{
					log("Ship status not changed");
				}
				//log("Success: requesting /get/getData_status.php ok");
			},
			error: function()
			{
				log("Error: requesting /get/getData_status.php failed");
			}
		});
	}, wait);
}

/**
 * Update points of interest and bookmarks
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function update_poi_bm()
{
	$.ajax(
	{
		url: "/get/getData_poi_bm.php",
		cache: false,
		dataType: "html",
		success: function(result)
		{
			log("Refreshing poi & bm data");
			$("#poi_bm").html(result);
		},
		error: function()
		{
			log("Error: requesting /get/getData_poi_bm.php failed");
		}
	});
}

$(function()
{
	get_data();
});

/**
 * Get the current system when called
 *
 * @param string formid
 * @param string coordformid
 * @param bool onlyid
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_cs(formid, coordformid, onlyid)
{
	coordformid = coordformid || false;
	onlyid = onlyid || false;
	$.ajax(
	{
		url: "/get/getData.php?action=onlysystem",
		cache: false,
		success: function(result)
		{
			$("#" + formid).val(result);
		}
	});
	if (coordformid !== false)
	{
		$.ajax(
		{
			url: "/get/getData.php?action=onlycoordinates",
			cache: false,
			success: function(results)
			{
				var returnedvalues = results;
				$("#" + coordformid).val(returnedvalues);

				// split coordinates for distance calculations
				var res = returnedvalues.split(",");
				var x = res[0];
				var y = res[1];
				var z = res[2];
				$("#coordsx_2").val(x);
				$("#coordsy_2").val(y);
				$("#coordsz_2").val(z);

			}
		});
	}
	if (onlyid !== false)
	{
		$.ajax(
		{
			url: "/get/getData.php?action=onlyid",
			cache: false,
			success: function(results)
			{
				$("#" + onlyid).val(results);
			}
		});
	}
}

/**
 * Uupdate data for system editing
 *
 * @param string editurl
 * @param int deleteid
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function update_values(editurl, deleteid)
{
	//log(editurl);
	deleteid = deleteid || false;
	$.ajax(
	{
		url: editurl,
		cache: false,
		dataType: "json",
		success: function(result)
		{
			jQuery.each(result, function(id, value)
			{
				if ($("#" + id).attr("type") == "checkbox")
				{
					if (value === "1")
					{
						$("#" + id).prop("checked", true);
					}

					if (id == "pinned")
					{
						if (value === "1")
						{
							$("#pin_click").html("&nbsp;Pinned to top");
							$("#weight").show();
						}
						else
						{
							$("#pin_click").html("&nbsp;Pin to top");
							$("#weight").hide();
						}
					}
				}
				else if ($("#" + id).attr("type") == "select")
				{
					document.getElementById(id).getElementsByTagName("option")[value].selected = "selected";
				}
				else
				{
					$("#" + id).val(value);
				}
			});
		}
	});

	if ($("#delete").length)
	{
		$("#delete").html("");
		if (deleteid !== false)
		{
			$("#delete").html('<a href="javascript:void(0)" onclick="confirmation(' + deleteid + ', \'log\')" title="Delete item"><div class="delete_button" style="right:-271px;"><img src="/style/img/delete.png" class="icon" alt="Delete" style="margin-right:0" /></div></a>');
		}
	}

	if ($("#delete_poi").length)
	{
		$("#delete_poi").html("");
		if (deleteid !== false)
		{
			$("#delete_poi").html('<a href="javascript:void(0)" data-replace="true" data-target=".entries" onclick="confirmation(' + deleteid + ', \'poi\')" title="Delete item"><div class="delete_button"><img src="/style/img/delete.png" class="icon" alt="Delete" style="margin-right:0" /></div></a>');
		}
	}

	if ($("#delete_bm").length)
	{
		$("#delete_bm").html("");
		if (deleteid !== false)
		{
			$("#delete_bm").html('<a href="javascript:void(0)" data-replace="true" data-target=".entries" onclick="confirmation(' + deleteid + ', \'bm\')" title="Delete item"><div class="delete_button"><img src="/style/img/delete.png" class="icon" alt="Delete" style="margin-right:0" /></div></a>');
		}
	}
}

/**
 * Update data (poi, log, what have you)
 *
 * @param string formid
 * @param string file
 * @param bool update_map
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function update_data(formid, file, update_map)
{
	update_map = update_map || false;
	var allTags = document.getElementById(formid).elements;
	var data_to_send = { };

	for (tg = 0; tg < allTags.length; tg+= 1)
	{
		if (allTags[tg].name)
		{
			if (allTags[tg].type == "checkbox")
			{
				if (allTags[tg].checked)
				{
					data_to_send[allTags[tg].name] = (allTags[tg].value);
				}
				else
				{
					data_to_send[allTags[tg].name] = "";
				}
			}
			else
			{
				data_to_send[allTags[tg].name] = (allTags[tg].value);
			}
		}
	}
	//log(data_to_send);
	var st = JSON.stringify(data_to_send);
	$.ajax(
	{
		type: "POST",
		url: file,
		data: { input: st}
	})
	.done(function(msg)
	{
		if (msg)
		{
			alert(msg);
		}
		else
		{
			$("#seslogsuccess").html('<img src="/style/img/check.png" class="icon" style="margin-right:5px" alt="Done">');
			setTimeout(function()
			{
				$("#seslogsuccess").html($("#old_val").html());
			}, 3000);
		}
	});

	if (update_map === true)
	{
		$.ajax(
		{
			url: "/get/getMapPoints.json.php",
			cache: false,
			dataType: "html",
			success: function()
			{
				log("success");
			}
		});
	}

	setTimeout(function()
	{
		get_data(true);
	}, 1200);

	if ($("#poi_bm").length)
	{
		log("Updating Poi & BM");
		update_poi_bm();
	}
}

/**
 * Add zero to time if < 10
 *
 * @param int i
 * @return int i
 */
function addZero(i)
{
	if (i < 10)
	{
		i = "0" + i;
	}
	return i;
}

/**
 * Update the clock
 */
function startTime()
{
	var today = new Date();
	var h = addZero(today.getHours());
	var m = today.getMinutes();
	var s = today.getSeconds();
	var monthNames = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
	var d = today.getDate();
	var year = today.getFullYear() + 1286;
	var mo = monthNames[today.getMonth()];
	m = addZero(m);
	s = addZero(s);

	if ($("#hrs").length)
	{
		$("#hrs").html(h + ":" + m + ":" + s);
	}
	if ($("#hrsns").length)
	{
		$("#hrsns").html(h + ":" + m);
	}

	if ($("#date").length)
	{
		$("#date").html(d + " " + mo + " " + year);
	}
	var t = setTimeout(function(){startTime();}, 500);
}

/**
 * Confirmation popup when deleting stuff
 *
 * @param int delid
 * @param string what
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function confirmation(delid, what)
{
	if (confirm("Sure you want to delete a thing?") === true)
	{
		var script = "";
		if (what == "log")
		{
			script = "/add/log.php?do&deleteid=" + delid;
		}
		else if (what == "poi")
		{
			script = "/add/Poi.php?do&deleteid=" + delid;
		}
		else if (what == "bm")
		{
			script = "/add/bookmark.php?do&deleteid=" + delid;
		}
		else if (what == "screenshot")
		{
			script = "/action/deleteScreenshot.php?img=" + delid;
		}

		if (script !== "")
		{
			$.ajax(
			{
				url: script,
				cache: false,
				success: function(result)
				{
					if (what == "screenshot")
					{
						window.location = result;
					}
					update_map();
					//log(delid + ' a thing was deleted");
				}
			});
		}
	}
	if (what != "screenshot")
	{
		get_data(true);
		tofront("null", true);
	}
}

/**
 * Toggle log adding
 *
 * @param string logsystem
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function toggle_log(logsystem)
{
	$("#log_form")[0].reset();
	$("#pin_click").html("&nbsp;Pin to top");
	$("#weight").hide();
	$("#edit_id").val("");

	if (logsystem === "")
	{
		get_cs("system_1", "false", "system_id");
		$(".addstations").toggle();
	}
	else
	{
		$("#system_1").val(logsystem);
	}

	tofront("addlog");
}

/**
 * Toggle log editing
 *
 * @param int log_id
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function toggle_log_edit(log_id)
{
	tofront("addlog");
	update_values("/get/getLogEditData.php?logid=" + log_id, log_id);
}

var last_system = "";
/**
 * Get info from clicking on a map point
 *
 * @param string system
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_mi(system)
{
	if (last_system == system)
	{
		$("#report").hide();
		last_system = "";
	}
	else
	{
		$("#report").show();
		$.ajax(
		{
			url: "/get/getMapData.php?system=" + system,
			cache: false,
			success: function(result)
			{
				$("#report").html(result);
			}
		});
	}
	last_system = system;
}

/**
 * Autocomplete system/station name
 *
 * @param string str
 * @param string divid
 * @param string link
 * @param string station
 * @param string idlink
 * @param int sysid
 * @param string dp
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function showResult(str, divid, link, station, idlink, sysid, dp)
{
	link = link || "no";
	idlink = idlink || "no";
	station = station || "no";
	sysid = sysid || "no";
	dp = dp || "no";

	if (str.length >= 1)
	{
		$("#suggestions_" + divid).show();
	}
	else
	{
		$("#suggestions_" + divid).hide();
	}

	if (window.XMLHttpRequest)
	{
		xmlhttp = new XMLHttpRequest();
	}

	xmlhttp.onreadystatechange = function()
	{
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
		{
			$("#suggestions_" + divid).html(xmlhttp.responseText);
		}
	};

	allegiance = getUrlVars().allegiance;
	system_allegiance = getUrlVars().system_allegiance;
	power = getUrlVars().power;

	var addtolink = "";
	var addtolink2 = "";
	var addtolink3 = "";

	if (system_allegiance != "undefined")
	{
		addtolink = "&system_allegiance=" + system_allegiance;
	}

	if (allegiance != "undefined")
	{
		addtolink2 = "&allegiance=" + allegiance;
	}

	if (power != "undefined")
	{
		addtolink3 = "&power=" + power;
	}

	if (station == "yes")
	{
		xmlhttp.open("GET", "/get/getStationNames.php?q=" + str + "&divid=" + divid + "&link=" + link + "&idlink=" + idlink + "&sysid=" + sysid + "&dp=" + dp + addtolink + addtolink2 + addtolink3, true);
	}
	else
	{
		xmlhttp.open("GET", "/get/getSystemNames.php?q=" + str + "&divid=" + divid + "&link=" + link + "&idlink=" + idlink + "&sysid=" + sysid + "&dp=" + dp + addtolink + addtolink2 + addtolink3, true);
	}
	xmlhttp.send();
}

/**
 * Change the value to the selected one
 *
 * @param int result
 * @param string coordinates
 * @param string divid
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function setResult(result, coordinates, divid)
{
	$("#system_" + divid).val(result);
	var res = coordinates.split(",");
	var x = res[0];
	var y = res[1];
	var z = res[2];

	$("#coordsx_" + divid).val(x);
	$("#coordsy_" + divid).val(y);
	$("#coordsz_" + divid).val(z);
	$("#suggestions_" + divid).hide();
}

/**
 * Change the value to the selected one for Bookmarks
 *
 * @param string name
 * @param int sysid
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function setbm(name, sysid)
{
	$("#bm_system_name").val(name);
	$("#bm_system_id").val(sysid);
	$("#bm_edit_id").val("");
	$("#bm_text").val("");
	$("#bm_catid").val("0");
	$("#suggestions_3").hide();
}

/**
 * Change the value to the selected one for Stations
 *
 * @param string name
 * @param int stationid
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function setl(name, stationid)
{
	$("#statname").val(name);
	$("#suggestions_41").hide();
}

/**
 * Change the value to the selected one for Data Point
 *
 * @param
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function setdp(name, coordinates, systemid)
{
	$("#system_name").val(name);
	$("#system_id").val(systemid);
	var res = coordinates.split(",");
	var x = res[0];
	var y = res[1];
	var z = res[2];
	$("#x").val(x);
	$("#y").val(y);
	$("#z").val(z);
	$("#suggestions_37").hide();
}

/*
function toinput(system, coordinates, price, tonnage, to, id)
{
	if (to == "from_system")
	{
		$("#from_system").val(system);
		$("#from_coords").val(coordinates);
		$("#price1").val(price);
		$("#tonnage").val(tonnage);
		$("#from_id").val(id);
	}
	else if (to == "to_system")
	{
		$("#to_system").val(system);
		$("#to_coords").val(coordinates);
		$("#price2").val(price);
		$("#tonnage").val(tonnage);
		$("#to_id").val(id);
	}
}
function hailait(name, to)
{
	var x = document.getElementsByName(name);
	var i;

	if (to == "buy")
	{
		var buys = document.getElementsByClassName("dark_highlight");
		var is;
		for (is = 0; is < buys.length; is+= 1)
		{
			buys[is].className="dark";
		}
		for (is = 0; is < buys.length; is+= 1)
		{
			buys[is].className="dark";
		}
		for (is = 0; is < buys.length; is+= 1)
		{
			buys[is].className="dark";
		}
		for (is = 0; is < buys.length; is+= 1)
		{
			buys[is].className="dark";
		}
		//
		for (i = 0; i < x.length; i+= 1)
		{
			x[i].className="dark_highlight";
		}
	}
	else if (to == "sell")
	{
		var sells = document.getElementsByClassName("dark_highlight2");
		var isa;
		for (isa = 0; isa < sells.length; isa+= 1)
		{
			sells[isa].className="dark";
		}
		for (isa = 0; isa < sells.length; isa+= 1)
		{
			sells[isa].className="dark";
		}
		for (isa = 0; isa < sells.length; isa+= 1)
		{
			sells[isa].className="dark";
		}
		for (isa = 0; isa < sells.length; isa+= 1)
		{
			sells[isa].className="dark";
		}
		//
		for (i = 0; i < x.length; i+= 1)
		{
			x[i].className="dark_highlight2";
		}
	}
}

function empty()
{
	document.getElementById("from_system").value='';
	document.getElementById("to_system").value='';
	document.getElementById("to_coords").value='';
	document.getElementById("from_coords").value='';
	document.getElementById("distance_mp").value='';
	document.getElementById("return").value='';

	var cases = document.getElementsByClassName("dark_highlight");
	var num = 0;
	for (num = 0; num < cases.length; num+= 1)
	{
		cases[num].className="dark";
	}
	for (num = 0; num < cases.length; num+= 1)
	{
		cases[num].className="dark";
	}
	for (num = 0; num < cases.length; num+= 1)
	{
		cases[num].className="dark";
	}
	for (num = 0; num < cases.length; num+= 1)
	{
		cases[num].className="dark";
	}

	var cases2 = document.getElementsByClassName("dark_highlight2");
	var num2 = 0;
	for (num2 = 0; num2 < cases2.length; num2+= 1)
	{
		cases2[num2].className="dark";
	}
	for (num2 = 0; num2 < cases2.length; num2+= 1)
	{
		cases2[num2].className="dark";
	}
	for (num2 = 0; num2 < cases2.length; num2+= 1)
	{
		cases2[num2].className="dark";
	}
	for (num2 = 0; num2 < cases2.length; num2+= 1)
	{
		cases2[num2].className="dark";
	}
}
*/
//
/**
 * Function to calculate distances and profits
 *
 * @param float coord_fromx
 * @param float coord_fromy
 * @param float coord_fromz
 * @param float coord_tox
 * @param float coord_toy
 * @param float coord_toz
 * @param string from
 * @param string to
 * @param float price1
 * @param float price2
 * @param int tonnage
 * @param int to_id
 * @param int from_id
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function calcDist(coord_fromx, coord_fromy, coord_fromz, coord_tox, coord_toy, coord_toz, from, to, price1, price2, tonnage, to_id, from_id)
{
	price1 = price1 || "";
	price2 = price2 || "";
	tonnage = tonnage || "160";
	to_id = to_id || "";
	from_id = from_id || "";

	var x1 = coord_fromx;
	var y1 = coord_fromy;
	var z1 = coord_fromz;

	var x2 = coord_tox;
	var y2 = coord_toy;
	var z2 = coord_toz;

	/*if (document.getElementById("distance_mp") && document.getElementById("to_system").value != "" && document.getElementById("from_system").value != "")
	{
		profit = 0;
		profit = price2 - price1;
		overall = profit * tonnage;

		document.getElementById("distance_mp").value = '' + Math.round(Math.sqrt(Math.pow((x1-(x2)),2) + Math.pow((y1-(y2)),2) + Math.pow((z1-(z2)),2))) + ' ly and ' + profit + ' CR/t, ' + overall + ' CR for ' + tonnage + ' t';

		$.ajax(
		{
			url: "/get/getReturnTrip.php?from=" + from_id + "&to=" + to_id + "&tonnage=" + tonnage,
			cache: false,
			success: function(result)
			{
				if (result != "false") {
					$("#return").val(result);
				}
			}
		});
	}*/

	if (x1 && x2 && y1 && y2 && z1 && z2)
	{
		if (to === "")
		{
			$("#dist_display").val("Missing information, try again");
		}
		else
		{
			var distance = numeral(Math.round(Math.sqrt(Math.pow((x1-(x2)), 2) + Math.pow((y1-(y2)), 2) + Math.pow((z1-(z2)),2)))).format("0,0");
			$("#dist_display").val("The distance from " + from + " to " + to + " is " + distance + " ly");
		}
	}
	else
	{
		$("#dist_display").val("Missing information, try again");
	}
}

/**
 * Add station to log form
 *
 * @param string station
 * @param int station_id
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function addstation(station, station_id)
{
	$("#statname").val(station);
}

/**
 * Save session log
 *
 * @param string log
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function savelog(log)
{
	var data = $("#logtext").val();
	$.ajax(
	{
	  type: "POST",
	  url: "/action/sessionLogSave.php",
	  data: { logtext: data }
	})
	.done(function(msg)
	{
		$("#seslogsuccess").html('<img src="/style/img/check.png" class="icon" alt="Done">');

		// display check.png for 3,5 seconds
		setTimeout(function()
		{
			if ($("#seslogsuccess").html('<img src="/style/img/check.png" class="icon" alt="Done">'))
			{
				$("#seslogsuccess").html($("#old_val").html());
			}

		}, 3500);
	});
}

/**
 * Show save icon for session log
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function showsave()
{
	$("#seslogsuccess").html('<a href="javascript:void(0)" onclick="savelog()" title="Save session log"><img src="/style/img/save.png" class="icon" alt="Save"></a>');
}

/**
 * Shove affected div to the front
 *
 * @param int divid
 * @param bool toback
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function tofront(divid, toback)
{
	setindex = zindexmax + 1;
	toback = toback || false;

	var divs = ["addlog", "calculate", "addPoi", "addstation", "distance", "editsystem", "report", "addBm", "search_system"];

	if (toback === false)
	{
		if (document.getElementById(divid).style.display == "block")
		{
			document.getElementById(divid).style.display = "none";
			$(".entries").fadeIn("fast");
		}
		else
		{
			$("#" + divid).fadeIn("fast");
			document.getElementById(divid).style.zindex = setindex;
			document.getElementsByClassName("entries")[0].style.display = "none";
		}
	}
	else
	{
		get_data(true);
		$(".entries").fadeIn("fast");
	}

	var index;
	for (index = 0; index < divs.length; index += 1)
	{
		if (document.getElementById(divs[index]) && divs[index] != divid)
		{
			document.getElementById(divs[index]).style.zindex = 0;
			document.getElementById(divs[index]).style.display = "none";
		}
	}
}

/**
 * Upload image to Imgur
 *
 * @param string file base64 of image
 * @param string fileurl
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function imgurUpload(file, fileurl)
{
	$("#uploaded").html('Uploading image...<br /><img src="/style/img/loading.gif" alt="Uploading..." />');
	$.ajax(
	{
		url: "https://api.imgur.com/3/image",
		headers:
		{
			Authorization: "Client-ID 36fede1dee010c0",
			Accept: "application/json"
		},
		type: "POST",
		data:
		{
			image: file,
			type: "base64"
		},
		success: function(result)
		{
			var url = result.data.link;

			$("#uploaded").html('Image succesfully uploaded!<br /><a target="_BLANK" href="' + url + '">Link to your image on imgur.com<img	class="ext_icon" src="/style/img/external_link.png" alt="ext" /></a>');

			// write to file so we can retrieve url later
			$.ajax(
			{
				url: "/add/imgurURL.php?url=" + url + "&file=" + fileurl,
				cache: false,
				dataType: "html",
				success: function(re)
				{
					log(re);
				}
			});
			//log(result);
		}
	});
}

/**
 * Set links as active
 *
 * @param int id
 * @param int num
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function setActive(id, num)
{
	for (i = 0; i <= num; i += 1)
	{
		if (document.getElementById("link_" + i))
		{
			document.getElementById("link_" + i).className = "link";
		}
	}
	document.getElementById("link_" + id).className = "active";
}

/**
 * Get wikipedia articles
 *
 * @param string search
 * @param int id
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_wikipedia(search, id)
{
	if (document.getElementById("wpsearch_" + id).style.display == "none")
	{
		$("#wpsearch_" + id).fadeIn();
		$("#wpsearch_" + id).html('<strong>Querying Wikipedia</strong><br /><img src="/style/img/loading.gif" alt="Loading..." />');

		$.ajax(
		{
			url: "/get/getWikipediaData.php?search=" + search,
			cache: false,
			dataType: "html",
			success: function(result)
			{
				$("#wpsearch_" + id).html(result);
			}
		});
	}
	else
	{
		document.getElementById("wpsearch_" + id).style.display = "none";
	}
}


/**
 * Update class and rating on NearestSystems.php
 *
 * @param int group_id
 * @param string class_name
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function getCR(group_id, class_name)
{
	$.ajax(
	{
		url: "/get/getRatingAndClass.php?group_id=" + group_id + "&class_name=" + class_name,
		cache: false,
		dataType: "json",
		success: function(result)
		{
			$("#rating").html(result.rating);
			if (class_name === "")
			{
				$("#class").html(result.classv);
			}
		}
	});
}

/**
 * Escape regex
 * http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
 *
 * @param string str
 * @author Sean Bright
 */
function escapeRegExp(str)
{
	return str.replace("/([.*+?^=!:${}()|\[\]\/\\])/g", "\\$1");
}

/**
 * Replace all occurrences of a string
 * http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
 *
 * @param string str
 * @param string find
 * @param string replace
 * @author Sean Bright
 */
function replaceAll(str, find, replace)
{
	return str.replace(new RegExp(escapeRegExp(find), "g"), replace);
}

/**
 * Refresh api data
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function refresh_api()
{
	$.ajax(
	{
		url: "/action/updateAPIdata.php?override",
		cache: false,
		dataType: "json",
		success: function(result)
		{
			log(result);
		}
	});

	$("#api_refresh").html('<img class="icon24" src="/style/img/check_24.png" alt="Refresh done" style="margin-right:10px" />');

	// wait a couple of seconds before updating data
	setTimeout(function()
	{
		get_data(true);
	}, 2500);

	setTimeout(function()
	{
		$("#api_refresh").html('<img class="icon24" src="/style/img/refresh_24.png" alt="Refresh" style="margin-right:10px" />');
	}, 30000);
}

/**
 * Ignore version update
 *
 * @param string ignore_version
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function ignore_version(version)
{
	$.ajax(
	{
		url: "/admin/setData.php?ignore_version=" + version,
		cache: false,
		dataType: "json",
		success: function(result)
		{
			log(result);
		}
	});

	$("#notice_new").fadeToggle("fast");
	$("#notifications").fadeToggle("fast");
}

/**
 * Send/fetch private comments from EDSM
 *
 * @param string system
 * @param string comment
 * @param bool send
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function edsm_comment(comment, send)
{
	if (send !== false)
	{
		$.ajax(
		{
			url: "/get/getData.php?action=onlysystem",
			cache: false,
			success: function(result)
			{
				$.ajax({
					url: "/add/EDSMComment.php?system_name=" + result + "&comment=" + comment,
					cache: false,
					dataType: "json",
					success: function(res)
					{
						log(res);
					}
				});
			}
		});
		$("#edsm_comment").fadeToggle("fast");
		$("#edsm_cmnt_pic").html('<img src="/style/img/check_24.png" alt="Comment sent" class="icon24" />');
	}
	else
	{
		// check for existing comments
		if ($("#edsm_comment").is(":hidden"))
		{
			$.ajax(
			{
				url: "/get/getData.php?action=onlysystem",
				cache: false,
				success: function(result)
				{
					$.ajax({
						url: "/get/getEDSMComment.php?system_name=" + result,
						cache: false,
						dataType: 'text',
						success: function(res)
						{
							if (res !== "")
							{
								//log("/get/getEDSMComment.php?system_name=" + result);
								$("#comment2").val(res);
							}
						}
					});
				}
			});
		}
	}
}

/**
 * Set reference systems
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function set_reference_systems(standard, force)
{
	force = force || false;

	add = "";
	if (force === true)
	{
		add = "?force";
	}

	var urli = "";
	if (standard !== false)
	{
		urli = "/get/getReferenceSystems.php?standard=true" + add;
	}
	else
	{
		urli = "/get/getReferenceSystems.php" + add;
	}

	$.ajax(
	{
		url: urli,
		cache: false,
		success: function(result)
		{
			$("#calculate").html(result);
		}
	});
}

/**
 * Bring info to view
 *
 * @param string div_id
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function to_view(div_id, e)
{
	$("#" + div_id).css(
	{
		left: e.pageX,
		top: e.pageY + 15
	});

	setTimeout(function()
	{
		$("#" + div_id).fadeToggle("fast");
	}, 700);
}

/**
 * Enlarge thumbnails
 *
 * @param string img
 * @param int og_width
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function enlarge(img, og_width)
{
	var width = "";
	if ($(img).width() == og_width)
	{
		if ($(img)[0].naturalWidth > $(img).parent().width())
		{
			width = $(img).parent().width();
		}
		else
		{
			width = $(img)[0].naturalWidth;
		}
	}
	else
	{
		width = og_width;
	}
	$(img).width(width);
	$(img).height("auto");
}

/**
 * Minimize or maximize the left panel
 *
 * @param string style
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function minmax(style)
{
	document.cookie = "style=" + style + "; expires=Thu, 18 Dec 2069 12:00:00 UTC; path=/";
	document.cookie = "style=" + style + "; expires=Thu, 18 Dec 2069 12:00:00 UTC; path=/admin";
	document.cookie = "style=" + style + "; expires=Thu, 18 Dec 2069 12:00:00 UTC; path=/SystemMap";
	location.reload();
	get_data(true);
}

/**
 * Audio record log
 *
 * @author nusofthq
 */
function __log(e, data)
{
	$("#audio_log").html(e);
}

var audio_context;
var recorder;

/**
 * Audio record start media
 *
 * @author nusofthq
 */
function startUserMedia(stream)
{
	var input = audio_context.createMediaStreamSource(stream);
	//__log('Media stream created.' );
	//__log("input sample rate " +input.context.sampleRate);

	// Feedback!
	//input.connect(audio_context.destination);
	//__log('Input connected to audio context destination.');

	recorder = new Recorder(input,
	{
		numChannels: 1
	});
	//__log('Recorder initialised.');
}

/**
 * Audio start recording
 *
 * @author nusofthq
 */
function startRecording(button)
{
	recorder && recorder.record();
	//button.disabled = true;
	//button.nextElementSibling.disabled = false;
	__log('Recording...');
}

/**
 * Audio stop recording
 *
 * @author nusofthq
 */
function stopRecording(button)
{
	recorder && recorder.stop();
	//button.disabled = true;
	//button.previousElementSibling.disabled = false;
	__log('Stopped recording<br />Wait while the audio file is created');

	// create WAV download link using audio data blob
	createDownloadLink();

	recorder.clear();
}

/**
 * Audio create download link
 *
 * @author nusofthq
 */
function createDownloadLink()
{
	recorder && recorder.exportWAV(function(blob)
	{
		/*var url = URL.createObjectURL(blob);
		var li = document.createElement('li');
		var au = document.createElement('audio');
		var hf = document.createElement('a');

		au.controls = true;
		au.src = url;
		hf.href = url;
		hf.download = new Date().toISOString() + '.wav';
		hf.innerHTML = hf.download;
		li.appendChild(au);
		li.appendChild(hf);
		recordingslist.appendChild(li);*/
	});
}

/**
 * Audio start audio
 *
 * @author nusofthq
 */
function start_audio()
{
	try
	{
		// webkit shim
		window.AudioContext = window.AudioContext || window.webkitAudioContext;
		navigator.getUserMedia = ( navigator.getUserMedia ||
		navigator.webkitGetUserMedia ||
		navigator.mozGetUserMedia ||
		navigator.msGetUserMedia);
		window.URL = window.URL || window.webkitURL;

		audio_context = new AudioContext;
		//__log('Audio context set up.');
		//__log('navigator.getUserMedia ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
	}
	catch (e)
	{
		__log('No web audio support in this browser!');
	}

	var go = true;
	navigator.getUserMedia({audio: true}, startUserMedia, function(e)
	{
		__log('<strong>No live audio input</strong><br /><br />If you <em>do have</em> a microphone connected, your browser might not<br />be allowing the microphone to be connected from "insecure" sources.<br /><br />In order to record audio logs from this source,<br />use https and port 3002 to access ED ToolBox.');
		go = false;
	});

	if (go === false)
	{
		$("#record_click").hide();
		$("#stop_click").hide();
	}
}
