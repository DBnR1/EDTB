<?php
/**
 * Add or edit points of interest
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

if (isset($_GET["do"])) {
    /** @require config */
    require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
    /** @require functions */
    require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
    /** @require MySQL */
    require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");

    $data = json_decode($_REQUEST["input"]);

    $p_system = $data->{"poi_system_name"};
    $p_name = $data->{"poi_name"};
    $p_x = $data->{"poi_coordx"};
    $p_y = $data->{"poi_coordy"};
    $p_z = $data->{"poi_coordz"};

    if (valid_coordinates($p_x, $p_y, $p_z)) {
        $addc = ", x = '$p_x', y = '$p_y', z = '$p_z'";
        $addb = ", '$p_x', '$p_y', '$p_z'";
    } else {
        $addc = ", x = null, y = null, z = null";
        $addb = ", null, null, null";
    }

    $p_entry = $data->{"poi_text"};
    $p_id = $data->{"poi_edit_id"};
    $category_id = $data->{"category_id"};

    $esc_name = $mysqli->real_escape_string($p_name);
    $esc_sysname = $mysqli->real_escape_string($p_system);
    $esc_entry= $mysqli->real_escape_string($p_entry);

    if ($p_id != "") {
        $stmt = "  UPDATE user_poi SET
                    poi_name = '$esc_name',
                    system_name = '$esc_sysname',
                    text = '$esc_entry',
                    category_id = '$category_id'" . $addc . "
                    WHERE id = '$p_id'";
    } elseif (isset($_GET["deleteid"])) {
        $stmt = "  DELETE FROM user_poi
                    WHERE id = '" . $_GET["deleteid"] . "'
                    LIMIT 1";
    } else {
        $stmt = "  INSERT INTO user_poi (poi_name, system_name, text, category_id, x, y, z, added_on)
                    VALUES
                    ('$esc_name',
                    '$esc_sysname',
                    '$esc_entry',
                    '$category_id'" . $addb . ",
                    UNIX_TIMESTAMP())";
    }

    $mysqli->query($stmt) or write_log($mysqli->error, __FILE__, __LINE__);

    exit;
}
?>
<div class="input" id="addPoi" style="text-align:center">
    <form method="post" id="poi_form" action="/Bookmarks">
        <div class="input-inner">
            <div class="suggestions" id="suggestions_33" style="margin-top:79px;margin-left:12px"></div>
            <table>
                <tr>
                    <td class="heading" colspan="2">Add/edit Point of Interest
                        <span class="right">
                            <a href="javascript:void(0)" onclick="tofront('addPoi')" title="Close form">
                                <img class="icon" src="/style/img/close.png" alt="X" />
                            </a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="dark" style="width:50%">
                        <input type="hidden" name="poi_edit_id" id="poi_edit_id">
                        <input class="textbox" type="text" name="poi_system_name" placeholder="System name" id="system_33" style="width:95%" oninput="showResult(this.value, '33')" />
                    </td>
                    <td class="dark" style="white-space:nowrap;width:30%">
                        <input class="textbox" type="text" name="poi_coordx" placeholder="x.x" id="coordsx_33" style="width:40px" />
                        <input class="textbox" type="text" name="poi_coordy" placeholder="y.y" id="coordsy_33" style="width:40px" />
                        <input class="textbox" type="text" name="poi_coordz" placeholder="z.z" id="coordsz_33" style="width:40px" />
                    </td>
                </tr>
                <tr>
                    <td class="dark" style="width:70%">
                        <input class="textbox" type="text" name="poi_name" id="poi_name" placeholder="POI name (optional)" style="width:95%" />
                    </td>
                    <td class="dark" style="white-space:nowrap;width:auto">
                        <select title="Category" class="selectbox" name="category_id" id="category_id" style="width:auto">
                            <option value="0">Category (optional)</option>
                            <?php
                            $query = "SELECT id, name FROM user_poi_categories";
                            $result = $mysqli->query($query);

                            while ($obj = $result->fetch_object()) {
                                echo '<option value="' . $obj->id . '">' . $obj->name . '</option>';
                            }

                            $result->close();
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="dark" colspan="2">
                        <textarea id="poi_text" name="poi_text" placeholder="Text (optional)" rows="10" cols="40"></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="dark" colspan="2">
                        <a href="javascript:void(0)" data-replace="true" data-target=".entries">
                            <div class="button" onclick="update_data('poi_form', '/Bookmarks/add_poi.php?do', true);tofront('null', true)">
                                Submit Point of Interest
                            </div>
                        </a>
                        <span id="delete_poi"></span>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>
