<?php
/**
 * Ajax backend file to fetch reference systems for trilateration
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
use EDTB\Trilateration\ReferenceSystems;

/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';
/** @require curSys */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/curSys.php';
/** @require ReferenceSystems class */
require_once __DIR__ . '/ReferenceSystems.php';

/**
 * check if system already has distances
 */
$query = "  SELECT id, reference_distances, edsm_message
            FROM user_systems_own
            WHERE name = '" . $curSys['esc_name'] . "'
            LIMIT 1";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
$systemExists = $result->num_rows;

$do = true;
$ref = [];
if ($systemExists > 0) {
    $sysObj = $result->fetch_object();
    $edsmMsg = $sysObj->edsm_message;
    $parts = explode(':::', $edsmMsg);

    $msgNum = $parts[0];

    /**
     * if the system has been succesfully trilaterated, we don't need any more references
     */
    if ($msgNum == '104' || $msgNum == '102') {
        $do = false;
    } else {
        $do = true;
        $values = explode('---', $sysObj->reference_distances);

        if (!isset($_GET['force'])) {
            $i = 1;
            foreach ($values as $value) {
                $values2 = explode(':::', $value);

                $ref[$i]['name'] = $values2[0];
                $ref[$i]['distance'] = $values2[1];
                $i++;
            }

            $systems = new ReferenceSystems();
            $systems->standard = isset($_GET['standard']) ? true : false;
            $referencesystems = $systems->referenceSystems();
        } else {
            for ($ii = 1; $ii <= 4; $ii++) {
                $ref[$ii]['name'] = '';
                $ref[$ii]['distance'] = '';
            }

            /**
             * put already used systems into an array so we don't use them again
             */
            $used = [];
            foreach ($values as $value) {
                $values2 = explode(':::', $value);

                $used[] = $values2[0];
            }

            $systems = new ReferenceSystems();
            $systems->standard = false;
            $systems->used = $used;
            $referencesystems = $systems->referenceSystems();
        }
    }
} else {
    for ($ii = 1; $ii <= 4; $ii++) {
        $ref[$ii]['name'] = '';
        $ref[$ii]['distance'] = '';
    }

    $systems = new ReferenceSystems();
    $systems->standard = isset($_GET['standard']) ? true : false;
    $referencesystems = $systems->referenceSystems();
}

$result->close();
?>
<script>
    $('a.send').click(function()
    {
        $.get('/action/shipControls.php?send=' + $(this).data('send'));
        $('#ref_' + $(this).data('id') + '_dist').focus();
    });

    $('#clear').click(function()
    {
         $('#ref_1_dist').val('');
         $('#ref_2_dist').val('');
         $('#ref_3_dist').val('');
         $('#ref_4_dist').val('');
    });
</script>
<form method="post" id="calc_form" action="/">
    <div class="input-inner">
        <table>
            <tr>
                <td class="heading" colspan="2">Calculate Coordinates
                    <span class="right">
                        <a href="javascript:void(0)" onclick="tofront('calculate')" title="Close form">
                            <img src="/style/img/close.png" class="icon" alt="X">
                        </a>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="light" colspan="2" style="text-align: left; font-size: 13px">Use this form to calculate coordinates for systems that have no known coordinates
                <br>in the <a href="http://edsm.net" target="_blank">EDSM</a><img src="/style/img/external_link.png" class="ext_link" alt="ext" style="margin-left: 4px">
                database by inserting distances from the system map into this form.<br><br>
                Clicking the <em>clipboard</em> icon will copy the system name to the client side clipboard.<br><br>
                Clicking the <em>magic</em> icon will send the system name to the ED client.<br>
                <strong>Note:</strong> have the system map open and the search box targeted before clicking the icon.</td>
            </tr>
            <tr>
                <td class="dark" colspan="2" style="font-size: 14px">
                    <strong>Target System:</strong>
                    <?= $curSys['name']?>
                    <input class="textbox" type="hidden" name="target_system" value="<?= $curSys['name']?>" id="target_system">
                </td>
            </tr>
            <?php
            if ($do === true) {
                ?>
                <tr>
                    <td class="light" style="text-align: right">
                        <strong><a href="javascript:void(0)" onclick="set_reference_systems(true)" title="Change reference systems">Reference system</a></strong>
                    </td>
                    <td class="light">
                        <strong>Distance (ly)</strong>
                        <div class="button" id="clear" style="width: 80px; white-space:nowrap;margin-top: 3px">Clear All</div>
                    </td>
                </tr>
                <?php
                $i = 1;
                foreach ($referencesystems as $refName => $refCoordinates) {
                    $refRname = $ref[$i]['name'] !== '' ? $ref[$i]['name'] : $refName;
                    ?>
                    <tr>
                        <td class="dark" style="text-align: right">
                            <input class="textbox" type="hidden" id="<?= $i ?>" name="reference_<?= $i ?>"
                                   value="<?= $refRname ?>"/>
                            <input class="textbox" type="hidden" name="reference_<?= $i ?>_coordinates"
                                   value="<?= $refCoordinates ?>"/>
                            <span class="left">
                                <a class="send" href="javascript:void(0)" title="Send to ED client"
                                   data-send="<?= $refRname ?>" data-id="<?= $i ?>">
                                    <img class="icon24" src="/style/img/magic.png" alt="Send"/>
                                </a>
                                <a href="javascript:void(0)" title="Copy to clipboard">
                                    <img class="btn" src="/style/img/clipboard.png" alt="Copy"
                                         data-clipboard-text="<?= $refRname ?>"/>
                                </a>
                            </span>
                            <strong><?= $refRname ?></strong>
                        </td>
                        <td class="dark">
                            <input class="textbox" type="number" step="any" min="0" id="ref_<?= $i ?>_dist"
                                   name="reference_<?= $i ?>_distance" value="<?= $ref[$i]['distance'] ?>"
                                   placeholder="1234.56" style="width: 100px" autocomplete="off"
                                   required="required"/><br/>
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
            } else {
                ?>
                <tr>
                    <td class="dark" colspan="2">
                        This system has known coordinates in Elite: Dangerous Star Map!<br><br>Thanks for your help in mapping the ED galaxy.
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td class="light" colspan="2">
                    <button id="submitc" onclick="update_data('calc_form', '/Trilateration/coord.php?do', true);tofront('null', true);return false">Submit Query</button>
                </td>
            </tr>
        </table>
    </div>
</form>
