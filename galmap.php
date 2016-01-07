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

//http://ed-board.net/3Dgalnet/
$pagetitle = "Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

if (is_numeric($coordx))
{
	$ucoordx = $coordx;
	$ucoordy = $coordy;
	$ucoordz = -$coordz;
}
else
{
	// get last known coordinates
	$last_coords = last_known_system();

	$ucoordx = $last_coords["x"];
	$ucoordy = $last_coords["y"];
	$ucoordz = -$last_coords["z"];

	$is_unknown = " *";
}
?>
<!-- Three.js -->
<script src="/source/three.min.js"></script>
<!-- ED3D-Galaxy-Map stuff -->
<link href="/source/ED3D-Galaxy-Map/css/styles.css" rel="stylesheet" type="text/css" />
<script src="/source/ED3D-Galaxy-Map/js/ed3dmap.js"></script>

<div style="display:none;" id="curx"><?php echo $ucoordx?></div>
<div style="display:none;" id="cury"><?php echo $ucoordy?></div>
<div style="display:none;" id="curz"><?php echo $ucoordz?></div>
<div style="display:none;" id="rcurx"><?php echo round($ucoordx)?></div>
<div style="display:none;" id="rcury"><?php echo round($ucoordy)?></div>
<div style="display:none;" id="rcurz"><?php echo round($ucoordz)?></div>

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
	<div id="edmap" style="position:absolute;left:353px;right:0px;top:0px;bottom:0px;width:auto;height:auto;z-index:5;"></div>

	<!-- Launch ED3Dmap -->
	<script type="text/javascript">
	Ed3d.init({
		basePath: '../source/ED3D-Galaxy-Map/',
		container: 'edmap',
		jsonPath: '/map_points.json',
		withHudPanel: true,
		startAnim: false,
		effectScaleSystem: [15,50],
		playerPos: [<?php echo $ucoordx;?>,<?php echo $ucoordy;?>,<?php echo $ucoordz;?>]
	});
	</script>

</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
