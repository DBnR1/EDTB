<?php
/**
 * Ajax backend file to fetch user's profile from FD API
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

/** @var string $lastApiRequest */
$lastApiRequest = edtbCommon('last_api_request', 'unixtime');

/** @var int $timeFrame */
$timeFrame = time() - 6 * 60;

if ($new === 'true') {
    $timeFrame = time();
}

if (isset($_GET['override'])) {
    $override = 'true';
}

if ($override === 'true') {
    $timeFrame = time() - 60;
}

if ($lastApiRequest < $timeFrame && file_exists($settings['cookie_file'])) {
    /**
     * run update script
     */
    if (file_exists($settings['curl_exe'])) {
        //write_log("Calling api");
        //exec("\"" . $settings["curl_exe"] . "\" -b \"" . $settings["cookie_file"] . "\" -H \"User-Agent: " . $settings["agent"] . "\" \"https://companion.orerve.net/profile\" -k", $out);
        exec('"' . $settings['curl_exe'] . '" -b "' . $settings['cookie_file'] . '" -H "User-Agent: ' . $settings['agent'] . '" "https://companion.orerve.net/profile" -k', $out);

        if (!empty($out)) {
            if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cache/profile.json', $out)) {
                $error = error_get_last();
                write_log('Error: ' . $error['message'], __FILE__, __LINE__);
            }
        } else {
            $out = 'no_data';
            if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cache/profile.json', $out)) {
                $error = error_get_last();
                write_log('Error: ' . $error['message'], __FILE__, __LINE__);
            }

            write_log('Error: no output from API, see http://edtb.xyz/?q=common-issues#api_info for help', __FILE__, __LINE__);
        }

        /**
         * update last_api_request value
         */
        edtbCommon('last_api_request', 'unixtime', true, time());
    } else {
        write_log('Error: ' . $settings['curl_exe'] . ' doesn\'t exist');
    }
}
