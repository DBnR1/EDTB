<?php
/**
 * Ajax back-end file to fetch points of interest and bookmarks
 * Functions are declared in /source/poi_bm.php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");
/** @require poi_bm */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/poi_bm.php");

/**
 * get usable coordinates
 */

$usable = usable_coords();
$usex = $usable["x"];
$usey = $usable["y"];
$usez = $usable["z"];
?>
<script>
    $("#addp").click(function()
    {
        tofront("addPoi");
        update_values("/get/getPoiEditData.php?Poi_id=0");
        $("#system_33").focus();
    });
</script>
<table>
    <tr>
        <td class="heading" style="min-width:400px">
            <a href="javascript:void(0)" id="addp" title="Add point of interest">Points of Interest</a>
        </td>
        <td class="heading" style="min-width:400px">
            Bookmarks
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top;padding:0">
            <?php
            // get poi in correct order
            $poi_res = mysqli_query($GLOBALS["___mysqli_ston"], "   SELECT SQL_CACHE user_poi.id, user_poi.poi_name AS item_name,
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
                                                                    ORDER BY -(sqrt(pow((item_coordx-(" . $usex . ")),2)+pow((item_coordy-(" . $usey . ")),2)+pow((item_coordz-(" . $usez . ")),2))) DESC, poi_name, system_name")
                                                                    or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
            echo maketable($poi_res, "Poi");
            ?>
        </td>
        <td style="vertical-align:top;padding:0">
            <?php
            // get bookmarks
            $bm_res = mysqli_query($GLOBALS["___mysqli_ston"], "    SELECT SQL_CACHE user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name,
                                                                    user_bookmarks.comment as text, user_bookmarks.added_on,
                                                                    IFNULL(edtb_systems.x, user_systems_own.x) AS item_coordx,
                                                                    IFNULL(edtb_systems.y, user_systems_own.y) AS item_coordy,
                                                                    IFNULL(edtb_systems.z, user_systems_own.z) AS item_coordz,
                                                                    user_bm_categories.name AS catname
                                                                    FROM user_bookmarks
                                                                    LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
                                                                    LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
                                                                    LEFT JOIN user_systems_own ON user_bookmarks.system_name = user_systems_own.name
                                                                    ORDER BY -(sqrt(pow((item_coordx-(" . $usex . ")),2)+pow((item_coordy-(" . $usey . ")),2)+pow((item_coordz-(" . $usez . ")),2))) DESC, system_name, user_bookmarks.added_on DESC")
                                                                    or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
            $i = 0;
            echo maketable($bm_res, "Bm");
            ?>
        </td>
    </tr>
</table>
