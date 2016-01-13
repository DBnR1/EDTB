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

$pagetitle = "Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/add/poi.php");

/*
*	show item
*/

function makeitem($arr, $type, &$to_last, &$i)
{
	$item_id = $arr["id"];
	$item_text = $arr["text"];
	$item_name = $arr["item_name"];
	$item_system_name = $arr["system_name"];
	$item_system_id = $arr["system_id"];
	$item_cat_name = $arr["catname"];

	$item_coordx = $arr["item_coordx"];
	$item_coordy = $arr["item_coordy"];
	$item_coordz = $arr["item_coordz"];

	$usable = usable_coords();
	$usex = $usable["x"];
	$usey = $usable["y"];
	$usez = $usable["z"];

	/*
	*	if coords are not set, see if user has calculated them
	*/

	if (!is_numeric($item_coordx) && !is_numeric($item_coordy) && !is_numeric($item_coordz))
	{
		$c_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
															FROM user_systems_own
															WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $item_system_name) . "'
															LIMIT 1")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$c_arr = mysqli_fetch_assoc($c_res);

		$item_coordx = $c_arr["x"] == "" ? "" : $c_arr["x"];
		$item_coordy = $c_arr["y"] == "" ? "" : $c_arr["y"];
		$item_coordz = $c_arr["z"] == "" ? "" : $c_arr["z"];
	}

	/*
	*	if poi has coordinates, show them first
	*/

	if (is_numeric($item_coordx) && is_numeric($item_coordy) && is_numeric($item_coordz) || isset($arr["last"]))
	{
		if (!isset($arr["last"]))
		{
			$distance = number_format(sqrt(pow(($item_coordx-($usex)), 2)+pow(($item_coordy-($usey)), 2)+pow(($item_coordz-($usez)), 2)), 1)." ly";
		}
		else
		{
			$distance = "n/a";
		}

		// if visited, change border color
		$style_override = "";
		$visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																				FROM user_visited_systems
																				WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $item_system_name) . "'
																				LIMIT 1"));
		if ($visited)
		{
			$style_override = ' style="border-left: 3px solid #3DA822;"';
		}

		if ($i % 2)
		{
			$tdclass = "station_info_price_info";
		}
		else
		{
			$tdclass = "station_info_price_info2";
		}

		echo '<tr>
				<td class="' . $tdclass . '" style="min-width:420px;max-width:500;">
					<div class="poi"' . $style_override . '>
						<a href="javascript:void(0);" onclick="update_values(\'/get/get' . $type . 'EditData.php?' . $type . '_id=' . $item_id . '\',\'' . $item_id . '\');tofront(\'add' . $type . '\');" style="color:inherit;" title="Click to edit entry">';

		$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																				FROM user_log
																				WHERE system_id = '" . $item_system_id . "'
																				AND system_id != '0'
																				LIMIT 1"));

		$loglink = "";
		if ($logged > 0)
		{
			$loglink = '&nbsp;[&nbsp;<a href="log.php?system=' . $item_system_name . '&system_id=' . $item_system_id . '" style="color:inherit;" title="Click to see log">Log entry</a>&nbsp;]&nbsp;';
		}

		if ($item_system_id != "" && $item_system_id != "0")
		{
			echo '(' . $distance . ')</a>&nbsp;<a title="System information" href="/system.php?system_id=' . $item_system_id . '" style="color:inherit;">';
		}
		else
		{
			echo '(' . $distance . ')</a>&nbsp;<a title="System information" href="/system.php?system_name=' . urlencode($item_system_name) . '" style="color:inherit;">';
		}

		if (empty($item_name))
		{
			echo $item_system_name;
		}
		else
		{
			echo $item_name;
		}

		echo '</a>' . $loglink  . '<span class="right" style="margin-left:5px;">' . $item_cat_name . '</span><br />';

		// make a link if text includes url
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		if (preg_match($reg_exUrl, $item_text, $url))
		{
			if (mb_strlen($item_text) >= 60)
			{
				$urli = "" . substr($item_text, 0, 60) . "...";
			}
			else
			{
				$urli = $poi_text;
			}
			$item_text = preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $item_text);
		}
		echo nl2br($item_text);
		echo '</div></td></tr>';
		$i++;
	}
	else
	{
		$to_last[$i]["id"] = $item_id;
		$to_last[$i]["text"] = $item_text;
		$to_last[$i]["item_name"] = $item_name;
		$to_last[$i]["system_name"] = $item_system_name;
		$to_last[$i]["system_id"] = $item_system_id;
		$to_last[$i]["catname"] = $item_cat_name;
		$to_last[$i]["last"] = true;
		$i++;
	}
}

/*
*	make item table
*/

function maketable($res, $type, &$to_last)
{
	global $coordx, $coordy, $coordz;

	$num = mysqli_num_rows($res);

	echo '<table>';

	if ($num > 0)
	{
		if (!is_numeric($coordx) || !is_numeric($coordy) || !is_numeric($coordz))
		{
			echo "<tr><td class='station_info_price_info' style='min-width:420px;max-width:500;'><p><strong>No coordinates for current location, last known location used.</strong></p></td></tr>";
		}

		$i = 0;
		$to_last = array();
		while ($arr = mysqli_fetch_assoc($res))
		{
			echo makeitem($arr, $type, $to_last, $i);
		}

		/*
		*	display item's with no coordinates at the end
		*/

		foreach ($to_last as $item)
		{
			echo makeitem($item, $type);
		}
	}
	else
	{
		if ($type == "Poi")
		{
			echo '<tr><td class="station_info_price_info" style="min-width:420px;max-width:500;"><strong>No points of interest.<br />Click the "Points of Interest" text to add one.</strong></td></tr>';
		}
		else
		{
			echo '<tr><td class="station_info_price_info" style="min-width:420px;max-width:500;"><strong>No bookmarks.<br />Click the allegiance icon on the top left corner to add one.</strong></td></tr>';
		}
	}

	echo '</table>';
}
?>
<div class="entries">
	<div class="entries_inner">
		<table>
			<tr>
				<td class="systeminfo_station_name" style="min-width:400px;"><a href="javascript:void(0);" onclick="tofront('addPoi');update_values('/get/getPoiEditData.php?Poi_id=0');$('#system_33').focus();" title="Add point of interest">Points of Interest</a></td>
				<td class="systeminfo_station_name" style="min-width:400px;">Bookmarks</td>
			</tr>
			<tr>
				<td style="vertical-align:top;">
					<?php
					$usable = usable_coords();
					$usex = $usable["x"];
					$usey = $usable["y"];
					$usez = $usable["z"];

					// get poi in correct order
					$poi_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_poi.id, user_poi.poi_name AS item_name, user_poi.system_name, user_poi.text,
																			user_poi.x AS item_coordx, user_poi.y AS item_coordy, user_poi.z AS item_coordz,
																			edtb_systems.id AS system_id,
																			user_poi_categories.name AS catname
																			FROM user_poi
																			LEFT JOIN edtb_systems ON user_poi.system_name = edtb_systems.name
																			LEFT JOIN user_poi_categories ON user_poi_categories.id = user_poi.category_id
																			ORDER BY sqrt(pow((item_coordx-(" . $usex . ")),2)+pow((item_coordy-(" . $usey . ")),2)+pow((item_coordz-(" . $usez . ")),2))")
																			or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
					echo maketable($poi_res, "Poi");
					?>
				</td>
				<td style="vertical-align:top;">
					<?php
					// get bookmarks
					$bm_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name,
																			user_bookmarks.comment as text, user_bookmarks.added_on,
																			edtb_systems.x AS item_coordx,
																			edtb_systems.y AS item_coordy,
																			edtb_systems.z AS item_coordz,
																			user_bm_categories.name AS catname
																			FROM user_bookmarks
																			LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
																			LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
																			ORDER BY sqrt(pow((item_coordx-(" . $usex . ")),2)+pow((item_coordy-(" . $usey . ")),2)+pow((item_coordz-(" . $usez . ")),2))")
																			or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
					echo maketable($bm_res, "Bm");
					?>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
