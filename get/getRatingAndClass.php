<?php
/**
 * Ajax backend file to fetch rating and class for nearest stations
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

if (isset($_GET['group_id']) && !empty($_GET['group_id'])) {
    $groupId = $_GET['group_id'];

    /**
     * set class
     */
    $data['classv'] .= '<option value="0">Class</option>';

    $query = "  SELECT DISTINCT class
                FROM edtb_modules
                WHERE class != ''
                AND group_id = '$groupId'
                ORDER BY class";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $found = $result->num_rows;

    if ($found > 0) {
        while ($obj = $result->fetch_object()) {
            $data['classv'] .= '<option value="' . $obj->class . '">Class ' . $obj->class . '</option>';
        }
    }

    $result->close();

    /**
     * set rating
     */
    $className = $_GET['class_name'];

    $alsoClass = '';
    if ($className !== '') {
        $alsoClass = " AND class='$className'";
    }

    $data['rating'] .= '<option value="0">Rating</option>';

    $query = "  SELECT DISTINCT rating
                FROM edtb_modules
                WHERE class != ''" . $alsoClass . "
                AND group_id = '$groupId'
                ORDER BY rating";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $foundRating = $result->num_rows;

    if ($foundRating > 0) {
        while ($obj = $result->fetch_object()) {
            $data['rating'] .= '<option value="' . $obj->rating . '">Rating ' . $obj->rating . '</option>';
        }
    }

    $result->close();
}

echo json_encode($data);
