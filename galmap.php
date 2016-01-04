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

//http://ed-board.net/3Dgalnet/
$pagetitle = "Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
?>
<!-- Three.js -->
<script src="/source/three.min.js"></script>
<!-- ED3D-Galaxy-Map stuff -->
<link href="/source/ED3D-Galaxy-Map/css/styles.css" rel="stylesheet" type="text/css" />
<script src="/source/ED3D-Galaxy-Map/js/ed3dmap.min.js?v=5"></script>

<div class="entries" style="position:absolute;bottom:0px;top:0px;height:auto;">
	<table style="margin-left:370px;">
		<tbody>
			<tr>
				<th style="text-align: center;">
					<ul class="pagination">
						<li class="actives"><a href="/galmap.php">Galaxy Map</a></li>
						<li><a href="/map.php">Neighborhood Map</a></li>
					</ul>
				</th>
			</tr>
		</tbody>
	</table>
		<div id="map_legend" onclick='$("#map_legend2").toggle();'>Legend</div>
		<div id="map_legend2">
			<table style="padding:5px;">
				<tr>
					<td><div style="background-color:#e7d884;width:7px;height:7px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Empire</td>
				</tr>
				<tr>
					<td><div style="background-color:#09b4f4;width:7px;height:7px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Alliance</td>
				</tr>
				<tr>
					<td><div style="background-color:#8c8c8c;width:7px;height:7px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Federation</td>
				</tr>
				<tr>
					<td><div style="background-color:#D9DADB;width:7px;height:7px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Other</td>
				</tr>
				<!--<tr>
					<td><div style="background-color:#FF0000;width:7px;height:7px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Sol</td>
				</tr>-->
				<tr>
					<td><div style="background-color:#FF0000;width:8px;height:8px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Current Location</td>
				</tr>
				<!--<tr>
					<td style="text-align:center;vertical-align:middle;">
						<div style="background-color:#2e92e7;width:8px;height:8px;border-radius:50%;">
							<div style="position:relative;left:2px;top:2px;background-color:#ccc;width:4px;height:4px;border-radius:50%;"></div>
						</div>
					</td>
					<td>System With a Log Entry</td>
				</tr>-->
				<tr>
					<td><div style="background-color:#00FF1E;width:8px;height:8px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Visited Point of Interest</td>
				</tr>
				<tr>
					<td><div style="background-color:#E87C09;width:8px;height:8px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Unvisited Point of Interest</td>
				</tr>
				<tr>
					<td><div style="background-color:#00FFBF;width:8px;height:8px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Rare Commodity</td>
				</tr>
				<tr>
					<td><div style="background-color:#F7E707;width:8px;height:8px;border-radius:50%;vertical-align:middle;"></div></td>
					<td>Bookmarked System</td>
				</tr>
			</table>
		</div>
	<div id="edmap" style="position:absolute;left:353px;right:0px;top:0px;bottom:0px;width:auto;height:auto;z-index:5;"></div>

	<!-- Launch ED3Dmap -->
	<script type="text/javascript">
	Ed3d.init({
		basePath: '../source/ED3D-Galaxy-Map/',
		container: 'edmap',
		jsonPath: 'get/getMapPoints.json.php',
		withHudPanel: false,
		startAnim: false,
		effectScaleSystem: [15,50]
	});
	</script>

</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
