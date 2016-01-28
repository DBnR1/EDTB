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
 * Ajax backend file to fetch bookmark edit data
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$bm_id = $_GET["Bm_id"];
$data = array();

if ($bm_id == "0")
{
	$data["bm_edit_id"] = "";
	$data["bm_system_name"] = "";
	$data["bm_system_id"] = "";
	$data["bm_catid"] = "0";
	$data["bm_text"] = "";
}
else
{
	$bm_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
															user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name AS bm_system_name,
															user_bookmarks.comment, user_bookmarks.category_id,
															edtb_systems.name AS system_name
															FROM user_bookmarks
															LEFT JOIN edtb_systems ON user_bookmarks.system_id = edtb_systems.id
															WHERE user_bookmarks.id = '" . $bm_id . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$bm_arr = mysqli_fetch_assoc($bm_res);

	$data["bm_edit_id"] = $bm_arr["id"];
	$data["bm_system_name"] = $bm_arr["system_name"] == "" ? $bm_arr["bm_system_name"] : $bm_arr["system_name"];
	$data["bm_system_id"] = $bm_arr["system_id"];
	$data["bm_catid"] = $bm_arr["category_id"];
	$data["bm_text"] = $bm_arr["comment"];
}
echo json_encode($data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
