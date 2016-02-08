<?php
/**
 * Ajax backend file to fetch points of interest and bookmarks
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
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

/**
 * get usable coordinates
 */

$usable = usable_coords();
$usex = $usable["x"];
$usey = $usable["y"];
$usez = $usable["z"];

/**
 * Make items
 *
 * @param array $arr
 * @param string $type
 * @param int $i
 * @return string
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function makeitem($arr, $type, &$i)
{
	global $usex, $usey, $usez;

	$item_id = $arr["id"];
	$item_text = $arr["text"];
	$item_name = $arr["item_name"];
	$item_system_name = $arr["system_name"];
	$item_system_id = $arr["system_id"];
	$item_cat_name = $arr["catname"];

	$item_coordx = $arr["item_coordx"];
	$item_coordy = $arr["item_coordy"];
	$item_coordz = $arr["item_coordz"];

	if (valid_coordinates($item_coordx, $item_coordy, $item_coordz))
	{
		$distance = number_format(sqrt(pow(($item_coordx-($usex)), 2)+pow(($item_coordy-($usey)), 2)+pow(($item_coordz-($usez)), 2)), 1)." ly";
	}
	else
	{
		$distance = "n/a";
	}

	// if visited, change border color
	$visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																			FROM user_visited_systems
																			WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $item_system_name) . "'
																			LIMIT 1"));

	$style_override = $visited ? ' style="border-left: 3px solid #3DA822"' : "";

	$tdclass = $i % 2 ? "dark" : "light";

	// check if system has screenshots
	$screenshots = has_screenshots($item_system_name) ? '<a href="/Gallery.php?spgmGal=' . urlencode($item_system_name) . '" title="View image gallery"><img src="/style/img/image.png" class="icon" alt="Gallery" style="margin-left:5px;margin-right:0;vertical-align:top" /></a>' : "";

	// check if system is logged
	$loglink = is_logged($item_system_name) ? '<a href="log.php?system=' . urlencode($item_system_name) . '" style="color:inherit" title="System has log entries"><img src="/style/img/log.png" class="icon" style="margin-left:5px;margin-right:0;vertical-align:top" /></a>' : "";

	echo '<tr>
			<td class="' . $tdclass . '" style="min-width:420px;max-width:500px">
				<div class="poi"' . $style_override . '>
					<a href="javascript:void(0)" onclick="update_values(\'/get/get' . $type . 'EditData.php?' . $type . '_id=' . $item_id . '\',\'' . $item_id . '\');tofront(\'add' . $type . '\')" style="color:inherit" title="Click to edit entry">';

	echo '(' . $distance . ')';

	if (!empty($item_system_id))
	{
		echo '</a>&nbsp;<a title="System information" href="/System.php?system_id=' . $item_system_id . '" style="color:inherit">';
	}
	elseif ($item_system_name != "")
	{
		echo '</a>&nbsp;<a title="System information" href="/System.php?system_name=' . urlencode($item_system_name) . '" style="color:inherit">';
	}
	else
	{
		echo '</a>&nbsp;<a href="#" style="color:inherit">';
	}

	if (empty($item_name))
	{
		echo $item_system_name;
	}
	else
	{
		echo $item_name;
	}

	echo '</a>' . $loglink . $screenshots . '<span class="right" style="margin-left:5px">' . $item_cat_name . '</span><br />';

	echo nl2br($item_text);
	echo '		</div>';
	echo '	</td>';
	echo '</tr>';
	$i++;
}

/**
 * Make item table
 *
 * @param resource $res
 * @param string $type
 * @return string
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function maketable($res, $type)
{
	global $curSys;

	$num = mysqli_num_rows($res);

	echo '<table>';

	if ($num > 0)
	{
		if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
		{
			echo '<tr>';
			echo '	<td class="dark" style="min-width:420px;max-width:500px">';
			echo '		<p><strong>No coordinates for current location, last known location used.</strong></p>';
			echo '	</td>';
			echo '</tr>';
		}

		$i = 0;
		$to_last = array();
		while ($arr = mysqli_fetch_assoc($res))
		{
			echo makeitem($arr, $type, $i);
		}
	}
	else
	{
		if ($type == "Poi")
		{
			echo '<tr>';
			echo '	<td class="dark" style="min-width:420px;max-width:500px">';
			echo '		<strong>No points of interest.<br />Click the "Points of Interest" text to add one.</strong>';
			echo '	</td>';
			echo '</tr>';
		}
		else
		{
			echo '<tr>';
			echo '	<td class="dark" style="min-width:420px;max-width:500px">';
			echo '		<strong>No bookmarks.<br />Click the allegiance icon on the top left corner to add one.</strong>';
			echo '	</td>';
			echo '</tr>';
		}
	}

	echo '</table>';
}
?>
<script>
	$("#addp").click(function()
	{
		tofront("addPoi");
		update_values("/get/getPoiEditData.php?Poi_id=0");
		$("#system_33").focus();
	});
</script>
<table>
	<tr>
		<td class="heading" style="min-width:400px">
			<a href="javascript:void(0)" id="addp" title="Add point of interest">Points of Interest</a>
		</td>
		<td class="heading" style="min-width:400px">
			Bookmarks
		</td>
	</tr>
	<tr>
		<td style="vertical-align:top;padding:0">
			<?php
			// get poi in correct order
			$poi_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE user_poi.id, user_poi.poi_name AS item_name,
																	user_poi.system_name, user_poi.text,
																	IFNULL(user_poi.x, user_systems_own.x) AS item_coordx,
																	IFNULL(user_poi.y, user_systems_own.y) AS item_coordy,
																	IFNULL(user_poi.z, user_systems_own.z) AS item_coordz,
																	edtb_systems.id AS system_id,
																	user_poi_categories.name AS catname
																	FROM user_poi
																	LEFT JOIN edtb_systems ON user_poi.system_name = edtb_systems.name
																	LEFT JOIN user_poi_categories ON user_poi_categories.id = user_poi.category_id
																	LEFT JOIN user_systems_own ON user_poi.system_name = user_systems_own.name
																	ORDER BY -(sqrt(pow((item_coordx-(" . $usex . ")),2)+pow((item_coordy-(" . $usey . ")),2)+pow((item_coordz-(" . $usez . ")),2))) DESC, poi_name, system_name")
																	or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
			echo maketable($poi_res, "Poi");
			?>
		</td>
		<td style="vertical-align:top;padding:0">
			<?php
			// get bookmarks
			$bm_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name,
																	user_bookmarks.comment as text, user_bookmarks.added_on,
																	IFNULL(edtb_systems.x, user_systems_own.x) AS item_coordx,
																	IFNULL(edtb_systems.y, user_systems_own.y) AS item_coordy,
																	IFNULL(edtb_systems.z, user_systems_own.z) AS item_coordz,
																	user_bm_categories.name AS catname
																	FROM user_bookmarks
																	LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
																	LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
																	LEFT JOIN user_systems_own ON user_bookmarks.system_name = user_systems_own.name
																	ORDER BY -(sqrt(pow((item_coordx-(" . $usex . ")),2)+pow((item_coordy-(" . $usey . ")),2)+pow((item_coordz-(" . $usez . ")),2))) DESC, system_name")
																	or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
			$i = 0;
			echo maketable($bm_res, "Bm");
			?>
		</td>
	</tr>
</table>
