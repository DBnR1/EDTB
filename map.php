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
 * Neighborhood map
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
*/

$pagetitle = "Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

if (isset($_GET["maxdistance"]) && is_numeric($_GET["maxdistance"]))
{
	$settings["maxdistance"] = $_GET["maxdistance"];
}
?>
<div class="entries">
	<table style="margin-left:370px">
		<tbody>
			<tr>
				<th style="text-align: center">
					<ul class="pagination">
						<li><a href="/galmap.php">Galaxy Map</a></li>
						<li class="actives"><a href="/map.php">Neighborhood Map</a></li>
					</ul>
				</th>
			</tr>
		</tbody>
	</table>
	<div class="entries_inner" style="overflow:hidden !important">
		<div id="container"></div>
		<div id="report" onclick='$("#report").fadeToggle("fast")'></div>
		<div id="disclaimer" onclick='$("#disclaimer").fadeToggle("fast")'></div>
		<div id="map_legend" onclick='$("#map_legend2").fadeToggle("fast")'>Legend</div>
		<div id="map_legend2">
			<table style="padding:5px">
				<tr>
					<td><div style="background-color:rgba(231, 216, 132, 0.7);width:7px;height:7px;border-radius:50%;vertical-align:middle"></div></td>
					<td>Empire</td>
				</tr>
				<tr>
					<td><div style="background-color:rgba(9, 180, 244, 0.7);width:7px;height:7px;border-radius:50%;vertical-align:middle"></div></td>
					<td>Alliance</td>
				</tr>
				<tr>
					<td><div style="background-color:rgba(140, 140, 140, 0.7);width:7px;height:7px;border-radius:50%;vertical-align:middle"></div></td>
					<td>Federation</td>
				</tr>
				<tr>
					<td><div style="background-color:rgba(255, 255, 255, 0.8);width:7px;height:7px;border-radius:50%;vertical-align:middle"></div></td>
					<td>Other</td>
				</tr>
				<tr>
					<td><div style="background-color:#37bf1c;width:7px;height:7px;border-radius:50%;vertical-align:middle"></div></td>
					<td>Sol</td>
				</tr>
				<tr>
					<td style="text-align:center;vertical-align:middle">
						<div style="background-color:#f00;width:8px;height:8px;border-radius:50%">
							<div style="position:relative;left:2px;top:2px;background-color:#ccc;width:4px;height:4px;border-radius:50%"></div>
						</div>
					</td>
					<td>Current Location</td>
				</tr>
				<tr>
					<td style="text-align:center;vertical-align:middle">
						<div style="background-color:#2e92e7;width:8px;height:8px;border-radius:50%">
							<div style="position:relative;left:2px;top:2px;background-color:#ccc;width:4px;height:4px;border-radius:50%"></div>
						</div>
					</td>
					<td>System With a Log Entry</td>
				</tr>
				<tr>
					<td>
						<img src="/style/img/goto-g.png" alt="POI Visited" style="margin-bottom:4px">
					</td>
					<td>Visited Point of Interest</td>
				</tr>
				<tr>
					<td>
						<img src="/style/img/goto.png" alt="POI Unvisited" style="margin-bottom:4px">
					</td>
					<td>Unvisited Point of Interest</td>
				</tr>
				<tr>
					<td>
						<img src="/style/img/rare.png" alt="Rare" style="margin-bottom:4px">
					</td>
					<td>Rare Commodity</td>
				</tr>
				<tr>
					<td>
						<img src="/style/img/bm.png" alt="Rare" style="margin-bottom:4px">
					</td>
					<td>Bookmarked System</td>
				</tr>
			</table>
		</div>
		<div id="map_settings">
			<?php
			if (isset($_GET["mode"]) && $_GET["mode"] == "2d")
			{
				$linkto = "map.php";
				$linkname = "Switch to 3D mode";
				$mode = "2d";
			}
			else
			{
				$linkto = "map.php?mode=2d";
				$linkname = "Switch to 2D mode";
				$mode = "3d";
			}
			?>
			<a href="<?php echo $linkto?>" style="margin-left:4px"><?php echo $linkname?></a><br />
			<form method="GET" action="map.php">
				<input type="hidden" name="mode" value="<?php echo $mode?>">
				<select class="distance" name="maxdistance" onchange="this.form.submit()">
					<?php
					$dropdowns = array_unique($dropdown);
					sort($dropdowns);

					foreach ($dropdowns as $value)
					{
						if ($settings["maxdistance"] == $value)
						{
							$selected = " SELECTED";
						}
						else
						{
							$selected = "";
						}

						echo '<option value="' . $value . '"' . $selected . '>Range ' . $value . ' ly</option>';
					}
					?>
				</select>
			</form>
		</div>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
