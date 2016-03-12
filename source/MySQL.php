<?php
/**
 * Backend functions
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

/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
require_once($settings["install_path"] . "/data/server_config.inc.php");

/**
 * Connect to MySQL database
 *
 * @param string $server
 * @param string $user
 * @param string $pwd
 * @param string $db
 */
function db_connect($server, $user, $pwd, $db)
{
    $link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $user, $pwd));
    if (!$link) {
        exit('Could not connect: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    }
    if (!((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $db))) {
        exit('Could not select database: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    }
}

db_connect($server, $user, $pwd, $db);
