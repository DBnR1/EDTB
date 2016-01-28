<?php
/*
*  ED ToolBox, a companion web app for the video game Elite Dangerous
*  (C) 1984 - 2016 Frontier Developments Plc.
*  ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/**
 * Ajax backend file to fetch map points for Neighborhood Map
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
Header("content-type: application/x-javascript");

if (isset($_GET["maxdistance"]) && is_numeric($_GET["maxdistance"]))
{
	$settings["maxdistance"] = $_GET["maxdistance"];
}

/*
*	if current coordinates aren't valid, use last known coordinates
*/

if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
{
	// get last known coordinates
	$last_coords = last_known_system();

	$curSys["x"] = $last_coords["x"];
	$curSys["y"] = $last_coords["y"];
	$curSys["z"] = $last_coords["z"];

	$disclaimer = "<p><strong>No coordinates for current location, last known location used</strong></p>";
}
else
{
	$disclaimer = "";
}

$data = "";
$last_row = "";

/*
*	fetch point of interest data for the map
*/

if ($settings["nmap_show_pois"] == "true")
{
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT poi_name, system_name, x, y, z
															FROM user_poi
															WHERE x != '' AND y != '' AND z != ''")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($row = mysqli_fetch_array($result))
	{
		$name = $row["system_name"];
		$disp_name = $row["poi_name"] != "" ? $row["poi_name"] : $row["system_name"];

		$poi_coordx = $row["x"];
		$poi_coordy = $row["y"];
		$poi_coordz = $row["z"];

		$coord = "$poi_coordx,$poi_coordy,$poi_coordz";

		$distance_from_current = "";
		if (valid_coordinates($poi_coordx, $poi_coordy, $poi_coordz))
		{
			$distance_from_current = sqrt(pow(($poi_coordx-($curSys["x"])), 2)+pow(($poi_coordy-($curSys["y"])), 2)+pow(($poi_coordz-($curSys["z"])), 2));
		}

		// only show systems if distance is less than the limit set by the user
		if ($distance_from_current != "" && $distance_from_current <= $settings["maxdistance"])
		{
			$visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																					FROM user_visited_systems
																					WHERE
																					system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $name) . "'
																					LIMIT 1"));

			if ($name == "SOL")
			{
				$marker = 'marker:{symbol:"circle",radius:3,fillColor:"#37bf1c"}';
			}
			elseif ($visited > 0)
			{
				$marker = 'marker:{symbol:"url(/style/img/goto-g.png)"}';
			}
			else
			{
				$marker = 'marker:{symbol:"url(/style/img/goto.png)"}';
			}

			$data = "{name:\"" . $disp_name . "\",data:[[" . $coord . "]]," . $marker . "}" . $last_row . "";

			$last_row = "," . $data . "";
		}
	}
}

/*
*	 fetch bookmark data for the map
*/

if ($settings["nmap_show_bookmarks"] == "true")
{
	$bm_result = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_bookmarks.comment, user_bookmarks.added_on,
															edtb_systems.name AS system_name, edtb_systems.x, edtb_systems.y, edtb_systems.z,
															user_bm_categories.name AS category_name
															FROM user_bookmarks
															LEFT JOIN edtb_systems ON user_bookmarks.system_id = edtb_systems.id
															LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
															WHERE edtb_systems.x != '' AND edtb_systems.y != '' AND edtb_systems.z != ''")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($bm_row = mysqli_fetch_array($bm_result))
	{
		$bm_system_name = $bm_row["system_name"];
		$bm_comment = $bm_row["comment"];
		$bm_added_on = $bm_row["added_on"];
		$bm_category_name = $bm_row["category_name"];

		// coordinates for distance calculations
		$bm_coordx = $bm_row["x"];
		$bm_coordy = $bm_row["y"];
		$bm_coordz = $bm_row["z"];
		$coord = "" . $bm_row["x"] . "," . $bm_row["y"] . "," . $bm_row["z"] . "";

		$distance_from_current = "";
		if (valid_coordinates($bm_coordx, $bm_coordy, $bm_coordz))
		{
			$distance_from_current = sqrt(pow(($bm_coordx-($curSys["x"])), 2)+pow(($bm_coordy-($curSys["y"])), 2)+pow(($bm_coordz-($curSys["z"])), 2));
		}

		// only show systems if distance is less than the limit set by the user
		if ($distance_from_current != "" && $distance_from_current <= $settings["maxdistance"])
		{
			$marker = 'marker:{symbol:"url(/style/img/bm.png)"}';

			$data = "{name:\"" . $bm_system_name . "\",data:[[" . $coord . "]]," . $marker . "}" . $last_row . "";

			$last_row = "," . $data . "";
		}
	}
}

/*
*	 fetch rares data for the map
*/

if ($settings["nmap_show_rares"] == "true")
{
	$rare_result = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																edtb_rares.item, edtb_rares.station, edtb_rares.system_name, edtb_rares.ls_to_star,
																edtb_systems.x, edtb_systems.y, edtb_systems.z
																FROM edtb_rares
																LEFT JOIN edtb_systems ON edtb_rares.system_name = edtb_systems.name")
																or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($rare_row = mysqli_fetch_array($rare_result))
	{
		$rare_item = $rare_row["item"];
		$rare_station = $rare_row["station"];
		$rare_system = $rare_row["system_name"];
		$rare_dist_to_star = number_format($rare_row["ls_to_star"]);

		$rare_disp_name = "" . $rare_item . " - " . $rare_system . " (" . $rare_station . " - " . $rare_dist_to_star . " ls)";

		// coordinates for distance calculations
		$rare_coordx = $rare_row["x"];
		$rare_coordy = $rare_row["y"];
		$rare_coordz = $rare_row["z"];

		$rare_coord = "" . $rare_coordx . "," . $rare_coordy . "," . $rare_coordz . "";

		$rare_distance_from_current = "";
		if (valid_coordinates($rare_coordx, $rare_coordy, $rare_coordz))
		{
			$rare_distance_from_current = sqrt(pow(($rare_coordx-($curSys["x"])), 2)+pow(($rare_coordy-($curSys["y"])), 2)+pow(($rare_coordz-($curSys["z"])), 2));
		}

		// only show systems if distance is less than the limit set by the user
		if ($rare_distance_from_current != "" && $rare_distance_from_current <= $settings["maxdistance"])
		{
			$rare_marker = 'marker:{symbol:"url(/style/img/rare.png)"}';

			$data = "{name:\"" . $rare_disp_name . "\",data:[[" . $rare_coord . "]]," . $rare_marker . "}" . $last_row . "";

			$last_row = "," . $data . "";
		}
	}
}

/*
*	fetch visited systems data for the map
*/

if ($settings["nmap_show_visited_systems"] == "true")
{
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT
														user_visited_systems.system_name AS system_name,
														edtb_systems.x, edtb_systems.y, edtb_systems.z, edtb_systems.id AS sysid, edtb_systems.allegiance
														FROM user_visited_systems
														LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
														GROUP BY user_visited_systems.system_name
														ORDER BY user_visited_systems.visit ASC")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($row = mysqli_fetch_array($result))
	{
		$name = $row["system_name"];
		$sysid = $row["sysid"];

		// coordinates for distance calculations
		$vs_coordx = $row["x"];
		$vs_coordy = $row["y"];
		$vs_coordz = $row["z"];

		/*
		*	if coords are not set, see if user has calculated them
		*/

		if (!valid_coordinates($vs_coordx, $vs_coordy, $vs_coordz))
		{
			$cb_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																	FROM user_systems_own
																	WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $name) . "'
																	LIMIT 1")
																	or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			$cb_arr = mysqli_fetch_assoc($cb_res);

			$vs_coordx = $cb_arr["x"] == "" ? "" : $cb_arr["x"];
			$vs_coordy = $cb_arr["y"] == "" ? "" : $cb_arr["y"];
			$vs_coordz = $cb_arr["z"] == "" ? "" : $cb_arr["z"];
		}

		$distance_from_current = "";
		if (valid_coordinates($vs_coordx, $vs_coordy, $vs_coordz))
		{
			$coord = "" . $vs_coordx . "," . $vs_coordy . "," . $vs_coordz . "";

			$distance_from_current = sqrt(pow(($vs_coordx-($curSys["x"])), 2)+pow(($vs_coordy-($curSys["y"])), 2)+pow(($vs_coordz-($curSys["z"])), 2));

			// only show systems if distance is less than the limit set by the user
			if ($distance_from_current <= $settings["maxdistance"])
			{
				$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																						FROM user_log
																						WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $name) . "'
																						LIMIT 1"));
				$allegiance = $row["allegiance"];

				if ($allegiance == "Federation")
				{
					$color = 'rgba(140, 140, 140, 0.7)';
				}
				elseif ($allegiance == "Alliance")
				{
					$color = 'rgba(9, 180, 244, 0.7)';
				}
				elseif ($allegiance == "Empire")
				{
					$color = 'rgba(231, 216, 132, 0.7)';
				}
				else
				{
					$color = 'rgba(255, 255, 255, 0.8)';
				}

				if ($logged > 0 && strtolower($name) != strtolower($curSys["name"]))
				{
					$marker = 'marker:{symbol:"circle",radius:3,fillColor:"' . $color . '",lineWidth:"2",lineColor:"#2e92e7"}';
				}
				elseif (strtolower($name) == strtolower($curSys["name"]))
				{
					$marker = 'marker:{symbol:"circle",radius:4,fillColor:"' . $color . '",lineWidth:"2",lineColor:"#f44b09"}';
				}
				else
				{
					$marker = 'marker:{symbol:"circle",radius:3,fillColor:"' . $color . '"}';
				}

				if (isset($name) && isset($coord))
				{
					$data = "{name:\"" . $name . "\",data:[[" . $coord . "]],".$marker."}" . $last_row . "";
				}
				else
				{
					$data = $last_row;
				}

				$last_row = "," . $data . "";
			}
		}
	}
}

// get the max/min values for map display
if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
{
	$maxx = $curSys["x"] + $settings["maxdistance"];
	$maxy = $curSys["y"] + $settings["maxdistance"];
	$maxz = $curSys["z"] + $settings["maxdistance"];
	$minx = $curSys["x"] - $settings["maxdistance"];
	$miny = $curSys["y"] - $settings["maxdistance"];
	$minz = $curSys["z"] - $settings["maxdistance"];
}
else
{
	$maxx = 100;
	$maxy = 100;
	$maxz = 100;
	$minx = -100;
	$miny = -100;
	$minz = -100;
}
// change between 3D and 2D maps
if (isset($_GET["mode"]) && $_GET["mode"] == "2d")
{
	?>
	/* http://stackoverflow.com/questions/524696/how-to-create-a-style-tag-with-javascript */
	var css = '#container {left:-12px;top:-20px;}',
		head = document.head || document.getElementsByTagName('head')[0],
		style = document.createElement('style');

	style.type = 'text/css';
	if (style.styleSheet){
		style.styleSheet.cssText = css;
	} else {
		style.appendChild(document.createTextNode(css));
	}

	head.appendChild(style);

	/* custom tooltip format */
	function tooltipFormatter() {
		return this.series.name.toUpperCase();
	}

	<?php
	$threed = "false";
	$zoomtype = "zoomType: 'xy',";
	$panning = "true";
	$pankey = "panKey: 'shift',";
}
else
{
	?>
	/* custom tooltip format */
	function tooltipFormatter() {
		return ""+this.series.name.toUpperCase()+" is "+Math.round(Math.sqrt(Math.pow((this.x-(<?php echo $curSys["x"] ?>)),2)+Math.pow((this.y-(<?php echo $curSys["y"] ?>)),2)+Math.pow((this.point.z-(<?php echo $curSys["z"] ?>)),2)))+" ly away";
	}
	<?php
	$threed = "true";
	$zoomtype = "";
	$panning = "false";
	$pankey = "";
}
?>
$(function ()
{
	// Give the points a 3D feel by adding a radial gradient
	/* Highcharts.getOptions().colors = $.map(Highcharts.getOptions().colors, function (color) {
		return {
			radialGradient: {
				cx: 0.4,
				cy: 0.3,
				r: 0.5
			},
			stops: [
				[0, color],
				[1, Highcharts.Color(color).brighten(-0.2).get('rgb')]
			]
		};
	}); */
	Highcharts.theme =
	{
		/* colors: ['rgba(117,38,38,0.7)', 'rgba(192,251,251,0.7)', 'rgba(120,171,173,0.7)', 'rgba(195,44,222,0.7)', 'rgba(255,179,0,0.7)', 'rgba(24,219,216,0.7)', 'rgba(128,0,0,0.7)', 'rgba(145,232,23,0.7)'], */
		chart:
		{
			backgroundColor: 'transparent',
			style:
			{
				fontFamily: "Telegrama"
			},
			plotBorderColor: '#606063'
		},
		xAxis:
		{
			gridLineColor: '#707073',
			backgroundColor: "#CCC",
			labels:
			{
				style:
				{
					color: '#E0E0E3'
				}
			},
			lineColor: '#707073',
			minorGridLineColor: '#505053',
			tickColor: '#707073',
			title:
			{
				style:
				{
					color: '#A0A0A3'

				}
			}
		},
		yAxis:
		{
			gridLineColor: '#707073',
			labels:
			{
				style:
				{
					color: '#E0E0E3'
				}
			},
			lineColor: '#707073',
			minorGridLineColor: '#505053',
			tickColor: '#707073',
			tickWidth: 1,
			title:
			{
				style:
				{
					color: '#A0A0A3'
				}
			}
		},
		tooltip:
		{
			backgroundColor: 'rgba(0, 0, 0, 0.85)',
			style:
			{
				color: '#FFFFFA',
				fontSize: '11px',
				fontFamily: 'Sintony',
				letterSpacing: 'normal'
			}
		},
		plotOptions:
		{
			series:
			{
				dataLabels:
				{
					color: '#B0B0B3'
				},
				marker:
				{
					lineColor: '#333'
				},
				enableMouseTracking: true,
			},
			boxplot:
			{
				fillColor: '#505053'
			},
			candlestick:
			{
				lineColor: 'white'
			}
	   }
	};

	// Apply the theme
	Highcharts.setOptions(Highcharts.theme);

	// get the jQuery wrapper
	var $report = $('#report');

	// Set up the chart
	var chart = new Highcharts.Chart(
	{
		loading:
		{
            labelStyle:
			{
                fontStyle: 'italic'
            }
        },
		chart:
		{
			renderTo: 'container',
			margin: 90,
			type: 'scatter',
			stickyTracking: false,
			<?php echo $zoomtype?>
			panning: <?php echo $panning?>,
			<?php echo $pankey?>
			options3d:
			{
				enabled: <?php echo $threed?>,
				alpha: 20,
				beta: 30,
				depth: 120,
				frame:
				{
					back:
					{
						color: "#1E2021"
					},
					side:
					{
						color: "#1E2021"
					},
					bottom:
					{
						color: "#1E2021"
					}
				}
			}
		},
		title:
		{
			text: ''
		},
		subtitle:
		{
			text: ''
		},
		plotOptions:
		{
			scatter:
			{
				width:10,
				height: 10,
				depth: 10
			},
			series:
			{
				animation: false,
				cursor: 'pointer',
				point:
				{
					events:
					{
						click: function ()
						{
							get_mi(this.series.name);
						}
					}
				}
			}
		},
		tooltip:
		{
			formatter: tooltipFormatter,
			animation: false
		},
		xAxis:
		{
			min: <?php echo round($minx)?>,
			max: <?php echo round($maxx)?>,
			gridLineWidth: 1
		},
		yAxis:
		{
			min: <?php echo round($miny)?>,
			max: <?php echo round($maxy)?>,
			title: null
		},
		zAxis:
		{
			min: <?php echo round($minz)?>,
			max: <?php echo round($maxz)?>
		},
		credits:
		{
			enabled: true
		},
		legend:
		{
			enabled: false
		},
		exporting:
		{
            enabled: false
        },
		series: [<?php echo $data ?>]
	});

	// Add mouse events for rotation
	$(chart.container).bind('mousedown.hc touchstart.hc', function (e)
	{
		e = chart.pointer.normalize(e);

		var posX = e.pageX,
			posY = e.pageY,
			alpha = chart.options.chart.options3d.alpha,
			beta = chart.options.chart.options3d.beta,
			newAlpha,
			newBeta,
			sensitivity = 5; // lower is more sensitive

		$(document).bind(
		{
			'mousemove.hc touchdrag.hc': function (e)
			{
				// Run beta
				newBeta = beta + (posX - e.pageX) / sensitivity;
				newBeta = Math.min(100, Math.max(-100, newBeta));
				chart.options.chart.options3d.beta = newBeta;

				// Run alpha
				newAlpha = alpha + (e.pageY - posY) / sensitivity;
				newAlpha = Math.min(100, Math.max(-100, newAlpha));
				chart.options.chart.options3d.alpha = newAlpha;

				chart.redraw(false);
			},
				'mouseup touchend': function ()
				{
					$(document).unbind('.hc');
				}
		});
	});
	$('#loader').hide();
});
<?php
if ($disclaimer != "")
{
	?>
	$('#disclaimer').html('<?php echo $disclaimer?>');
	<?php
}
else
{
	?>
	$('#disclaimer').html('');
	<?php
}

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
