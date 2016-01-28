/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2016 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    This program is free software; you can redistribute it and/or
*    modify it under the terms of the GNU General Public License
*    as published by the Free Software Foundation; either version 2
*    of the License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/**
 * Javascript
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

var zindexmax = 100000;

function slide()
{
	var sliderOptions =
	{
	  currentMargin: 0,
	  marginSpeed: -10
	};
	var s = $('#ltitle');

	if (s.width() >= 198)
	{
		value = s.width() - 194;
		s.css("right",value+"px");
	}
}

function slideout()
{
	var sliderOptions =
	{
	  currentMargin: 0,
	  marginSpeed: -10
	};
	var s = $('#ltitle');

	if (s.width() >= 198)
	{
		s.css("right","0px");
	}
}

// http://papermashup.com/read-url-get-variables-withjavascript/
function getUrlVars()
{
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value)
    {
        vars[key] = value;
    });
    return vars;
}

/*
*	update map_points.json
*/

function update_map()
{
	$.ajax(
	{
		url: "/get/getMapPoints.json.php",
		cache: false,
		dataType: 'html',
		success: function()
		{
			//console.log('success')
		}
	});
}

var requestno = 0;
// function to update current system and log every so often -->
function get_data(override)
{
    var override = override || false;

    if (override == true)
	{
        requestno = 0;
		time = 0;
    }
	else
	{
		time = 4800;
	}

	system_id = getUrlVars()["system_id"];
	system_name = getUrlVars()["system_name"];

	slog_sort = getUrlVars()["slog_sort"];
	glog_sort = getUrlVars()["glog_sort"];

    // get system info and log
    $.ajax(
    {
        url: "/get/getData.php?request="+requestno+"&system_id="+system_id+"&system_name="+system_name+"&slog_sort="+slog_sort+"&glog_sort="+glog_sort,
        cache: false,
        dataType: 'json',
        success: function(result)
        {
            var returnedvalue = result;

            $('#nowplaying').html(result['now_playing']);

            if (result['renew'] != "false")
            {
                $('#t1').html(result['system_title']);
                $('#systeminfo').html(result['system_info']);
                $('#scrollable').html(result['log_data']);
                $('#stations').html(result['station_data']);

				/*if (result['cmdr_status'] != "false")
				{
					$('#cmdr_status').html(result['cmdr_status']);
				}

				if (result['ship_status'] != "false")
				{
					$('#ship_status').html(result['ship_status']);
				}*/

				if (result['notifications'] != "false")
				{
					$('#notifications').html(result['notifications']);
					if (result['notifications_data'] != "false")
					{
						$('#notice_new').html(result['notifications_data']);
					}
				}

				// clear reference distances if we're in a new system
				if (result['new_sys'] != "false")
				{
					$('#ref_1_dist').val('');
					$('#ref_2_dist').val('');
					$('#ref_3_dist').val('');
					$('#ref_4_dist').val('');
				}

				// if we're on the system info page
                if (document.getElementById('system_page'))
                {
                    $('#si_name').html(result['si_name']);
					$('#si_stations').html(result['si_stations']);
					$('#si_detailed').html(result['si_detailed']);
                }

                if (document.getElementById('container'))
                {
                    var chart = $('#container').highcharts();

                    if (chart)
                    {
                        $('#container').highcharts().destroy();
                    }
                    var mode = getUrlVars()["mode"];
                    var maxdistance = getUrlVars()["maxdistance"];
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src ="/get/getMapPoints.js.php?mode="+mode+"&maxdistance="+maxdistance;

                    $('head').append(script);
                }
				if (result['update_map'] != "false")
				{
					update_map();
				}
            }
            requestno = 1;
        }
    });

    // get api data
	setTimeout(function()
	{
		$.ajax(
		{
			url: "/get/getData_status.php",
			cache: false,
			dataType: 'json',
			success: function(result)
			{
				if (result['cmdr_status'] != "false")
				{
					$('#cmdr_status').html(result['cmdr_status']);
				}

				if (result['ship_status'] != "false")
				{
					$('#ship_status').html(result['ship_status']);
				}
			}
		});
	}, time);
}

$(function()
{
    get_data();
});
/*
// function for systems.php
function get_system_data()
{
	if (document.getElementById('systemsdata'))
	{
		sort = getUrlVars()["sort"];

		$.ajax(
		{
			url: "/get/getSystemsData.php?sort="+sort,
			cache: false,
			dataType: 'json',
			success: function(result)
			{
				var returnedvalue = result;

				$('#systemsdata').html(result['systemsdata']);
			}
		});
	}
}

$(function()
{
    get_system_data();
});
*/
// function to get the current system when called -->
function get_cs(formid, coordformid, onlyid)
{
    coordformid = coordformid || false;
	onlyid = onlyid || false;
    $.ajax({
        url: "/get/getData.php?action=onlysystem",
        cache: false,
        success: function(result)
        {
            $('#'+formid).val(result);
        }
    });
    if (coordformid !== false)
    {
        $.ajax({
            url: "/get/getData.php?action=onlycoordinates",
            cache: false,
            success: function(results)
            {
                var returnedvalues = results;
                $('#'+coordformid).val(returnedvalues);

				// split coordinates for ditance calculations
				var res = returnedvalues.split(",");
				var x = res[0];
				var y = res[1];
				var z = res[2];
				$('#coordsx_2').val(x);
				$('#coordsy_2').val(y);
				$('#coordsz_2').val(z);

            }
        });
    }
    if (onlyid !== false)
    {
        $.ajax({
            url: "/get/getData.php?action=onlyid",
            cache: false,
            success: function(results)
            {
                $('#'+onlyid).val(results);
            }
        });
    }
}

// function to update data for system editing
function update_values(editurl, deleteid)
{
	console.log(editurl);
    deleteid = deleteid || false;
    $.ajax({
        url: editurl,
        cache: false,
        dataType: 'json',
        success: function(result)
        {
            jQuery.each(result, function(id, value)
            {
                if (document.getElementById(id).type == "checkbox")
                {
                    if (value == 0)
					{
                        //document.getElementById(id).checked="false";
                    }
                    else
					{
                        document.getElementById(id).checked="true";
                    }
                }
                else if (document.getElementById(id).type == "select")
                {
					document.getElementById(id).getElementsByTagName('option')[value].selected = 'selected'
                }
                else
                {
                    $('#'+id).val(value);
                }

            });
        }
    });

    if (document.getElementById('delete'))
    {
        document.getElementById('delete').innerHTML = '';
        if (deleteid !== false)
        {
            document.getElementById('delete').innerHTML = '<a href="javascript:void(0);" onclick="confirmation('+deleteid+',\'log\')" title="Delete item"><div class="delete_button" style="right:-271px;"><img src="/style/img/delete.png" alt="Delete" /></div></a>';
        }
    }

    if (document.getElementById('delete_poi'))
    {
        document.getElementById('delete_poi').innerHTML = '';
        if (deleteid !== false)
        {
            document.getElementById('delete_poi').innerHTML = '<a href="/poi.php" data-replace="true" data-target=".entries" onclick="confirmation('+deleteid+',\'poi\')" title="Delete item"><div class="delete_button"><img src="/style/img/delete.png" alt="Delete" /></div></a>';
        }
    }

    if (document.getElementById('delete_bm'))
    {
        document.getElementById('delete_bm').innerHTML = '';
        if (deleteid !== false)
        {
            //document.getElementById('delete_bm').innerHTML = '<a href="javascript:void(0);" onclick="confirmation('+deleteid+',\'bm\')"><input class="delete_button" type="button" value="Delete Bookmark" style="width:125px;margin-left:10px;"></a>';
			document.getElementById('delete_bm').innerHTML = '<a href="/poi.php" data-replace="true" data-target=".entries" onclick="confirmation('+deleteid+',\'bm\')" title="Delete item"><div class="delete_button"><img src="/style/img/delete.png" alt="Delete" /></div></a>';
        }
    }

    /*if (document.getElementById('delete_station'))
    {
        document.getElementById('delete_station').innerHTML = '';
        if (deleteid !== false)
        {
            document.getElementById('delete_station').innerHTML = '<a href="javascript:void(0);" onclick="confirmation('+deleteid+',\'station\')"><input class="button" type="button" value="Delete station" style="width:125px;margin-left:120px;"></a>';
        }
    }*/
}

// function to update data (poi, log, what have you)
function update_data(formid, file, update_map)
{
	update_map = update_map || false;
    var allTags = document.getElementById(formid).elements;
    var data_to_send = { };

    for (var tg = 0; tg< allTags.length; tg++)
    {
        var tag = allTags[tg];
        if (tag.name)
        {
            if (tag.type == "checkbox")
            {
                if (tag.checked)
                {
                    data_to_send[tag.name] = (tag.value);
                }
                else
                {
                    data_to_send[tag.name] = "";
                }
            }
            else
            {
                data_to_send[tag.name] = (tag.value);
            }
        }
    }
    //console.log(data_to_send);
    var st = JSON.stringify(data_to_send);
    $.ajax({
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
            var system_requests = 0;
            document.getElementById('seslogsuccess').innerHTML = '<img src="/style/img/check.png">';
            setTimeout(function()
            {
                document.getElementById('seslogsuccess').innerHTML = '';
            }, 3000);
        }
    });

	if (update_map == true)
	{
		$.ajax(
		{
			url: "/get/getMapPoints.json.php",
			cache: false,
			dataType: 'html',
			success: function()
			{
				//console.log('success')
			}
		});
	}
}

function addZero(i)
{
    if (i < 10) {

        i = "0" + i;
    }
    return i;
}

// function to update the clock
function startTime()
{
    var today=new Date();
    var h=addZero(today.getHours());
    var m=today.getMinutes();
    var s=today.getSeconds();
    var monthNames = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
    var d=today.getDate();
    var year=today.getFullYear()+1286;
    var mo=monthNames[today.getMonth()];
    m = checkTime(m);
    s = checkTime(s);

    document.getElementById('hrs').innerHTML = h+":"+m+":"+s;
    document.getElementById('date').innerHTML = d+" "+mo+" "+year;
    var t = setTimeout(function(){startTime()},500);
}

function checkTime(i)
{
    if (i<10) {i = "0" + i}  // add zero in front of numbers < 10
    return i;
}
/*  */

// confirmation popup
function confirmation(delid, what)
{
    if (confirm("Sure you want to delete a thing?") == true)
    {
        var script = "";
        if (what == "log")
            var script = "/add/log.php?do&deleteid="+delid;
        else if (what == "poi")
            var script = "/add/poi.php?do&deleteid="+delid;
        else if (what == "bm")
            var script = "/add/bookmark.php?do&deleteid="+delid;
        else if (what == "screenshot")
            var script = "/add/deleteScreenshot.php?img="+delid;
       /* else if (what == "station")
            var location = "/add/station.php?deleteid="+delid;
        else if (what == "system")
            var location = "/add/systemE.php?deleteid="+delid;*/

        if (script != "")
        {
            $.ajax({
                url: script,
                cache: false,
                success: function(result)
                {
					if (what == "screenshot")
					{
						var url = result;
						console.log(url);
						window.location = url;
					}
					update_map();
                    //console.log(delid+' a thing was deleted');
                }
            });
        }
    }
	if (what != "screenshot")
	{
		get_data(true);
		tofront('null', true);
	}
}

function toggle_log(logsystem)
{
	$('#log_form').trigger('reset');
	$('#edit_id').val('');
	if (logsystem == "")
	{
		get_cs('system_1', 'false', 'system_id');
		$('.addstations').toggle();
	}
	else
	{
		this.document.getElementById('system_1').value=logsystem;
	}
	tofront('addlog');
}

function toggle_log_edit(log_id)
{
	tofront('addlog');
	update_values('/get/getLogEditData.php?logid='+log_id, log_id);
}

// get info from clicking on a map point
var last_system = "";
function get_mi(system)
{
    if (last_system == system)
    {
        document.getElementById('report').style.display = "none";
    }
    else
    {
        document.getElementById('report').style.display = "block";
        $.ajax({
        url: "/get/getMapData.php?system="+system,
        cache: false,
        success: function(result)
        {
            $('#report').html(result);
        }
        });
    }
    last_system = system;
}

// autocomplete scripts for adding points of interest
function showResult(str, divid, link, station, idlink, sysid, dp)
{
	link = link || "no";
	idlink = idlink || "no";
	station = station || "no";
	sysid = sysid || "no";
	dp = dp || "no";

    if (str.length>=2)
    {
        document.getElementById("suggestions_"+divid).style.display="block";
    }
    else
        document.getElementById("suggestions_"+divid).style.display="none";

    if (window.XMLHttpRequest)
    {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    } else {  // code for IE6, IE5

    }
        xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("suggestions_"+divid).innerHTML=xmlhttp.responseText;
        }
    };

	allegiance = getUrlVars()["allegiance"];
	system_allegiance = getUrlVars()["system_allegiance"];
	power = getUrlVars()["power"];

	addtolink = "";
	addtolink2 = "";
	addtolink3 = "";

	if (system_allegiance != "undefined")
		addtolink = "&system_allegiance="+system_allegiance;

	if (allegiance != "undefined")
		addtolink2 = "&allegiance="+allegiance;

	if (power != "undefined")
		addtolink3 = "&power="+power;

	if (station == "yes") {
		xmlhttp.open("GET","/get/getStationNames.php?q="+str+"&divid="+divid+"&link="+link+"&idlink="+idlink+"&sysid="+sysid+"&dp="+dp+addtolink+addtolink2+addtolink3,true);
	}
	else {
		xmlhttp.open("GET","/get/getSystemNames.php?q="+str+"&divid="+divid+"&link="+link+"&idlink="+idlink+"&sysid="+sysid+"&dp="+dp+addtolink+addtolink2+addtolink3,true);
	}
    xmlhttp.send();
}

// now change the value to the selected one
function setResult(result, coordinates, divid)
{
    $('#system_'+divid).val(result);
	var res = coordinates.split(",");
	var x = res[0];
	var y = res[1];
	var z = res[2];

    $('#coordsx_'+divid).val(x);
	$('#coordsy_'+divid).val(y);
	$('#coordsz_'+divid).val(z);
    document.getElementById("suggestions_"+divid).style.display="none";
}
function setbm(name, sysid)
{
    $('#bm_system_name').val(name);
	$('#bm_system_id').val(sysid);
	$('#bm_edit_id').val('');
	$('#bm_text').val('');
	$('#bm_catid').val('0');
	document.getElementById("suggestions_3").style.display="none";
}
function setl(name, stationid)
{
    $('#statname').val(name);
	//$('#station_id').val(stationid);
	document.getElementById("suggestions_41").style.display="none";
}
function setdp(name, coordinates, systemid)
{
    $('#system_name').val(name);
	$('#system_id').val(systemid);
	var res = coordinates.split(",");
	var x = res[0];
	var y = res[1];
	var z = res[2];
	$('#x').val(x);
	$('#y').val(y);
	$('#z').val(z);
	document.getElementById("suggestions_37").style.display="none";
}

function toinput(system, coordinates, price, tonnage, to, id)
{
	if (to == "from_system")
	{
		$('#from_system').val(system);
		$('#from_coords').val(coordinates);
		$('#price1').val(price);
		$('#tonnage').val(tonnage);
		$('#from_id').val(id);
	}
	else if (to == "to_system")
	{
		$('#to_system').val(system);
		$('#to_coords').val(coordinates);
		$('#price2').val(price);
		$('#tonnage').val(tonnage);
		$('#to_id').val(id);
	}
}
/*
function hailait(name, to)
{
	var x = document.getElementsByName(name);
	var i;

	if (to == "buy")
	{
		var buys = document.getElementsByClassName("dark_highlight");
		var is;
		for (is = 0; is < buys.length; is++)
		{
			buys[is].className="dark";
		}
		for (is = 0; is < buys.length; is++)
		{
			buys[is].className="dark";
		}
		for (is = 0; is < buys.length; is++)
		{
			buys[is].className="dark";
		}
		for (is = 0; is < buys.length; is++)
		{
			buys[is].className="dark";
		}
		//
		for (i = 0; i < x.length; i++)
		{
			x[i].className="dark_highlight";
		}
	}
	else if (to == "sell")
	{
		var sells = document.getElementsByClassName("dark_highlight2");
		var isa;
		for (isa = 0; isa < sells.length; isa++)
		{
			sells[isa].className="dark";
		}
		for (isa = 0; isa < sells.length; isa++)
		{
			sells[isa].className="dark";
		}
		for (isa = 0; isa < sells.length; isa++)
		{
			sells[isa].className="dark";
		}
		for (isa = 0; isa < sells.length; isa++)
		{
			sells[isa].className="dark";
		}
		//
		for (i = 0; i < x.length; i++)
		{
			x[i].className="dark_highlight2";
		}
	}
}

function empty()
{
	document.getElementById('from_system').value='';
	document.getElementById('to_system').value='';
	document.getElementById('to_coords').value='';
	document.getElementById('from_coords').value='';
	document.getElementById('distance_mp').value='';
	document.getElementById('return').value='';

	var cases = document.getElementsByClassName("dark_highlight");
	var num = 0;
	for (num = 0; num < cases.length; num++)
	{
		cases[num].className="dark";
	}
	for (num = 0; num < cases.length; num++)
	{
		cases[num].className="dark";
	}
	for (num = 0; num < cases.length; num++)
	{
		cases[num].className="dark";
	}
	for (num = 0; num < cases.length; num++)
	{
		cases[num].className="dark";
	}

	var cases2 = document.getElementsByClassName("dark_highlight2");
	var num2 = 0;
	for (num2 = 0; num2 < cases2.length; num2++)
	{
		cases2[num2].className="dark";
	}
	for (num2 = 0; num2 < cases2.length; num2++)
	{
		cases2[num2].className="dark";
	}
	for (num2 = 0; num2 < cases2.length; num2++)
	{
		cases2[num2].className="dark";
	}
	for (num2 = 0; num2 < cases2.length; num2++)
	{
		cases2[num2].className="dark";
	}
}
*/
// function to calculate distances
function calcDist(coord_fromx, coord_fromy, coord_fromz, coord_tox, coord_toy, coord_toz, from, to, price1, price2, tonnage, to_id, from_id)
{
	price1 = price1 || "";
	price2 = price2 || "";
	tonnage = tonnage || "160";
	to_id = to_id || "";
	from_id = from_id || "";

    //var coor_from = coord_from.split(",");

    var x1 = coord_fromx;
    var y1 = coord_fromy;
    var z1 = coord_fromz;

    //var coor_to = coord_to.split(",");

    var x2 = coord_tox;
    var y2 = coord_toy;
    var z2 = coord_toz;

	if (document.getElementById("distance_mp") && document.getElementById("to_system").value != "" && document.getElementById("from_system").value != "")
	{
		profit = 0;
		profit = price2 - price1;
		overall = profit * tonnage;

		document.getElementById('distance_mp').value = ''+Math.round(Math.sqrt(Math.pow((x1-(x2)),2)+Math.pow((y1-(y2)),2)+Math.pow((z1-(z2)),2)))+' ly and '+profit+' CR/t, '+overall+' CR for '+tonnage+' t';

		$.ajax({
			url: "/get/getReturnTrip.php?from="+from_id+"&to="+to_id+"&tonnage="+tonnage,
			cache: false,
			success: function(result)
			{
				if (result != "false") {
					$('#return').val(result);
				}
			}
		});
	}

	if (x1 && x2 && y1 && y2 && z1 && z2)
	{
		if (to == "")
			document.getElementById('dist_display').value = 'Missing information, try again';
		else
			document.getElementById('dist_display').value = 'The distance from '+from+' to '+to+' is '+Math.round(Math.sqrt(Math.pow((x1-(x2)),2)+Math.pow((y1-(y2)),2)+Math.pow((z1-(z2)),2)))+' ly';
	}
	else
	{
		document.getElementById('dist_display').value = 'Missing information, try again';
	}
}

// function to add station to log form
function addstation(station, station_id)
{
    document.getElementById("statname").value=station;
	//document.getElementById("station_id").value=station_id;
}

// function to save session log
function savelog(log)
{
    var data = this.document.getElementById('logtext').value;
    $.ajax({
      type: "POST",
      url: "/add/sessionLogSave.php",
      data: { logtext: data }
    })
    .done(function( msg )
	{
        document.getElementById('seslogsuccess').innerHTML = '<img src="/style/img/check.png">';

        setTimeout(function()
        {
			if (document.getElementById('seslogsuccess').innerHTML == '<img src="/style/img/check.png">')
			{
				document.getElementById('seslogsuccess').innerHTML = '';
			}

        }, 3000);
    });
}
function showsave()
{
    document.getElementById('seslogsuccess').innerHTML = '<a href="javascript:void(0);" onclick="savelog()" title="Save session log"><img src="/style/img/save.png"></a>'
}
/*  */

// function to shove affected div to the front, stackoverflow.com/questions/4012112/how-to-bring-the-selected-div-on-top-of-all-other-divs
function tofront(divid, toback)
{
    setindex = zindexmax++;
    toback = toback || false;

    var divs = ['addlog','calculate','addPoi','addstation','distance','editsystem','report','addBm','search_system'];

    if (toback == false)
    {
        if (document.getElementById(divid).style.display == "block")
        {
            document.getElementById(divid).style.display = "none";
			$(".entries").fadeIn("fast");
        }
        else
        {
			$("#"+divid).fadeIn("fast");
            document.getElementById(divid).style.zindex = setindex;
            document.getElementsByClassName('entries')[0].style.display = "none";
        }
    }
    else
    {
        get_data(true);
		$(".entries").fadeIn("fast");
    }
    var index;
    for (index = 0; index < divs.length; ++index)
    {
        if (document.getElementById(divs[index]) && divs[index] != divid)
        {
            document.getElementById(divs[index]).style.zindex = 0;
            document.getElementById(divs[index]).style.display = "none";
        }
    }
}

/*
* 	upload to imgur
*/

function imgurUpload(file, fileurl)
{
	$('#uploaded').html("Uploading image...<br /><img src='/style/img/loading.gif' style='vertical-align:middle;' />");
	$.ajax({
		url: 'https://api.imgur.com/3/image',
		headers:
		{
			Authorization: 'Client-ID 36fede1dee010c0',
			Accept: 'application/json'
		},
		type: 'POST',
		data:
		{
			image: file,
			type: 'base64'
		},
		success: function(result)
		{
			var url = result.data.link;

			$('#uploaded').html("Image succesfully uploaded!<br /><a target='_BLANK' href='"+url+"'>Link to your image on imgur.com<img src='/style/img/external_link.png' style='margin-bottom:3px;margin-left:6px;' alt='ext' /></a>");

			// write to file so we can retrieve url later
			$.ajax(
			{
				url: "/add/imgurURL.php?url="+url+"&file="+fileurl,
				cache: false,
				dataType: 'html',
				success: function(re)
				{
					//console.log(re);
				}
            });
			//console.log(result);
		}
    });
}

/*
*	set link divs as active
*/

function setActive(id, num)
{
	for (i = 0; i <= num; i++)
	{
		if (document.getElementById('link_'+i))
		{
			document.getElementById('link_'+i).className = "link";
		}
	}
	document.getElementById('link_'+id).className = "active";
}

/*
*	get wikipedia articles
*/

function get_wikipedia(search, id)
{
	if (document.getElementById("wpsearch_"+id).style.display == "none")
	{
		//$("#wpsearch_"+id).toggle();
		//document.getElementById("wpsearch_"+id).style.display = "block";
		$("#wpsearch_"+id).fadeIn();
		$("#wpsearch_"+id).html('<strong>Querying Wikipedia</strong><br /><img src="/style/img/loading.gif" alt="loading" />');

		$.ajax(
		{
			url: "/get/getWikipediaData.php?search="+search,
			cache: false,
			dataType: 'html',
			success: function(result)
			{
				$("#wpsearch_"+id).html(result);
				//console.log(returnedvalue)
			}
		});
	}
	else
	{
		document.getElementById("wpsearch_"+id).style.display = "none";
	}
}

/*
*	update class and rating on nearest_systems.php
*/

function getCR(group_id, class_name)
{
	$.ajax({
	url: "/get/getRatingAndClass.php?group_id="+group_id+"&class_name="+class_name,
	cache: false,
    dataType: 'json',
	success: function(result)
	{
		$('#rating').html(result['rating']);
		if (class_name == "")
			$('#class').html(result['class']);
	}
	});
}

/*
* 	http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
*/

function escapeRegExp(str)
{
    return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}
function replaceAll(str, find, replace)
{
  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

/*
*	refresh api data
*/

function refresh_api()
{
	$.ajax({
	url: "/get/getAPIdata.php?override",
	cache: false,
    dataType: 'json',
	success: function(result)
	{
		//
	}
	});

	$('#api_refresh').html('<img src="/style/img/check_24.png"  alt="Refresh done" style="height:24px;width:24px" />');

	// wait a couple of seconds before updating data
	setTimeout(function()
	{
		get_data(true);
	}, 2500);

	setTimeout(function()
	{
		$('#api_refresh').html('<img src="/style/img/refresh_24.png" alt="Refresh" style="height:24px;width:24px" />');
	}, 30000);
}

/*
*	ignore version update
*/

function ignore_version(version)
{
	$.ajax({
	url: "/admin/setData.php?ignore_version="+version,
	cache: false,
    dataType: 'json',
	success: function(result)
	{
		//
	}
	});

	$("#notice_new").fadeToggle("fast");
	$("#notifications").fadeToggle("fast");
}
