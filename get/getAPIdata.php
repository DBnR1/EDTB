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
 * Ajax backend file to fetch user's profile from FD API
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$last_api_request = edtb_common("last_api_request", "unixtime");
$time_frame = time()-5*60;

if ($newSystem !== false)
{
	$time_frame = time();
}

if (isset($_GET["override"]))
{
	$time_frame = time()-30;
}

if ($last_api_request < $time_frame)
{
	// run update script
	if (file_exists($curl_exe))
	{
		//
		exec("\"". $curl_exe . "\" -b \"" . $cookie_file . "\" -c \"" . $cookie_file . "\" -H \"User-Agent: " . $agent . "\" \"https://companion.orerve.net/profile\" -k", $out);

		if (!empty($out))
		{
			if (!file_put_contents("" . $_SERVER["DOCUMENT_ROOT"] . "/profile.json", $out))
			{
				$error = error_get_last();
				write_log("Error: " . $error["message"] . "", __FILE__, __LINE__);
			}
		}
		else
		{
			write_log("Error: no output", __FILE__, __LINE__);
		}

		/*
		*	update last_api_request value
		*/

		edtb_common("last_api_request", "unixtime", true, time());
	}
	else
	{
		write_log("Error: last_api_request error");
	}
}

/*
*	parse data from companion json
*/

$profile_file = "" . $_SERVER["DOCUMENT_ROOT"] . "/profile.json";

if (file_exists($profile_file))
{
	$profile_file = file_get_contents($profile_file);
	$profile = json_decode($profile_file, true);

	$api["commander"] = $profile["commander"];
	$api["ship"] = $profile["ship"];
	$api["stored_ships"] = $profile["ships"];
}

global $api;
