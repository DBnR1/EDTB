<?php
/**
 * Ajax backend file to fetch bookmark edit data
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

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");

$bm_id = 0 + $_GET["Bm_id"];
$data = [];

if ($bm_id == "0") {
    $data["bm_edit_id"] = "";
    $data["bm_system_name"] = "";
    $data["bm_system_id"] = "";
    $data["bm_catid"] = "0";
    $data["bm_text"] = "";
} else {
    $query = "  SELECT
                user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name AS bm_system_name,
                user_bookmarks.comment, user_bookmarks.category_id,
                edtb_systems.name AS system_name
                FROM user_bookmarks
                LEFT JOIN edtb_systems ON user_bookmarks.system_id = edtb_systems.id
                WHERE user_bookmarks.id = '$bm_id'
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $bm_obj = $result->fetch_object();

    $data["bm_edit_id"] = $bm_obj->id;
    $data["bm_system_name"] = $bm_obj->system_name == "" ? $bm_obj->bm_system_name : $bm_obj->system_name;
    $data["bm_system_id"] = $bm_obj->system_id;
    $data["bm_catid"] = $bm_obj->category_id;
    $data["bm_text"] = $bm_obj->comment;

    $result->close();
}

echo json_encode($data);
