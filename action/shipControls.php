<?php
/**
 * Backend file for ship controls
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$actions = isset($_POST["data"]) ? $_POST["data"] : false;
$send = isset($_GET["send"]) ? $_GET["send"] : false;

$shell = new COM("WScript.Shell");
$shell->AppActivate("Elite - Dangerous (CLIENT)");

if ($actions !== false) {
    foreach ($actions as $action) {
        if (substr($action, 0, 10) != "sleep_for_") {
            if (is_array($action)) {
                $repeat = $action[0];
                $keypress = $action[1];

                for ($i = 0; $i < $repeat; $i++) {
                    $shell->SendKeys($keypress);
                }
            } else {
                $shell->SendKeys("{" . $action . "}");
                echo $action;
            }
        } else {
            $sleep = str_replace("sleep_for_", "", $action);
            usleep($sleep);
        }
    }
    exit;
}

if ($send !== false) {
    $shell->SendKeys($send);
    //write_log($send);
    exit;
}
