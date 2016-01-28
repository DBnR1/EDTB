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
 * Ajax backend file to get Imgur url for screenshots
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$url = isset($_GET["url"]) ? $_GET["url"] : "";
$file = isset($_GET["file"]) ? $_GET["file"] : "";

if (!empty($file) && !empty($url))
{
	$path = "" . $_SERVER["DOCUMENT_ROOT"] . "/screenshots/Imgur";

	if (!is_dir($path))
	{
		if (!mkdir($path, 0775, true))
		{
			write_log("Could not create Imgurdir", __FILE__, __LINE__);
		}
	}

	$filename = "" . urldecode($file) . ".txt";
	file_put_contents("" . $path . "/" . $filename . "", $url) or write_log("Error #10", __FILE__, __LINE__);
}
else
{
	write_log("Error #11", __FILE__, __LINE__);
}

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
