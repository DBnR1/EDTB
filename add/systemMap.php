<?php
/**
 * Ajax backend file to save system map
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

/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

if (isset($_GET["string"]) && isset($_GET["system"]))
{
	$string = $_GET["string"];
	$system = $_GET["system"];
}
else
{
	write_log("Error: String or system not set", __FILE__, __LINE__);
	exit;
}

/**
 * insert / update
 */

if ($string == "delete")
{
	mysqli_query($GLOBALS["___mysqli_ston"], "	DELETE FROM user_system_map
												WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
												LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
}
else
{
	mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_system_map (system_name, string)
												VALUES
												('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "',
												'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $string) . "')
												ON DUPLICATE KEY UPDATE string = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $string) . "'")
												or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
}
