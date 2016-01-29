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

/** @var ini_dir ini file directory */
$ini_dir = str_replace("/EDTB", "", $_SERVER['DOCUMENT_ROOT']);
/** @var ini_file ini file */
$ini_file = "" . $ini_dir . "/data/edtoolbox_v1.ini";
/** @var settings global user settings variable */
$settings = parse_ini_file($ini_file);

global $settings;

/** @var galnet_feed feed url for galnet news page */
$galnet_feed = "http://feed43.com/8865261068171800.xml";

/** @var base_dir path to EDTB */
$base_dir = "" . $settings["install_path"] . "/EDTB/";

$settings["new_screendir"] = "" . $settings["install_path"] . "/EDTB/screenshots";

/** @array referencesystems referencesystems for trilateration */
$referencesystems = array(  "Sadr" => "-1794.69,53.6875,365.844",
                            "HD 1" => "-888.375,99.3125,-489.75",
                            "Cant" => "126.406,-249.031,87.7812",
                            "Nox"  => "38.8438,-17.7812,-63.875");

/** @var agent user agent for FD api */
$agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4';
/** @var cookie_file cookie file for FD api */
$cookie_file = "" . $_SERVER["DOCUMENT_ROOT"] . "\cache\cookies";
/** @var curl_exe path to curl executable file */
$curl_exe = "" . $settings["install_path"] . "\bin\curl.exe";
