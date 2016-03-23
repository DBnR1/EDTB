<?php
/**
 * Ajax backend file to put Imgur url into a file for Gallery
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

$url = isset($_GET["url"]) ? $_GET["url"] : "";
$file = isset($_GET["file"]) ? $_GET["file"] : "";

if (!empty($file) && !empty($url)) {
    $path = $settings["new_screendir"] . "/Imgur";

    if (!is_dir($path)) {
        if (!mkdir($path, 0775, true)) {
            $error = error_get_last();
            write_log("Error: " . $error["message"], __FILE__, __LINE__);
        }
    }

    $filename = urldecode($file) . ".txt";
    if (!file_put_contents($path . "/" . $filename, $url)) {
        $error = error_get_last();
        write_log("Error: " . $error["message"], __FILE__, __LINE__);
    }
} else {
    write_log("Error: url or file not set", __FILE__, __LINE__);
}
