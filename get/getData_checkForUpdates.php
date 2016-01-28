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
 * Ajax backend file to check if a new version of ED ToolBox is available
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$data['notifications'] = '';
$data['notifications_data'] = 'false';

$current_version = $settings["edtb_version"];
$last_check = edtb_common("last_update_check", "unixtime");
$time_frame = time()-24*60*60;

if ($last_check < $time_frame)
{
	if ($json_file = file_get_contents("http://data.edtb.xyz/version.json"))
	{
		$json_data = json_decode($json_file, true);

		$newest_version = $json_data["currentVersion"];

		// update latest_version value
		edtb_common("latest_version", "value", true, $newest_version);

	}
	else
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'] . "", __FILE__, __LINE__);
	}
	edtb_common("last_update_check", "unixtime", true, time());
}

$newest_version = edtb_common("latest_version", "value");

if (version_compare($current_version, $newest_version) < 0)
{
	// get last_update_check value
	$ignore_version = edtb_common("last_update_check", "value");

	if ($newest_version != $ignore_version)
	{
		if ($json_file = file_get_contents("http://data.edtb.xyz/version.json"))
		{
			$json_data = json_decode($json_file, true);

			$short_desc = $json_data["short"];
			$long_desc = $json_data["versionInformation"];
			$data['notifications'] .= '<a href="javascript:void(0)" title="New version available" onclick="$(\'#notice_new\').fadeToggle(\'fast\')"><img src="/style/img/upgrade.png" style="height:26px;width:26px;margin-right:6px" alt="Upgrade" /></a>';
			$data['notifications_data'] = $short_desc . '<br /><br /><br />' . $long_desc;
			$data['notifications_data'] .= '<br /><br /><strong><a href="javascript:void(0)" onclick="ignore_version(\'' . $newest_version . '\')">Click here if you want to ignore this version</a></strong>';
		}
	}
}
/*
*	Display notification if user hasn't updated data in a while
*/

$last_update = edtb_common("last_data_update", "unixtime");
$now = time()-(7*24*60*60); // 7 days

if ($now > $last_update)
{
	$data['notifications'] .= '<a href="javascript:void(0)" title="Notice" onclick="$(\'#notice\').fadeToggle(\'fast\')"><img src="/style/img/notice.png" style="height:26px;width:26px" alt="Notice" /></a>';
}

if ($data['notifications'] == "")
{
	$data['notifications'] = 'false';
}