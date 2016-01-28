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
 * System data for Marvin
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$va_text = array();

/*
*	System Info
*/

if (isset($_GET["sys"]))
{
	$num_visits = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																				FROM user_visited_systems
																				WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'"))
																				or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$va_text .= "No system data.";

	if (!empty($curSys["name"]))
	{
		$va_system = str_replace(".", "", $curSys["name"]);

		$va_text = "The " . tts_override($va_system) . " system.\n\r";

		$va_allegiance = $curSys["allegiance"] == "None" ? "No additional data available. " : $curSys["allegiance"];
		$va_allegiance = $va_allegiance == "" ? "No additional data available. " : $va_allegiance;

		$rant = "";
		$rants = array();
		if ($curSys["allegiance"] == "Federation")
		{
			$rants[] = "Please tell me we're here to kill Federal scum!";
			$rants[] = "Let's show these Federal bastards who's boss!";
			$rants[] = "Why do you insist on flying through Federal space you dick!?";
			$rants[] = "Why do you insist on flying through Federal space you little shit!?";
			$rants[] = "What's that smell? Oh, we're in Federation space, never mind.";
			$rants[] = "For fuck's sake, another Federation system?! Really?!";
			$rants[] = "Let's get the fuck out already!";
			$rants[] = "Oh good, a Federation system. We can dump our waste here.";
			$rants[] = "Why do I even bother maintaining this ship if you're just going to sully it by flying through Federal infested space?";

			shuffle($rants);

			$rant = $rants[0];
		}

		$va_government = $curSys["government"] == "None" ? "" : " " . $curSys["government"] . "";

		if (!empty($curSys["power"]) && !empty($curSys["power_state"]))
		{
			$va_power_text = array();
			$va_power_text[] = $curSys["power"];

			if ($curSys["power"] == "Felicia Winters")
			{
				$va_power_text[] = random_insult("Felicia Winters");
			}

			if ($curSys["power"] == "Zachary Hudson")
			{
				$va_power_text[] = random_insult("Zachary Hudson");
			}

			if ($curSys["power"] == "Arissa Lavigny-Duval")
			{
				$va_power_text[] = "Arissa Lavigny-Duval, bask in her glory!";
				$va_power_text[] = "Arissa Lavigny-Duval, bask in her glory! Do it! Bask motherfucker!";
				$va_power_text[] = "the one and only Arissa Lavigny-Duval";

				if ($curSys["population"] < 100000)
				{
					$va_power_text[] = "Arissa Lavigny-Duval. It's a hellhole but they all count.";
					$va_power_text[] = "Arissa Lavigny-Duval. It's small but cute";
				}
			}

			if ($curSys["power_state"] == "Contested")
			{
				$va_power = " system that is currently contested";
			}
			else
			{
				shuffle($va_power_text);
				$va_power = $curSys["power_state"] == "None" ? "" : " " . strtolower($curSys["power_state"]) . " by " . $va_power_text[0] . "";
			}
		}
		else
		{
			$va_power = "";
		}

		if ($curSys["population"] >= 1000000000)
			$round = -6;
		elseif ($curSys["population"] >= 10000000 && $curSys["population"] < 1000000000)
			$round = -5;
		elseif ($curSys["population"] >= 1000000 && $curSys["population"] < 10000000)
			$round = -4;
		elseif ($curSys["population"] >= 100000 && $curSys["population"] < 1000000)
			$round = -3;
		elseif ($curSys["population"] >= 10000 && $curSys["population"] < 100000)
			$round = -3;
		elseif ($curSys["population"] >= 1000 && $curSys["population"] < 10000)
			$round = -2;
		elseif ($curSys["population"] >= 100 && $curSys["population"] < 1000)
			$round = -1;
		else
			$round = 0;

		if ($curSys["population"] == 0)
		{
			$va_pop = "";
		}
		else
		{
			$va_pop = $curSys["population"] == "None" ? ". It is unpopulated." : ", with a population of about " . number_format(round($curSys["population"], $round)) . ".";
		}

		$article = "";
		if ($va_allegiance != "No additional data available. ")
		{
			if (preg_match('/([aeiouAEIOU])/', $va_allegiance{0}))
			{
				$article = "An";
			}
			else
			{
				$article = "A";
			}
		}

		$va_text .= "" . $article . " " . $va_allegiance . "" . strtolower($va_government) . "" . $va_power . "" . $va_pop . "";

		$va_text .= " " . $rant . "";

		$ress = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, ls_from_star
															FROM edtb_stations
															WHERE system_id = '" . $curSys["id"] . "'
															ORDER BY -ls_from_star DESC, name")
		or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$count = mysqli_num_rows($ress);

		if ($count > 0)
		{
			$c = 0;
			while ($arra = mysqli_fetch_assoc($ress))
			{
				if ($c == 0)
				{
					$first_station_name = $arra["name"];
					$first_station_ls_from_star = $arra["ls_from_star"];
				}
				else
				{
					break;
				}
				$c++;
			}
		}

		if ($count == 1)
		{
			if ($first_station_ls_from_star != 0)
			{
				$va_text .= " The systems' only spaceport is " . $first_station_name . " " . number_format(round($first_station_ls_from_star)) . " light seconds away.";
			}
			else
			{
				$va_text .= " The systems' only spaceport is " . $first_station_name . ".";
			}
		}
		elseif ($count > 1)
		{
			if ($first_station_ls_from_star != 0)
			{
				$va_text .= " It has " . $count . " spaceports, the nearest one is " . $first_station_name . " " . number_format(round($first_station_ls_from_star)) . " light seconds away.";
			}
			else
			{
				$va_text .= " It has " . $count . " spaceports.";
			}
		}

		if ($num_visits == 1)
		{
			$inputs = array();
			$inputs[] = " We have not visited this system before.";
			$inputs[] = " This is our first time visiting this system.";
			shuffle($inputs);

			$va_text .= $inputs[0];
		}
		elseif ($num_visits == 2)
		{
			$vis_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT visit
																	FROM user_visited_systems
																	WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																	ORDER BY visit ASC
																	LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
			$vis_arr = mysqli_fetch_assoc($vis_res);

			$first_vis = get_timeago(strtotime($vis_arr["visit"]));
			$va_text .= " We have visited this system once before. That was " . $first_vis . ".";
		}
		else
		{
			$vis_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT visit
																	FROM user_visited_systems
																	WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																	ORDER BY visit ASC
																	LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
			$vis_arr = mysqli_fetch_assoc($vis_res);

			$num_vis = $num_visits-1;
			$first_vis = get_timeago(strtotime($vis_arr["visit"]));
			$va_text .= " We have visited this system " . $num_vis . " times before. Our first visit was " . $first_vis . ".";
		}
	}

	echo $va_text;

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}

/*
*	Closest Station
*/

if (isset($_GET["cs"]))
{
	if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
	{
		$usex = $curSys["x"];
		$usey = $curSys["y"];
		$usez = $curSys["z"];
	}
	else
	{
		$last_coords = last_known_system();

		$usex = $last_coords["x"];
		$usey = $last_coords["y"];
		$usez = $last_coords["z"];
		$last_system = $last_coords["name"];

		$add2 = "I am unable to determine the coordinates of our current location. Our last known location is the " . tts_override($last_system) . " system. ";
	}

	$cs_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT
														edtb_stations.system_id AS system_id,
														edtb_stations.name AS station_name,
														edtb_stations.max_landing_pad_size,
														edtb_stations.ls_from_star,
														edtb_stations.type,
														edtb_stations.shipyard,
														edtb_stations.outfitting,
														edtb_stations.commodities_market,
														edtb_stations.black_market,
														edtb_stations.refuel,
														edtb_stations.repair,
														edtb_stations.rearm,
														edtb_systems.allegiance AS allegiance,
														edtb_systems.id AS system_id,
														edtb_systems.x AS coordx,
														edtb_systems.y AS coordy,
														edtb_systems.z AS coordz,
														edtb_systems.name as system_name
														FROM edtb_stations
														LEFT JOIN edtb_systems on edtb_stations.system_id = edtb_systems.id
														WHERE edtb_systems.x != ''
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2)),
														-edtb_stations.ls_from_star DESC
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	echo $add2;

	$cs_arr = mysqli_fetch_assoc($cs_res);

	$cs_system = tts_override($cs_arr["system_name"]);
	$cs_allegiance = $cs_arr["allegiance"];

	$ss_coordx = $cs_arr["coordx"];
	$ss_coordy = $cs_arr["coordy"];
	$ss_coordz = $cs_arr["coordz"];

	$cs_distance = sqrt(pow(($ss_coordx-($usex)), 2)+pow(($ss_coordy-($usey)), 2)+pow(($ss_coordz-($usez)), 2));

	$cs_station_name = $cs_arr["station_name"];
	$cs_max_landing_pad_size = $cs_arr["max_landing_pad_size"] == "L" ? "large" : "medium";
	$cs_ls_from_star = $cs_arr["ls_from_star"];
	$cs_type = $cs_arr["type"];
	$cs_shipyard = $cs_arr["shipyard"];
	$cs_outfitting = $cs_arr["outfitting"];
	$cs_commodities_market = $cs_arr["commodities_market"];
	$cs_black_market = $cs_arr["black_market"];
	$cs_refuel = $cs_arr["refuel"];
	$cs_repair = $cs_arr["repair"];
	$cs_rearm = $cs_arr["rearm"];

	$cs_facilities = array(	"a shipyard" => $cs_shipyard,
		"outfitting" => $cs_outfitting,
		"a commodities market" => $cs_commodities_market,
		"a black market" => $cs_black_market,
		"refuel" => $cs_refuel,
		"repair" => $cs_repair,
		"restock" => $cs_rearm);

	$count = 0;
	foreach ($cs_facilities as $cs_name => $cs_included)
	{
		if ($cs_included == 1)
		{
			$count++;
		}
	}

	$cs_services = "";
	$i = 0;
	foreach ($cs_facilities as $cs_name => $cs_included)
	{
		if ($cs_included == 1)
		{
			if ($i == $count-1)
			{
				$cs_services .= ", and ";
			}
			elseif ($i != 0 && $i != $count-1)
			{
				$cs_services .= ", ";
			}
			else
			{
				$cs_services .= ", and is equipped with ";
			}

			$cs_services .= $cs_name;
			$i++;
		}
	}

	$article = "";
	if (!empty($cs_type))
	{
		if (preg_match('/([aeiouAEIOU])/', $cs_type{0}))
		{
			$article = "an";
		}
		else
		{
			$article = "a";
		}
	}

	if ($cs_distance == 0)
	{
		echo 'The nearest spaceport is in this system. ';
	}
	else
	{
		echo 'The nearest spaceport is in the ' . $cs_system . ' system, ' . number_format($cs_distance, 1) . ' light years away.';
	}

	echo ' ' . $cs_station_name;
	if (!empty($cs_type))
	{
		$cs_type = str_ireplace("Unknown Planetary", "unknown planetary port", $cs_type);
		echo ' is ' . $article . ' ' . $cs_type;
	}
	if ($cs_ls_from_star != 0)
	{
		echo ' ' . number_format($cs_ls_from_star) . ' light seconds away from the main star';
	}

	echo '. It has ' . $cs_max_landing_pad_size . ' sized landing pads';

	echo $cs_services;

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}

/*
*	Random Musings
*/

if (isset($_GET["rm"]))
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, text
														FROM edtb_musings
														WHERE used = '0'
														ORDER BY rand()
														LIMIT 1")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$arr = mysqli_fetch_assoc($res);

	$rm_id = $arr["id"];
	$rm_text = $arr["text"];
	echo $rm_text;

	mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_musings
												SET used = '1'
												WHERE id = '" . $rm_id . "'
												LIMIT 1")
												or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}

/*
*	current system short
*/

if (isset($_GET["sys_short"]))
{
	$va_text .= "unknown";
	if (!empty($curSys["name"]))
	{
		$va_text = $curSys["name"];
	}

	echo tts_override($va_text);

	exit;
}

/*
*	distance to X
*/

if (isset($_GET["dist"]))
{
	$to = $_GET["dist"];

	$distance = "";

	//write_log($to);

	$to = str_replace("system", "", $to);

	if (system_exists($to))
	{
		if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
		{
			$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																sqrt(pow((IFNULL(edtb_systems.x, user_systems_own.x)-(" . $curSys["x"] . ")),2)+pow((IFNULL(edtb_systems.y, user_systems_own.y)-(" . $curSys["y"] . ")),2)+pow((IFNULL(edtb_systems.z, user_systems_own.z)-(" . $curSys["z"] . ")),2))
																AS distance
																FROM edtb_systems
																LEFT JOIN user_systems_own ON edtb_systems.name = user_systems_own.name
																WHERE edtb_systems.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $to) . "'
																LIMIT 1")
																or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			$arr = mysqli_fetch_assoc($res);
			$distance = number_format($arr["distance"], 1);
		}
	}
	else
	{
		$distance = "I'm sorry, I didn't get that.";
	}

	echo $distance;

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}

/*
*	curSys access
*/

if (isset($_GET["curSys"]))
{
	$search = $_GET["curSys"];

	$info = "";

	if (array_key_exists($search, $curSys))
	{
		$info = $curSys[$search] == "" ? "None" : $curSys[$search];
	}
	else
	{
		$info = "" . $search . " is not recognised";
	}

	echo $info;

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}
