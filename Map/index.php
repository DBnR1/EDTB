<?php
/**
 * Neighborhood map
 *
 * Front-end file for Neighborhood map
 *
 * @package EDTB\Main
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

if (isset($_GET['maxdistance']) && is_numeric($_GET['maxdistance'])) {
    $settings['maxdistance'] = $_GET['maxdistance'];
}
?>
<script src="Vendor/Highcharts/js/highcharts.js"></script>
<script src="Vendor/Highcharts/js/highcharts-3d.js"></script>
<div class="entries">
    <table class="edmap_table">
        <tbody>
        <tr>
            <th style="text-align: center">
                <ul class="pagination">
                    <li><a href="/GalMap">Galaxy Map</a></li>
                    <li class="actives"><a href="/Map">Neighborhood Map</a></li>
                </ul>
            </th>
        </tr>
        </tbody>
    </table>
    <div class="entries_inner" style="overflow:hidden !important">
        <div id="container"></div>
        <div id="report" onclick="$('#report').fadeToggle('fast')"></div>
        <div id="disclaimer" onclick="$('#disclaimer').fadeToggle('fast')"></div>
        <div id="map_legend">Legend</div>
        <div id="map_legend2">
            <table style="padding: 5px">
                <tr>
                    <td><div style="background-color: rgba(231, 216, 132, 0.7); width: 7px;  height: 7px;border-radius:50%;vertical-align: middle"></div></td>
                    <td>Empire</td>
                </tr>
                <tr>
                    <td><div style="background-color: rgba(9, 180, 244, 0.7); width: 7px;  height: 7px;border-radius:50%;vertical-align: middle"></div></td>
                    <td>Alliance</td>
                </tr>
                <tr>
                    <td><div style="background-color: rgba(140, 140, 140, 0.7); width: 7px;  height: 7px;border-radius:50%;vertical-align: middle"></div></td>
                    <td>Federation</td>
                </tr>
                <tr>
                    <td><div style="background-color: rgba(255, 255, 255, 0.8); width: 7px;  height: 7px;border-radius:50%;vertical-align: middle"></div></td>
                    <td>Other</td>
                </tr>
                <tr>
                    <td><div style="background-color: #37bf1c; width: 7px;  height: 7px;border-radius:50%;vertical-align: middle"></div></td>
                    <td>Sol</td>
                </tr>
                <tr>
                    <td style="text-align: center; vertical-align: middle">
                        <div style="background-color: #f00; width: 8px; height: 8px;border-radius:50%">
                            <div style="position: relative; left: 2px; top: 2px;background-color:#ccc;width:4px;height:4px;border-radius:50%"></div>
                        </div>
                    </td>
                    <td>Current Location</td>
                </tr>
                <tr>
                    <td style="text-align: center; vertical-align: middle">
                        <div style="background-color: #2e92e7; width: 8px; height: 8px;border-radius:50%">
                            <div style="position: relative; left: 2px; top: 2px;background-color:#ccc;width:4px;height:4px;border-radius:50%"></div>
                        </div>
                    </td>
                    <td>System With a Log Entry</td>
                </tr>
                <tr>
                    <td>
                        <img src="/style/img/goto-g.png" alt="POI Visited" style="margin-bottom: 4px; width:12px;height: 12px">
                    </td>
                    <td>Visited Point of Interest</td>
                </tr>
                <tr>
                    <td>
                        <img src="/style/img/goto.png" alt="POI Unvisited" style="margin-bottom: 4px; width:12px;height: 12px">
                    </td>
                    <td>Unvisited Point of Interest</td>
                </tr>
                <tr>
                    <td>
                        <img src="/style/img/rare.png" alt="Rare" style="margin-bottom: 4px; width:12px;height: 12px">
                    </td>
                    <td>Rare Commodity</td>
                </tr>
                <tr>
                    <td>
                        <img src="/style/img/bm.png" alt="Rare" style="margin-bottom: 4px; width:12px;height: 12px">
                    </td>
                    <td>Bookmarked System</td>
                </tr>
            </table>
        </div>
        <div id="map_settings">
            <?php
            if (isset($_GET['mode']) && $_GET['mode'] === '2d') {
                $linkto = '/Map';
                $linkname = 'Switch to 3D mode';
                $mode = '2d';
            } else {
                $linkto = '/Map/?mode=2d';
                $linkname = 'Switch to 2D mode';
                $mode = '3d';
            }
            ?>
            <a href="<?= $linkto?>" style="margin-left: 4px"><?= $linkname?></a><br>
            <form method="GET" action="/Map">
                <input type="hidden" name="mode" value="<?= $mode?>">
                <select title="Range" class="distance" name="maxdistance" onchange="this.form.submit()">
                    <?php
                    $dropdowns = array_unique($dropdown);
                    sort($dropdowns);

                    foreach ($dropdowns as $value) {
                        $selected = $settings['maxdistance'] == $value ? 'selected="selected"' : '';

                        echo '<option value="' . $value . '" ' . $selected . '>Range ' . $value . ' ly</option>';
                    }

                    ?>
                </select>
            </form>
        </div>
    </div>
</div>
<!-- Hide divs by clicking outside of them -->
<script>
    $(document).mouseup(function (e)
    {
        var container = [];
        container.push($('#map_legend2'));

        $.each(container, function(key, value)
        {
            if (!$(value).is(e.target) // if the target of the click isn't the container...
                && $(value).has(e.target).length === 0) // ... nor a descendant of the container
            {
                $(value).fadeOut("fast");
            }
        });
    });
</script>
<?php
/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->displayFooter();
