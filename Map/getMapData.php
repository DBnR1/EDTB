<?php
/**
 * Ajax backend file to fetch data for Neighborhood Map points
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

use \EDTB\source\System;

$system = $_GET['system'] ?? '';

if (empty($system)) {
    exit;
}

$escSystem = $mysqli->real_escape_string($system);

/**
 * check if system has screenshots
 */
$screenshots = System::hasScreenshots($system) ? '<a href="/Gallery?spgmGal=' . urlencode(stripInvalidDosChars($system)) .
    '" title="View image gallery"><img src="/style/img/image.png" alt="Gallery" class="icon" style="margin-left: 5px; vertical-align: top"></a>' :
    '';

/**
 * check if system is in the bookmarks
 */
$query = "  SELECT user_bookmarks.comment, user_bookmarks.added_on
            FROM user_bookmarks
            WHERE user_bookmarks.system_name = '$escSystem'
            LIMIT 1";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

$count2 = $result->num_rows;

if ($count2 > 0) {
    $obj = $result->fetch_object();
    $comment = $obj->comment;
    $addedOn = $obj->added_on;

    if ($comment !== '') {
        echo 'Bookmark comment: ' . $comment . ' - ';
    }

    echo 'Bookmark added: ' . get_timeago($addedOn, false);
    echo '<br>';
}

$result->close();

/**
 * check if system is point of interest
 */
$query = "  SELECT user_poi.text AS text, user_visited_systems.visit AS visit
            FROM user_poi LEFT JOIN user_visited_systems ON user_visited_systems.system_name = user_poi.system_name
            WHERE user_poi.system_name = '$escSystem'
            OR user_visited_systems.system_name = '$escSystem'
            UNION SELECT user_poi.text AS text, user_visited_systems.visit AS visit
            FROM user_poi RIGHT JOIN user_visited_systems ON user_visited_systems.system_name = user_poi.system_name
            WHERE user_poi.system_name = '$escSystem'
            OR user_poi.poi_name = '$escSystem'
            OR user_visited_systems.system_name = '$escSystem'
            LIMIT 1";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

$count = $result->num_rows;

if ($count > 0) {
    $obja = $result->fetch_object();
    $text = htmlspecialchars($obja->text);
    $visit = $obja->visit;
    $visitOg = $obja->visit;

    if (!$visit && !$text) {
        echo '<a href="/System?system_name=' . urlencode($system) . '" style="color: inherit">' . $system . '</a>' . $screenshots .
            '<br>No additional information';
    } else {
        if (isset($visit)) {
            $visit = date_create($visit);
            $visitDate = date_modify($visit, '+1286 years');

            $visit = date_format($visitDate, 'd.m.Y, H:i');
        }

        if ($text !== null) {
            echo $text . '<br>';
        }

        if (!empty($visit)) {
            $query = "  SELECT id
                        FROM user_visited_systems
                        WHERE system_name = '$escSystem'";

            $visits = $mysqli->query($query)->num_rows;

            $visitUnix = strtotime($visitOg);
            $visitAgo = get_timeago($visitUnix);
            echo '<a href="/System?system_name=' . urlencode($system) . '" style="color: inherit">';
            echo $system . '</a>' . $screenshots . '&nbsp;&nbsp;|&nbsp;';
            echo 'Total visits: ' . $visits . '&nbsp;&nbsp;|&nbsp;&nbsp;';
            echo 'First visit: ' . $visit . ' (' . $visitAgo . ')';
        } else {
            echo '<a href="/System?system_name=' . urlencode($system) . '" style="color: inherit">' . $system . '</a>';
        }

        $query = "  SELECT id, LEFT(log_entry, 100) AS text
                    FROM user_log
                    WHERE system_name = '$escSystem'
                    ORDER BY stardate
                    LIMIT 1";

        $logResult = $mysqli->query($query);

        $logged = $logResult->num_rows;
        if ($logged > 0) {
            $logObj = $logResult->fetch_object();
            $text = $logObj->text;

            echo '<br>';
            echo '<a href="/Log?system=' . urlencode($system) .
                '" style="color: inherit; font-weight: 700" title="View the log for this system">';
            echo $text . ' ...';
            echo '</a>';
        }
        $logResult->close();
    }
    $result->close();
    exit;
}

echo 'No additional information';
