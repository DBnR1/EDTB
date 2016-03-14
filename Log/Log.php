<?php
/**
 * Log entries for a specific system
 *
 * No description
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

/** @var string logsystem */
$logsystem = $_GET["system"];
if (!$logsystem) {
    exit("No system set");
}

$logsystem_id = !isset($_GET["system_id"]) ? "-1" : 0 + $_GET["system_id"];
/*if (!$logsystem_id) exit("No system id set"); */

/** @var string pagetitle */
$pagetitle = "ED ToolBox";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
/** @require functions file */
require_once("MakeLog.class.php");
?>
<div class="entries">
    <div class="entries_inner">
        <?php
        /**
         * get system-specific log
         */
        $esc_logsys_name = $mysqli->real_escape_string($logsystem);
        $query = "  SELECT user_log.id, user_log.station_id, user_log.system_name, user_log.log_entry, user_log.stardate,
                    user_log.pinned, user_log.type, user_log.title, user_log.audio,
                    edtb_stations.name AS station_name
                    FROM user_log
                    LEFT JOIN edtb_stations ON edtb_stations.id = user_log.station_id
                    WHERE user_log.system_id = '$logsystem_id'
                    OR user_log.system_name = '$esc_logsys_name'
                    ORDER BY -user_log.pinned, user_log.weight, user_log.stardate DESC";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $num = $result->num_rows;

        if ($num > 0) {
            $log = new MakeLog();
            echo $log->make_log_entries($result, "log");
        } else {
            echo '<h2>No log entries for ' . $logsystem . '</h2><br />';
            echo '<a href="javascript:void(0)" id="toggle" onclick="toggle_log(\'' . addslashes($logsystem) . '\')" title="Add log entry" style="color:inherit">';
            echo 'Click here to add one</a>';
        }

        $result->close();
        ?>
    </div>
</div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
