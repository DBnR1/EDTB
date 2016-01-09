<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2016 Mauri Kujala (contact@edtb.xyz)
*
*    This program is free software; you can redistribute it and/or
*    modify it under the terms of the GNU General Public License
*    as published by the Free Software Foundation; either version 2
*    of the License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$poi_id = $_GET["poi_id"];

$poi_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, poi_name, system_name, text, category_id, x, y, z,
														FROM user_poi
														WHERE id = '" . $poi_id . "'
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
$poi_arr = mysqli_fetch_assoc($poi_res);

$data = array();
$data["poi_edit_id"] = $poi_arr["id"];
$data["system_33"] = $poi_arr["system_name"];
if (isset($poi_arr["x"]))
{
	$data["coordsx_33"] = $poi_arr["x"];
	$data["coordsy_33"] = $poi_arr["y"];
	$data["coordsz_33"] = $poi_arr["z"];
}
else
{
	$data["coordsx_33"] = "";
	$data["coordsy_33"] = "";
	$data["coordsz_33"] = "";

}
$data["poi_text"] = $poi_arr["text"];
$data["poi_name"] = $poi_arr["poi_name"];
$data["category_id"] = $poi_arr["category_id"];

echo json_encode($data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
