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
 * Ajax backend file to fetch rating and class for nearest stations
 *
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

if (isset($_GET["group_id"]) && !empty($_GET["group_id"]))
{
	$group_id = $_GET["group_id"];

	/*
	*	set class
	*/

	$data['class'] .= '<option value="0">Class</option>';
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT class
														FROM edtb_modules
														WHERE class != ''
														AND group_id = '" . $group_id . "'
														ORDER BY class") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$found = mysqli_num_rows($res);

	if ($found > 0)
	{
		while ($arr = mysqli_fetch_assoc($res))
		{
			$data['class'] .= '<option value="' . $arr["class"] . '">Class ' . $arr["class"] . '</option>';
		}
	}

	/*
	*	set rating
	*/

	$class_name = $_GET["class_name"] == "" ? "" : $_GET["class_name"];

	$also_class = "";
	if ($class_name != "")
	{
		$also_class = " AND class='" . $class_name . "'";
	}

	$data['rating'] .= '<option value="0">Rating</option>';
	$rating_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT rating
																FROM edtb_modules
																WHERE class != ''" . $also_class . "
																AND group_id = '" . $group_id . "'
																ORDER BY rating") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$found_rating = mysqli_num_rows($rating_res);

	if ($found_rating > 0)
	{
		while ($rating_arr = mysqli_fetch_assoc($rating_res))
		{
			$data['rating'] .= '<option value="' . $rating_arr["rating"] . '">Rating ' . $rating_arr["rating"] . '</option>';
		}
	}
}

echo json_encode($data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
