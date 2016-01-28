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
 * Add or edit log entries
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

if (isset($_GET["do"]))
{
	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

	$data = json_decode($_REQUEST["input"], true);

	$l_system_name = $data["system_name"];
	$l_station_name = $data["station_name"];

	// get system id
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id AS system_id
														FROM edtb_systems
														WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_system_name). "'
														LIMIT 1");
	$arr = mysqli_fetch_assoc($res);
	$l_system = $arr["system_id"];

	// get station id
	$res2 = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id AS station_id
														FROM edtb_stations
														WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_station_name). "'
														AND system_id = '" . $l_system . "'
														LIMIT 1");
	$arr2 = mysqli_fetch_assoc($res2);
	$l_station = $arr2["station_id"];

	if ($l_system_name == "")
	{
		$l_system = "0";
		$l_system_name = "";
	}

	$l_entry = $data["log_entry"];
	$l_id = $data["edit_id"];

	if ($l_id != "")
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_log SET
													system_id = '" . $l_system . "',
													system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_system_name) . "',
													station_id = '" . $l_station . "',
													log_entry = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_entry) . "'
													WHERE id = '" . $l_id . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]));
	}
	elseif (isset($_GET["deleteid"]))
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	DELETE FROM user_log
													WHERE id = '" . $_GET["deleteid"] . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]));
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_log (system_id, system_name, station_id, log_entry)
													VALUES
													('" . $l_system . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_system_name) . "',
													'" . $l_station . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_entry) . "')") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]));
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit;
}

?>
<div class="input" id="addlog">
	<form method="post" id="log_form" action="log.php">
		<div class="input-inner">
			<div class="suggestions" id="suggestions_41" style="margin-left:400px;margin-top:79px"></div>
			<div class="suggestions" id="suggestions_1" style="margin-left:10px;margin-top:79px"></div>
			<table>
				<tr>
					<td class="heading" colspan="2">Add/Edit Log Entry
						<span class="right">
							<a href="javascript:void(0)" onclick="tofront('addlog');$('.addstations').toggle()" title="Close form">
								<img src="/style/img/close.png" alt="X" style="width:16px;height:16px" />
							</a>
						</span>
					</td>
				</tr>
				<tr>
					<td class="dark">
						<input type="hidden" name="edit_id" id="edit_id" />
						<input class="textbox" type="text" name="system_name" placeholder="System name (leave empty for general log entry)" id="system_1" style="width:96%;margin-left:0" oninput="showResult(this.value, '1')" />
					</td>
					<td class="dark">
						<input class="textbox" type="text" name="station_name" placeholder="Station name (optional)" id="statname" style="width:96%" oninput="showResult(this.value, '41', 'no', 'yes', 'no', document.getElementById('system_1').value)" />
					</td>
				</tr>
				<tr>
					<td class="dark" colspan="2">
						<textarea id="html" name="log_entry" placeholder="Log entry" rows="10" cols="40"></textarea>
					</td>
				</tr>
				<tr>
					<td class="dark" colspan="2">
						<a href="javascript:void(0)">
							<div class="button" onclick="update_data('log_form', '/add/log.php?do', true);tofront('null', true);$('#log_form').trigger('reset');return false">Submit log entry</div>
						</a>
						<span id="delete"></span>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>
