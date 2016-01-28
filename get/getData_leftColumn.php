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
 * Ajax backend file for the left column data
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

/*
*	System title for the left column
*/

$data['system_title'] .= '';

$pic = "system.png";
if (isset($curSys["allegiance"]))
{
	$pic = $curSys["allegiance"] == "Empire" ? "empire.png" : $pic;
	$pic = $curSys["allegiance"] == "Alliance" ? "alliance.png" : $pic;
	$pic = $curSys["allegiance"] == "Federation" ? "federation.png" : $pic;
}

$data['system_title'] = '	<div class="leftpanel-add-data">
								<a href="javascript:void(0)" id="toggle" onclick="setbm(\'' . addslashes($curSys["name"]) . '\', \'' . $curSys["id"] . '\');tofront(\'addBm\');$(\'#bm_text\').focus()" title="Bookmark system">
									<img src="/style/img/' . $pic . '" style="margin-right:5px" alt="' . $curSys["allegiance"] . '" />
								</a>
							</div>';

$data['system_title'] .= "<div class='leftpanel-title-text'><span id='ltitle'>";

$bookmarked = 0;
if ($curSys["id"] != "-1")
{
	$bres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
														FROM user_bookmarks
														WHERE system_id = '" . $curSys["id"] . "'
														AND system_id != ''
														LIMIT 1");
	$bookmarked = mysqli_num_rows($bres);
}
else
{
	$bres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
														FROM user_bookmarks
														WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
														LIMIT 1");
	$bookmarked = mysqli_num_rows($bres);
}

$pres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
													FROM user_poi
													WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
													AND system_name != ''
													LIMIT 1");
$poid = mysqli_num_rows($pres);

$class = "title";
if ($bookmarked > 0)
{
	$class = "bookmarked";
}

if ($poid > 0)
{
	$class = "poid";
}

$data['system_title'] .= "<a class='" . $class . "' href='javascript:void(0);' onclick='tofront(\"distance\");get_cs(\"system_2\",\"coords_2\");$(\"#system_6\").focus();' onmouseover='slide();' onmouseout='slideout();' title='Calculate distances'>";

if (isset($curSys["name"]) && !empty($curSys["name"]))
{
	$data['system_title'] .= htmlspecialchars($curSys["name"]);
}
else
{
	$data['system_title'] .= "Location unavailable";
}
$data['system_title'] .= "</a>";

$data['system_title'] .= "</span></div><div class='leftpanel-title-border'></div>";

/*
*	System information for the left column
*/

$data['system_info'] = "";

if (!empty($curSys["allegiance"]))
{
	$population_s = $curSys["population"] == "0" ? "" : " - Population: " . number_format($curSys["population"]);
	$population_s = $curSys["population"] == "None" ? "" : $curSys["population"];
	$population_s = $curSys["government"] == "" ? "" : " - " . $curSys["government"];

	$data['system_info'] .= '<div class="subtitle" id="t2">' . $curSys["allegiance"] . '' . $government_s . '' . $population_s . '</div>';

	if (!empty($curSys["economy"]))
	{
		$data['system_info'] .= '<div class="text" id="t3">&boxur; Economy: ' . $curSys["economy"] . '</div>';
	}
}
else
{
	$data['system_info'] .= '<div class="subtitle" id="t2">Welcome</div>';
	$data['system_info'] .= '<div class="text" id="t3">&boxur; CMDR ' . $settings["cmdr_name"] . '</div>';
}

/*
*    Stations for the left column
*/

$ress = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
													id, name, ls_from_star, max_landing_pad_size, faction, government, allegiance,
													state, type, import_commodities, export_commodities,
													prohibited_commodities, economies, selling_ships, shipyard,
													outfitting, commodities_market, black_market, refuel, repair, rearm, is_planetary
													FROM edtb_stations
													WHERE system_id = '" . $curSys["id"] . "'
													ORDER BY -ls_from_star DESC, name
													LIMIT 5") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
$count = mysqli_num_rows($ress);

if ($count > 0)
{
	$c = 0;
	while ($arra = mysqli_fetch_assoc($ress))
	{
		$station_name = $arra["name"];

		if ($c == 0)
		{
			$first_station_name = $arra["name"];
			$first_station_ls_from_star = $arra["ls_from_star"];
		}

		$ls_from_star = $arra["ls_from_star"];
		$max_landing_pad_size = $arra["max_landing_pad_size"] == "" ? "" : "<strong>Landing pad:</strong> " . $arra["max_landing_pad_size"] . "<br />";
		$station_id = $arra["id"];

		$faction = $arra["faction"] == "" ? "" : "<strong>Faction:</strong> " . $arra["faction"] . "<br />";
		$government = $arra["government"] == "" ? "" : "<strong>Government:</strong> " . $arra["government"] . "<br />";
		$allegiance = $arra["allegiance"] == "" ? "" : "<strong>Allegiance:</strong> " . $arra["allegiance"] . "<br />";

		$state = $arra["state"] == "" ? "" : "<strong>State:</strong> " . $arra["state"] . "<br />";
		$s_type = $arra["type"];
		$type = $arra["type"] == "" ? "" : "<strong>Type:</strong> " . $arra["type"] . "<br />";
		$economies = $arra["economies"] == "" ? "" : "<strong>Economies:</strong> " . $arra["economies"] . "<br />";

		$import_commodities = $arra["import_commodities"] == "" ? "" : "<br /><strong>Import commodities:</strong> " . $arra["import_commodities"] . "<br />";
		$export_commodities = $arra["export_commodities"] == "" ? "" : "<strong>Export commodities:</strong> " . $arra["export_commodities"] . "<br />";
		$prohibited_commodities = $arra["prohibited_commodities"] == "" ? "" : "<strong>Prohibited commodities:</strong> " . $arra["prohibited_commodities"] . "<br />";

		$selling_ships = $arra["selling_ships"] == "" ? "" : "<br /><strong>Selling ships:</strong> " . str_replace("'", "", $arra["selling_ships"]) . "<br />";

		$shipyard = $arra["shipyard"];
		$outfitting = $arra["outfitting"];
		$commodities_market = $arra["commodities_market"];
		$black_market = $arra["black_market"];
		$refuel = $arra["refuel"];
		$repair = $arra["repair"];
		$rearm = $arra["rearm"];
		$is_planetary = $arra["is_planetary"];

		$icon = get_station_icon($s_type, $is_planetary, "margin:3px;margin-left:0px;margin-right:6px");

		$includes = array(  "shipyard" => $shipyard,
							"outfitting" => $outfitting,
							"commodities market" => $commodities_market,
							"black market" => $black_market,
							"refuel" => $refuel,
							"repair" => $repair,
							"restock" => $rearm);

		$i = 0;
		$services = "";
		foreach ($includes as $name => $included)
		{
			if ($included == 1)
			{
				if ($i != 0)
				{
					$services .= ", ";
				}
				else
				{
					$services .= "<strong>Facilities:</strong> ";
				}

				$services .= $name;
			$i++;
			}
		}
		$services .= "<br />";

		$info = $type.$max_landing_pad_size.$faction.$government.$allegiance.$state.$economies.$services.$import_commodities.$export_commodities.$prohibited_commodities.$selling_ships;

		$info = str_replace("['", "", $info);
		$info = str_replace("']", "", $info);
		$info = str_replace("', '", ", ", $info);

		//$info = $info == "" ? "Edit station information" : $info;

		// $station_data .= '<div><a href="javascript:void(0)" onclick="update_values(\'/get/getStationEditData.php?station_id=' . $station_id . '\',\'' . $station_id . '\');tofront(\'addstation\')" style="color:inherit" onmouseover="$(\'#statinfo_' . $station_id . '\').toggle()" onmouseout="$(\'#statinfo_' . $station_id . '\').toggle()">' . $station_name;
		$station_data .= '<div>' . $icon  . '<a href="javascript:void(0)" style="color:inherit" onmouseover="$(\'#statinfo_' . $station_id . '\').fadeToggle(\'fast\')" onmouseout="$(\'#statinfo_' . $station_id . '\').toggle()">' . $station_name;

		if (!empty($ls_from_star))
		{
			$station_data .= ' (' . number_format($ls_from_star) . ' ls)';
		}

		$station_data .= "</a>&nbsp;<a href='javascript:void(0);' title='Add to new log as station' onclick='addstation(\"" . $station_name . "\", \"" . $station_id . "\")'><img src='/style/img/right.png' alt='Add to log' style='vertical-align:top;width:16px;height:16px' class='addstations' /></a>";

		$station_data .= '<div class="stationinfo" id="statinfo_' . $station_id . '">' . $info . '</div></div>';

		$c++;
	}
}
else
{
	// link to calculate coordinates
	if (empty($curSys["coordinates"]) && !empty($curSys["name"]))
	{
		$station_data .= "<span style='margin-bottom:6px;height:40px'><a href='javascript:void(0);' onclick='tofront(\"calculate\");get_cs(\"target_system\")' title='No coordinates found, click here to calculate'>";
		$station_data .= "<img src='/style/img/calculator.png' alt='Calculate' />";
		$station_data .= "&nbsp;*&nbsp;No coordinates, click to calculate them.</a></span><br /><br />&nbsp";
	}
	$station_data .= 'No station data available';
}


/*
*	if system coords are user calculated, show calc button
*/

$system_user_calculated = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																						FROM user_systems_own
																						WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																						LIMIT 1"));
if ($system_user_calculated > 0 && !empty($curSys["name"]))
{
	$station_data .= '<span style="float:right;margin-right:8px;margin-top:6px"><a href="javascript:void(0)" onclick="tofront(\'calculate\');get_cs(\'target_system\')" title="Review distances">';
	$station_data .= '<img src="/style/img/calculator.png" alt="Calc" />';
	$station_data .= '</a></span>';
}

$data['station_data'] = $station_data;
