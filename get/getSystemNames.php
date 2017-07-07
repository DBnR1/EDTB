<?php
/**
 * Ajax backend file to fetch system names
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
    $search = addslashes($_GET['q']);
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

    $query = "  (SELECT
                    edtb_systems.id, edtb_systems.name,
                    edtb_systems.x, edtb_systems.y, edtb_systems.z
                    FROM edtb_systems
                    WHERE edtb_systems.name
                    LIKE('%" . $escSearch . "%')
                    ORDER BY edtb_systems.name = '$escSearch' DESC,
                    edtb_systems.name)
                UNION
                (SELECT
                    user_systems_own.id AS own_id, user_systems_own.name,
                    user_systems_own.x, user_systems_own.y, user_systems_own.z
                    FROM user_systems_own
                    WHERE user_systems_own.name
                    LIKE('%" . $escSearch . "%')
                    ORDER BY user_systems_own.name = '$escSearch' DESC,
                    user_systems_own.name)
                    LIMIT 30";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
    $found = $result->num_rows;

    if ($found === 0) {
        echo '<a href="#">Nothing found</a>';
    } else {
        while ($suggest = $result->fetch_object()) {
            $suggestCoords = $suggest->x . ',' . $suggest->y . ',' . $suggest->z;
            // find systems
            if ($_GET['link'] === 'yes') {
                if (isset($suggest->id)) {
                    ?>
                    <a href="/System?system_id=<?= $suggest->id?>">
                        <?= $suggest->name?>
                    </a><br>
                    <?php
                } else {
                    ?>
                    <a href="/System?system_name=<?= urlencode($suggest->name)?>">
                        <?= $suggest->name?>
                    </a><br>
                    <?php
                }
            }
            // nearest systems
            elseif ($_GET['idlink'] === 'yes') {
                ?>
                <a href="/NearestSystems?system=<?= $suggest->id?><?= $addtl?>">
                    <?= $suggest->name?>
                </a><br>
                <?php
            }
            // bookmarks
            elseif ($_GET['sysid'] === 'yes') {
                ?>
                <a href="javascript:void(0);" onclick='setbm("<?= addslashes($suggest->name)?>", <?= $suggest->id?>)'>
                    <?= $suggest->name?>
                </a><br>
                <?php
            }
            // data point
            elseif ($_GET['dp'] === 'yes') {
                ?>
                <a href="javascript:void(0);" onclick='setdp("<?= addslashes($suggest->name)?>", "<?= $suggestCoords?>", <?= $suggest->id?>)'>
                    <?= $suggest->name?>
                </a><br>
                <?php
            } else {
                ?>
                <a href="javascript:void(0);" onclick="setResult('<?= addslashes($suggest->name)?>', '<?= $suggestCoords?>', '<?= $divid ?>')">
                    <?= $suggest->name?>
                </a><br>
                <?php
            }
        }
    }
}
