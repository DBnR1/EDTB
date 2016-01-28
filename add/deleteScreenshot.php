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
 * Ajax backend file to delete screenshots
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$img = isset($_GET["img"]) ? $_GET["img"] : "";

if (empty($img))
{
	write_log("Error: screenshot deletion failed, variable img not set", __FILE__, __LINE__);
	$redir_url = "/gallery.php?removed=2";
	echo $redir_url;
	exit;
}

$pathinfo = pathinfo($img);
$path = $pathinfo['dirname'];
$file = $pathinfo['basename'];
$system = str_replace("screenshots/", "", $path);
$redir_url = "/gallery.php?spgmGal=" . urlencode($system) . "&removed";
$path = "" . $_SERVER["DOCUMENT_ROOT"] . "/" . $path . "";

$image = "" . $path . "/" . $file . "";
$thumb = "" . $path . "/thumbs/" . $file . "";

if (file_exists($image))
{
	if (!unlink($image))
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'] . " - Could not remove " . $image . "", __FILE__, __LINE__);
		$redir_url = "/gallery.php?spgmGal=" . urlencode($system) . "&removed=1";
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
		$redir_url = "/gallery.php?spgmGal=" . urlencode($system) . "&removed=1";
	}
}
else
{
	write_log("Error: Could not remove " . $thumb . " - file doesn't exist", __FILE__, __LINE__);
	$redir_url = "/gallery.php?spgmGal=" . urlencode($system) . "&removed=1";
}

/*
*	delete dir if it's now empty
*/

if (is_dir_empty("" . $path . "/thumbs"))
{
	$redir_url = "/gallery.php?removed";

	// remove thumbs dir first
	if (!rmdir("" . $path . "/thumbs"))
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'] . " - Could not remove directory", __FILE__, __LINE__);
		$redir_url = "/gallery.php?removed=1";
	}

	// remove dir
	if (!rmdir("" . $path . ""))
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'] . " - Could not remove directory", __FILE__, __LINE__);
		$redir_url = "/gallery.php?removed=1";
	}
}

echo $redir_url;
