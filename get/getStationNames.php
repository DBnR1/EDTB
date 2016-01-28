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
 * Ajax backend file to fetch station names
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
$action = isset($_GET["action"]) ? $_GET["action"] : "";

if (isset($_GET["q"]) && !empty($_GET["q"]) && isset($_GET["divid"]))
{
	$search = $_GET["q"];
	$divid = $_GET["divid"];

	$addtl = "";
	if (isset($_GET["allegiance"]) && $_GET["allegiance"] != "undefined")
	{
		$addtl .= "&allegiance=" . $_GET['allegiance'] . "";
	}

	if (isset($_GET["system_allegiance"]) && $_GET["system_allegiance"] != "undefined")
	{
		$addtl .= "&system_allegiance=" . $_GET['system_allegiance'] . "";
	}

	if (isset($_GET["power"]) && $_GET["power"] != "undefined")
	{
		$addtl .= "&power=" . $_GET['power'] . "";
	}

	$suggest_query = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT(edtb_systems.name) AS system_name,
																edtb_systems.id AS system_id,
																edtb_systems.x,
																edtb_systems.y,
																edtb_systems.z,
																edtb_stations.name AS station_name
																FROM edtb_systems
																LEFT JOIN edtb_stations ON edtb_stations.system_id = edtb_systems.id
																WHERE edtb_stations.name LIKE('%" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $search) . "%')
																ORDER BY edtb_stations.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $search) . "',
																edtb_stations.name
																LIMIT 30") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	if (isset($_GET["sysid"]) && $_GET["sysid"] != "no")
	{
		$suggest_query = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_systems.name AS system_name,
																	edtb_systems.id AS system_id,
																	edtb_systems.x,
																	edtb_systems.y,
																	edtb_systems.z,
																	edtb_stations.name AS station_name,
																	edtb_stations.id AS station_id
																	FROM edtb_systems
																	LEFT JOIN edtb_stations ON edtb_stations.system_id = edtb_systems.id
																	WHERE edtb_stations.name LIKE('%" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $search) . "%')
																	AND edtb_systems.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_GET["sysid"]) . "'
																	ORDER BY edtb_stations.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $search) . "',
																	edtb_stations.name
																	LIMIT 30") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	$found = mysqli_num_rows($suggest_query);

	if ($found == 0)
	{
		echo '<a href="#">Nothing found</a>';
		((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
		exit;
	}

	while ($suggest = mysqli_fetch_assoc($suggest_query))
	{
		if ($_GET["link"] == "yes")
		{
			?>
			<a href="/system.php?system_id=<?php echo $suggest['system_id']?>">
				<?php echo $suggest['station_name']?>&nbsp;&nbsp;(<?php echo $suggest['system_name']?>)
			</a><br />
			<?php
		}
		elseif ($_GET["idlink"] == "yes")
		{
			?>
			<a href="/nearest_systems.php?system=<?php echo $suggest['system_id']?><?php echo $addtl?>">
				<?php echo $suggest['station_name']?>&nbsp;&nbsp;(<?php echo $suggest['system_name']?>)
			</a><br />
			<?php
		}
		elseif ($_GET["sysid"] != "no")
		{
			?>
			<a href="javascript:void(0);" onclick="setl('<?php echo $suggest['station_name']?>','<?php echo $suggest['station_id']?>');">
				<?php echo $suggest['station_name']?>
			</a><br />
			<?php
		}
		else
		{
			$suggest_coords = "" . $suggest['x'] . "," . $suggest['y'] . "," . $suggest['z'] . "";
			?>
			<a href="javascript:void(0);" onclick="setResult('<?php echo str_replace("'", "", $suggest['system_name']); ?>', '<?php echo $suggest_coords; ?>', '<?php echo $divid ?>');">
				<?php echo $suggest['system_name'] ?>
			</a><br />
			<?php
		}
	}
}

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
