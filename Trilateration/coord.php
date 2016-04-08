<?php
/**
 * Trilaterate coordinates from user input and send to EDSM
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

/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require trilateration class */
require_once("Trilateration.php");

if (isset($_GET["do"])) {
    $data = json_decode($_REQUEST["input"]);

    $target_system = $data->{"target_system"};

    $reference_1_system = $data->{"reference_1"};
    $reference_2_system = $data->{"reference_2"};
    $reference_3_system = $data->{"reference_3"};
    $reference_4_system = $data->{"reference_4"};

    $reference_1_coordinates = $data->{"reference_1_coordinates"};
    $reference_2_coordinates = $data->{"reference_2_coordinates"};
    $reference_3_coordinates = $data->{"reference_3_coordinates"};
    $reference_4_coordinates = $data->{"reference_4_coordinates"};

    $reference_1_distance = $data->{"reference_1_distance"};
    $reference_2_distance = $data->{"reference_2_distance"};
    $reference_3_distance = $data->{"reference_3_distance"};
    $reference_4_distance = $data->{"reference_4_distance"};

    if (is_numeric($reference_1_distance) && is_numeric($reference_2_distance) && is_numeric($reference_3_distance) && is_numeric($reference_4_distance)) {
        /**
         * submit to EDSM
         */
        $json_string = '{
        "data": {
            "fromSoftware": "ED ToolBox",
            "fromSoftwareVersion": "' . $settings["edtb_version"] . '",
            "commander": "' . $settings["edsm_cmdr_name"] . '",
            "p0": {
                "name": "' . $target_system . '"
            },
            "refs": [{
                "name": "' . $reference_1_system . '",
                "dist": ' . $reference_1_distance . '
            }, {
                "name": "' . $reference_2_system . '",
                "dist": ' . $reference_2_distance . '
            }, {
                "name": "' . $reference_3_system . '",
                "dist": ' . $reference_3_distance . '
            }, {
                "name": "' . $reference_4_system . '",
                "dist": ' . $reference_4_distance . '
            }]
        }
        }';

        $opts = array(
            "http" => array(
                "method" => "POST",
                "header" => "Content-type: json\r\n" . "Referer: http://www.edsm.net/api-v1/submit-distances\r\n",
                "content" => $json_string
                )
            );

        $context = stream_context_create($opts);

        $result = file_get_contents("http://www.edsm.net/api-v1/submit-distances", false, $context);
        $result_j = json_decode($result);
        $edsm_msg = $result_j->{"basesystem"}->{"msg"};

        write_log("EDSM message: " . $edsm_msg, __FILE__, __LINE__);

        $edsm_msg = $result_j->{"basesystem"}->{"msgnum"} . ":::" . $edsm_msg;

        /**
         * calculate coordinates
         */
        $reference_distances = $reference_1_system . ":::" . $reference_1_distance . "---" . $reference_2_system . ":::" . $reference_2_distance . "---" . $reference_3_system . ":::" . $reference_3_distance . "---" . $reference_4_system . ":::" . $reference_4_distance;

        $reference_1 = explode(",", $reference_1_coordinates);
        $reference_2 = explode(",", $reference_2_coordinates);
        $reference_3 = explode(",", $reference_3_coordinates);
        $reference_4 = explode(",", $reference_4_coordinates);

        $system1 = array($reference_1[0], $reference_1[1], $reference_1[2], $reference_1_distance);
        $system2 = array($reference_2[0], $reference_2[1], $reference_2[2], $reference_2_distance);
        $system3 = array($reference_3[0], $reference_3[1], $reference_3[2], $reference_3_distance);
        $system4 = array($reference_4[0], $reference_4[1], $reference_4[2], $reference_4_distance);

        $coords_calc = new Trilateration();
        $newcoords = $coords_calc->trilateration3d($system1, $system2, $system3, $system4);
        $newcoords_x = $newcoords[0];
        $newcoords_y = $newcoords[1];
        $newcoords_z = $newcoords[2];

        $esc_target_system = $mysqli->real_escape_string($target_system);
        $esc_reference_distances = $mysqli->real_escape_string($reference_distances);
        $esc_edsm_msg = $mysqli->real_escape_string($edsm_msg);

        $system_exists = System::exists($target_system, true);

        if (!$system_exists) {
            $stmt = "   INSERT INTO user_systems_own
                        (name, x, y, z, reference_distances, edsm_message)
                        VALUES
                        ('$esc_target_system',
                        '$newcoords_x',
                        '$newcoords_y',
                        '$newcoords_z',
                        '$esc_reference_distances',
                        '$esc_edsm_msg')";
        } else {
            $stmt = "   UPDATE user_systems_own
                        SET name = '$esc_target_system',
                            x = '$newcoords_x',
                            y = '$newcoords_y',
                            z = '$newcoords_z',
                            reference_distances = '$esc_reference_distances',
                            edsm_message = '$esc_edsm_msg'
                        WHERE name = '$esc_target_system'
                        LIMIT 1";
        }

        $mysqli->query($stmt) or write_log($mysqli->error, __FILE__, __LINE__);
    } else {
        write_log("Error: Distances not numeric or all distances not given.", __FILE__, __LINE__);
    }

    exit;
}
?>
<script>
    var clipboard = new Clipboard(".btn");

    clipboard.on("error", function(e)
    {
        console.log(e);
    });
</script>
<div class="input" id="calculate" style="text-align:center">
</div>
