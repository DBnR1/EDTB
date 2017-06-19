<?php
/**
 * Ajax backend file for system and general log
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

use EDTB\Log\MakeLog;

/**
 * System logs
 */
$logdata = '';
if (!empty($curSys['name'])) {
    if (isset($_GET['slog_sort']) && $_GET['slog_sort'] !== 'undefined') {
        if ($_GET['slog_sort'] === 'asc') {
            $ssort = 'ASC';
        }
        if ($_GET['slog_sort'] === 'desc') {
            $ssort = 'DESC';
        }
    } else {
        $ssort = 'DESC';
    }

    // figure out what coords to calculate from
    $usable_coords = usable_coords();
    $usex = $usable_coords['x'];
    $usey = $usable_coords['y'];
    $usez = $usable_coords['z'];
    $exact = $usable_coords['current'] === true ? '' : ' *';

    /**
     * if log range is set to zero, only show logs from current system
     */
    if ($settings['log_range'] == 0) {
        $query = "  SELECT SQL_CACHE
                    user_log.id, user_log.system_name AS log_system_name, user_log.station_id,
                    user_log.log_entry, user_log.stardate,
                    user_log.title, user_log.pinned, user_log.type, user_log.audio,
                    edtb_systems.name AS system_name,
                    edtb_stations.name AS station_name
                    FROM user_log
                    LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
                    LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
                    WHERE user_log.system_name = '$esc_cursys_name'
                    ORDER BY -user_log.pinned ASC, user_log.weight, user_log.stardate " . $ssort;
    }
    /**
     * if log range is set to -1, show all logs
     */
    elseif ($settings['log_range'] == -1) {
        $query = '  SELECT SQL_CACHE
                    user_log.id, user_log.system_name AS log_system_name, user_log.station_id,
                    user_log.log_entry, user_log.stardate,
                    user_log.title, user_log.pinned, user_log.type, user_log.audio,
                    sqrt(pow((IFNULL(edtb_systems.x, user_systems_own.x)-(' . $usex . ')),2)
                    +pow((IFNULL(edtb_systems.y, user_systems_own.y)-(' . $usey . ')),2)
                    +pow((IFNULL(edtb_systems.z, user_systems_own.z)-(' . $usez . ")),2)) AS distance,
                    edtb_systems.name AS system_name,
                    edtb_stations.name AS station_name
                    FROM user_log
                    LEFT JOIN edtb_systems ON user_log.system_name = edtb_systems.name
                    LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
                    LEFT JOIN user_systems_own ON user_log.system_name = user_systems_own.name
                    WHERE user_log.system_name != ''
                    ORDER BY -user_log.pinned ASC, user_log.weight, user_log.stardate " . $ssort;
    }
    /**
     * in other cases, show logs from x ly away from last known location
     */
    else {
        $query = '  SELECT SQL_CACHE
                    user_log.id, user_log.system_id, user_log.system_name AS log_system_name,
                    user_log.station_id, user_log.log_entry, user_log.stardate,
                    user_log.title, user_log.pinned, user_log.type, user_log.audio,
                    sqrt(pow((IFNULL(edtb_systems.x, user_systems_own.x)-(' . $usex . ')),2)
                    +pow((IFNULL(edtb_systems.y, user_systems_own.y)-(' . $usey . ')),2)
                    +pow((IFNULL(edtb_systems.z, user_systems_own.z)-(' . $usez . ')),2)) AS distance,
                    edtb_systems.name AS system_name,
                    edtb_stations.name AS station_name
                    FROM user_log
                    LEFT JOIN edtb_systems ON user_log.system_name = edtb_systems.name
                    LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
                    LEFT JOIN user_systems_own ON user_log.system_name = user_systems_own.name
                    WHERE
                    IFNULL(edtb_systems.x, user_systems_own.x) BETWEEN ' . $usex . '-' . $settings['log_range'] . '
                    AND ' . $usex . '+' . $settings['log_range'] . ' &&
                    IFNULL(edtb_systems.y, user_systems_own.y) BETWEEN ' . $usey . '-' . $settings['log_range'] . '
                    AND ' . $usey . '+' . $settings['log_range'] . ' &&
                    IFNULL(edtb_systems.z, user_systems_own.z) BETWEEN ' . $usez . '-' . $settings['log_range'] . '
                    AND ' . $usez . '+' . $settings['log_range'] . "
                    OR
                    user_log.system_name = '$esc_cursys_name'
                    ORDER BY -user_log.pinned ASC, user_log.weight, user_log.system_name = '$esc_cursys_name' DESC,
                    distance ASC,
                    user_log.stardate " . $ssort . '
                    LIMIT 10';
    }
    //write_log($query);
    $log_res = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $num = $log_res->num_rows;

    if ($num > 0) {
        $logs = new MakeLog();

        $logs->time_difference = $system_time;

        $logdata .= $logs->make_log_entries($log_res, 'system');
    }

    $log_res->close();
}

/**
 *    General log
 */
$sort = 'DESC';
if (isset($_GET['glog_sort']) && $_GET['glog_sort'] !== 'undefined') {
    if ($_GET['glog_sort'] === 'asc') {
        $sort = 'ASC';
    }
    if ($_GET['glog_sort'] === 'desc') {
        $sort = 'DESC';
    }
}

$query = "  SELECT SQL_CACHE
            id, log_entry, stardate, pinned, title, audio
            FROM user_log WHERE system_id = '' AND system_name = ''
            ORDER BY -pinned, weight, stardate " . $sort . '
            LIMIT 5';

$glog_res = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

$gnum = $glog_res->num_rows;

if ($gnum > 0) {
    $general_logs = new MakeLog();

    $general_logs->time_difference = $system_time;

    $logdata .= $general_logs->make_log_entries($glog_res, 'general');
}

$glog_res->close();

$data['log_data'] = $logdata;
