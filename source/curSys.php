<?php
/**
 * Get current system
 *
 * This script parses the netLog file to determine the user's current location and fetches
 * related information from the database and puts that information to global variable $curSys
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

/** @require configs */
require_once(__DIR__ . "/config.inc.php");
/** @require functions */
require_once(__DIR__ . "/functions.php");
/** @array curSys */
$curSys = [];

if (is_dir($settings["log_dir"]) && is_readable($settings["log_dir"])) {
    /**
     * select the newest file
     */
    if (!$files = scandir($settings["log_dir"], SCANDIR_SORT_DESCENDING)) {
        $error = error_get_last();
        write_log("Error: " . $error["message"], __FILE__, __LINE__);
    }
    $newest_file = $files[0];

    /**
     * read file to an array
     */
    if (!$line = file($settings["log_dir"] . "/" . $newest_file)) {
        $error = error_get_last();
        write_log("Error: " . $error["message"], __FILE__, __LINE__);
    } else {
        // reverse array
        $lines = array_reverse($line);

        foreach ($lines as $line_num => $line) {
            $pos = strpos($line, "System:");
            /**
             * skip lines that contain "ProvingGround" because they are CQC systems
             */
            $pos2 = strrpos($line, "ProvingGround");

            if ($pos !== false && $pos2 === false) {
                preg_match_all("/\((.*?)\) B/", $line, $matches);
                $cssystemname = $matches[1][0];
                $curSys["name"] = $cssystemname;

                preg_match_all("/\{(.*?)\} System:/", $line, $matches2);
                $visited_time = $matches2[1][0];

                $curSys["name"] = isset($curSys["name"]) ? $curSys["name"] : "";
                $curSys["esc_name"] = $mysqli->real_escape_string($curSys["name"]);

                /**
                 * define defaults
                 */
                $curSys["coordinates"] = "";
                $curSys["x"] = "";
                $curSys["y"] = "";
                $curSys["z"] = "";
                $curSys["id"] = -1;
                $curSys["population"] = "";
                $curSys["allegiance"] = "";
                $curSys["economy"] = "";
                $curSys["government"] = "";
                $curSys["ruling_faction"] = "";
                $curSys["state"] = "unknown";
                $curSys["security"] = "unknown";
                $curSys["power"] = "";
                $curSys["power_state"] = "";
                $curSys["needs_permit"] = "";
                $curSys["updated_at"] = "";
                $curSys["simbad_ref"] = "";

                $sys_name = $mysqli->real_escape_string($curSys["name"]);

                /**
                 * fetch data from edtb_systems
                 */
                $query = "  SELECT id, x, y, z, ruling_faction, population, government, allegiance, state,
                            security, economy, power, power_state, needs_permit, updated_at, simbad_ref
                            FROM edtb_systems
                            WHERE name = '$sys_name'
                            LIMIT 1";

                $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
                $exists = $result->num_rows;

                if ($exists > 0) {
                    $obj = $result->fetch_object();

                    $curSys["coordinates"] = $obj->x . "," . $obj->y . "," . $obj->z;
                    $curSys["id"] = $obj->id;
                    $curSys["population"] = $obj->population;
                    $curSys["allegiance"] = $obj->allegiance;
                    $curSys["economy"] = $obj->economy;
                    $curSys["government"] = $obj->government;
                    $curSys["ruling_faction"] = $obj->ruling_faction;
                    $curSys["state"] = $obj->state;
                    $curSys["security"] = $obj->security;
                    $curSys["power"] = $obj->power;
                    $curSys["power_state"] = $obj->power_state;
                    $curSys["needs_permit"] = $obj->needs_permit;
                    $curSys["updated_at"] = $obj->updated_at;
                    $curSys["simbad_ref"] = $obj->simbad_ref;

                    $curSys["x"] = $obj->x;
                    $curSys["y"] = $obj->y;
                    $curSys["z"] = $obj->z;

                } else {
                    $query = "  SELECT x, y, z
                                FROM user_systems_own
                                WHERE name = '$sys_name'
                                LIMIT 1";

                    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                    $oexists = $result->num_rows;

                    if ($oexists > 0) {
                        $obj = $result->fetch_object();

                        $curSys["x"] = $obj->x == "" ? "" : $obj->x;
                        $curSys["y"] = $obj->y == "" ? "" : $obj->y;
                        $curSys["z"] = $obj->z == "" ? "" : $obj->z;
                        $curSys["coordinates"] = $curSys["x"] . "," . $curSys["y"] . "," . $curSys["z"];
                    } else {
                        $curSys["coordinates"] = "";
                        $curSys["x"] = "";
                        $curSys["y"] = "";
                        $curSys["z"] = "";
                    }
                }

                $result->close();

                /**
                 * fetch previous system
                 */
                $prev_system = edtb_common("last_system", "value");

                if ($prev_system != $cssystemname && !empty($cssystemname)) {
                    /**
                     * add system to user_visited_systems
                     */
                    $query = "  SELECT system_name
                                FROM user_visited_systems
                                ORDER BY id
                                DESC LIMIT 1";

                    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
                    $obj = $result->fetch_object();

                    $visited_on = date("Y-m-d") . " " . $visited_time;

                    if ($obj->system_name != $curSys["name"] && !empty($curSys["name"])) {
                        $query = "  INSERT INTO user_visited_systems (system_name, visit)
                                    VALUES
                                    ('$sys_name',
                                    '$visited_on')";

                        $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                        /**
                         * export to EDSM
                         */
                        if ($settings["edsm_api_key"] != "" && $settings["edsm_export"] == "true" && $settings["edsm_cmdr_name"] != "") {
                            $visited_on_utc = date("Y-m-d H:i:s");
                            $export = file_get_contents("http://www.edsm.net/api-logs-v1/set-log?commanderName=" . urlencode($settings["edsm_cmdr_name"]) . "&apiKey=" . $settings["edsm_api_key"] . "&systemName=" . urlencode($curSys["name"]) . "&dateVisited=" . urlencode($visited_on_utc) . "");

                            $exports = json_decode($export);

                            if ($exports->{"msgnum"} != "100") {
                                write_log($export, __FILE__, __LINE__);
                            }
                        }

                        $newSystem = true;
                        //write_log($prev_system . " new: " . $cssystemname);
                    }
                    $result->close();

                    // update latest system
                    edtb_common("last_system", "value", true, $curSys["name"]);

                    $newSystem = true;
                } else {
                    $newSystem = false;
                }

                break;
            }
        }
    }
} else {
    write_log("Error: " . $settings["log_dir"] . " doesn't exist or is not readable", __FILE__, __LINE__);
}
