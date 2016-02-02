<?php
/**
 * Ajax backend file to fetch reference systems for trilateration
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

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

$system_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, reference_distances
															FROM user_systems_own
															WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $curSys["name"]) . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

$system_exists = mysqli_num_rows($system_res);

if ($system_exists > 0)
{
	$system_arr = mysqli_fetch_assoc($system_res);
	$values = explode("---", $system_arr["reference_distances"]);

	$i = 1;
	foreach ($values as $value)
	{
		$values2 = explode(":::", $value);

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

$referencesystems = isset($_GET["standard"]) ? reference_systems(true) : reference_systems();

$i = 1;
foreach ($referencesystems as $ref_name => $ref_coordinates)
{
	$ref_rname = $ref_name;
	if ($ref[$i]["name"] != "")
	{
		$ref_rname = $ref[$i]["name"];
	}
	echo '<tr class="refid">';
	echo '	<td class="dark" style="text-align:right">';
	echo '		<input class="textbox" type="hidden" id="' . $i . '" name="reference_' . $i . '" value="' . $ref_rname . '" />';
	echo '		<input class="textbox" type="hidden" name="reference_' . $i . '_coordinates" value="' . $ref_coordinates . '" />';
	echo '		<span class="left">';
	echo '			<a class="send" href="javascript:void(0)" title="Send to ED" data-send="' . $ref_rname . '" data-id="' . $i . '">';
	echo '				<img class="btn" src="/style/img/magic.png" alt="Send" style="margin-right:5px" />';
	echo '			</a>';
	echo '			<a href="javascript:void(0)" title="Copy to clipboard">';
	echo '				<img class="btn" src="/style/img/clipboard.png" alt="Copy" data-clipboard-text="' . $ref_rname . '" />';
	echo '			</a>';
	echo '		</span>';
	echo '		<strong>' . $ref_rname . '</strong>';
	echo '	</td>';
	echo '	<td class="dark">';
	echo '		<input class="textbox" type="text" id="ref_' . $i . '_dist" name="reference_' . $i . '_distance" value="' . $ref[$i]["distance"] . '" placeholder="1234.56" style="width:100px" /><br />';
	echo '		<span class="settings_info" style="font-size:11px">No commas or spaces</span>';
	echo '	</td>';
	echo '</tr>';
	$i++;
}

echo '	<script>
			$("a.send").click(function()
			{
				$.get("/action/shipControls.php?send=" + $(this).data("send"));
				$(\'#ref_\' + $(this).data("id") + \'_dist\').focus();
				console.log($(this).data("send"));
			});
		</script>';