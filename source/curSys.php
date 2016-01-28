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
 * This script parses the netLog file to determine the user's current location and fetches
 * related information from the database and puts that information to global variable $curSys
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

/*
*	get current system
*/

$curSys = array();
if (is_dir($settings["log_dir"]) && is_readable($settings["log_dir"]))
{
    // select the newest  file
    if (!$files = scandir($settings["log_dir"], SCANDIR_SORT_DESCENDING))
	{
		$error = error_get_last();
		write_log("Error: " . $error["message"] . "", __FILE__, __LINE__);
	}
    $newest_file = $files[0];

    // read file to an array
    if (!$line = file("" . $settings["log_dir"] . "/" . $newest_file . ""))
	{
		$error = error_get_last();
		write_log("Error: " . $error["message"] . "", __FILE__, __LINE__);
	}
	else
	{
		//  reverse array
		$lines = array_reverse($line);

		foreach ($lines as $line_num => $line)
		{
			$pos = strpos($line, "System:");
			// skip lines that contain "ProvingGround" because they are CQC systems
			$pos2 = strrpos($line, "ProvingGround");

			if ($pos !== false && $pos2 === false)
			{
				preg_match_all("/\((.*?)\) B/", $line, $matches);
				$cssystemname = $matches[1][0];
				$curSys["name"] = $cssystemname;

				preg_match_all("/\{(.*?)\} System:/", $line, $matches2);
				$visited_time = $matches2[1][0];

				$curSys["name"] = isset($curSys["name"]) ? $curSys["name"] : "";

				// define defaults
				$curSys["coordinates"] = "";
				$curSys["x"] = "";
				$curSys["y"] = "";
				$curSys["z"] = "";
				$curSys["id"] = -1;
				$curSys["population"] = "";
				$curSys["allegiance"] = "";
				$curSys["economy"] = "";
				$curSys["government"] = "";
				$curSys["ruling_faction"] = "";
				$curSys["state"] = "unknown";
				$curSys["security"] = "unknown";
				$curSys["power"] = "";
				$curSys["power_state"] = "";
				$curSys["needs_permit"] = "";
				$curSys["updated_at"] = "";
				$curSys["simbad_ref"] = "";

				$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT 	id, x, y, z, ruling_faction, population, government, allegiance, state,
																			security, economy, power, power_state, needs_permit, updated_at, simbad_ref
																	FROM edtb_systems
																	WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																	LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
				$exists = mysqli_num_rows($res);

				if ($exists > 0)
				{
					$arr = mysqli_fetch_assoc($res);

					$curSys["coordinates"] = "" . $arr['x'] . "," . $arr['y'] . "," . $arr['z'] . "";
					$curSys["id"] = $arr["id"];
					$curSys["population"] = $arr["population"];
					$curSys["allegiance"] = $arr["allegiance"];
					$curSys["economy"] = $arr["economy"];
					$curSys["government"] = $arr["government"];
					$curSys["ruling_faction"] = $arr["ruling_faction"];
					$curSys["state"] = $arr["state"];
					$curSys["security"] = $arr["security"];
					$curSys["power"] = $arr["power"];
					$curSys["power_state"] = $arr["power_state"];
					$curSys["needs_permit"] = $arr["needs_permit"];
					$curSys["updated_at"] = $arr["updated_at"];
					$curSys["simbad_ref"] = $arr["simbad_ref"];

					$curSys["x"] = $arr["x"];
					$curSys["y"] = $arr["y"];
					$curSys["z"] = $arr["z"];
				}
				else
				{
					$cres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																		FROM user_systems_own
																		WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																		LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

					$oexists = mysqli_num_rows($cres);

					if ($oexists > 0)
					{
						$carr = mysqli_fetch_assoc($cres);

						$curSys["x"] = $carr["x"] == "" ? "" : $carr["x"];
						$curSys["y"] = $carr["y"] == "" ? "" : $carr["y"];
						$curSys["z"] = $carr["z"] == "" ? "" : $carr["z"];
						$curSys["coordinates"] = "" . $curSys["x"] . "," . $curSys["y"] . "," . $curSys["z"] . "";
					}
					else
					{
						$curSys["coordinates"] = "";
						$curSys["x"] = "";
						$curSys["y"] = "";
						$curSys["z"] = "";
					}
				}

				// fetch previous system
				$prev_system = edtb_common("last_system", "value");

				if ($prev_system != $cssystemname && !empty($cssystemname))
				{
					// add system to user_visited_systems
					$rows = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT system_name
																		FROM user_visited_systems
																		ORDER BY id
																		DESC LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
					$vs_arr = mysqli_fetch_assoc($rows);

					$visited_on = "" . date("Y-m-d") . " " . $visited_time . "";

					if ($vs_arr["system_name"] != $curSys["name"] && !empty($curSys["name"]))
					{
						mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_visited_systems (system_name, visit)
																	VALUES
																	('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "',
																	'" . $visited_on . "')") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

						// export to edsm
						if ($settings["edsm_api_key"] != "" && $settings["edsm_export"] == "true" && $settings["edsm_cmdr_name"] != "")
						{
							$visited_on_utc = date("Y-m-d H:i:s");
							$export = file_get_contents("http://www.edsm.net/api-logs-v1/set-log?commanderName=" . urlencode($settings["edsm_cmdr_name"]) . "&apiKey=" . $settings["edsm_api_key"] . "&systemName=" . urlencode($curSys["name"]) . "&dateVisited=" . urlencode($visited_on_utc) . "");

							$exports = json_decode($export, true);

							if ($exports["msgnum"] != "100")
							{
								write_log($export, __FILE__, __LINE__);
							}
						}

						$newSystem = true;
					}

					// update latest system
					edtb_common("last_system", "value", true, $curSys["name"]);

					$newSystem = true;
				}
				else
				{
					$newSystem = false;
				}

				global $curSys, $newSystem;

				break;
			}
		}
	}
}
else
{
	write_log("Error: " . $settings["log_dir"] . " is not a directory", __FILE__, __LINE__);
}
