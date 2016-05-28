<?php
/**
 * Navigation data for Marvin
 *
 * Original file by Mauri Kujala, used as template for
 * NavigationData.php by Khromm
 *
 * @package EDTB\Marvin
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
/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");

/**
 * Query the database
 */
if (isset($_GET["name"])) {
    $search = $mysqli->real_escape_string($_GET["name"]);

    $query = "  SELECT system_name
                FROM user_bookmarks LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
                WHERE user_bm_categories.name = '$search'
                ORDER BY user_bookmarks.id DESC
                LIMIT 1
                ";

    $result = $mysqli->query($query);

    $obj = $result->fetch_object();

	$system_name = $obj->system_name;

    echo $system_name;

    $result->close();

    exit;
}

