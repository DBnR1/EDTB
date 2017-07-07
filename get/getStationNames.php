<?php
/**
 * Ajax backend file to fetch station names
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
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

$action = $_GET['action'] ?? '';

if (isset($_GET['q'], $_GET['divid'])) {
    $search = $_GET['q'];
    $divid = $_GET['divid'];

    $addtl = '';
    if (isset($_GET['allegiance']) && $_GET['allegiance'] !== 'undefined') {
        $addtl .= '&allegiance=' . $_GET['allegiance'];
    }

    if (isset($_GET['system_allegiance']) && $_GET['system_allegiance'] !== 'undefined') {
        $addtl .= '&system_allegiance=' . $_GET['system_allegiance'];
    }

    if (isset($_GET['power']) && $_GET['power'] !== 'undefined') {
        $addtl .= '&power=' . $_GET['power'];
    }

    $escSearch = $mysqli->real_escape_string($search);
    $escSysid = $mysqli->real_escape_string($_GET['sysid']);

    $query = "  SELECT DISTINCT(edtb_systems.name) AS system_name,
                edtb_systems.id AS system_id,
                edtb_systems.x,
                edtb_systems.y,
                edtb_systems.z,
                edtb_stations.name AS station_name
                FROM edtb_systems
                LEFT JOIN edtb_stations ON edtb_stations.system_id = edtb_systems.id
                WHERE edtb_stations.name LIKE('%" . $escSearch . "%')
                ORDER BY edtb_stations.name = '$escSearch',
                edtb_stations.name
                LIMIT 30";

    if (isset($_GET['sysid']) && $_GET['sysid'] !== 'no') {
        $query = "  SELECT edtb_systems.name AS system_name,
                    edtb_systems.id AS system_id,
                    edtb_systems.x,
                    edtb_systems.y,
                    edtb_systems.z,
                    edtb_stations.name AS station_name,
                    edtb_stations.id AS station_id
                    FROM edtb_systems
                    LEFT JOIN edtb_stations ON edtb_stations.system_id = edtb_systems.id
                    WHERE edtb_stations.name LIKE('%" . $escSearch . "%')
                    AND edtb_systems.name = '$escSysid'
                    ORDER BY edtb_stations.name = '$escSearch',
                    edtb_stations.name
                    LIMIT 30";
    }

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $found = $result->num_rows;

    if ($found === 0) {
        echo '<a href="#">Nothing found</a>';

        exit;
    }

    while ($suggest = $result->fetch_object()) {
        if ($_GET['link'] === 'yes') {
            ?>
            <a href="/System?system_id=<?= $suggest->system_id?>">
                <?= $suggest->station_name?>&nbsp;&nbsp;(<?= $suggest->system_name?>)
            </a><br>
            <?php
        } elseif ($_GET['idlink'] === 'yes') {
            ?>
            <a href="/NearestSystems?system=<?= $suggest->system_id?><?= $addtl?>">
                <?= $suggest->station_name?>&nbsp;&nbsp;(<?= $suggest->system_name?>)
            </a><br>
            <?php
        } elseif ($_GET['sysid'] !== 'no') {
            ?>
            <a href="javascript:void(0);" onclick="setl('<?= $suggest->station_name?>', '<?= $suggest->station_id?>')">
                <?= $suggest->station_name?>
            </a><br>
            <?php
        } else {
            $suggestCoords = $suggest->x . ',' . $suggest->y . ',' . $suggest->z;
            ?>
            <a href="javascript:void(0);" onclick="setResult('<?= str_replace("'", '', $suggest->system_name)?>', '<?= $suggestCoords?>', '<?= $divid ?>')">
                <?= $suggest->system_name?>
            </a><br>
            <?php
        }
    }

    $result->close();
}
