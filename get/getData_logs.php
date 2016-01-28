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
 * Ajax backend file for system and general log
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

/*
*    System log
*/

if (!empty($curSys["name"]))
{
	if (isset($_GET["slog_sort"]) && $_GET["slog_sort"] != "undefined")
	{
		if ($_GET['slog_sort'] == 'asc') $ssort = 'ASC';
		if ($_GET['slog_sort'] == 'desc') $ssort = 'DESC';
	}
	else
	{
		$ssort = 'DESC';
	}

	/*
	*	if log range is set to zero, only show logs from current system
	*/

	if ($settings["log_range"] == 0)
	{
		$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE
																user_log.id, user_log.system_name AS log_system_name, user_log.station_id,
																user_log.log_entry, user_log.stardate,
																edtb_systems.name AS system_name,
																edtb_stations.name AS station_name
																FROM user_log
																LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
																LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
																WHERE user_log.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																ORDER BY stardate " . $ssort . "") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	/*
	*	if log range is set to -1, show all logs
	*/
	elseif ($settings["log_range"] == -1)
	{
		$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE
																user_log.id, user_log.system_name AS log_system_name, user_log.station_id,
																user_log.log_entry, user_log.stardate,
																edtb_systems.name AS system_name,
																edtb_stations.name AS station_name
																FROM user_log
																LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
																LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
																WHERE user_log.system_name != ''
																ORDER BY stardate " . $ssort . "") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	/*
	*	in other cases, show logs from x ly away from last known location
	*/
	else
	{
		// figure out what coords to calculate from
		$usable_coords = usable_coords();
		$usex = $usable_coords["x"];
		$usey = $usable_coords["y"];
		$usez = $usable_coords["z"];
		$exact = $usable_coords["current"] === true ? "" : " *";

		$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE
																user_log.id, user_log.system_id, user_log.system_name AS log_system_name,
																user_log.station_id, user_log.log_entry, user_log.stardate,
																sqrt(pow((edtb_systems.x-(" . $usex . ")),2)
																+pow((edtb_systems.y-(" . $usey . ")),2)
																+pow((edtb_systems.z-(" . $usez . ")),2)) AS distance,
																sqrt(pow((user_systems_own.x-(" . $usex . ")),2)
																+pow((user_systems_own.y-(" . $usey . ")),2)
																+pow((user_systems_own.z-(" . $usez . ")),2)) AS distance2,
																edtb_systems.name AS system_name,
																edtb_stations.name AS station_name
																FROM user_log
																LEFT JOIN edtb_systems ON user_log.system_id = edtb_systems.id
																LEFT JOIN edtb_stations ON user_log.station_id = edtb_stations.id
																LEFT JOIN user_systems_own ON user_log.system_name = user_systems_own.name
																WHERE
																edtb_systems.x BETWEEN " . $usex . "-" . $settings["log_range"] . "
																AND " . $usex . "+" . $settings["log_range"] . " &&
																edtb_systems.y BETWEEN " . $usey . "-" . $settings["log_range"] . "
																AND " . $usey . "+" . $settings["log_range"] . " &&
																edtb_systems.z BETWEEN " . $usez . "-" . $settings["log_range"] . "
																AND " . $usez . "+" . $settings["log_range"] . "
																OR
																user_systems_own.x BETWEEN " . $usex . "-" . $settings["log_range"] . "
																AND " . $usex . "+" . $settings["log_range"] . " &&
																user_systems_own.y BETWEEN " . $usey . "-" . $settings["log_range"] . "
																AND " . $usey . "+" . $settings["log_range"] . " &&
																user_systems_own.z BETWEEN " . $usez . "-" . $settings["log_range"] . "
																AND " . $usez . "+" . $settings["log_range"] . "
																OR
																user_log.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
																ORDER BY user_log.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "' DESC,
																distance ASC, distance2 ASC,
																user_log.stardate " . $ssort . "
																LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	$num = mysqli_num_rows($log_res);

	$logdata = "";
	if ($num > 0)
	{
		$this_system = "";
		$this_id = "";
		$i = 0;
		while ($log_arr = mysqli_fetch_assoc($log_res))
		{
			if ($this_id != $log_arr["id"])
			{
				$system_name = $log_arr["system_name"] == "" ? $log_arr["log_system_name"] : $log_arr["system_name"];
				$log_station_name = $log_arr["station_name"];
				$log_text = $log_arr["log_entry"];
				$date = date_create($log_arr["stardate"]);
				$log_added = date_modify($date, "+1286 years");
				$distance = $log_arr["distance"] == "" ? number_format($log_arr["distance2"], 1) : number_format($log_arr["distance"], 1);

				if ($this_system != $system_name)
				{
					if ($distance != 0)
					{
						$add = " (distance " . $distance . " ly" . $exact . ")";
					}
					else
					{
						$add = "";
					}

					$sortable = "";
					if ($i == 0)
					{
						if (isset($_GET["slog_sort"]) && $_GET["slog_sort"] != "undefined")
						{
							if ($_GET['slog_sort'] == 'asc') $sssort = 'desc';
							if ($_GET['slog_sort'] == 'desc') $sssort = 'asc';
						}
						else
						{
							$sssort = 'asc';
						}

						$sortable = '<span class="right"><a href="/index.php?slog_sort=' . $sssort . '" title="Sort by date asc/desc"><img src="/style/img/sort.png" alt="Sort" /></a></span>';
					}

					// check if system has screenshots
					$screenshots = has_screenshots($system_name) ? '<a href="/gallery.php?spgmGal=' . urlencode($system_name) . '" title="View image gallery"><img src="/style/img/image.png" alt="Gallery" style="margin-left:5px;margin-right:3px;vertical-align:top" /></a>' : "";

					$logdata .= '<h2><img src="/style/img/system_log.png" alt="log" style="margin-right:6px" />System log for <a href="/system.php?system_name=' . urlencode($system_name) . '">' . $system_name . '</a>' . $screenshots . '' . $add . '' . $sortable . '</h2>';
					$logdata .= '<hr>';
				}

				$logdata .= '<h3>
								<a href="javascript:void(0)" onclick="toggle_log_edit(\'' . $log_arr["id"] . '\')" style="color:inherit" title="Edit entry">';
				$logdata .= date_format($log_added, "j M Y, H:i");
				if (!empty($log_station_name))
				{
					$logdata .= '&nbsp;[Station: ' . htmlspecialchars($log_station_name) . ']';
				}
				$logdata .= '</a></h3><pre class="entriespre" style="margin-bottom: 20px">';
				$logdata .= $log_text;
				$logdata .= '</pre>';
			}

			$this_system = $system_name;
			$this_id = $log_arr["id"];
			$i++;
		}
	}
}
else
{
	$logdata = "";
}

/*
*    General log
*/

if (isset($_GET["glog_sort"]) && $_GET["glog_sort"] != "undefined")
{
	if ($_GET['glog_sort'] == 'asc') $sort = 'ASC';
	if ($_GET['glog_sort'] == 'desc') $sort = 'DESC';
}
else
{
	$sort = 'DESC';
}

$glog_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE
														id, log_entry, stardate
														FROM user_log WHERE system_id = '' AND system_name = ''
														ORDER BY stardate " . $sort . "
														LIMIT 5") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
$gnum = mysqli_num_rows($glog_res);

if ($gnum > 0)
{
	$sortable = "";
	if (isset($_GET["glog_sort"]) && $_GET["glog_sort"] != "undefined")
	{
		if ($_GET['glog_sort'] == 'asc') $gssort = 'desc';
		if ($_GET['glog_sort'] == 'desc') $gssort = 'asc';
	}
	else
	{
		$gssort = 'asc';
	}

	$sortable = '<span class="right"><a href="/index.php?glog_sort=' . $gssort . '" title="Sort by date asc/desc"><img src="/style/img/sort.png" alt="Sort" /></a></span>';

	$logdata .= '<h2><img src="/style/img/log.png" alt="log" style="margin-right:6px" />Commander\'s Log' . $sortable . '</h2>';
	$logdata .= '<hr>';

	while ($glog_arr = mysqli_fetch_assoc($glog_res))
	{
		$glog_text = $glog_arr["log_entry"];
		$gdate = date_create($glog_arr["stardate"]);
		$glog_added = date_modify($gdate, "+1286 years");
		$logdata .= '<h3>
						<a href="javascript:void(0)"
						onclick="tofront(\'addlog\');update_values(\'/get/getLogEditData.php?logid=' . $glog_arr["id"] . '\',\'' . $glog_arr["id"] . '\')"
						style="color:inherit"
						title="Edit entry">';
		$logdata .= date_format($glog_added, "j M Y, H:i");
		$logdata .= '</a></h3><pre class="entriespre">';
		$logdata .= $glog_text;
		$logdata .= '</pre>';
	}
}
$data['log_data'] = $logdata;

