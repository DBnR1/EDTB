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

$img = isset($_GET["img"]) ? $_GET["img"] : "";

$pathinfo = pathinfo($img);
$path = $pathinfo['dirname'];
$file = $pathinfo['basename'];
$path = "" . $_SERVER["DOCUMENT_ROOT"] . "/" . $path . "";

$image = "" . $path . "/" . $file . "";
$thumb = "" . $path . "/thumbs/" . $file . "";

if (file_exists($image))
{
	if (!unlink($image))
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'] . " - Could not remove " . $image . "", __FILE__, __LINE__);
	}
}
else
{
	write_log("Error: Could not remove " . $image . " - file doesn't exist", __FILE__, __LINE__);
}

if (file_exists($thumb))
{
	if (!unlink($thumb))
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'] . " - Could not remove " . $thumb . "", __FILE__, __LINE__);
	}
}
else
{
	write_log("Error: Could not remove " . $thumb . " - file doesn't exist", __FILE__, __LINE__);
}

