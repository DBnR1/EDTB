<?php
/**
 * Config file
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

/** set default timezone to utc */
date_default_timezone_set('UTC');

/** @require ini config */
require_once __DIR__ . '/config_ini.inc.php';
/** @require server config */
require_once $settings['install_path'] . '/data/server_config.inc.php';
/** @require MySQL */
require_once __DIR__ . '/MySQL.php';
/** @require functions */
require_once __DIR__ . '/functions_safe.php';

/**
 * Expand the $settings global variable with stuff from the database
 */
$settingsRes = ' SELECT SQL_CACHE user_settings.variable, user_settings.value, edtb_settings_info.type
                  FROM user_settings
                  LEFT JOIN edtb_settings_info ON edtb_settings_info.variable = user_settings.variable';

$result = $mysqli->query($settingsRes) or write_log($mysqli->error, __FILE__, __LINE__);

/**
 * fetch object array
 */
while ($obj = $result->fetch_object()) {
    $variable = $obj->variable;
    $value = $obj->value;

    if ($obj->type === 'array') {
        // split by new line
        $values = preg_split("/\r\n|\r|\n|" . PHP_EOL . '/', $value);

        foreach ($values as $arvalue) {
            if (!empty($arvalue)) {
                $count = 0;
                $parts = explode('>>', $arvalue);

                $var = $parts[0];
                $val = $parts[1];

                $valuesS = explode(',', $val);
                $count = count($valuesS);

                if ($count > 1) {
                    $i = 0;
                    foreach ($valuesS as $valF) {
                        $settings[$variable][$var][$i] = $valF;
                        $i++;
                    }
                } else {
                    $settings[$variable][$var] = $val;
                }
            }
        }
    } elseif ($obj->type === 'csl') {
        $values = explode(',', $value);

        $i = 0;
        foreach ($values as $arvalue) {
            $settings[$variable][$i] = trim($arvalue);
            $i++;
        }
    } else {
        $settings[$variable] = $value;
    }
}

$result->close();

$dropdown = $settings['dropdown'];
array_push($dropdown, $settings['maxdistance']);

/** @constant string galnet_feed feed url for galnet news page */
define('GALNET_FEED', 'http://news.galnet.fr/en/feed');

/** @var string new_screendir */
$settings['new_screendir'] = empty($settings['new_screendir']) ? $settings['install_path'] . '/EDTB/screenshots' : $settings['new_screendir'];

/** @var string agent user agent for FD api */
$settings['agent'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D257';
/** @var string cookie_file cookie file for FD api */
$settings['cookie_file'] =  $_SERVER['DOCUMENT_ROOT'] . "\\cache\\cookies";
/** @var string curl_exe path to curl executable file */
$settings['curl_exe'] = $settings['install_path'] . "\\bin\\curl.exe";

/**
 * parse data from companion json
 */
$profileFile = $_SERVER['DOCUMENT_ROOT'] . '/cache/profile.json';

if (file_exists($profileFile)) {
    $profileFile = file_get_contents($profileFile);

    if ($profileFile === 'no_data') {
        $api['commander'] = 'no_data';
        $api['ship'] = 'no_data';
        $api['stored_ships'] = 'no_data';
    } else {
        $profile = json_decode($profileFile);

        $api['commander'] = $profile->{'commander'};
        $api['ship'] = $profile->{'ship'};
        $api['stored_ships'] = $profile->{'ships'};
    }
}

