<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2015 Mauri Kujala (contact@edtb.xyz)
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

	$reference_1_coordinates = $data["reference_1_coordinates"];
	$reference_2_coordinates = $data["reference_2_coordinates"];
	$reference_3_coordinates = $data["reference_3_coordinates"];
	$reference_4_coordinates = $data["reference_4_coordinates"];

	$reference_1_distance = $data["reference_1_distance"];
	$reference_2_distance = $data["reference_2_distance"];
	$reference_3_distance = $data["reference_3_distance"];
	$reference_4_distance = $data["reference_4_distance"];

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
													(name, x, y, z)
													VALUES
													('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "',
													'" . $newcoords_x . "',
													'" . $newcoords_y . "',
													'" . $newcoords_z . "')");
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_systems_own
													SET name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $target_system) . "',
														x = '" . $newcoords_x . "',
														y = '" . $newcoords_y . "',
														z = '" . $newcoords_z . "'
														LIMIT 1");
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit();
}
?>
<div class="input" id="calculate" style="text-align:center;">
    <div class="input-inner" style="margin-left:150px;">Calculate Coordinates<br />
        <form method="post" id="calc_form" action="coorddata.php">
            <input class="textbox" type="text" name="target_system" value="<?php echo $current_system?>" id="target_system" placeholder="Target system" style="width:65%;" /><br />
            <?php
            $i = 1;
            foreach ($referencesystems as $ref_name => $ref_coordinates)
            {
                echo '<input class="textbox" type="text" name="reference_' . $i . '" value="' . $ref_name . '" placeholder="Reference System ' . $i . '" style="width:20%;" onkeyup="showResult(this.value, \'' . $i . '\')" readonly="readonly" />
                <input class="textbox" type="text" name="reference_' . $i . '_coordinates" placeholder="Reference System ' . $i . ' Coordinates" style="width:42%;" value="' . $ref_coordinates . '" readonly="readonly" />
                <input class="textbox" type="text" name="reference_' . $i . '_distance" placeholder="Distance" style="width:15%;" /><br />';
                $i++;
            }
            ?>
        </form>
        <button onclick="update_data('calc_form', '/add/coord.php?do', true);tofront('null', true);">Submit Query</button>
    </div>
</div>
