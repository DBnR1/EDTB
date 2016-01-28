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
 * Ajax backend file to fetch log edit data
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$log_id = $_GET["logid"];

$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_log.id, user_log.system_id, user_log.system_name AS log_system_name,
														user_log.station_id, user_log.log_entry, user_log.stardate,
														edtb_systems.name AS system_name,
														edtb_stations.name AS station_name
														FROM user_log
														LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
														LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
														WHERE user_log.id = '" . $log_id . "'
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
$log_arr = mysqli_fetch_assoc($log_res);

$data = array();
$data["edit_id"] = $log_arr["id"];
$data["system_1"] = $log_arr["system_name"] == "" ? $log_arr["log_system_name"] : $log_arr["system_name"];
$data["statname"] = $log_arr["station_name"];
$data["html"] = $log_arr["log_entry"];

echo json_encode($data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
