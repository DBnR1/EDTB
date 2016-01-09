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

$logsystem = $_GET["system"];
if (!$logsystem) exit("No system set");

$logsystem_id = $_GET["system_id"] == "" ? "-1" : $_GET["system_id"];
/*if (!$logsystem_id) exit("No system id set");*/

$pagetitle = "ED ToolBox";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
?>
<script type="text/javascript">
	function addname()
	{
		this.document.getElementById('system_1').value='<?php echo $logsystem ?>';
		this.document.getElementById('system_id').value='<?php echo $logsystem_id ?>';
	}
</script>
<div class="entries">
	<div class="entries_inner">
		<?php
		// get system-specific log
		$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_log.id, user_log.station_id, user_log.log_entry, user_log.stardate,
																edtb_stations.name AS station_name
																FROM user_log
																LEFT JOIN edtb_stations ON edtb_stations.id = user_log.station_id
																WHERE user_log.system_id = '" . $logsystem_id . "' OR user_log.system_name = '" . $logsystem . "'
																ORDER BY user_log.stardate DESC") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]));
		$num = mysqli_num_rows($log_res);

		if ($num > 0)
		{
			echo "<h2>System log for <a href='system.php?system_id=" . $logsystem_id . "'>" . $logsystem . "</a></h2>";
			echo '<hr>';

			while ($log_arr = mysqli_fetch_assoc($log_res))
			{
				$log_station_name = $log_arr["station_name"];
				$log_text = $log_arr["log_entry"];
				$date = date_create($log_arr["stardate"]);
				$log_added = date_modify($date, "+1286 years");

				echo '<h3><a href="index.php?action=editlog&amp;logid=' . $log_arr["id"] . '&amp;returnto=' . $_SERVER["REQUEST_URI"] . '" style="color:inherit;" title="Click to edit entry">';
				echo date_format($log_added, "j M Y, H:i");
				if (!empty($log_station_name))
				{
					echo "&nbsp;[Station: " . $log_station_name . "]";
				}
				echo '</a></h3><pre class="entriespre">';
				echo $log_text;
				echo '</pre>';
			}
		}
		else
		{
			echo "<h2>No log entries for " . $logsystem . "</h2><br />
					<a href=\"javascript:void(0);\" id=\"toggle\" onclick=\"addname();tofront('addlog')\" title=\"Add log entry\" style=\"color:inherit;\">Click here to add one</a>";
		}
		?>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
