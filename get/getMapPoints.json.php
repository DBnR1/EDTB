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
 * Ajax backend file to fetch map points for Galaxy Map
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
Header("content-type: application/json");

$last_system_name = $curSys["name"];
if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
{
	// get last known coordinates
	$last_coords = last_known_system();

	$curSys["x"] = $last_coords["x"];
	$curSys["y"] = $last_coords["y"];
	$curSys["z"] = $last_coords["z"];

	$last_system_name = $last_coords["name"];
}

$data = "";
$data_start = '{"categories":{';
if ($settings["galmap_show_visited_systems"] == "true")
{
	$data_start .= '"Visited Systems":{"1":{"name":"Empire","color":"e7d884"},"2":{"name":"Federation","color":"FFF8E6"},"3":{"name":"Alliance","color":"09b4f4"},"21":{"name":"Independent","color":"34242F"},"99":{"name":"Rest","color":"8c8c8c"}},';
}
$data_start .= '"Other":{"5":{"name":"Current location","color":"FF0000"},';

if ($settings["galmap_show_bookmarks"] == "true")
{
	$data_start .= '"6":{"name":"Bookmarked systems","color":"F7E707"},';
}
if ($settings["galmap_show_pois"] == "true")
{
	$data_start .= '"7":{"name":"Points of interest, unvisited","color":"E87C09"},"8":{"name":"Points of interest, visited","color":"00FF1E"},';
}
if ($settings["galmap_show_rares"] == "true")
{
	$data_start .= '"10":{"name":"Rare commodities","color":"8B9F63"},';
}
$data_start .= '"11":{"name":"Logged systems","color":"2938F8"}}}, "systems":[';

$last_row = "";

/*
*	fetch visited systems data for the map
*/

if ($settings["galmap_show_visited_systems"] == "true")
{
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT
														user_visited_systems.system_name AS system_name, user_visited_systems.visit,
														edtb_systems.x, edtb_systems.y, edtb_systems.z, edtb_systems.id AS sysid, edtb_systems.allegiance
														FROM user_visited_systems
														LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
														GROUP BY user_visited_systems.system_name
														ORDER BY user_visited_systems.visit ASC")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($row = mysqli_fetch_array($result))
	{
		$info = "";

		$name = $row["system_name"];

		$sysid = $row["sysid"];
		// coordinates
		$vs_coordx = $row["x"];
		$vs_coordy = $row["y"];
		$vs_coordz = $row["z"];

		/*
		*	if coords are not set, see if user has calculated them
		*/

		if (!valid_coordinates($vs_coordx, $vs_coordy, $vs_coordz))
		{
			$cb_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT x, y, z
																FROM user_systems_own
																WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $name) . "'
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			$cb_arr = mysqli_fetch_assoc($cb_res);

			$vs_coordx = $cb_arr["x"] == "" ? "" : $cb_arr["x"];
			$vs_coordy = $cb_arr["y"] == "" ? "" : $cb_arr["y"];
			$vs_coordz = $cb_arr["z"] == "" ? "" : $cb_arr["z"];
		}

		if (valid_coordinates($vs_coordx, $vs_coordy, $vs_coordz))
		{
			$allegiance = $row["allegiance"];
			$visit = $row["visit"];
			$visit_og = $row["visit"];

			if ($allegiance == "Federation")
			{
				$cat = ',"cat":[2]';
			}
			elseif ($allegiance == "Alliance")
			{
				$cat = ',"cat":[3]';
			}
			elseif ($allegiance == "Empire")
			{
				$cat = ',"cat":[1]';
			}
			elseif ($allegiance == "Independent")
			{
				$cat = ',"cat":[21]';
			}
			else
			{
				$cat = ',"cat":[99]';
			}

			$info .= '<div class="map_info"><span class="map_info_title">Visited system</span><br />';

			if (isset($visit))
			{
				$visit = date_create($visit);
				$visit_date = date_modify($visit, "+1286 years");

				$visit = date_format($visit_date, "d.m.Y, H:i");

				$visit_unix = strtotime($visit_og);
				$visit_ago = get_timeago($visit_unix);

				$info .= '<strong>First visit</strong><br />' . $visit . ' (' . $visit_ago . ')<br />';
			}

			$info .= '</div>';

			if (isset($name) && isset($vs_coordx) && isset($vs_coordy) && isset($vs_coordz))
			{
				$data = '{"name":"' . $name  . '"' . $cat . ',"coords":{"x":' . $vs_coordx . ',"y":' . $vs_coordy . ',"z":' . $vs_coordz . '},"infos":' . json_encode($info) . '}' . $last_row . '';
			}
			else
			{
				$data = $last_row;
			}

			$last_row = "," . $data . "";
		}
	}
}

/*
*	 fetch point of interest data for the map
*/

if ($settings["galmap_show_pois"] == "true")
{
	$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT user_poi.poi_name, user_poi.system_name,
														user_poi.x, user_poi.y, user_poi.z, user_poi.text,
														user_poi_categories.name AS category_name
														FROM user_poi
														LEFT JOIN user_poi_categories ON user_poi.category_id = user_poi_categories.id
														WHERE user_poi.x != '' AND user_poi.y != '' AND user_poi.z != ''")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($row = mysqli_fetch_array($result))
	{
		$info = "";
		$cat = "";
		$name = $row["system_name"];

		if (strtolower($name) != strtolower($curSys["name"]))
		{
			$disp_name = $row["system_name"];
			$poi_name = $row["poi_name"];
			$text = $row["text"];
			$category_name = $row["category_name"];

			$poi_coordx = $row["x"];
			$poi_coordy = $row["y"];
			$poi_coordz = $row["z"];

			$visitres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, visit
																	FROM user_visited_systems
																	WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $name) . "'
																	ORDER BY visit ASC
																	LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
			$visited = mysqli_num_rows($visitres);

			if ($visited > 0)
			{
				$cat = ',"cat":[8]';
			}
			else
			{
				$cat = ',"cat":[7]';
			}

			$info .= '<div class="map_info"><span class="map_info_title">Point of Interest</span><br />';
			$info .= $category_name == "" ? "" : '<strong>Category</strong><br />' . $category_name . '<br /><br />';
			$info .= $poi_name == "" ? "" : '<strong>Name</strong><br />' . $poi_name . '<br /><br />';
			$info .= $text == "" ? "" : '<strong>Comment</strong><br />' . $text . '<br />';

			$info .= '</div>';

			$data = '{"name":"' . $disp_name  . '"' . $cat . ',"coords":{"x":' . $poi_coordx . ',"y":' . $poi_coordy . ',"z":' . $poi_coordz . '},"infos":' . json_encode($info) . '}' . $last_row . '';

			$last_row = "," . $data . "";
		}
	}
}

/*
*	 fetch bookmark data for the map
*/

if ($settings["galmap_show_bookmarks"] == "true")
{
	$bm_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT user_bookmarks.comment, user_bookmarks.added_on,
															edtb_systems.name AS system_name, edtb_systems.x, edtb_systems.y, edtb_systems.z,
															user_bm_categories.name AS category_name
															FROM user_bookmarks
															LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
															LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);


	while ($bm_row = mysqli_fetch_array($bm_result))
	{
		$info = "";
		$cat = "";
		$bm_system_name = $bm_row["system_name"];

		// coordinates
		$bm_coordx = $bm_row["x"];
		$bm_coordy = $bm_row["y"];
		$bm_coordz = $bm_row["z"];

		/*
		*	if coords are not set, see if user has calculated them
		*/

		if (!valid_coordinates($bm_coordx, $bm_coordy, $bm_coordz))
		{
			$bb_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT x, y, z
																FROM user_systems_own
																WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_system_name) . "'
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			$bb_arr = mysqli_fetch_assoc($bb_res);

			$bm_coordx = $bb_arr["x"] == "" ? "" : $bb_arr["x"];
			$bm_coordy = $bb_arr["y"] == "" ? "" : $bb_arr["y"];
			$bm_coordz = $bb_arr["z"] == "" ? "" : $bb_arr["z"];
		}

		if (valid_coordinates($bm_coordx, $bm_coordy, $bm_coordz))
		{
			if (strtolower($bm_system_name) != strtolower($curSys["name"]))
			{
				$bm_comment = $bm_row["comment"];
				$bm_added_on = $bm_row["added_on"];
				$bm_category_name = $bm_row["category_name"];

				$cat = ',"cat":[6]';

				$info .= '<div class="map_info"><span class="map_info_title">Bookmarked System</span><br />';

				if (isset($bm_added_on))
				{
					$bm_added_on_og = $bm_added_on;
					$bm_added_on = gmdate("Y-m-d\TH:i:s\Z", $bm_added_on);
					$bm_added_on = date_create($bm_added_on);
					$bm_added_on_date = date_modify($bm_added_on, "+1286 years");

					$bm_added_on = date_format($bm_added_on_date, "d.m.Y, H:i");

					$bm_added_on_ago = get_timeago($bm_added_on_og);

					$info .= '<strong>Bookmarked on</strong><br />' . $bm_added_on . ' (' . $bm_added_on_ago . ')<br /><br />';
				}
				$info .= $bm_category_name == "" ? "" : '<strong>Category</strong><br />' . $bm_category_name . '<br /><br />';
				$info .= $bm_comment == "" ? "" : '<strong>Comment</strong><br />' . $bm_comment . '<br /><br />';

				$info .= '</div>';

				$data = '{"name":"' . $bm_system_name  . '"' . $cat . ',"coords":{"x":' . $bm_coordx . ',"y":' . $bm_coordy . ',"z":' . $bm_coordz . '},"infos":' . json_encode($info) . '}' . $last_row . '';
				$last_row = "," . $data . "";
			}
		}
	}
}

/*
*	 fetch rares data for the map
*/

if ($settings["galmap_show_rares"] == "true")
{
	$rare_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT
																edtb_rares.item, edtb_rares.station, edtb_rares.system_name, edtb_rares.ls_to_star,
																edtb_systems.x, edtb_systems.y, edtb_systems.z
																FROM edtb_rares
																LEFT JOIN edtb_systems ON edtb_rares.system_name = edtb_systems.name
																WHERE edtb_rares.system_name != ''")
																or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	while ($rare_row = mysqli_fetch_array($rare_result))
	{
		$info = "";
		$cat = "";
		$rare_system = $rare_row["system_name"];

		// coordinates
		$rare_coordx = $rare_row["x"];
		$rare_coordy = $rare_row["y"];
		$rare_coordz = $rare_row["z"];

		if (strtolower($rare_system) != strtolower($curSys["name"]) && valid_coordinates($rare_coordx, $rare_coordy, $rare_coordz))
		{
			$rare_item = $rare_row["item"];
			$rare_station = $rare_row["station"];
			$rare_dist_to_star = number_format($rare_row["ls_to_star"]);
			$rare_disp_name = $rare_system;


			$cat = ',"cat":[10]';

			$info .= '<div class="map_info"><span class="map_info_title">Rare Commodity</span><br />';
			$info .= '<strong>Rare commodity</strong><br />' . $rare_item . '<br /><br />';
			$info .= '<strong>Station</strong><br />' . $rare_station . '<br /><br />';
			$info .= '<strong>Distance from star</strong><br />' . number_format($rare_dist_to_star) . ' ls';

			$info .= '</div>';

			$data = '{"name":"' . $rare_disp_name  . '"' . $cat . ',"coords":{"x":' . $rare_coordx . ',"y":' . $rare_coordy . ',"z":' . $rare_coordz . '},"infos":' . json_encode($info) . '}' . $last_row . '';

			$last_row = "," . $data . "";
		}
	}
}

/*
*	 fetch logged systems data for the map
*/

$log_result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT user_log.id, user_log.stardate, user_log.log_entry, user_log.system_name,
															edtb_systems.x, edtb_systems.y, edtb_systems.z
															FROM user_log
															LEFT JOIN edtb_systems ON user_log.system_name = edtb_systems.name
															WHERE user_log.system_name != ''")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

while ($log_row = mysqli_fetch_array($log_result))
{
	$info = "";
	$cat = "";
	$log_system = $log_row["system_name"];

	// coordinates
	$log_coordx = $log_row["x"];
	$log_coordy = $log_row["y"];
	$log_coordz = $log_row["z"];

	if (!valid_coordinates($log_coordx, $log_coordy, $log_coordz))
	{
		$lb_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT x, y, z
															FROM user_systems_own
															WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $log_system) . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$lb_arr = mysqli_fetch_assoc($lb_res);

		$log_coordx = $lb_arr["x"] == "" ? "" : $lb_arr["x"];
		$log_coordy = $lb_arr["y"] == "" ? "" : $lb_arr["y"];
		$log_coordz = $lb_arr["z"] == "" ? "" : $lb_arr["z"];
	}

	if (valid_coordinates($log_coordx, $log_coordy, $log_coordz))
	{
		$log_date = $log_row["stardate"];
		$date = date_create($log_date);
		$log_added = date_modify($date, "+1286 years");
		$text = $log_row["log_entry"];

		if (mb_strlen($text) > 40)
		{
			$text = "" . substr($text, 0, 40) . "...";
		}

		$cat = ',"cat":[11]';

		$info .= '<div class="map_info"><span class="map_info_title">Logged System</span><br />';
		$info .= '<strong>Log entry</strong><br /><a href="/log.php?system=' . urlencode($log_system) . '" style="color:inherit;font-weight:bold" title="View the log for this system">' . $text . ' </a><br /><br />';

		$info .= '<strong>Added</strong><br />' . date_format($log_added, "j M Y, H:i") . '';

		$info .= '</div>';

		$data = '{"name":"' . $log_system  . '"' . $cat . ',"coords":{"x":' . $log_coordx . ',"y":' . $log_coordy . ',"z":' . $log_coordz . '},"infos":' . json_encode($info) . '}' . $last_row . '';

		$last_row = "," . $data . "";
	}
}

//$info = '</div>';
$cur_sys_data = "";

if (strtolower($last_system_name) == strtolower($curSys["name"]) && valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
{
	$cur_sys_data = ',{"name":"' . $curSys["name"]  . '","cat":[5],"coords":{"x":' . $curSys["x"] . ',"y":' . $curSys["y"] . ',"z":' . $curSys["z"] . '}}';
}

$data = "" . $data_start . "" . $data . "" . $cur_sys_data . "]}";
$map_json = "" . $_SERVER["DOCUMENT_ROOT"] . "/map_points.json";
file_put_contents($map_json, $data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
