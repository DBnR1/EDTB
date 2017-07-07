<?php
/**
 * Ajax backend file to fetch point of interest edit data
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

$poiId = 0 + $_GET['Poi_id'];
$data = [];

if ($poiId == '0') {
    $data['poi_edit_id'] = '';
    $data['system_33'] = '';
    $data['coordsx_33'] = '';
    $data['coordsy_33'] = '';
    $data['coordsz_33'] = '';
    $data['poi_text'] = '';
    $data['poi_name'] = '';
    $data['category_id'] = '0';
} else {
    $query = "  SELECT id, poi_name, system_name, text, category_id, x, y, z
                FROM user_poi
                WHERE id = '$poiId'
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $poiObj = $result->fetch_object();

    $data['poi_edit_id'] = $poiObj->id;
    $data['system_33'] = $poiObj->system_name;

    if (isset($poiObj->x)) {
        $data['coordsx_33'] = $poiObj->x;
        $data['coordsy_33'] = $poiObj->y;
        $data['coordsz_33'] = $poiObj->z;
    } else {
        $data['coordsx_33'] = '';
        $data['coordsy_33'] = '';
        $data['coordsz_33'] = '';
    }

    $data['poi_text'] = $poiObj->text;
    $data['poi_name'] = $poiObj->poi_name;
    $data['category_id'] = $poiObj->category_id;

    $result->close();
}

echo json_encode($data);
