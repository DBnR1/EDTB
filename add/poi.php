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

	$p_system = strtoupper($data["poi_system_name"]);
	$p_name = $data["poi_name"];
	$p_x = $data["poi_coordx"];
	$p_y = $data["poi_coordy"];
	$p_z = $data["poi_coordz"];
	$p_entry = $data["poi_text"];
	$p_id = $data["poi_edit_id"];
	$category_id = $data["category_id"];

	if ($p_id != "")
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_poi SET
													poi_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_name) . "',
													system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_system) . "',
													text = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_entry) . "',
													category_id = '" . $category_id . "',
													x = '" . $p_x . "',
													y = '" . $p_y . "',
													z = '" . $p_z . "'
													WHERE id = '" . $p_id . "'");
	}
	else if (isset($_GET["deleteid"]))
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM user_poi WHERE id = '" . $_GET["deleteid"] . "' LIMIT 1");
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_poi (poi_name, system_name, text, category_id, x, y, z)
													VALUES
														('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_name) . "',
														'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_system) . "',
														'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $p_entry) . "',
														'" . $category_id . "',
														'" . $p_x . "',
														'" . $p_y . "',
														'" . $p_z . "')");
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit();
}

if ($_SERVER['PHP_SELF'] == "/poi.php")
{
?>
    <div class="input" id="addpoi" style="text-align:center;">
		<form method="post" id="poi_form" action="poi.php">
			<div class="input-inner">
			<div class="suggestions" id="suggestions_33" style="margin-top:110px;margin-left:10px;"></div>
				<table>
					<tr>
						<td class="systeminfo_station_name" colspan="2">Add/edit Point of Interest</td>
					</tr>
					<tr>
						<td class="station_info_price_info2" style="width:90%;">
							<input type="hidden" name="poi_edit_id" id="poi_edit_id">
							<input class="textbox" type="text" name="poi_system_name" placeholder="System name" id="system_33" style="width:95%;" onkeyup="showResult(this.value, '33')" />
						</td>
						<td class="station_info_price_info2">
							<input class="textbox" type="text" name="poi_coordx" placeholder="x.x" id="coordsx_33" style="" />
							<input class="textbox" type="text" name="poi_coordy" placeholder="y.y" id="coordsy_33" style="" />
							<input class="textbox" type="text" name="poi_coordz" placeholder="z.z" id="coordsz_33" style="" />
						</td>
					</tr>
					<tr>
						<td class="station_info_price_info2">
							<input class="textbox" type="text" name="poi_name" id="poi_name" placeholder="POI name (optional)" style="width:95%;" />
						</td>
						<td class="station_info_price_info2">
							<select class="selectbox" name="category_id" id="category_id" style="width:auto;">
								<option value="0">Category (optional)</option>
								<?php
								$pcat_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM user_poi_categories");

								while ($pcat_arr = mysqli_fetch_assoc($pcat_res))
								{
									echo '<option value="' . $pcat_arr["id"] . '">' . $pcat_arr["name"] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="station_info_price_info2" colspan="2">
							<input class="textbox" type="text" name="poi_text" id="poi_text" placeholder="Text (optional)" style="width:95%;" />
						</td>
					</tr>
					<tr>
						<td class="station_info_price_info2" colspan="2">
							<button id="submitpoi">Submit Point of Interest</button>
							<span id="delete_poi"></span>
						</td>
					</tr>
				</table>
			</div>
		</form>
    </div>
	<script>
	$("#submitpoi").click(function(event)
	{
		event.preventDefault();
		update_data('poi_form', '/add/poi.php?do', true);
		tofront('null', true);
	});
	</script>
<?php
}