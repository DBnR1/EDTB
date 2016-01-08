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
													LIMIT 1");
	}
	else if (isset($_GET["deleteid"]))
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	DELETE FROM user_log
													WHERE id = '" . $_GET["deleteid"] . "'
													LIMIT 1");
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_log (system_id, system_name, station_id, log_entry)
													VALUES
													('" . $l_system . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_system_name) . "',
													'" . $l_station . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $l_entry) . "')");
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
	exit();
}

?>
<!-- stuff for log editor -->
<link type="text/css" rel="stylesheet" href="/source/markitup/skins/markitup/style.css" />
<link type="text/css" rel="stylesheet" href="/source/markitup/sets/html/style.css" />
<script type="text/javascript" src="/source/markitup/sets/html/set.js"></script>
<script type="text/javascript" src="/source/markitup/jquery.markitup.js"></script>

<div class="input" id="addlog">
	<form method="post" id="log_form" action="log.php">
		<div class="input-inner">
			<div class="suggestions" id="suggestions_41" style="margin-left:400px;margin-top:73px;;"></div>
			<div class="suggestions" id="suggestions_1" style="margin-left:10px;margin-top:73px;"></div>
			<table>
				<tr>
					<td class="systeminfo_station_name" colspan="2">Add/Edit Log Entry</td>
				</tr>
				<tr>
					<td class="station_info_price_info2">
						<input type="hidden" name="edit_id" id="edit_id" />
						<!--<input type="hidden" name="system_id" id="system_id" />
						<input type="hidden" name="station_id" id="station_id" />-->
						<input class="textbox" type="text" name="system_name" placeholder="System name (leave empty for general log entry)" id="system_1" style="width:96%;margin-left:0px;" oninput="showResult(this.value, '1')" />
					</td>
					<td class="station_info_price_info2">
						<input class="textbox" type="text" name="station_name" placeholder="Station name (optional)" id="statname" style="width:96%;" oninput="showResult(this.value, '41', 'no', 'yes', 'no', document.getElementById('system_1').value)" /><br />
					</td>
				</tr>
				<tr>
					<td class="station_info_price_info2" colspan="2">
						<textarea id="html" name="log_entry" placeholder="Log entry" rows="10" cols="40"></textarea>
					</td>

				</tr>
				<tr>
					<td class="station_info_price_info2" colspan="2">
						<button id="submits">Submit log entry</button>
						<span id="delete"></span>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>
<script>
$("#submits").click(function(event)
{
	update_data('log_form', '/add/log.php?do', true);
	tofront('null', true);
	$('#log_form').trigger('reset');
	return false;
});
</script>