<?php
/**
 * Trilaterate coordinates from user input and send to EDSM
 *
 * No description
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

if (isset($_GET["do"]))
{
	$data = json_decode($_REQUEST["input"], true);

	$target_system = $data["target_system"];

	$reference_1_system = $data["reference_1"];
	$reference_2_system = $data["reference_2"];
	$reference_3_system = $data["reference_3"];
	$reference_4_system = $data["reference_4"];

	$reference_1_coordinates = $data["reference_1_coordinates"];
	$reference_2_coordinates = $data["reference_2_coordinates"];
	$reference_3_coordinates = $data["reference_3_coordinates"];
	$reference_4_coordinates = $data["reference_4_coordinates"];

	$reference_1_distance = $data["reference_1_distance"];
	$reference_2_distance = $data["reference_2_distance"];
	$reference_3_distance = $data["reference_3_distance"];
	$reference_4_distance = $data["reference_4_distance"];

	if (is_numeric($reference_1_distance) && is_numeric($reference_2_distance) && is_numeric($reference_3_distance) && is_numeric($reference_4_distance))
	{
		$reference_distances = $reference_1_system . ":::" . $reference_1_distance . "---" . $reference_2_system . ":::" . $reference_2_distance . "---" . $reference_3_system . ":::" . $reference_3_distance . "---" . $reference_4_system . ":::" . $reference_4_distance;

		$reference_1 = explode(",", $reference_1_coordinates);
		$reference_2 = explode(",", $reference_2_coordinates);
		$reference_3 = explode(",", $reference_3_coordinates);
		$reference_4 = explode(",", $reference_4_coordinates);

		$system1 = array($reference_1[0], $reference_1[1], $reference_1[2], $reference_1_distance);
		$system2 = array($reference_2[0], $reference_2[1], $reference_2[2], $reference_2_distance);
		$system3 = array($reference_3[0], $reference_3[1], $reference_3[2], $reference_3_distance);
		$system4 = array($reference_4[0], $reference_4[1], $reference_4[2], $reference_4_distance);

		$newcoords = trilateration3d($system1, $system2, $system3, $system4);
		$newcoords_x = $newcoords[0];
		$newcoords_y = $newcoords[1];
		$newcoords_z = $newcoords[2];

		$system_exists = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																					FROM user_systems_own
																					WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "'
																					LIMIT 1"));

		if ($system_exists == 0)
		{
			mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_systems_own
														(name, x, y, z, reference_distances)
														VALUES
														('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "',
														'" . $newcoords_x . "',
														'" . $newcoords_y . "',
														'" . $newcoords_z . "',
														'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $reference_distances) . "')") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		}
		else
		{
			mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_systems_own
														SET name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "',
															x = '" . $newcoords_x . "',
															y = '" . $newcoords_y . "',
															z = '" . $newcoords_z . "',
															reference_distances = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $reference_distances) . "'
														WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "'
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		}

		/**
		 * submit to EDSM
		 */

		$json_string = '{
		"data": {
			"fromSoftware": "ED ToolBox",
			"fromSoftwareVersion": "' . $settings["edtb_version"] . '",
			"commander": "' . $settings["edsm_cmdr_name"] . '",
			"p0": {
				"name": "' . $target_system . '"
			},
			"refs": [{
				"name": "' . $reference_1_system . '",
				"dist": ' . $reference_1_distance . '
			}, {
				"name": "' . $reference_2_system . '",
				"dist": ' . $reference_2_distance . '
			}, {
				"name": "' . $reference_3_system . '",
				"dist": ' . $reference_3_distance . '
			}, {
				"name": "' . $reference_4_system . '",
				"dist": ' . $reference_4_distance . '
			}]
		}
		}';

		$opts = array('http' => array('method' => 'POST', 'header' => "Content-type: json\r\n" ."Referer: http://www.edsm.net/api-v1/submit-distances\r\n", 'content' => $json_string));

		$context = stream_context_create($opts);

		$result = file_get_contents('http://www.edsm.net/api-v1/submit-distances', false, $context);

		write_log($json_string, __FILE__, __LINE__);
		write_log($result, __FILE__, __LINE__);
	}
	else
	{
		write_log("Error: Distances not numeric or all distances not given.", __FILE__, __LINE__);
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}
?>
<script>
	var clipboard = new Clipboard('.btn');

	clipboard.on('success', function(e) {
		//console.log(e);
	});

	clipboard.on('error', function(e) {
		//console.log(e);
	});
</script>
<div class="input" id="calculate" style="text-align:center">
	<form method="post" id="calc_form" action="coorddata.php">
		<div class="input-inner">
			<table>
				<tr>
					<td class="heading" colspan="2">Calculate Coordinates
						<span class="right">
							<a href="javascript:void(0)" onclick="tofront('calculate')" title="Close form">
								<img src="/style/img/close.png" alt="X" style="width:16px;height:16px" />
							</a>
						</span>
					</td>
				</tr>
				<tr>
					<td class="light" colspan="2" style="text-align:left;font-size:13px">Use this form to calculate coordinates for systems that have no known coordinates<br />in the <a href="http://edsm.net" target="_BLANK">EDSM</a><img src="/style/img/external_link.png" alt="ext" style="margin-left:4px" /> database by inserting distances from the system map into this form.<br /><br />
					Clicking the <strong>clipboard</strong> icon will copy the system name to the client side clipboard.<br /><br />
					Clicking the <strong>magic</strong> icon will send the system name to the ED client.<br />
					<strong>Note:</strong> have the system map open and the search box targeted before clicking the icon.</td>
				</tr>
				<tr>
					<td class="dark" colspan="2" style="font-size:14px">
						<strong>Target System:</strong> <?php echo $curSys["name"]?><input class="textbox" type="hidden" name="target_system" value="<?php echo $curSys["name"]?>" id="target_system" />
					</td>
				</tr>
				<tr id="ref_id">
					<td class="light" style="text-align:right"><strong><a href="javascript:void(0)" onclick="set_reference_systems(true)" title="Change reference systems">Reference system</a></strong></td>
					<td class="light">
						<strong>Distance (ly)</strong>
						<div class="button" id="clear" style="width:80px;white-space:nowrap;margin-top:3px" onclick="$('#ref_1_dist').val('');$('#ref_2_dist').val('');$('#ref_3_dist').val('');$('#ref_4_dist').val('');return false">Clear All
						</div>
					</td>
				</tr>
				<tr id="refid" style="visibility:hidden"><td colspan="2" style="visibility:hidden"></td></tr>
				<tr>
					<td class="light" colspan="2">
						<button id="submitc" onclick="update_data('calc_form', '/add/coord.php?do', true);tofront('null', true);return false">Submit Query</button>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>
