<?php
/**
 * Settings
 *
 * No description
 *
 * @package EDTB\Admin
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

if (isset($_GET['do'])) {
    /** @require configs */
    require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
    /** @require functions */
    require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
    /** @require MySQL */
    require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

    $data = json_decode($_REQUEST['input']);

    foreach ($data as $var => $value) {
        $escVal = $mysqli->real_escape_string($value);
        $escVar = $mysqli->real_escape_string($var);

        $query = "  UPDATE user_settings
                    SET value = '$escVal'
                    WHERE variable = '$escVar'
                    LIMIT 1";

        $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
    }

    exit;
}

/** @require Theme class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Theme.php';

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->pageTitle = 'Settings';

/**
 * display the header
 */
$header->displayHeader();

$catId = $_GET['cat_id'] ?? '2';
?>
    <div class="notify_success" id="notify" style="display: none">Settings edited</div>
    <div class="entries">
        <div class="entries_inner">
            <h2>
                <img src="/style/img/settings.png" alt="Settings" class="icon24"/>Settings
            </h2>
            <hr>
            <?php
            /**
             * fetch setting categories
             */
            echo '<ul class="pagination">';

            $query = '  SELECT id, name
                FROM edtb_settings_categories
                ORDER BY weight';

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

            $i = 0;
            while ($obj = $result->fetch_object()) {
                $id = $obj->id;
                $name = $obj->name;

                if ($id == $catId) {
                    $active = " class='actives'";
                    $currentCategory = $name;
                } else {
                    $active = '';
                }

                if (($i % 5) === 0) {
                    echo '</ul><br><ul class="pagination" style="margin-top:-25px">';
                }

                echo '<li' . $active . '><a data-replace="true" data-target=".rightpanel" class="mtelink" href="/Admin?cat_id=' .
                    $id . '">' . $name . '</a></li>';
                $i++;
            }
            $result->close();

            echo '</ul>';

            $query = "  SELECT
                user_settings.id,
                edtb_settings_info.name,
                user_settings.variable,
                edtb_settings_info.type,
                edtb_settings_info.info,
                user_settings.value
                FROM user_settings
                LEFT JOIN edtb_settings_info ON edtb_settings_info.variable = user_settings.variable
                WHERE edtb_settings_info.category_id = '$catId'
                ORDER BY edtb_settings_info.weight";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            ?>
            <form method="post" id="settings_form" action="/Admin">
                <table style="max-width: 720px; margin-bottom: 15px">
                    <tr>
                        <td class="heading">Edit <?= $currentCategory ?></td>
                    </tr>
                    <?php
                    $i = 0;
                    while ($obj = $result->fetch_object()) {
                        $name = $obj->name;
                        $type = $obj->type;
                        $variable = $obj->variable;
                        $info = !empty($obj->info) ? '<div class="settings_info">' . $obj->info . '</div>' : '';
                        $value = $obj->value;

                        $tdclass = ($i % 2) ? 'dark' : 'light';

                        if ($type === 'numeric') {
                            ?>
                            <tr>
                                <td class="<?= $tdclass ?>">
                                    <div>
                                        <?= $info ?>
                                        <input class="textbox" type="number" name="<?= $variable ?>"
                                               placeholder="<?= $name ?>" value="<?= $value ?>"
                                               style="width: 100px"/>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } elseif ($type === 'textbox' || $type === 'csl') {
                            ?>
                            <tr>
                                <td class="<?= $tdclass ?>">
                                    <div>
                                        <?= $info ?>
                                        <input class="textbox" type="text" name="<?= $variable ?>"
                                               placeholder="<?= $name ?>" value="<?= $value ?>"
                                               style="width: 520px"/>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } elseif ($type === 'array') {
                            ?>
                            <tr>
                                <td class="<?= $tdclass ?>">
                                    <div>
                                        <?= $info ?>
                                        <textarea class="textarea" name="<?= $variable ?>"
                                                  placeholder="<?= $name ?>"
                                                  style="width: 520px; height: 220px"><?= $value ?></textarea>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } elseif ($type === 'tf') {
                            if ($value === 'true') {
                                $tSel = ' selected="selected"';
                                $fSel = '';
                            } elseif ($value === 'false') {
                                $tSel = '';
                                $fSel = ' selected="selected"';
                            }
                            ?>
                            <tr>
                                <td class="<?= $tdclass ?>">
                                    <div>
                                        <span class="settings_info"><?= $name ?></span><br>
                                        <select title="tf" class="selectbox" name="<?= $variable ?>" style="width: 100px">
                                            <option value="true"<?= $tSel ?>>Yes</option>
                                            <option value="false"<?= $fSel ?>>No</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } elseif (substr($type, 0, 4) === 'enum') {
                            $values = str_replace('enum::', '', $type);

                            $values = explode('&&', $values);
                            ?>
                            <tr>
                                <td class="<?= $tdclass ?>">
                                    <div>
                                        <span class="settings_info"><?= $name ?></span><br>
                                        <select title="Enum" class="selectbox" name="<?= $variable ?>" style="width: auto">
                                            <?php
                                            foreach ($values as $val) {
                                                $parts = explode('>>', $val);

                                                $valValue = $parts[0];
                                                $valName = $parts[1];

                                                $selected = $value === $valValue ? 'selected="selected"' : '';

                                                echo '<option value="' . $valValue . '" ' . $selected . '>' . $valName .
                                                    '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        $i++;
                    }
                    $result->close();

                    $lclass = $tdclass === 'dark' ? 'light' : 'dark';
                    ?>
                    <tr>
                        <td class="<?= $lclass ?>">
                            <a href="#" data-replace="true" data-target=".entries">
                                <div class="button"
                                     onclick="update_data('settings_form', '/Admin/index.php?do', true);$('#notify').fadeToggle('fast')">
                                    Submit changes
                                </div>
                            </a>
                        </td>
                    </tr>
                </table>
            </form>
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
$footer->displayFooter();
