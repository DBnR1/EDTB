<?php
/**
 * Ajax back-end file to fetch points of interest and bookmarks
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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
*/

/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require curSys */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/curSys.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

use \EDTB\Bookmarks\PoiBm;

/**
 * get usable coordinates
 */
$usable = usable_coords();
$usex = $usable['x'];
$usey = $usable['y'];
$usez = $usable['z'];
?>
<script>
    $('#addp').click(function () {
        tofront('addPoi');
        update_values('/Bookmarks/getPoiEditData.php?Poi_id=0');
        $('#system_33').focus();
    });

    $(document).ready(function () {
        var $body = $('body');

        $body.on('click', '#showBM , #showPOI', function () {
            showHide($(this).attr('id'));
        });

        $body.on('click', '.categ-btns', function () {
            showHidePanels($(this).attr('id'));
        });

        function showHidePanels(trigger) {
            //console.log(trigger);
            $('.categ-panels').each(function (index) {
                $(this).css('display', 'none');
            });

            $('#' + trigger.replace('btn', 'panel')).css('display', 'block');
        }

        function showHide(trigger) {
            var $trigger = $('#' + trigger);

            if ($trigger.attr('id') === 'showPOI') {
                var $poiPart = $('#poiPart');

                if ($poiPart.css('display') === 'none') {
                    $poiPart.css('display', 'block');
                } else {
                    $poiPart.css('display', 'none');
                }
            }
            else if ($trigger.attr('id') === 'showBM') {
                var $bmPart = $('#bmPart');

                if ($bmPart.css('display') === 'none') {
                    $bmPart.css('display', 'block');
                } else {
                    $bmPart.css('display', 'none');
                }
            }
        }
    });
</script>

<table style="width:100%;">
    <tr>
        <td class="heading poi_minmax">
            <a href="javascript:void(0)" id="addp" title="Add point of interest">Points of Interest</a>
            <label style="float: right;"> Show POI's<input type="checkbox" name="showPOI" value="showPOI"
                                                           style="display: block; float: right; margin-left: 20px;" id="showPOI"
                                                           checked="checked"></label>
        </td>
    </tr>
    <tr>
        <td class="poi_minmax" id="poiPart" style="vertical-align: top; padding: 0">
            <?php
            /**
             * fetch poi in correct order
             */
            $query = '  SELECT SQL_CACHE user_poi.id, user_poi.poi_name AS item_name,
                        user_poi.system_name, user_poi.text, user_poi.added_on,
                        IFNULL(user_poi.x, user_systems_own.x) AS item_coordx,
                        IFNULL(user_poi.y, user_systems_own.y) AS item_coordy,
                        IFNULL(user_poi.z, user_systems_own.z) AS item_coordz,
                        edtb_systems.id AS system_id,
                        user_poi_categories.name AS catname
                        FROM user_poi
                        LEFT JOIN edtb_systems ON user_poi.system_name = edtb_systems.name
                        LEFT JOIN user_poi_categories ON user_poi_categories.id = user_poi.category_id
                        LEFT JOIN user_systems_own ON user_poi.system_name = user_systems_own.name
                        ORDER BY catname ASC';

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

            $poi = new PoiBm();

            $poi->usex = $usex;
            $poi->usey = $usey;
            $poi->usez = $usez;
            $poi->time_difference = $systemTime;

            echo $poi->makeTable($result, 'Poi');

            $result->close();
            ?>
        </td>
    </tr>
    <tr>
        <td class="heading poi_minmax">
            Bookmarks
            <label style="float: right;"> Show Bookmarks<input type="checkbox" name="showBM" value="showBM"
                                                               style="display: block; float: right; margin-left: 20px;" id="showBM"
                                                               checked="checked"></label>
        </td>
    </tr>

    <tr>
        <td class="poi_minmax" id="bmPart" style="vertical-align: top; padding: 0">
            <?php
            /**
             * fetch bookmarks
             */
            $query = '  SELECT SQL_CACHE user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name,
                        user_bookmarks.comment AS text, user_bookmarks.added_on,
                        IFNULL(edtb_systems.x, user_systems_own.x) AS item_coordx,
                        IFNULL(edtb_systems.y, user_systems_own.y) AS item_coordy,
                        IFNULL(edtb_systems.z, user_systems_own.z) AS item_coordz,
                        user_bm_categories.name AS catname
                        FROM user_bookmarks
                        LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
                        LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
                        LEFT JOIN user_systems_own ON user_bookmarks.system_name = user_systems_own.name
                        ORDER BY catname ASC';

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

            $i = 0;
            $bm = new PoiBm();

            $bm->usex = $usex;
            $bm->usey = $usey;
            $bm->usez = $usez;
            $bm->time_difference = $systemTime;

            echo $bm->makeTable($result, 'Bm');

            $result->close();
            ?>
        </td>
    </tr>
</table>
