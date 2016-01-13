<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2016 Mauri Kujala (contact@edtb.xyz)
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

/*
*
* 	Ajax backend file responsible for updating most of the on-the-fly stuff
*
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
$action = isset($_GET["action"]) ? $_GET["action"] : "";
$request = isset($_GET["request"]) ? $_GET["request"] : 0;

if ($action == "onlycoordinates")
{
	echo $current_coordinates;
	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit();
}
else if ($action == "onlysystem")
{
	echo $current_system;
	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit();
}
else if ($action == "onlyid")
{
	echo $current_id;
	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit();
}

$data = array();

/*
* 	Now Playing
*/

$data['now_playing'] = "";
if (isset($settings["nowplaying_file"]) && $settings["nowplaying_file"] != "")
{
	$nowplaying = file_get_contents($settings["nowplaying_file"]);

	$data['now_playing'] = '<img src="/style/img/music.png" style="vertical-align:middle;margin-right:6px;" alt="Now playing" />';
	$data['now_playing'] .= $nowplaying;
}

/*
* 	I we've arrived in a new system or
* 	are requesting page for the first time
*/

if ($newSystem !== FALSE || $request == 0)
{
	/*
	*	update galmap json if system is new or file doesn't exist
	*/

	$data['update_map'] = "false";
	if ($newSystem !== FALSE || !file_exists("" . $_SERVER["DOCUMENT_ROOT"] . "/map_points.json"))
	{
		$data['update_map'] = "true";
	}

	$data['current_system_name'] = $current_system;
	$data['current_coordinates'] = $current_coordinates;

	/*
	* 	Check if the system is in our database
	*/

	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
														population, allegiance, economy, government, ruling_faction, state, security, power, power_state, simbad_ref
														FROM edtb_systems
														WHERE id = '" . $current_id . "'
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$exists = mysqli_num_rows($res);
	$arr = mysqli_fetch_assoc($res);

	if ($exists > 0)
	{
		$population = $arr["population"];
		$ruling_faction = $arr["ruling_faction"];
		$government = $arr["government"];
		$allegiance = $arr["allegiance"];
		$state = $arr["state"];
		$security = $arr["security"];
		$power = $arr["power"];
		$power_state = $arr["power_state"];
		$simbad_ref = $arr["simbad_ref"];
	}
	else
	{
		$population = "";
		$ruling_faction = "";
		$government = "";
		$allegiance = "";
		$state = "unknown";
		$security = "unknown";
		$power = "";
		$power_state = "";
		$simbad_ref = "";
	}

	/*
	*	System title for the left column
	*/

	$data['system_title'] .= '';
	$pic = "system.png";

	if (isset($allegiance))
	{
		$pic = $allegiance == "Empire" ? "empire.png" : $pic;
		$pic = $allegiance == "Alliance" ? "alliance.png" : $pic;
		$pic = $allegiance == "Federation" ? "federation.png" : $pic;
	}

	$data['system_title'] = '	<div class="leftpanel-add-data">
									<a href="javascript:void(0);" id="toggle" onclick="setbm(\'' . $current_system . '\', \'' . $current_id . '\');tofront(\'addBm\');$(\'#bm_text\').focus();" title="Bookmark system">
										<img src="/style/img/' . $pic . '" style="vertical-align:middle;margin-right:5px;" alt="' . $allegiance . '" />
									</a>
								</div>';

	$data['system_title'] .= "<div class='leftpanel-title-text'><span id='ltitle'>";

	$bookmarked = 0;
	if ($current_id != "")
	{
		$bres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
															FROM user_bookmarks
															WHERE system_id = '" . $current_id . "'
															LIMIT 1");
		$bookmarked = mysqli_num_rows($bres);
	}

	$pres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
														FROM user_poi
														WHERE system_name = '" . $current_system . "'
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
	if (isset($current_system) && $current_system != "")
	{
		$data['system_title'] .= htmlspecialchars($current_system);
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

	if ($allegiance != "")
	{
		$population_s = $arr["population"] == "0" ? "" : " - Population: " . number_format($arr["population"]);
		$population_s = $arr["population"] == "None" ? "" : $population_s;
		$government_s = $arr["government"] == "" ? "" : " - " . $arr["government"];
		$economy = $arr["economy"];

		$data['system_info'] .= '<div class="subtitle" id="t2">' . $allegiance . '' . $government_s . '' . $population_s . '</div>';

		if ($economy != "")
		{
			$data['system_info'] .= '<div class="text" id="t3">└ Economy: ' . $economy . '</div>';
		}
	}
	else
	{
		$data['system_info'] .= '<div class="subtitle" id="t2">Welcome</div>';
		$data['system_info'] .= '<div class="text" id="t3">└ CMDR ' . $settings["cmdr_name"] . '</div>';
	}

	/*
	*	if system coords are user calculated, show calc button
	*/

	$system_user_calculated = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																							FROM user_systems_own
																							WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $current_system) . "'
																							LIMIT 1"));
	if ($system_user_calculated > 0 && $current_system != "")
	{
		$data['system_info'] .= '<span style="float:right;margin-right:10px;margin-top:12px;"><a href="javascript:void(0);" onclick="tofront(\'calculate\');get_cs(\'target_system\');" title="Review distances">';
		$data['system_info'] .= '<img src="/style/img/calculator.png" style="vertical-align:middle;" />';
		$data['system_info'] .= '</a></span>';
	}

	/*
	*	Stuff specifically for system.php
	*/

	// If system id is set, show info about that system
	if ($_GET["system_id"] != "undefined" || $_GET["system_name"] != "undefined")
	{
		$system_id = $_GET["system_id"] != "undefined" ? $_GET["system_id"] : "-1";

		if ($system_id == "-1")
		{
			$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																FROM edtb_systems
																WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], urldecode($_GET["system_name"])) . "'
																LIMIT 1");
			$arr = mysqli_fetch_assoc($res);

			$system_id = $arr["id"];
		}

		$si_system_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
																	id,
																	name,
																	population,
																	allegiance,
																	economy,
																	government,
																	ruling_faction,
																	state,
																	security,
																	power,
																	power_state,
																	x AS si_system_coordx,
																	y AS si_system_coordy,
																	z AS si_system_coordz,
																	simbad_ref
																	FROM edtb_systems
																	WHERE id = '" . $system_id . "'
																	LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$si_system_arr = mysqli_fetch_assoc($si_system_res);

		$si_system_name = $si_system_arr["name"];

		$si_system_display_name = $si_system_name;
		$simbad_ref = $si_system_arr["simbad_ref"];

		if ($simbad_ref != "")
		{
			$si_system_display_name = '<a href="http://simbad.u-strasbg.fr/simbad/sim-id?Ident=' . urlencode($si_system_name) . '" target="_BLANK" title="View on Simbad">' . $si_system_name . '&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;" alt="ext" /></a>';
		}

		$si_system_id = $si_system_arr["id"];
		$si_system_population = $si_system_arr["population"] == "" ? "None" : $si_system_arr["population"];
		$si_system_allegiance = $si_system_arr["allegiance"] == "" ? "None" : $si_system_arr["allegiance"];
		$si_system_economy = $si_system_arr["economy"] == "" ? "None" : $si_system_arr["economy"];
		$si_system_government = $si_system_arr["government"] == "" ? "None" : $si_system_arr["government"];
		$si_system_ruling_faction = $si_system_arr["ruling_faction"] == "" ? "None" : $si_system_arr["ruling_faction"];
		$si_system_state = $si_system_arr["state"] == "" ? "None" : $si_system_arr["state"];
		$si_system_power = $si_system_arr["power"] == "" ? "None" : $si_system_arr["power"];
		$si_system_security = $si_system_arr["security"] == "" ? "None" : $si_system_arr["security"];
		$si_system_power_state = $si_system_arr["power_state"] == "" ? "None" : $si_system_arr["power_state"];

		// get distance to current system
		if (valid_coordinates($coordx, $coordy, $coordz))
		{
			$adds = "";
			$dist1 = sqrt(pow(($coordx-($si_system_arr["si_system_coordx"])), 2)+pow(($coordy-($si_system_arr["si_system_coordy"])), 2)+pow(($coordz-($si_system_arr["si_system_coordz"])), 2));
		}
		else
		{
			// get last known coordinates
			$last_coords = last_known_system();

			$last_coordx = $last_coords["x"];
			$last_coordy = $last_coords["y"];
			$last_coordz = $last_coords["z"];

			$dist1 = sqrt(pow(($last_coordx-($si_system_arr["si_system_coordx"])), 2)+pow(($last_coordy-($si_system_arr["si_system_coordy"])), 2)+pow(($last_coordz-($si_system_arr["si_system_coordz"])), 2));
			$adds = " *";
		}
		$si_dist_add = "<a href='system.php?system_id=" . $current_id . "'>" . $current_system . "</a>: " . number_format($dist1, 1) . " ly" . $adds . " - ";

		$coordx = $si_system_arr["si_system_coordx"];
		$coordy = $si_system_arr["si_system_coordy"];
		$coordz = $si_system_arr["si_system_coordz"];
	}
	// if system_id not set, show info about current system
	else
	{
		$si_system_name = $current_system;
		$si_system_display_name = $si_system_name;

		if ($simbad_ref != "")
		{
			$si_system_display_name = '<a href="http://simbad.u-strasbg.fr/simbad/sim-id?Ident=' . urlencode($si_system_name) . '" target="_BLANK" title="View on Simbad">' . $si_system_name . '&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;" alt="ext" /></a>';
		}

		$si_system_id = $current_id;
		$si_system_population = $population == "" ? "None" : $population;
		$si_system_allegiance = $allegiance == "" ? "None" : $allegiance;
		$si_system_economy = $economy == "" ? "None" : $economy;
		$si_system_government = $government == "" ? "None" : $government;
		$si_system_ruling_faction = $ruling_faction == "" ? "None" : $ruling_faction;
		$si_system_state = $state == "" ? "None" : $state;
		$si_system_power = $power == "" ? "None" : $power;
		$si_system_security = $security == "" ? "None" : $security;
		$si_system_power_state = $power_state == "" ? "None" : $power_state;
	}

	/*
	*    basic system info
	*/

	// get distance to system
	if (is_numeric($coordx))
	{
		$add3 = "";
		$ud_coordx = $coordx;
		$ud_coordy = $coordy;
		$ud_coordz = $coordz;

		// get rares closeby
		if (isset($settings["rare_range"]) && $settings["rare_range"] == "-1")
		{
			$rares_closeby = 0;
		}
		else
		{
			$rare_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
																	sqrt(pow((edtb_systems.x-(" . $coordx . ")),2)+pow((edtb_systems.y-(" . $coordy . ")),2)+pow((edtb_systems.z-(" . $coordz . ")),2)) AS distance,
																	edtb_rares.item, edtb_rares.system_name, edtb_rares.station, edtb_rares.price,
																	edtb_rares.sc_est_mins, edtb_rares.ls_to_star,
																	edtb_rares.needs_permit, edtb_rares.max_landing_pad_size,
																	edtb_systems.x, edtb_systems.y, edtb_systems.z
																	FROM edtb_rares
																	LEFT JOIN edtb_systems ON edtb_rares.system_name = edtb_systems.name
																	WHERE
																	edtb_systems.x BETWEEN " . $coordx . "-" . $settings["rare_range"] . "
																	AND " . $coordx . "+" . $settings["rare_range"] . " &&
																	edtb_systems.y BETWEEN " . $coordy . "-" . $settings["rare_range"] . "
																	AND " . $coordy . "+" . $settings["rare_range"] . " &&
																	edtb_systems.z BETWEEN " . $coordz . "-" . $settings["rare_range"] . "
																	AND " . $coordz . "+" . $settings["rare_range"] . "
																	ORDER BY
																	edtb_rares.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $si_system_name) . "' DESC,
																	distance ASC
																	LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			$rares_closeby = mysqli_num_rows($rare_res);
		}
	}
	else
	{
		// get last known coordinates
		$last_coords = last_known_system();

		$last_coordx = $last_coords["x"];
		$last_coordy = $last_coords["y"];
		$last_coordz = $last_coords["z"];

		$ud_coordx = $last_coordx;
		$ud_coordy = $last_coordy;
		$ud_coordz = $last_coordz;

		$add3 = " *";

		$rares_closeby = 0;
	}

	// get distances to user defined systems
	$user_dists = "<span style=\"float:right;font-size:11px;\">" . $si_dist_add . "";
	if (isset($settings["dist_systems"]))
	{
		$num_dists = count($settings["dist_systems"]);
		$i = 1;
		foreach ($settings["dist_systems"] as $dist_sys => $dist_sys_display_name)
		{
			$user_dist_q = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, x, y, z
																		FROM edtb_systems
																		WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $dist_sys) . "'
																		LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			$user_dist_a = mysqli_fetch_assoc($user_dist_q);
			$dist_sys_id = $user_dist_a["id"];

			$dist_sys_coordx = $user_dist_a["x"];
			$dist_sys_coordy = $user_dist_a["y"];
			$dist_sys_coordz = $user_dist_a["z"];

			$user_dist = sqrt(pow(($ud_coordx-($dist_sys_coordx)), 2)+pow(($ud_coordy-($dist_sys_coordy)), 2)+pow(($ud_coordz-($dist_sys_coordz)), 2));
			$user_dists .= "<a href='system.php?system_id=" . $dist_sys_id . "'>" . $dist_sys_display_name . "</a>: " . number_format($user_dist, 1) . " ly" . $add3 . "";

			if ($i != $num_dists)
			{
				$user_dists .= " - ";
			}

			$i++;
		}
	}
	$user_dists .= "</span>";

	$data['si_name'] = '<div class="stationinfo" id="rares" style="display:none;position:absolute;margin-top:20px;margin-left:690px;z-index:1000;max-height:630px;overflow:auto;white-space:nowrap;">';

	if ($rares_closeby > 0)
	{
		$actual_num_res = 0;
		while ($rare_arr = mysqli_fetch_assoc($rare_res))
		{
			if ($rare_arr["distance"] <= $settings["rare_range"])
			{
				$data['si_name'] .= "[";
				$data['si_name'] .= number_format($rare_arr["distance"], 1);
				$data['si_name'] .= "&nbsp;ly]&nbsp;";
				$data['si_name'] .= $rare_arr["item"];
				$data['si_name'] .= "&nbsp;(";
				$data['si_name'] .= number_format($rare_arr["price"]);
				$data['si_name'] .= "&nbsp;CR)";
				$data['si_name'] .= "<br /><span style='font-weight:normal;'>";
				$data['si_name'] .= "<a href='/system.php?system_name=" . urlencode($rare_arr["system_name"]) . "'>";
				$data['si_name'] .= $rare_arr["system_name"];
				$data['si_name'] .= "</a>&nbsp;(";
				$data['si_name'] .= $rare_arr["station"];
				$data['si_name'] .= ")&nbsp;-&nbsp;";
				$data['si_name'] .= number_format($rare_arr["ls_to_star"], 0);
				$data['si_name'] .= "&nbsp;ls&nbsp;";
				$data['si_name'] .= "(";
				$data['si_name'] .= $rare_arr["sc_est_mins"];
				$data['si_name'] .= "&nbsp;min)&nbsp;";
				$data['si_name'] .= $rare_arr["needs_permit"] = "1" ? "" : "&nbsp;-&nbsp;Permit needed";
				$data['si_name'] .= "-&nbsp;";
				$data['si_name'] .= $rare_arr["max_landing_pad_size"];
				$data['si_name'] .= "</span><br /><br />";
				$actual_num_res++;
			}
		}
	}
	else
	{
		$data['si_name'] .= "No rares nearby";
	}

	$data['si_name'] .= "</div>";

	$num_visits = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id
																			FROM user_visited_systems
																			WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $si_system_name) . "'"));

	if ($actual_num_res > 0 && is_numeric($coordx))
	{
		$rare_text = "&nbsp;&nbsp;<span onclick='$(\"#rares\").fadeToggle(\"fast\");'><a href='javascript:void(0);' title'Click for more info'>[ Rares within " . $settings["rare_range"] . " ly: " . $actual_num_res . " ]</a></span>";
	}

	$data['si_name'] .= "" . $si_system_display_name . " <span style='font-size:11px;text-transform:uppercase;vertical-align:middle;'>[ State: " . $si_system_state . " - Security: " . $si_system_security . " - Visits: " . $num_visits . " ]" . $rare_text . "" . $user_dists . "</span>";

	/*
	*    station info for system.php
	*/

	$si_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE *
														FROM edtb_stations
														WHERE system_id = '" . $si_system_id . "'
														ORDER BY -ls_from_star DESC, name") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$station_exists = mysqli_num_rows($si_res);

	if ($station_exists == 0)
	{
		$data['si_stations'] = "No station data available";
	}
	else
	{
		while ($sarr2 = mysqli_fetch_assoc($si_res))
		{
			$s_name = $sarr2["name"];
			$s_explode = explode(" ", $s_name);

			$firsts = explode("'s", $s_explode[0]);
			$first = $firsts[0];
			$rest = str_replace($s_explode[0], "", $s_name);
			$station_id = $sarr2["id"];

			$s_name = "<span class='wp' onclick='get_wikipedia(\"" . urlencode($first) . "\", \"" . $station_id . "\");'><a href='javascript:void(0);' title='Ask Wikipedia about " . $first . "' style='font-weight:inherit;'>" . $s_explode[0] . "</a></span> " . $rest . "";

			$ls_from_star = $sarr2["ls_from_star"];
			$max_landing_pad_size = $sarr2["max_landing_pad_size"];

			$s_faction = $sarr2["faction"] == "" ? "" : "<strong>Faction:</strong> " . $sarr2["faction"] . "";
			$s_distance_from_star = $ls_from_star == 0 ? "" : "" . number_format($ls_from_star, 0) . " ls - ";
			$s_information = "<span style='float:right;margin-right:8px;'>└&nbsp;" . $s_distance_from_star . "Landing pad: " . $max_landing_pad_size . "</span><br />";
			$s_government = $sarr2["government"] == "" ? "Government unknown" : "" . $sarr2["government"] . "";
			$s_allegiance = $sarr2["allegiance"] == "" ? "Allegiance unknown" : "" . $sarr2["allegiance"] . "";

			$s_state = $sarr2["state"] == "" ? "" : "<strong>State:</strong> " . $sarr2["state"] . "<br />";
			$type = $sarr2["type"] == "" ? "Type unknown" : "" . $sarr2["type"] . "";
			$economies = $sarr2["economies"] == "" ? "Economies unknown" : "" . $sarr2["economies"] . "";
			$economies = $economies == "" ? "Economies unknown" : $economies;

			$import_commodities = $sarr2["import_commodities"] == "" ? "" : "<br /><strong>Import commodities:</strong> " . $sarr2["import_commodities"] . "<br />";
			$export_commodities = $sarr2["export_commodities"] == "" ? "" : "<strong>Export commodities:</strong> " . $sarr2["export_commodities"] . "<br />";
			$prohibited_commodities = $sarr2["prohibited_commodities"] == "" ? "" : "<strong>Prohibited commodities:</strong> " . $sarr2["prohibited_commodities"] . "<br />";

			$selling_ships = $sarr2["selling_ships"] == "" ? "" : "<br /><br /><strong>Selling ships:</strong> " . str_replace("'", "", $sarr2["selling_ships"]) . "";

			$selling_modules = "";

			if ($sarr2["selling_modules"] != "")
			{
				$modules = $sarr2["selling_modules"];

				$modules_s = explode("-", $modules);

				$modules_t = "";
				$last_class = "";
				$last_module_name = "";

				foreach ($modules_s as $module)
				{
					$m_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE class, rating, price, group_name
																		FROM edtb_modules
																		WHERE id = '" . $module . "'
																		LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
					$m_num = mysqli_num_rows($m_res);

					if ($m_num > 0)
					{
						$m_arr = mysqli_fetch_assoc($m_res);

						$m_name = $m_arr["group_name"];
						$m_class = $m_arr["class"];
						$m_rating = $m_arr["rating"];
						$m_price = $m_arr["price"];

						if ($m_class != $last_class)
							$modules_t .= "<br />";

						if ($m_name != $last_module_name)
							$modules_t .= "<br /><strong>" . $m_name . "</strong><br />";

						$modules_t .= " [" . $m_class . "" . $m_rating . " - " . number_format($m_price, 0) . "]";

						$last_module_name = $m_name;
						$last_class = $m_class;
					}
				}
				$selling_modules = "<br /><br />
									<div onclick=\"$('#modules_" . $station_id . "').fadeToggle('fast');\"><a href='javascript:void(0);'><img src=\"/style/img/plus.png\" alt=\"plus\" \>&nbsp;Selling modules</a></div>
									<div id='modules_" . $station_id . "' style='display:none;'>" . $modules_t . "</div>";
			}

			$shipyard = $sarr2["shipyard"];
			$outfitting = $sarr2["outfitting"];
			$commodities_market = $sarr2["commodities_market"];
			$black_market = $sarr2["black_market"];
			$refuel = $sarr2["refuel"];
			$repair = $sarr2["repair"];
			$rearm = $sarr2["rearm"];
			$is_planetary = $sarr2["is_planetary"];

			$icon = get_station_icon($type, $is_planetary);

			$facilities = array("shipyard" => $shipyard,
								"outfitting" => $outfitting,
								"market" => $commodities_market,
								"black_market" => $black_market,
								"refuel" => $refuel,
								"repair" => $repair,
								"restock" => $rearm);

			$i = 0;
			$services = "";
			foreach ($facilities as $name => $included)
			{
				$dname = str_replace("_", " ", $name);
				if ($included == 1)
				{
					$services .= '<img src="/style/img/facilities/' . $name . '.png" alt="' . $name . '" style="margin-right:10px;" onmouseover="$(\'#' . $name . '_' . $station_id . '\').fadeToggle(\'fast\');" onmouseout="$(\'#' . $name . '_' . $station_id . '\').toggle();" />';
					$services .= '<div class="facilityinfo" style="display:none;" id="' . $name . '_' . $station_id . '">Station has ' . $dname . '</div>';
				}
				else
				{
					$services .= '<img src="/style/img/facilities/' . $name . '_not.png" alt="' . $name . ' not included" style="margin-right:10px;" onmouseover="$(\'#' . $name . '_not_' . $station_id . '\').fadeToggle(\'fast\');" onmouseout="$(\'#' . $name . '_not_' . $station_id . '\').toggle();" />';
					$services .= '<div class="facilityinfo" style="display:none;" id="' . $name . '_not_' . $station_id . '">Station doesn\'t have ' . $dname . '</div>';
				}
			}

			$info = $s_faction.$s_information.$import_commodities.$export_commodities.$prohibited_commodities;
			$info = str_replace("['", "", $info);
			$info = str_replace("']", "", $info);
			$info = str_replace("', '", ", ", $info);

			$economies = str_replace("['", "", $economies);
			$economies = str_replace("']", "", $economies);
			$economies = str_replace("', '", ", ", $economies);

			$data['si_stations'] .= '<div class="systeminfo_station">';
				//$data['si_stations'] .= '<div class="systeminfo_station_name" onclick="$(\'#info_'.$station_id.'\').toggle();$(\'#prices_'.$station_id.'\').toggle();">';
				$data['si_stations'] .= '<div class="systeminfo_station_name">';
					$data['si_stations'] .= '' . $icon . '' . $s_name . '	<span style="font-weight:normal;font-size:10px;">
																	[ ' . $type . ' - ' . $s_allegiance . ' - ' . $s_government . ' - ' . $economies . ' ]
																</span>';
					$data['si_stations'] .= '<span style="float:right"><a href="http://eddb.io/station/' . $station_id . '" title="View station on eddb.io" target="_BLANK"><img src="/style/img/eddb.png" alt="EDDB" /></a></span>';
				$data['si_stations'] .= '</div><div class="wpsearch" id="wpsearch_' . $station_id . '" style="display:none;"></div>';

				$data['si_stations'] .= '<div id="info_'. $station_id .'" class="systeminfo_station_info">';
					//$data['si_stations'] .= $services;
					//$data['si_stations'] .= "<br /><br />";
					$data['si_stations'] .= $info;
					if ($info != "")
					{
						$data['si_stations'] .= "<br />";
					}

					$data['si_stations'] .= $services;
					$data['si_stations'] .= $selling_ships;
					$data['si_stations'] .= $selling_modules;
				$data['si_stations'] .= '</div>';

				// prices information
				/*$p_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT 	listings.supply, listings.buy_price, listings.sell_price, listings.demand,
												commodities.name, commodities.average_price, commodities.category_id, commodities.category
												FROM listings
												LEFT JOIN commodities ON listings.commodity_id = commodities.id
												WHERE listings.station_id = '" . $station_eddb_id . "'
												ORDER BY commodities.category_id");

				$data['si_stations'] .= '<div id="prices_'. $station_id .'" class="systeminfo_station_prices"><table width="100%">';

					$cur_cat = "";
					while ($arr3 = mysqli_fetch_assoc($p_res))
					{
						$category_id = $arr3["category_id"];
						$category = $arr3["category"];
						$commodity = $arr3["name"];

						$supply = $arr3["supply"];
						$buy = $arr3["buy_price"];
						$sell = $arr3["sell_price"];
						$demand = $arr3["demand"];

						$max_profit = $arr4["profit"];

						if ($cur_cat != $category_id)
						{
							$data['si_stations'] .= '<tr>';
								$data['si_stations'] .= '<td class="station_info_price_category">' . $category . '</td>';
								$data['si_stations'] .= '<td class="station_info_price_category">Supply</td>';
								$data['si_stations'] .= '<td class="station_info_price_category">Buy price</td>';
								$data['si_stations'] .= '<td class="station_info_price_category">Sell price</td>';
								$data['si_stations'] .= '<td class="station_info_price_category">Demand</td>';
							$data['si_stations'] .= '</tr>';
						}

						$data['si_stations'] .= '<tr>';
							$data['si_stations'] .= '<td class="station_info_price_info">' . $commodity . '</td>';
							$data['si_stations'] .= '<td class="station_info_price_info">' . number_format($supply) . '</td>';
							$data['si_stations'] .= '<td class="station_info_price_info">' . number_format($buy) . '</td>';
							$data['si_stations'] .= '<td class="station_info_price_info">' . number_format($sell) . '</td>';
							$data['si_stations'] .= '<td class="station_info_price_info">' . number_format($demand) . '</td>';
						$data['si_stations'] .= '</tr>';

						$cur_cat = $arr3["category_id"];
					}
				$data['si_stations'] .= '</table></div>';*/

			$data['si_stations'] .= '</div>';
		}
	}

	/*
	*    detailed system info
	*/

	if ($exists == 0 && $_GET["system_id"] == "undefined" && $_GET["system_name"] == "undefined")
	{
		$data['si_detailed'] = "No data available for this system";
	}
	else
	{
		if ($si_system_power != "None" && $si_system_power_state != "None")
		{
			$si_system_data = '' . $si_system_power . ' [' . $si_system_power_state . ']';
		}
		else if ($si_system_power == "" && $si_system_power_state != "")
		{
			$si_system_data = $si_system_power_state;
		}
		else
		{
			$si_system_data = "";
		}
		$data['si_detailed'] .= '<img src="/style/img/powers/' . str_replace(" ", "_", $si_system_power) . '.jpg" alt="' . $si_system_power . '" style="vertical-align:top;width:250px;height:419px;margin-bottom:6px; border: 1px solid #000;" /><br />';
		$data['si_detailed'] .= '<span style="font-size: 13px; font-weight:bold;">' . $si_system_data . '</span><br /><br />';
		$data['si_detailed'] .= '<span>
									<strong>Allegiance:</strong> ' . $si_system_allegiance . '<br />
									<strong>Government:</strong> ' . $si_system_government . '<br />
									<strong>Population:</strong> ' . number_format($si_system_population) . '<br />
									<strong>Economy:</strong> ' . $si_system_economy . '<br />
									<strong>Faction:</strong> ' . $si_system_ruling_faction . '
								</span>';
	}

	/*
	*    System log
	*/

	if ($current_system != "")
	{
		if ($settings["log_range"] == 0 || $coordx == "" || $current_id != "-1")
		{
			$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
																	user_log.id, user_log.system_name AS log_system_name, user_log.station_id,
																	user_log.log_entry, user_log.stardate,
																	edtb_systems.name AS system_name,
																	edtb_stations.name AS station_name
																	FROM user_log
																	LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
																	LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
																	WHERE user_log.system_id = '" . $current_id . "'
																	ORDER BY stardate DESC") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		}
		else
		{
			$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
																	user_log.id, user_log.system_id, user_log.system_name AS log_system_name,
																	user_log.station_id, user_log.log_entry, user_log.stardate,
																	sqrt(pow((edtb_systems.x-(" . $coordx . ")),2)
																	+pow((edtb_systems.y-(" . $coordy . ")),2)
																	+pow((edtb_systems.z-(" . $coordz . ")),2)) AS distance,
																	sqrt(pow((user_systems_own.x-(" . $coordx . ")),2)
																	+pow((user_systems_own.y-(" . $coordy . ")),2)
																	+pow((user_systems_own.z-(" . $coordz . ")),2)) AS distance2,
																	edtb_systems.name AS system_name,
																	edtb_stations.name AS station_name
																	FROM user_log
																	LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
																	LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
																	LEFT JOIN user_systems_own ON user_log.system_name = user_systems_own.name
																	WHERE
																	edtb_systems.x BETWEEN " . $coordx . "-" . $settings["log_range"] . "
																	AND " . $coordx . "+" . $settings["log_range"] . " &&
																	edtb_systems.y BETWEEN " . $coordy . "-" . $settings["log_range"] . "
																	AND " . $coordy . "+" . $settings["log_range"] . " &&
																	edtb_systems.z BETWEEN " . $coordz . "-" . $settings["log_range"] . "
																	AND " . $coordz . "+" . $settings["log_range"] . "
																	OR
																	user_systems_own.x BETWEEN " . $coordx . "-" . $settings["log_range"] . "
																	AND " . $coordx . "+" . $settings["log_range"] . " &&
																	user_systems_own.y BETWEEN " . $coordy . "-" . $settings["log_range"] . "
																	AND " . $coordy . "+" . $settings["log_range"] . " &&
																	user_systems_own.z BETWEEN " . $coordz . "-" . $settings["log_range"] . "
																	AND " . $coordz . "+" . $settings["log_range"] . "
																	ORDER BY user_log.system_name = '" . $current_system . "' DESC,
																	distance ASC, distance2 ASC,
																	user_log.stardate DESC
																	LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		}
		$num = mysqli_num_rows($log_res);

		$logdata = "";
		if ($num > 0)
		{
			$this_system = "";
			$this_id = "";
			while ($log_arr = mysqli_fetch_assoc($log_res))
			{
				if ($this_id != $log_arr["id"])
				{
					$system_name = $log_arr["system_name"] == "" ? $log_arr["log_system_name"] : $log_arr["system_name"];
					$log_station_name = $log_arr["station_name"];
					$log_text = $log_arr["log_entry"];
					$date = date_create($log_arr["stardate"]);
					$log_added = date_modify($date, "+1286 years");
					$distance = $log_arr["distance"] == "" ? round($log_arr["distance2"]) : round($log_arr["distance"]);

					if ($this_system != $system_name)
					{
						if ($distance != 0)
						{
							$add = " (distance " . $distance . " ly)";
						}
						else
						{
							$add = "";
						}

						$logdata .= '<h2><img src="/style/img/system_log.png" alt="pic" style="vertical-align:middle;" />&nbsp;&nbsp;System log for ' . $system_name . '' . $add . '</h2>';
						$logdata .= '<hr>';
					}

					$logdata .= '<h3>
									<a href="javascript:void(0);" onclick="$(\'#test\').attr(\'id\', \'html\');$(\'#html\').markItUp(mySettings);update_values(\'/get/getLogEditData.php?logid=' . $log_arr["id"] . '\',\'' . $log_arr["id"] . '\');tofront(\'addlog\');" style="color:inherit;" title="Edit entry">';
					$logdata .= date_format($log_added, "j M Y, H:i");
					if (!empty($log_station_name))
					{
						$logdata .= '&nbsp;[Station: ' . htmlspecialchars($log_station_name) . ']';
					}
					$logdata .= '</a></h3><pre class="entriespre" style="margin-bottom: 20px;">';
					$logdata .= $log_text;
					$logdata .= '</pre>';
				}

				$this_system = $system_name;
				$this_id = $log_arr["id"];
			}
		}
	}
	else
	{
		$logdata = "";
	}

	/*
	*    General log
	*/

	$glog_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
															id, log_entry, stardate
															FROM user_log WHERE system_id = '' AND system_name = ''
															ORDER BY stardate DESC
															LIMIT 5") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$gnum = mysqli_num_rows($glog_res);

	if ($gnum > 0)
	{
		$logdata .= '<h2><img src="/style/img/log.png" alt="pic" style="vertical-align:middle;" />&nbsp;&nbsp;Commander\'s Log</h2>';
		$logdata .= '<hr>';

		while ($glog_arr = mysqli_fetch_assoc($glog_res))
		{
			$glog_text = $glog_arr["log_entry"];
			$gdate = date_create($glog_arr["stardate"]);
			$glog_added = date_modify($gdate, "+1286 years");
			$logdata .= '<h3>
							<a href="javascript:void(0);"
							onclick="update_values(\'/get/getLogEditData.php?logid=' . $glog_arr["id"] . '\',\'' . $glog_arr["id"] . '\');tofront(\'addlog\');$(\'#html\').markItUp(mySettings);"
							style="color:inherit;"
							title="Edit entry">';
			$logdata .= date_format($glog_added, "j M Y, H:i");
			$logdata .= '</a></h3><pre class="entriespre">';
			$logdata .= $glog_text;
			$logdata .= '</pre>';
		}
	}
	$data['log_data'] = $logdata;

	/*
	*    Stations for the left column
	*/

	$ress = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE
														id, name, ls_from_star, max_landing_pad_size, faction, government, allegiance,
														state, type, import_commodities, export_commodities,
														prohibited_commodities, economies, selling_ships, shipyard,
														outfitting, commodities_market, black_market, refuel, repair, rearm, is_planetary
														FROM edtb_stations
														WHERE system_id = '" . $current_id . "'
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

			$icon = get_station_icon($s_type, $is_planetary, "margin:3px;margin-left:0px;margin-right:6px;vertical-align:middle;");

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

			$info = $info == "" ? "Edit station information" : $info;

			// $station_data .= '<div><a href="javascript:void(0);" onclick="update_values(\'/get/getStationEditData.php?station_id=' . $station_id . '\',\'' . $station_id . '\');tofront(\'addstation\');" style="color:inherit;" onmouseover="$(\'#statinfo_' . $station_id . '\').toggle();" onmouseout="$(\'#statinfo_' . $station_id . '\').toggle();">' . $station_name;
			$station_data .= '<div>' . $icon  . '<a href="javascript:void(0);" style="color:inherit;" onmouseover="$(\'#statinfo_' . $station_id . '\').fadeToggle(\'fast\');" onmouseout="$(\'#statinfo_' . $station_id . '\').toggle();">' . $station_name;

			if ($ls_from_star != 0)
			{
				$station_data .= ' (' . number_format($ls_from_star) . ' ls)';
			}

			$station_data .= "</a>&nbsp;<a href='javascript:void(0);' title='Add to new log as station' onclick='addstation(\"" . $station_name . "\", \"" . $station_id . "\");'><img src='/style/img/right.png' alt='Add to log' style='vertical-align:top;' class='addstations' width='16' height='16' /></a>";

			$station_data .= '<div class="stationinfo" id="statinfo_' . $station_id . '">' . $info . '</div></div>';

			$c++;
		}
	}
	else
	{
		// link to calculate coordinates
		if ($current_coordinates == "" && $current_system != "")
		{
			$station_data .= "<span style='margin-bottom:6px;height:40px;'><a href='javascript:void(0);' onclick='tofront(\"calculate\");get_cs(\"target_system\");' title='No coordinates found, click here to calculate'>";
			$station_data .= "<img src='/style/img/calculator.png' style='vertical-align:middle;' />";
			$station_data .= "&nbsp;*&nbsp;No coordinates, click to calculate them.</a></span><br /><br />&nbsp;";
		}
		$station_data .= 'No station data available';
	}

	$data['station_data'] = $station_data;

	$data['renew'] = "true";
}
else
{
	$data['renew'] = "false";
}

echo json_encode($data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
