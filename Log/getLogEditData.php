<?php
/**
 * Ajax backend file to fetch log edit data
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

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");

$log_id = 0 + $_GET["logid"];

$query = "  SELECT user_log.id, user_log.system_id, user_log.system_name AS log_system_name,
            user_log.station_id, user_log.log_entry, user_log.stardate, user_log.title,
            user_log.weight, user_log.pinned, user_log.type, user_log.audio,
            edtb_systems.name AS system_name,
            edtb_stations.name AS station_name
            FROM user_log
            LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
            LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
            WHERE user_log.id = '$log_id'
            LIMIT 1";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

$obj = $result->fetch_object();

$data = [];
$data["edit_id"] = $obj->id;
$data["system_1"] = $obj->system_name == "" ? $obj->log_system_name : $obj->system_name;
$data["statname"] = $obj->station_name;
$data["html"] = $obj->log_entry;
$data["log_type"] = $obj->type;
$data["title"] = $obj->title;
$data["pinned"] = $obj->pinned;
$data["weight"] = $obj->weight;
$data["audiofiles"] = $obj->audio;

$result->close();

echo json_encode($data);
