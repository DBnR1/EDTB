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

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

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

	$reference_distances = "" . $reference_1_system . ":" . $reference_1_distance . "-" . $reference_2_system . ":" . $reference_2_distance . "-" . $reference_3_system . ":" . $reference_3_distance . "-" . $reference_4_system . ":" . $reference_4_distance . "";

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
													'" . $reference_distances . "')") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_systems_own
													SET name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "',
														x = '" . $newcoords_x . "',
														y = '" . $newcoords_y . "',
														z = '" . $newcoords_z . "',
														reference_distances = '" . $reference_distances . "'
													WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}

	/*
	*	submit to EDSM
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

	$opts = array('http' => array('method' => 'POST', 'header' => "Content-type: json\r\n" ."Referer: http://www.edsm.net/api-v1/submit-distances\r\n", 'content' => $json_string ) );

	$context = stream_context_create($opts);

	$result = file_get_contents('http://www.edsm.net/api-v1/submit-distances', false, $context);

	write_log($json_string, __FILE__, __LINE__);
	write_log($result, __FILE__, __LINE__);

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit();
}

$system_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, reference_distances
															FROM user_systems_own
															WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $current_system) . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

$system_exists = mysqli_num_rows($system_res);

if ($system_exists > 0)
{
	$system_arr = mysqli_fetch_assoc($system_res);
	$values = explode("-", $system_arr["reference_distances"]);

	$i = 1;
	foreach ($values as $value)
	{
		$values2 = explode(":", $value);

		$ref[$i]["name"] = $values2[0];
		$ref[$i]["distance"] = $values2[1];
		$i++;
	}
}
else
{
	$ref[1]["name"] = "";
	$ref[1]["distance"] = "";
	$ref[2]["name"] = "";
	$ref[2]["distance"] = "";
	$ref[3]["name"] = "";
	$ref[3]["distance"] = "";
	$ref[4]["name"] = "";
	$ref[4]["distance"] = "";
}
?>
<div class="input" id="calculate" style="text-align:center;">
	<form method="post" id="calc_form" action="coorddata.php">
		<div class="input-inner">
			<table>
				<tr>
					<td class="heading" colspan="2">Calculate Coordinates<span class="right"><a href="javascript:void(0);" onclick="tofront('calculate');" title="Close form"><img src="/style/img/close.png" alt="close" /></a></span></td>
				</tr>
				<tr>
					<td class="light" colspan="2" style="text-align:left;">Target System</td>
				</tr>
				<tr>
					<td class="dark" colspan="2">
						<input class="textbox" type="text" name="target_system" value="<?php echo $current_system?>" id="target_system" placeholder="Target system" style="width:300px;" />
					</td>
				</tr>
				<tr>
					<td class="light" style="text-align:right;">Reference system</td>
					<td class="light">Distance&nbsp;&nbsp;&nbsp;<button id="clear" style="width:80px;white-space:nowrap;" onclick="$('#ref_1_dist').val('');$('#ref_2_dist').val('');$('#ref_3_dist').val('');$('#ref_4_dist').val('');return false;">Clear All</button></td>
				</tr>
					<?php
					$i = 1;
					foreach ($referencesystems as $ref_name => $ref_coordinates)
					{
						$ref_rname = $ref_name;
						if ($ref[$i]["name"] != "")
						{
							$ref_rname = $ref[$i]["name"];
						}
						echo '<tr><td class="dark" style="text-align:right;"><input class="textbox" type="hidden" name="reference_' . $i . '" value="' . $ref_rname . '" />
						<input class="textbox" type="hidden" name="reference_' . $i . '_coordinates" value="' . $ref_coordinates . '" />
						<strong>' . $ref_rname . '</strong></td><td class="dark">
						<input class="textbox" type="text" id="ref_' . $i . '_dist" name="reference_' . $i . '_distance" value="' . $ref[$i]["distance"] . '" placeholder="Distance" style="width:100px;" /></td></tr>';
						$i++;
					}
					?>
				<tr>
					<td class="light" colspan="2">
						<button id="submitc" onclick="update_data('calc_form', '/add/coord.php?do', true);tofront('null', true);return false;">Submit Query</button>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>
