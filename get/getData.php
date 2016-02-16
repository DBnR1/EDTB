<?php
/**
 * Main ajax backend file
 *
 * This file and required files are responsible for updating all of the on-the-fly stuff
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

$action = isset($_GET["action"]) ? $_GET["action"] : "";
$request = isset($_GET["request"]) ? $_GET["request"] : 0;

if ($action == "onlycoordinates")
{
	echo $curSys["coordinates"];

	exit;
}
elseif ($action == "onlysystem")
{
	echo $curSys["name"];

	exit;
}
elseif ($action == "onlyid")
{
	echo $curSys["id"];

	exit;
}

$data = array();

/**
* 	Now Playing
 */

$data['now_playing'] = "";

if ((isset($settings["nowplaying_file"]) && !empty($settings["nowplaying_file"])) or (isset($settings["nowplaying_vlc_password"]) && !empty($settings["nowplaying_vlc_password"])))
{
	$nowplaying = "";

	/**
	 *  from file
	 */

	if (isset($settings["nowplaying_file"]) && !empty($settings["nowplaying_file"]))
	{
		$nowplaying .= file_get_contents($settings["nowplaying_file"]);
	}

	/**
	 *  from VLC (@author Travis)
	 */

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

	$data['now_playing'] = '<img src="/style/img/music.png" class="icon" alt="Now playing" />';
	$data['now_playing'] .= $nowplaying;
}

/**
 * If we've arrived in a new system or
 * are requesting page for the first time
 */

$data['update_in_progress'] = "false";
$data['update_notification_data'] = "false";
if ($newSystem !== false || $request == 0)
{
	/**
	 * update system and station data in the background if last update was more than 6 hours ago
	 */

	$last_update = edtb_common("last_data_update", "unixtime");
	$time_frame = time()-6*60*60;

	if ($last_update < $time_frame)
	{
		// fetch last update start time
		$last_data_update_start = edtb_common("last_data_update_start", "unixtime");
		$start_time_frame = time()-120;

		// run update script
		if ($last_data_update_start < $start_time_frame)
		{
			$batch_file = $settings["install_path"] . "/bin/UpdateData/updatedata_bg.bat";
			if (file_exists($batch_file))
			{
				edtb_common("last_data_update_start", "unixtime", true, time());

				pclose(popen("start \"UpdateData\" /b \"" . $batch_file . "\"", "r"));

				$data['update_in_progress'] = "true";
				$data['update_notification'] .= '<a href="javascript:void(0)" title="Data update in progress" onclick="$(\'#notice\').fadeToggle(\'fast\')"><img src="/style/img/notice.png" class="icon26" alt="Update" /></a>';
				$data['update_notification_data'] = 'System and station data is being updated in the background.<br /><br />You can continue using ED ToolBox normally.';
			}
			else
			{
				write_log("Error: " . $batch_file . " doesn't exist");
			}
		}
	}

	/**
	 * update galmap json if system is new or file doesn't exist
	 * or if last update was more than an hour ago
	 */

	$data['update_map'] = "false";
	$last_map_update = edtb_common("last_map_update", "unixtime");
	$map_update_time_frame = time()-1*60*60;

	if ($newSystem !== false || !file_exists($_SERVER["DOCUMENT_ROOT"] . "/map_points.json") || $last_map_update < $map_update_time_frame)
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

	/**
	 * Data for the left column
	 */

	require_once($_SERVER["DOCUMENT_ROOT"] . "/get/getData_leftColumn.php");

	/**
	 * Stuff specifically for System.php
	 */

	require_once($_SERVER["DOCUMENT_ROOT"] . "/get/getData_systemInfo.php");

	/**
	 * System and general logs
	 */

	require_once($_SERVER["DOCUMENT_ROOT"] . "/get/getData_logs.php");

	/**
	 * Check for updates
	 */

	require_once($_SERVER["DOCUMENT_ROOT"] . "/get/getData_checkForUpdates.php");

	/**
	 * set data renew tag
	 */

	$data['renew'] = "true";
}
else
{
	$data['renew'] = "false";
}

// make screenshot gallery
make_gallery($curSys["name"]);

echo json_encode($data);

