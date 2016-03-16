<?php
/**
 * Screenshot gallery
 *
 * Front-end file for the screenshot gallery.
 * Back-end files are /source/Vendor/spgm/spgm.php and /source/functions.php (make_gallery function)
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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/** @require Theme class */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/Theme.class.php");

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->page_title = "Screenshot Gallery";

/**
 * display the header
 */
$header->display_header();
?>
<div class="entries">
    <div class="entries_inner">
        <?php
        if (isset($_GET["removed"])) {
            if ($_GET["removed"] != "1") {
                echo '<div class="notify_success">Screenshot succesfully deleted</div>';
            } else {
                echo '<div class="notify_deleted">Screenshot deletion failed.</div>';
            }
        }

        if (is_dir($settings["old_screendir"]) && $settings["old_screendir"] != "C:\\Users" && $settings["old_screendir"] != "C:\\Users\\") {
            ?>
            <table id="wrapper">
                <tr>
                    <td id="center">
                        <?php
                        require_once($_SERVER["DOCUMENT_ROOT"] . "/Gallery/Vendor/spgm/spgm.php");
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        } else {
            echo notice('Your screenshot directory is empty or gallery is disabled.<br />Set the variable "old_screendir" in the <a href="/Admin/ini_editor.php">INI-editor</a> to enable gallery.');
        }
        ?>
    </div>
</div>
<?php
/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->display_footer();
