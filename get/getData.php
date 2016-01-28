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
 * Main ajax backend file responsible for updating most of the on-the-fly stuff
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$action = isset($_GET["action"]) ? $_GET["action"] : "";
$request = isset($_GET["request"]) ? $_GET["request"] : 0;

if ($action == "onlycoordinates")
{
	echo $curSys["coordinates"];

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit;
}
elseif ($action == "onlysystem")
{
	echo $curSys["name"];

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit;
}
elseif ($action == "onlyid")
{
	echo $curSys["id"];

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit;
}

$data = array();

/*
* 	Now Playing
*/

$data['now_playing'] = "";

if ((isset($settings["nowplaying_file"]) && !empty($settings["nowplaying_file"])) or (isset($settings["nowplaying_vlc_password"]) && !empty($settings["nowplaying_vlc_password"])))
{
	$nowplaying = "";

	if (isset($settings["nowplaying_file"]) && !empty($settings["nowplaying_file"]))
	{
		$nowplaying .= file_get_contents($settings["nowplaying_file"]);
	}

	if (isset($settings["nowplaying_vlc_password"]) && !empty($settings["nowplaying_vlc_password"]))
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
		$result = file_get_contents($url, false, $context);

		$json_data = json_decode($result, true);

		$nowplaying .= $json_data["information"]["category"]["meta"]["now_playing"];
	}

	if (empty($nowplaying))
	{
		$nowplaying = "Not playing";
	}

	$data['now_playing'] = '<img src="/style/img/music.png" style="vertical-align:middle;margin-right:6px" alt="Now playing" />';
	$data['now_playing'] .= $nowplaying;
}

/*
* 	If we've arrived in a new system or
* 	are requesting page for the first time
*/

if ($newSystem !== false || $request == 0)
{
	/*
	*	update system and station data in the background if last update was more than 6 hours ago
	*/

	$last_update = edtb_common("last_data_update", "unixtime");
	$time_frame = time()-6*60*60;

	if ($last_update < $time_frame)
	{
		// run update script
		if (file_exists("" . $settings["install_path"] . "/bin/UpdateData/updatedata_bg.bat"))
		{
			$handle = popen("start \"UpdateData\" /b \"" . $settings["install_path"] . "/bin/UpdateData/updatedata_bg.bat\"", "r");

			pclose($handle);
		}
		else
		{
			write_log("Error: update error");
		}
	}

	/*
	*	update galmap json if system is new or file doesn't exist
	*	or if last update was more than an hour ago
	*/

	$data['update_map'] = "false";
	$last_map_update = edtb_common("last_map_update", "unixtime");
	$map_update_time_frame = time()-1*60*60;

	if ($newSystem !== false || !file_exists("" . $_SERVER["DOCUMENT_ROOT"] . "/map_points.json") || $last_map_update < $map_update_time_frame)
	{
		$data['update_map'] = "true";
	}

	$data['new_sys'] = "false";
	if ($newSystem !== false)
	{
		$data['new_sys'] = "true";
	}

	$data['current_system_name'] = $curSys["name"];
	$data['current_coordinates'] = $curSys["coordinates"];

	/*
	*	Data for the left column
	*/

	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/get/getData_leftColumn.php");

	/*
	*	Stuff specifically for system.php
	*/

	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/get/getData_systemInfo.php");

	/*
	*	System and general logs
	*/

	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/get/getData_logs.php");

	/*
	*	User and Ship status from API
	*/

	//require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/get/getData_status.php");

	/*
	*	Check for updates
	*/

	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/get/getData_checkForUpdates.php");

	/*
	*	set data renew tag
	*/

	$data['renew'] = "true";

	/*
	*	update last_access time
	*/

	update_last_access();
}
else
{
	$data['renew'] = "false";
}

echo json_encode($data);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
