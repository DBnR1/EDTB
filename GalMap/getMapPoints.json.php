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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/** @require congig */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';
/** @require curSys */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/curSys.php';

header('content-type: application/json');

$lastSystemName = $curSys['name'];
if (!valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
    // get last known coordinates
    $lastCoords = last_known_system();

    $curSys['x'] = $lastCoords['x'];
    $curSys['y'] = $lastCoords['y'];
    $curSys['z'] = $lastCoords['z'];

    $lastSystemName = $lastCoords['name'];
}

$data = '';
$dataStart = '{"categories":{';
if ($settings['galmap_show_visited_systems'] === 'true') {
    $dataStart .= '"Visited Systems":{"1":{"name":"Empire","color":"e7d884"},"2":{"name":"Federation","color":"FFF8E6"},"3":{"name":"Alliance","color":"09b4f4"},"21":{"name":"Independent","color":"34242F"},"99":{"name":"Rest","color":"8c8c8c"}},';
}
$dataStart .= '"Other":{"5":{"name":"Current location","color":"FF0000"},';

if ($settings['galmap_show_bookmarks'] === 'true') {
    $dataStart .= '"6":{"name":"Bookmarked systems","color":"F7E707"},';
}
if ($settings['galmap_show_pois'] === 'true') {
    $dataStart .= '"7":{"name":"Points of interest, unvisited","color":"E87C09"},"8":{"name":"Points of interest, visited","color":"00FF1E"},';
}
if ($settings['galmap_show_rares'] === 'true') {
    $dataStart .= '"10":{"name":"Rare commodities","color":"8B9F63"},';
}
$dataStart .= '"11":{"name":"Logged systems","color":"2938F8"}}}, "systems":[';

$lastRow = '';

/**
 * fetch visited systems data for the map
 */
if ($settings['galmap_show_visited_systems'] === 'true') {
    $query = '  SELECT
                user_visited_systems.system_name AS system_name, user_visited_systems.visit,
                edtb_systems.x, edtb_systems.y, edtb_systems.z, edtb_systems.id AS sysid, edtb_systems.allegiance
                FROM user_visited_systems
                LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                GROUP BY user_visited_systems.system_name
                ORDER BY user_visited_systems.visit ASC';

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = '';

        $name = $row->system_name;

        $sysid = $row->sysid;
        // coordinates
        $vsCoordx = $row->x;
        $vsCoordy = $row->y;
        $vsCoordz = $row->z;

        /**
         * if coords are not set, see if user has calculated them
         */
        if (!valid_coordinates($vsCoordx, $vsCoordy, $vsCoordz)) {
            $escName = $mysqli->real_escape_string($name);

            $query = "  SELECT x, y, z
                        FROM user_systems_own
                        WHERE name = '$escName'
                        LIMIT 1";

            $coordRes = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $coordRes->fetch_object();

            $vsCoordx = $obj->x;
            $vsCoordy = $obj->y;
            $vsCoordz = $obj->z;
        }

        /**
         * if we now have valid coordinates, get on with it
         */
        if (valid_coordinates($vsCoordx, $vsCoordy, $vsCoordz)) {
            $allegiance = $row->allegiance;
            $visit = $row->visit;
            $visitOg = $row->visit;

            switch ($allegiance) {
                case 'Empire':
                    $cat = ',"cat":[1]';
                    break;
                case 'Alliance':
                    $cat = ',"cat":[3]';
                    break;
                case 'Federation':
                    $cat = ',"cat":[2]';
                    break;
                case 'Independent':
                    $cat = ',"cat":[21]';
                    break;
                default:
                    $cat = ',"cat":[99]';
            }

            $info .= '<div class="map_info"><span class="map_info_title">Visited system</span><br>';

            if (isset($visit)) {
                $visit = date_create($visit);
                $visitDate = date_modify($visit, '+1286 years');

                $visit = date_format($visitDate, 'd.m.Y, H:i');

                $visitUnix = strtotime($visitOg);
                $visitAgo = get_timeago($visitUnix);

                $info .= '<strong>First visit</strong><br>' . $visit . ' (' . $visitAgo . ')<br>';
            }

            $info .= '</div>';

            $data = $lastRow;
            if (isset($name) && isset($vsCoordx) && isset($vsCoordy) && isset($vsCoordz)) {
                $data =
                    '{"name":"' . $name . '"' . $cat . ',"coords":{"x":' . $vsCoordx . ',"y":' . $vsCoordy . ',"z":' . $vsCoordz .
                    '},"infos":' . json_encode($info) . '}' . $lastRow;
            }

            $lastRow = ',' . $data;
        }
    }
    $result->close();
}

/**
 *  fetch point of interest data for the map
 */
if ($settings['galmap_show_pois'] === 'true') {
    $query = "  SELECT user_poi.poi_name, user_poi.system_name,
                user_poi.x, user_poi.y, user_poi.z, user_poi.text,
                user_poi_categories.name AS category_name
                FROM user_poi
                LEFT JOIN user_poi_categories ON user_poi.category_id = user_poi_categories.id
                WHERE user_poi.x != '' AND user_poi.y != '' AND user_poi.z != ''";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = '';
        $cat = '';
        $name = $row->system_name;

        if (strtolower($name) != strtolower($curSys['name'])) {
            $escName = $mysqli->real_escape_string($name);
            $dispName = $row->system_name;
            $poiName = $row->poi_name;
            $text = $row->text;
            $categoryName = $row->category_name;

            $poiCoordx = $row->x;
            $poiCoordy = $row->y;
            $poiCoordz = $row->z;

            $query = "  SELECT id, visit
                        FROM user_visited_systems
                        WHERE system_name = '$escName'
                        ORDER BY visit ASC
                        LIMIT 1";

            $visited = $mysqli->query($query)->num_rows;

            $cat = $visited > 0 ? ',"cat":[8]' : ',"cat":[7]';

            $info .= '<div class="map_info"><span class="map_info_title">Point of Interest</span><br>';
            $info .= $categoryName === '' ? '' : '<strong>Category</strong><br>' . $categoryName . '<br><br>';
            $info .= $poiName === '' ? '' : '<strong>Name</strong><br>' . $poiName . '<br><br>';
            $info .= $text === '' ? '' : '<strong>Comment</strong><br>' . $text . '<br>';

            $info .= '</div>';

            $data = '{"name":"' . $dispName . '"' . $cat . ',"coords":{"x":' . $poiCoordx . ',"y":' . $poiCoordy . ',"z":' .
                $poiCoordz . '},"infos":' . json_encode($info) . '}' . $lastRow;

            $lastRow = ',' . $data;
        }
    }
    $result->close();
}

/**
 *  fetch bookmark data for the map
 */
if ($settings['galmap_show_bookmarks'] === 'true') {
    $query = '  SELECT user_bookmarks.comment, user_bookmarks.added_on,
                edtb_systems.name AS system_name, edtb_systems.x, edtb_systems.y, edtb_systems.z,
                user_bm_categories.name AS category_name
                FROM user_bookmarks
                LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
                LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id';

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = '';
        $cat = '';
        $bmSystemName = $row->system_name;

        // coordinates
        $bmCoordx = $row->x;
        $bmCoordy = $row->y;
        $bmCoordz = $row->z;

        /**
         * if coords are not set, see if user has calculated them
         */
        if (!valid_coordinates($bmCoordx, $bmCoordy, $bmCoordz)) {
            $escName = $mysqli->real_escape_string($bmSystemName);
            $query = "  SELECT x, y, z
                        FROM user_systems_own
                        WHERE name = '$escName'
                        LIMIT 1";

            $coordRes = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $coordRes->fetch_object();

            $bmCoordx = $obj->x;
            $bmCoordy = $obj->y;
            $bmCoordz = $obj->z;

            $coordRes->close();
        }

        if (valid_coordinates($bmCoordx, $bmCoordy, $bmCoordz)) {
            if (strtolower($bmSystemName) !== strtolower($curSys['name'])) {
                $bmComment = $row->comment;
                $bmAddedOn = $row->added_on;
                $bmCategoryName = $row->category_name;

                $cat = ',"cat":[6]';

                $info .= '<div class="map_info"><span class="map_info_title">Bookmarked System</span><br>';

                if (isset($bmAddedOn)) {
                    $bmAddedOnOg = $bmAddedOn;
                    $bmAddedOn = gmdate("Y-m-d\TH:i:s\Z", $bmAddedOn);
                    $bmAddedOn = date_create($bmAddedOn);
                    $bmAddedOnDate = date_modify($bmAddedOn, '+1286 years');

                    $bmAddedOn = date_format($bmAddedOnDate, 'd.m.Y, H:i');

                    $bmAddedOnAgo = get_timeago($bmAddedOnOg);

                    $info .= '<strong>Bookmarked on</strong><br>' . $bmAddedOn . ' (' . $bmAddedOnAgo . ')<br><br>';
                }
                $info .= $bmCategoryName === '' ? '' : '<strong>Category</strong><br>' . $bmCategoryName . '<br><br>';
                $info .= $bmComment === '' ? '' : '<strong>Comment</strong><br>' . $bmComment . '<br><br>';

                $info .= '</div>';

                $data = '{"name":"' . $bmSystemName . '"' . $cat . ',"coords":{"x":' . $bmCoordx . ',"y":' . $bmCoordy . ',"z":' .
                    $bmCoordz . '},"infos":' . json_encode($info) . '}' . $lastRow;
                $lastRow = ',' . $data;
            }
        }
    }
    $result->close();
}

/**
 *  fetch rares data for the map
 */
if ($settings['galmap_show_rares'] === 'true') {
    $query = "  SELECT
                edtb_rares.item, edtb_rares.station, edtb_rares.system_name, edtb_rares.ls_to_star,
                edtb_systems.x, edtb_systems.y, edtb_systems.z
                FROM edtb_rares
                LEFT JOIN edtb_systems ON edtb_rares.system_name = edtb_systems.name
                WHERE edtb_rares.system_name != ''";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($row = $result->fetch_object()) {
        $info = '';
        $cat = '';
        $rareSystem = $row->system_name;

        // coordinates
        $rareCoordx = $row->x;
        $rareCoordy = $row->y;
        $rareCoordz = $row->z;

        if (strtolower($rareSystem) != strtolower($curSys['name']) && valid_coordinates($rareCoordx, $rareCoordy, $rareCoordz)) {
            $rareItem = $row->item;
            $rareStation = $row->station;
            $rareDistToStar = number_format($row->ls_to_star);
            $rareDispName = $rareSystem;

            $cat = ',"cat":[10]';

            $info .= '<div class="map_info"><span class="map_info_title">Rare Commodity</span><br>';
            $info .= '<strong>Rare commodity</strong><br>' . $rareItem . '<br><br>';
            $info .= '<strong>Station</strong><br>' . $rareStation . '<br><br>';
            $info .= '<strong>Distance from star</strong><br>' . number_format($rareDistToStar) . ' ls';

            $info .= '</div>';

            $data = '{"name":"' . $rareDispName . '"' . $cat . ',"coords":{"x":' . $rareCoordx . ',"y":' . $rareCoordy . ',"z":' .
                $rareCoordz . '},"infos":' . json_encode($info) . '}' . $lastRow;

            $lastRow = ',' . $data;
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
    $info = '';
    $cat = '';
    $logSystem = $row->system_name;

    // coordinates
    $logCoordx = $row->x;
    $logCoordy = $row->y;
    $logCoordz = $row->z;

    if (!valid_coordinates($logCoordx, $logCoordy, $logCoordz)) {
        $escLogSysName = $mysqli->real_escape_string($logSystem);

        $escName = $mysqli->real_escape_string($logSystem);
        $query = "  SELECT x, y, z
                    FROM user_systems_own
                    WHERE name = '$escLogSysName'
                    LIMIT 1";

        $coordRes = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $obj = $coordRes->fetch_object();

        $logCoordx = $obj->x;
        $logCoordy = $obj->y;
        $logCoordz = $obj->z;

        $coordRes->close();
    }

    if (valid_coordinates($logCoordx, $logCoordy, $logCoordz)) {
        $logDate = $row->stardate;
        $date = date_create($logDate);
        $logAdded = date_modify($date, '+1286 years');
        $text = $row->log_entry;

        if (mb_strlen($text) > 40) {
            $text = substr($text, 0, 40) . '...';
        }

        $text = !empty($text) ? $text : 'No entry';

        $cat = ',"cat":[11]';

        $info .= '<div class="map_info"><span class="map_info_title">Logged System</span><br>';
        $info .= '<strong>Log entry</strong><br><a href="/Log?system=' . urlencode($logSystem) .
            '" style="color: inherit; font-weight: 700" title="View the log for this system">' . $text . ' </a><br><br>';

        $info .= '<strong>Added</strong><br>' . date_format($logAdded, 'j M Y, H:i') . '';

        $info .= '</div>';

        $data =
            '{"name":"' . $logSystem . '"' . $cat . ',"coords":{"x":' . $logCoordx . ',"y":' . $logCoordy . ',"z":' . $logCoordz .
            '},"infos":' . json_encode($info) . '}' . $lastRow;

        $lastRow = ',' . $data;
    }
}
$result->close();

//$info = '</div>';
$curSysData = '';

if (strtolower($lastSystemName) === strtolower($curSys['name']) && valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
    $comma = !empty($data) ? ',' : '';
    $curSysData =
        $comma . '{"name":"' . $curSys['name'] . '","cat":[5],"coords":{"x":' . $curSys['x'] . ',"y":' . $curSys['y'] . ',"z":' .
        $curSys['z'] . '}}';
}

$data = $dataStart . $data . $curSysData . ']}';

$mapJson = $_SERVER['DOCUMENT_ROOT'] . '/GalMap/map_points.json';
file_put_contents($mapJson, $data);

edtb_common('last_map_update', 'unixtime', true, time());
