<?php
/*
*  ED ToolBox, a companion web app for the video game Elite Dangerous
*  (C) 1984 - 2016 Frontier Developments Plc.
*  ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/**
 * Config file
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$ini_dir = str_replace("/EDTB", "", $_SERVER['DOCUMENT_ROOT']);
$ini_file = "" . $ini_dir . "/data/edtoolbox_v1.ini";
$settings = parse_ini_file($ini_file);

global $settings;

// feed url for galnet news page
$galnet_feed = "http://feed43.com/8865261068171800.xml";

$base_dir = "" . $settings["install_path"] . "/EDTB/";

$settings["new_screendir"] = "" . $settings["install_path"] . "/EDTB/screenshots";

$referencesystems = array(  "Sadr" => "-1794.69,53.6875,365.844",
                            "HD 1" => "-888.375,99.3125,-489.75",
                            "Cant" => "126.406,-249.031,87.7812",
                            "Nox"  => "38.8438,-17.7812,-63.875");

$agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4';
$cookie_file = "" . $_SERVER["DOCUMENT_ROOT"] . "\cache\cookies";
$curl_exe = "" . $settings["install_path"] . "\bin\curl.exe";
