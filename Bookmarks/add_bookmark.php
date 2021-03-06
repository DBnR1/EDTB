<?php
/**
 * Ajax backend file to add or edit bookmarks
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

use EDTB\Bookmarks\PoiBm;

if (isset($_GET['do'])) {
    /** @require functions */
    require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
    /** @require PoiBm class */
    require_once __DIR__ . '/PoiBm.php';

    $data = json_decode($_REQUEST['input']);

    $AddOrDelete = new PoiBm();
    $AddOrDelete->addOrDeleteBookmark($data);

    exit;
}
?>
<div class="input" id="addBm">
    <form method="post" id="bm_form" action="/">
        <div class="input-inner">
            <div class="suggestions" id="suggestions_3" style="margin-top: 79px; margin-left: 14px"></div>
            <table>
                <tr>
                    <td class="heading" colspan="2">Add/edit bookmark
                        <span class="right">
                            <a href="javascript:void(0)" onclick="tofront('addBm')" title="Close form">
                                <img src="/style/img/close.png" class="icon" alt="X">
                            </a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="dark" style="text-align: left">
                        <input type="hidden" name="bm_edit_id" id="bm_edit_id">
                        <input type="hidden" name="bm_system_id" id="bm_system_id">
                        <input class="textbox" type="text" name="bm_system_name" placeholder="System name" id="bm_system_name" style="width: 410px" oninput="showResult(this.value, '3', 'no', 'no', 'no', 'yes')">
                    </td>
                    <td class="dark">
                        <select title="Category" class="selectbox" name="bm_catid" id="bm_catid" style="width: 140px">
                            <option value="0">Category (optional)</option>
                            <?php
                            $query = 'SELECT id, name FROM user_bm_categories ORDER BY name';
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
                    <td colspan="2" class="dark">
                        <textarea id="bm_text" name="bm_text" placeholder="Comment (optional)" rows="10" cols="40"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="dark">
                        <a href="javascript:void(0)"><div class="button" id="add_bm_click">Add Bookmark</div></a>
                        <span id="delete_bm"></span>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>
<script>
    $('#add_bm_click').click(function()
    {
        update_data('bm_form', '/Bookmarks/add_bookmark.php?do', true);
        tofront('null', true);
    });
</script>
