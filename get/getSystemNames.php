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
$action = isset($_GET["action"]) ? $_GET["action"] : "";

if (isset($_GET["q"]) && $_GET["q"] != "" && isset($_GET["divid"]))
{
	$search = addslashes($_GET["q"]);
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

	$suggest_query = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT(name),
																id, x, y, z
																FROM edtb_systems
																WHERE name LIKE('%" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $search) . "%')
																ORDER BY name = '" . $search . "' DESC,
																name
																LIMIT 30") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$found = mysqli_num_rows($suggest_query);

	if ($found == 0)
	{
		echo '<a href="#">Nothing found</a>';
	}
	else
	{
		while ($suggest = mysqli_fetch_assoc($suggest_query))
		{
			$suggest_coords = "" . $suggest['x'] . "," . $suggest['y'] . "," . $suggest['z'] . "";
			// find systems
			if ($_GET["link"] == "yes")
			{
				?>
				<a href="system.php?system_id=<?php echo $suggest['id']?>">
					<?php echo $suggest['name']; ?>
				</a><br />
				<?php
			}
			// nearest systems
			else if ($_GET["idlink"] == "yes")
			{
				?>
				<a href="nearest_systems.php?system=<?php echo $suggest['id']?><?php echo $addtl?>">
					<?php echo $suggest['name']; ?>
				</a><br />
				<?php
			}
			// bookmarks
			else if ($_GET["sysid"] == "yes")
			{
				?>
				<a href="javascript:void(0);" onclick='setbm("<?php echo addslashes($suggest['name']);?>", "<?php echo $suggest['id'];?>");'>
					<?php echo $suggest['name']; ?>
				</a><br />
				<?php
			}
			// data point
			else if ($_GET["dp"] == "yes")
			{
				?>
				<a href="javascript:void(0);" onclick='setdp("<?php echo addslashes($suggest['name']);?>", "<?php echo $suggest_coords; ?>", "<?php echo $suggest['id'];?>");'>
					<?php echo $suggest['name']; ?>
				</a><br />
				<?php
			}
			else
			{
				?>
				<a href="javascript:void(0);" onclick="setResult('<?php echo addslashes($suggest['name']); ?>', '<?php echo $suggest_coords; ?>', '<?php echo $divid ?>');">
					<?php echo $suggest['name']; ?>
				</a><br />
				<?php
			}
		}
	}
}

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
