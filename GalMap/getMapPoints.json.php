<?php
/**
 * Ajax backend file to fetch map points for Galaxy Map
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @require congig */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

Header("content-type: application/json");

$last_system_name = $curSys["name"];
if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
    // get last known coordinates
    $last_coords = last_known_system();

    $curSys["x"] = $last_coords["x"];
    $curSys["y"] = $last_coords["y"];
    $curSys["z"] = $last_coords["z"];

    $last_system_name = $last_coords["name"];
}

$data = "";
$data_start = '{"categories":{';
if ($settings["galmap_show_visited_systems"] == "true") {
    $data_start .= '"Visited Systems":{"1":{"name":"Empire","color":"e7d884"},"2":{"name":"Federation","color":"FFF8E6"},"3":{"name":"Alliance","color":"09b4f4"},"21":{"name":"Independent","color":"34242F"},"99":{"name":"Rest","color":"8c8c8c"}},';
}
$data_start .= '"Other":{"5":{"name":"Current location","color":"FF0000"},';

if ($settings["galmap_show_bookmarks"] == "true") {
    $data_start .= '"6":{"name":"Bookmarked systems","color":"F7E707"},';
}
if ($settings["galmap_show_pois"] == "true") {
    $data_start .= '"7":{"name":"Points of interest, unvisited","color":"E87C09"},"8":{"name":"Points of interest, visited","color":"00FF1E"},';
}
if ($settings["galmap_show_rares"] == "true") {
    $data_start .= '"10":{"name":"Rare commodities","color":"8B9F63"},';
}
$data_start .= '"11":{"name":"Logged systems","color":"2938F8"}}}, "systems":[';

$last_row = "";

/**
 * fetch visited systems data for the map
 */
if ($settings["galmap_show_visited_systems"] == "true") {
    $query = "  SELECT
                user_visited_systems.system_name AS system_name, user_visited_systems.visit,
                edtb_systems.x, edtb_systems.y, edtb_systems.z, edtb_systems.id AS sysid, edtb_systems.allegiance
                FROM user_visited_systems
                LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                GROUP BY user_visited_systems.system_name
                ORDER BY user_visited_systems.visit ASC";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = "";

        $name = $row->system_name;

        $sysid = $row->sysid;
        // coordinates
        $vs_coordx = $row->x;
        $vs_coordy = $row->y;
        $vs_coordz = $row->z;

        /**
         * if coords are not set, see if user has calculated them
         */
        if (!valid_coordinates($vs_coordx, $vs_coordy, $vs_coordz)) {
            $esc_name = $mysqli->real_escape_string($name);

            $query = "  SELECT x, y, z
                        FROM user_systems_own
                        WHERE name = '$esc_name'
                        LIMIT 1";

            $coord_res = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $coord_res->fetch_object();

            $vs_coordx = $obj->x == "" ? "" : $obj->x;
            $vs_coordy = $obj->y == "" ? "" : $obj->y;
            $vs_coordz = $obj->z == "" ? "" : $obj->z;
        }

        /**
         * if we now have valid coordinates, get on with it
         */
        if (valid_coordinates($vs_coordx, $vs_coordy, $vs_coordz)) {
            $allegiance = $row->allegiance;
            $visit = $row->visit;
            $visit_og = $row->visit;

            switch ($allegiance) {
                case "Empire":
                    $cat = ',"cat":[1]';
                    break;
                case "Alliance":
                    $cat = ',"cat":[3]';
                    break;
                case "Federation":
                    $cat = ',"cat":[2]';
                    break;
                case "Independent":
                    $cat = ',"cat":[21]';
                    break;
                default:
                    $cat = ',"cat":[99]';
            }

            $info .= '<div class="map_info"><span class="map_info_title">Visited system</span><br />';

            if (isset($visit)) {
                $visit = date_create($visit);
                $visit_date = date_modify($visit, "+1286 years");

                $visit = date_format($visit_date, "d.m.Y, H:i");

                $visit_unix = strtotime($visit_og);
                $visit_ago = get_timeago($visit_unix);

                $info .= '<strong>First visit</strong><br />' . $visit . ' (' . $visit_ago . ')<br />';
            }

            $info .= '</div>';

            if (isset($name) && isset($vs_coordx) && isset($vs_coordy) && isset($vs_coordz)) {
                $data = '{"name":"' . $name  . '"' . $cat . ',"coords":{"x":' . $vs_coordx . ',"y":' . $vs_coordy . ',"z":' . $vs_coordz . '},"infos":' . json_encode($info) . '}' . $last_row;
            } else {
                $data = $last_row;
            }

            $last_row = "," . $data;
        }
    }
    $result->close();
}

/**
 *  fetch point of interest data for the map
 */
if ($settings["galmap_show_pois"] == "true") {
    $query = "  SELECT user_poi.poi_name, user_poi.system_name,
                user_poi.x, user_poi.y, user_poi.z, user_poi.text,
                user_poi_categories.name AS category_name
                FROM user_poi
                LEFT JOIN user_poi_categories ON user_poi.category_id = user_poi_categories.id
                WHERE user_poi.x != '' AND user_poi.y != '' AND user_poi.z != ''";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = "";
        $cat = "";
        $name = $row->system_name;

        if (strtolower($name) != strtolower($curSys["name"])) {
            $esc_name = $mysqli->real_escape_string($name);
            $disp_name = $row->system_name;
            $poi_name = $row->poi_name;
            $text = $row->text;
            $category_name = $row->category_name;

            $poi_coordx = $row->x;
            $poi_coordy = $row->y;
            $poi_coordz = $row->z;

            $query = "  SELECT id, visit
                        FROM user_visited_systems
                        WHERE system_name = '$esc_name'
                        ORDER BY visit ASC
                        LIMIT 1";

            $visited = $mysqli->query($query)->num_rows;

            $cat = $visited > 0 ? ',"cat":[8]' :  ',"cat":[7]';

            $info .= '<div class="map_info"><span class="map_info_title">Point of Interest</span><br />';
            $info .= $category_name == "" ? "" : '<strong>Category</strong><br />' . $category_name . '<br /><br />';
            $info .= $poi_name == "" ? "" : '<strong>Name</strong><br />' . $poi_name . '<br /><br />';
            $info .= $text == "" ? "" : '<strong>Comment</strong><br />' . $text . '<br />';

            $info .= '</div>';

            $data = '{"name":"' . $disp_name  . '"' . $cat . ',"coords":{"x":' . $poi_coordx . ',"y":' . $poi_coordy . ',"z":' . $poi_coordz . '},"infos":' . json_encode($info) . '}' . $last_row;

            $last_row = "," . $data;
        }
    }
    $result->close();
}

/**
 *  fetch bookmark data for the map
 */
if ($settings["galmap_show_bookmarks"] == "true") {
    $query = "  SELECT user_bookmarks.comment, user_bookmarks.added_on,
                edtb_systems.name AS system_name, edtb_systems.x, edtb_systems.y, edtb_systems.z,
                user_bm_categories.name AS category_name
                FROM user_bookmarks
                LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
                LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = "";
        $cat = "";
        $bm_system_name = $row->system_name;

        // coordinates
        $bm_coordx = $row->x;
        $bm_coordy = $row->y;
        $bm_coordz = $row->z;

        /**
         * if coords are not set, see if user has calculated them
         */
        if (!valid_coordinates($bm_coordx, $bm_coordy, $bm_coordz)) {
            $esc_name = $mysqli->real_escape_string($bm_system_name);
            $query = "  SELECT x, y, z
                        FROM user_systems_own
                        WHERE name = '$esc_name'
                        LIMIT 1";

            $coord_res = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $coord_res->fetch_object();

            $bm_coordx = $obj->x == "" ? "" : $obj->x;
            $bm_coordy = $obj->y == "" ? "" : $obj->y;
            $bm_coordz = $obj->z == "" ? "" : $obj->z;

            $coord_res->close();
        }

        if (valid_coordinates($bm_coordx, $bm_coordy, $bm_coordz)) {
            if (strtolower($bm_system_name) != strtolower($curSys["name"])) {
                $bm_comment = $row->comment;
                $bm_added_on = $row->added_on;
                $bm_category_name = $row->category_name;

                $cat = ',"cat":[6]';

                $info .= '<div class="map_info"><span class="map_info_title">Bookmarked System</span><br />';

                if (isset($bm_added_on)) {
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

                $data = '{"name":"' . $bm_system_name  . '"' . $cat . ',"coords":{"x":' . $bm_coordx . ',"y":' . $bm_coordy . ',"z":' . $bm_coordz . '},"infos":' . json_encode($info) . '}' . $last_row;
                $last_row = "," . $data;
            }
        }
    }
    $result->close();
}

/**
 *  fetch rares data for the map
 */
if ($settings["galmap_show_rares"] == "true") {
    $query = "  SELECT
                edtb_rares.item, edtb_rares.station, edtb_rares.system_name, edtb_rares.ls_to_star,
                edtb_systems.x, edtb_systems.y, edtb_systems.z
                FROM edtb_rares
                LEFT JOIN edtb_systems ON edtb_rares.system_name = edtb_systems.name
                WHERE edtb_rares.system_name != ''";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = "";
        $cat = "";
        $rare_system = $row->system_name;

        // coordinates
        $rare_coordx = $row->x;
        $rare_coordy = $row->y;
        $rare_coordz = $row->z;

        if (strtolower($rare_system) != strtolower($curSys["name"]) && valid_coordinates($rare_coordx, $rare_coordy, $rare_coordz)) {
            $rare_item = $row->item;
            $rare_station = $row->station;
            $rare_dist_to_star = number_format($row->ls_to_star);
            $rare_disp_name = $rare_system;

            $cat = ',"cat":[10]';

            $info .= '<div class="map_info"><span class="map_info_title">Rare Commodity</span><br />';
            $info .= '<strong>Rare commodity</strong><br />' . $rare_item . '<br /><br />';
            $info .= '<strong>Station</strong><br />' . $rare_station . '<br /><br />';
            $info .= '<strong>Distance from star</strong><br />' . number_format($rare_dist_to_star) . ' ls';

            $info .= '</div>';

            $data = '{"name":"' . $rare_disp_name  . '"' . $cat . ',"coords":{"x":' . $rare_coordx . ',"y":' . $rare_coordy . ',"z":' . $rare_coordz . '},"infos":' . json_encode($info) . '}' . $last_row;

            $last_row = "," . $data;
        }
    }
    $result->close();
}

/**
 *  fetch logged systems data for the map
 */
$query = "  SELECT user_log.id, user_log.stardate, user_log.log_entry, user_log.system_name,
            edtb_systems.x, edtb_systems.y, edtb_systems.z
            FROM user_log
            LEFT JOIN edtb_systems ON user_log.system_name = edtb_systems.name
            WHERE user_log.system_name != ''";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

while ($row = $result->fetch_object()) {
    $info = "";
    $cat = "";
    $log_system = $row->system_name;

    // coordinates
    $log_coordx = $row->x;
    $log_coordy = $row->y;
    $log_coordz = $row->z;

    if (!valid_coordinates($log_coordx, $log_coordy, $log_coordz)) {
        $esc_log_sys_name = $mysqli->real_escape_string($log_system);

        $esc_name = $mysqli->real_escape_string($log_system);
        $query = "  SELECT x, y, z
                    FROM user_systems_own
                    WHERE name = '$esc_log_sys_name'
                    LIMIT 1";

        $coord_res = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $obj = $coord_res->fetch_object();

        $log_coordx = $obj->x == "" ? "" : $obj->x;
        $log_coordy = $obj->y == "" ? "" : $obj->y;
        $log_coordz = $obj->z == "" ? "" : $obj->z;

        $coord_res->close();
    }

    if (valid_coordinates($log_coordx, $log_coordy, $log_coordz)) {
        $log_date = $row->stardate;
        $date = date_create($log_date);
        $log_added = date_modify($date, "+1286 years");
        $text = $row->log_entry;

        if (mb_strlen($text) > 40) {
            $text = substr($text, 0, 40) . "...";
        }

        $text = !empty($text) ? $text : "No entry";

        $cat = ',"cat":[11]';

        $info .= '<div class="map_info"><span class="map_info_title">Logged System</span><br />';
        $info .= '<strong>Log entry</strong><br /><a href="/Log?system=' . urlencode($log_system) . '" style="color:inherit;font-weight:700" title="View the log for this system">' . $text . ' </a><br /><br />';

        $info .= '<strong>Added</strong><br />' . date_format($log_added, "j M Y, H:i") . '';

        $info .= '</div>';

        $data = '{"name":"' . $log_system  . '"' . $cat . ',"coords":{"x":' . $log_coordx . ',"y":' . $log_coordy . ',"z":' . $log_coordz . '},"infos":' . json_encode($info) . '}' . $last_row;

        $last_row = "," . $data;
    }
}
$result->close();

//$info = '</div>';
$cur_sys_data = "";

if (strtolower($last_system_name) == strtolower($curSys["name"]) && valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
    $comma = !empty($data) ? "," : "";
    $cur_sys_data = $comma . '{"name":"' . $curSys["name"]  . '","cat":[5],"coords":{"x":' . $curSys["x"] . ',"y":' . $curSys["y"] . ',"z":' . $curSys["z"] . '}}';
}

$data = $data_start . $data . $cur_sys_data . "]}";

$map_json = $_SERVER["DOCUMENT_ROOT"] . "/GalMap/map_points.json";
file_put_contents($map_json, $data);

edtb_common("last_map_update", "unixtime", true, time());
