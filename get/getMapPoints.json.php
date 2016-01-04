<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2015 Mauri Kujala (contact@edtb.xyz)
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

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
Header("content-type: application/json");

if (!is_numeric($coordx))
{
	// get last known coordinates
	$last_coords = last_known_system();

	$coordx = $last_coords["x"];
	$coordy = $last_coords["y"];
	$coordz = $last_coords["z"];

	$disclaimer = "<p><b>No coordinates for current location, last known location used</b></p>";
}
else
{
	$disclaimer = "";
}

$data = "";
$last_row = "";

?>
{
	"categories":
	{
		"Allegiances":
		{
			"1":
			{
				"name":"Empire",
				"color":"e7d884"
			},
			"2":
			{
				"name":"Federation",
				"color":"8c8c8c"
			},
			"3":
			{
				"name":"Alliance",
				"color":"09b4f4"
			},
			"4":
			{
				"name":"Visited",
				"color":"D9DADB"
			},
			"5":
			{
				"name":"Current",
				"color":"FF0000"
			},
			"6":
			{
				"name":"Bookmarked",
				"color":"F7E707"
			},
			"7":
			{
				"name":"Point of interest",
				"color":"E87C09"
			},
			"8":
			{
				"name":"Point of interest, visited",
				"color":"00FF1E"
			},
			"9":
			{
				"name":"Sol",
				"color":"FF0000"
			},
			"10":
			{
				"name":"Rare",
				"color":"00FFBF"
			},
			"11":
			{
				"name":"Logged",
				"color":"ccc"
			},
			"12":
			{
				"name":"Rest",
				"color":"5C5E5E"
			}
		}
	}, "systems":[
<?php

// fetch visited systems data for the map
if ($settings["galmap_show_visited_systems"] == "true")
{
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT DISTINCT
														user_visited_systems.system_name AS system_name,
														edtb_systems.x, edtb_systems.y, edtb_systems.z, edtb_systems.id AS sysid, edtb_systems.allegiance
														FROM user_visited_systems
														LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
														ORDER BY user_visited_systems.visit ASC");

	while ($row = mysqli_fetch_array($result))
	{
		$name = $row["system_name"];
		$sysid = $row["sysid"];

		// coordinates for distance calculations
		$vs_coordx = $row["x"];
		$vs_coordy = $row["y"];
		$vs_coordz = $row["z"];

		if ($vs_coordx != "")
		{
			//$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id FROM user_log WHERE system_id = '" . $sysid . "' LIMIT 1"));

			$allegiance = $row["allegiance"];

			if ($allegiance == "Federation")
			{
				$cat = ',"cat": [2]';
			}
			else if ($allegiance == "Alliance")
			{
				$cat = ',"cat": [3]';
			}
			else if ($allegiance == "Empire")
			{
				$cat = ',"cat": [1]';
			}
			else
			{
				$cat = ',"cat": [12]';
			}

			if ($name == $current_system)
			{
				$cat = ',"cat": [5]';
			}

			/*if ($logged == 1 && $name != $current_system)
			{
				$cat = ',"cat": [11]';
			}
			else if ($name == $current_system)
			{
				$cat = ',"cat": [5]';
			}
			else
			{
				$cat = ',"cat": [12]';
			}*/

			if (isset($name) && isset($vs_coordx))
			{
				$data = '{"name": "' . $name  . '"' . $cat . ',"coords": {"x": ' . $vs_coordx . ',"y": ' . $vs_coordy . ',"z": ' . $vs_coordz . '}}' . $last_row . '';
			}
			else
			{
				$data = $last_row;
			}

			$last_row = "," . $data . "";
		}
	}
}

// fetch point of interest data for the map
if ($settings["galmap_show_pois"] == "true")
{
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT poi_name, system_name, coordinates
															FROM user_poi
															WHERE coordinates != ''");

	while ($row = mysqli_fetch_array($result))
	{
		$name = $row["system_name"];
		$disp_name = $row["poi_name"] != "" ? $row["poi_name"] : $row["system_name"];
		$coord = $row["coordinates"];

		// parse coordinates for distance calculations
		$poi_coord = explode(",", $coord);
		$poi_coordx = $poi_coord[0];
		$poi_coordy = $poi_coord[1];
		$poi_coordz = $poi_coord[2];

		if ($coordx != "")
		{
			$distance_from_current = sqrt(pow(($poi_coordx-($coordx)), 2)+pow(($poi_coordy-($coordy)), 2)+pow(($poi_coordz-($coordz)), 2));
		}
		else
		{
			$distance_from_current = 0;
		}

		$visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																				FROM user_visited_systems
																				WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $name) . "'
																				LIMIT 1"));
		if ($visited > 0)
		{
			$cat = ',"cat": [8]';
		}
		else
		{
			$cat = ',"cat": [7]';
		}

		$data = '{"name": "' . $disp_name  . '"' . $cat . ',"coords": {"x": ' . $poi_coordx . ',"y": ' . $poi_coordy . ',"z": ' . $poi_coordz . '}}' . $last_row . '';

		$last_row = "," . $data . "";
	}
}

// fetch bookmark data for the map
if ($settings["galmap_show_bookmarks"] == "true")
{
	$bm_result = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_bookmarks.comment, user_bookmarks.added_on,
															edtb_systems.name AS system_name, edtb_systems.x, edtb_systems.y, edtb_systems.z,
															user_bm_categories.name AS category_name
															FROM user_bookmarks
															LEFT JOIN edtb_systems ON user_bookmarks.system_id = edtb_systems.id
															LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
															WHERE edtb_systems.x != ''");


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

		$data = '{"name": "' . $bm_system_name  . '","cat": [6],"coords": {"x": ' . $bm_coordx . ',"y": ' . $bm_coordy . ',"z": ' . $bm_coordz . '}}' . $last_row . '';
		$last_row = "," . $data . "";
	}
}

// fetch rares data for the map
if ($settings["galmap_show_rares"] == "true")
{
	$rare_result = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																edtb_rares.item, edtb_rares.station, edtb_rares.system, edtb_rares.distance_to_star,
																edtb_systems.x, edtb_systems.y, edtb_systems.z
																FROM edtb_rares
																LEFT JOIN edtb_systems ON edtb_rares.system = edtb_systems.name");

	while ($rare_row = mysqli_fetch_array($rare_result))
	{
		$rare_item = $rare_row["item"];
		$rare_station = $rare_row["station"];
		$rare_system = $rare_row["system"];
		$rare_dist_to_star = number_format($rare_row["distance_to_star"]);

		$rare_disp_name = "" . $rare_item . " - " . $rare_system . " (" . $rare_station . " - " . $rare_dist_to_star . " ls)";

		// coordinates for distance calculations
		$rare_coordx = $rare_row["x"];
		$rare_coordy = $rare_row["y"];
		$rare_coordz = $rare_row["z"];

		if ($coordx != "")
		{
			$rare_distance_from_current = sqrt(pow(($rare_coordx-($coordx)), 2)+pow(($rare_coordy-($coordy)), 2)+pow(($rare_coordz-($coordz)), 2));
		}
		else
		{
			$rare_distance_from_current = 0;
		}

		$data = '{"name": "' . $rare_disp_name  . '","cat": [10],"coords": {"x": ' . $rare_coordx . ',"y": ' . $rare_coordy . ',"z": ' . $rare_coordz . '}}' . $last_row . '';

		$last_row = "," . $data . "";
	}
}

echo $data;
echo ']}';
((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);