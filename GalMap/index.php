<?php
/**
 * Galaxy map
 *
 * Front-end file for Galaxy map
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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
*/

//http://ed-board.net/3Dgalnet/

/** @require Theme class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Theme.php';

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->pageTitle = 'Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map';

/**
 * display the header
 */
$header->displayHeader();

/**
 * determine coordinates for the map distance calculations
 */
if (valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
    $ucoordx = $curSys['x'];
    $ucoordy = $curSys['y'];
    $ucoordz = -$curSys['z'];
} else {
    // get last known coordinates
    $lastCoords = last_known_system();

    $ucoordx = $lastCoords['x'];
    $ucoordy = $lastCoords['y'];
    $ucoordz = -$lastCoords['z'];

    $isUnknown = ' *';
}

if (!valid_coordinates($ucoordx, $ucoordy, $ucoordz)) {
    $ucoordx = '0';
    $ucoordy = '0';
    $ucoordz = '0';

    $isUnknown = ' *';
}
?>
    <!-- Three.js -->
    <script src="/source/Vendor/three.min.js"></script>
    <!-- ED3D-Galaxy-Map -->
    <link href="Vendor/ED3D-Galaxy-Map/css/styles.css?ver=<?= $settings['edtb_version']?>" rel="stylesheet" type="text/css" />
    <script src="Vendor/ED3D-Galaxy-Map/js/ed3dmap.js"></script>

    <div style="display: none" id="curx"><?= $ucoordx?></div>
    <div style="display: none" id="cury"><?= $ucoordy?></div>
    <div style="display: none" id="curz"><?= $ucoordz?></div>
    <div style="display: none" id="rcurx"><?= round($ucoordx)?></div>
    <div style="display: none" id="rcury"><?= round($ucoordy)?></div>
    <div style="display: none" id="rcurz"><?= round($ucoordz)?></div>

    <div class="entries" style="position: absolute;  bottom: 0; top: 0;height: auto">
        <table class="edmap_table">
            <tbody>
            <tr>
                <th style="text-align: center">
                    <ul class="pagination">
                        <li class="actives"><a href="/GalMap">Galaxy Map</a></li>
                        <li><a href="/Map">Neighborhood Map</a></li>
                    </ul>
                </th>
            </tr>
            </tbody>
        </table>
        <div class="edmap" id="edmap"></div>
        <!-- Launch ED3Dmap -->
        <script type="text/javascript">
            Ed3d.init({
                basePath: 'Vendor/ED3D-Galaxy-Map/',
                container: 'edmap',
                jsonPath: '/GalMap/map_points.json',
                withHudPanel: true,
                startAnim: false,
                effectScaleSystem: [15,50],
                playerPos: [<?= $ucoordx?>,<?= $ucoordy?>,<?= $ucoordz?>]
            });
        </script>
    </div>
<?php
/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->displayFooter();
