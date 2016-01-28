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
 * Now playing data for Marvin
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

if (isset($_GET["nowplaying"]))
{
	// VLC contents supercedes file contents if enabled.

	$nowplaying = "";

	if (isset($settings["nowplaying_file"]) && $settings["nowplaying_file"] != "")
	{
		$nowplaying = file_get_contents($settings["nowplaying_file"]);
	}

	if (isset($settings["nowplaying_vlc_password"]) && $settings["nowplaying_vlc_password"] != "")
	{
		$username = "";
		$password = $settings["nowplaying_vlc_password"];
		$url = $settings["nowplaying_vlc_url"];

		$opts = array(
		  'http' => array(
			'method' => "GET",
			'header' => "Authorization: Basic " . base64_encode("$username:$password")
		  )
		);

		$context = stream_context_create($opts);

		if (!$result = file_get_contents($url, false, $context))
		{
			$error = error_get_last();
			write_log("Error: " . $error['message'] . "", __FILE__, __LINE__);
		}

		$json_data = json_decode($result, true);

		$nowplaying = $json_data["information"]["category"]["meta"]["now_playing"];
	}

	if (empty($nowplaying))
	{
		$nowplaying = "Not playing";
	}

	echo tts_override($nowplaying);

	exit();
}
