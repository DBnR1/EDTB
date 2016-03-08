<?php
/**
 * Ajax backend file to save audio logs
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
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");

$data = substr($_POST["data"], strpos($_POST["data"], ",") + 1);
$decodedData = base64_decode($data);

$audiodir = $_SERVER["DOCUMENT_ROOT"] . "/audio_logs";

if (!is_dir($audiodir))
{
    if (!mkdir($audiodir, 0775, true))
    {
        $error = error_get_last();
        write_log("Error: " . $error["message"], __FILE__, __LINE__);
    }
}

$filename = $audiodir . "/" . $_POST["fname"];

if (!$fp = fopen($filename, 'wb'))
{
    $error = error_get_last();
    write_log("Error: " . $error["message"], __FILE__, __LINE__);
    exit;
}
else
{
    fwrite($fp, $decodedData);
    fclose($fp);
    ?>
    <script>
        $("#audiofiles").html("ddd");
        $("#audiofiles").append(<?php echo $_POST["fname"];?>);
        $("#audiofiles").append("ddd");
    </script>
    <?php
}
