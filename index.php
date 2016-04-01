<?php
/**
 * Front page
 *
 * Front-end file for the front page
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/Theme.class.php");

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->page_title = "ED ToolBox";

/**
 * display the header
 */
$header->display_header();

/**
 * if the user is coming from importing log entries, 
 * display a notice and delete possible duplicates
 */
if (isset($_GET["import_done"])) {
    ?>
    <div class="entries">
        <div class="entries_inner">
            <!-- update GalMap -->
            <script type="text/javascript">
                update_map();
            </script>
            <?php
            /**
             * remove duplicates
             */
            $query = "  SELECT id, system_name
                        FROM user_visited_systems
                        ORDER BY visit ASC";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

            $this_s = "";
            while ($obj = $result->fetch_object()) {
                $sys_name = $obj->system_name;
                $sys_id = $obj->id;

                if ($sys_name == $this_s && $sys_id != "1") {
                    $stmt = "   DELETE FROM user_visited_systems
                                WHERE id = '$sys_id '
                                LIMIT 1";

                    $mysqli->query($stmt) or write_log($mysqli->error, __FILE__, __LINE__);
                }

                $this_s = $sys_name;
            }

            $result->close();

            echo notice("Succesfully added " . number_format($_GET["num"]) . " visited systems to the database.<br /><br />You may now continue using ED ToolBox.", "Logs imported");
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

    exit;
}
?>
<div class="entries">
    <div class="entries_inner" id="scrollable">
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
