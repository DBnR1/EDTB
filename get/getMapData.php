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
 * Ajax backend file to fetch data for Neighborhood Map points
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$system = $_GET["system"];

/*
/*	 check if system has screenshots
*/

$screenshots = has_screenshots($system) ? '<a href="/gallery.php?spgmGal=' . urlencode($system) . '" title="View image gallery"><img src="/style/img/image.png" alt="Gallery" style="margin-left:5px;vertical-align:top" /></a>' : "";

/*
*	check if system is in the bookmarks
*/

$ress2 = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_bookmarks.comment, user_bookmarks.added_on
													FROM user_bookmarks
													WHERE user_bookmarks.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

$count2 = mysqli_num_rows($ress2);

if ($count2 > 0)
{
	$arras = mysqli_fetch_assoc($ress2);
	$comment = $arras["comment"];
	$added_on = $arras["added_on"];

	if ($comment != "")
	{
		echo 'Bookmark comment: ' . $comment . ' - ';
	}

	echo 'Bookmark added: ' . get_timeago($added_on, false) . '';
	echo '<br />';
}

/*
*	check if system is point of interest
*/

$ress = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_poi.text AS text, user_visited_systems.visit AS visit
													FROM user_poi LEFT JOIN user_visited_systems ON user_visited_systems.system_name = user_poi.system_name
													WHERE user_poi.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
													OR user_visited_systems.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
													UNION SELECT user_poi.text AS text, user_visited_systems.visit AS visit
													FROM user_poi RIGHT JOIN user_visited_systems ON user_visited_systems.system_name = user_poi.system_name
													WHERE user_poi.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
													OR user_poi.poi_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
													OR user_visited_systems.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

$count = mysqli_num_rows($ress);

if ($count > 0)
{
	$arra = mysqli_fetch_assoc($ress);
	$text = htmlspecialchars($arra["text"]);
	$visit = $arra["visit"];
	$visit_og = $arra["visit"];

	if (!$visit && !$text)
	{
		echo '<a href="system.php?system_name=' . urlencode($system) . '" style="color:inherit">' . $system . '</a>' . $screenshots . '<br />No additional information';
	}
	else
	{
		$logres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, LEFT(log_entry , 100) AS text
																FROM user_log
																WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
																ORDER BY stardate
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		$logged = mysqli_num_rows($logres);

		if (isset($visit))
		{
			$visit = date_create($visit);
			$visit_date = date_modify($visit, "+1286 years");

			$visit = date_format($visit_date, "d.m.Y, H:i");
		}

		if ($text != null)
		{
			echo "" . $text . "<br />";
		}

		if (!empty($visit))
		{
			$visits = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																					FROM user_visited_systems
																					WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'"));
			$visit_unix = strtotime($visit_og);
			$visit_ago = get_timeago($visit_unix);
			echo "<a href=\"/system.php?system_name=" . urlencode($system) . "\" style=\"color:inherit;\">" . $system . "</a>" . $screenshots . "&nbsp;&nbsp;|&nbsp;
			Total visits: " . $visits . "&nbsp;&nbsp;|&nbsp;&nbsp;First visit: " . $visit . " (" . $visit_ago . ")";
		}
		else
		{
			echo "<a href=\"/system.php?system_name=" . urlencode($system) . "\" style=\"color:inherit;\">" . $system . "</a>";
		}

		if ($logged > 0)
		{
			$logarr = mysqli_fetch_assoc($logres);
			$text = $logarr["text"];

			echo '<br />
					<a href="/log.php?system=' . urlencode($system) . '" style="color:inherit;font-weight:bold" title="View the log for this system">
						' . $text . ' ...
					</a>';
		}
	}
	exit;
}

echo 'No additional information';
