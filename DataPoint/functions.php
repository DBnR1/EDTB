<?php
/**
 * Functions for DataPoint
 *
 * No description
 *
 * @package EDTB\Main
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

use \EDTB\source\System;

/**
 * Parse data for Data Point
 *
 * @param string $key field name
 * @param string $value field value
 * @param float $dX x coordinate
 * @param float $dY y coordinate
 * @param float $dZ z coordinate
 * @param bool $dist
 * @param string $table table name
 * @param bool $enum
 *
 * @return string $thisRow parsed html td tag
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function setData($key, $value, $dX, $dY, $dZ, &$dist, $table, $enum)
{
    $thisRow = '';

    // Regular Expression filter for links
    $regExUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

    $value = $value === '' ? 'n/a' : $value;

    /**
     * show distances
     */
    if ($dist !== false) {
        // figure out what coords to calculate from
        $usableCoords = usableCoords();
        $usex = $usableCoords['x'];
        $usey = $usableCoords['y'];
        $usez = $usableCoords['z'];
        $exact = $usableCoords['current'] === true ? '' : ' *';

        if (validCoordinates($dX, $dY, $dZ)) {
            $distance = number_format(sqrt((($dX - $usex) ** 2) + (($dY - $usey) ** 2) + (($dZ - $usez) ** 2)), 2);
            $thisRow .= '<td class="datapoint_td" style="white-space: nowrap">' . $distance . $exact . '</td>';
        } else {
            $thisRow .= '<td class="datapoint_td">n/a</td>';
        }

        $dist = false;
    }
    /**
     * make a link for systems with an id
     */
    if ($key === 'system_id' && $value != '0') {
        $thisRow .= '<td class="datapoint_td">';
        $thisRow .= '<a href="/System?system_id=' . $value . '">' . $value . '</a>';
        $thisRow .= '</td>';
    }
    /**
     * make a link for systems with system name
     */
    elseif ((strpos($key, 'system_name') !== false && $value != '0') || ($key === 'name' && $table === 'edtb_systems')) {
        /**
         * provide crosslinks to screenshot gallery, log page, etc
         */
        $itemCrosslinks = System::crosslinks($value);

        $thisRow .= '<td class="datapoint_td">';
        $thisRow .= '<a href="/System?system_name=' . urlencode($value) . '">' . $value . $itemCrosslinks . '</a>';
        $thisRow .= '</td>';
    }
    /**
     * number format some values
     */
    elseif (strpos($key, 'price') !== false || strpos($key, 'ls') !== false || strpos($key, 'population') !== false ||
        strpos($key, 'distance') !== false
    ) {
        if (is_numeric($value) && $value != null) {
            $thisRow .= '<td class="datapoint_td">' . number_format($value) . '</td>';
        } else {
            $thisRow .= '<td class="datapoint_td">n/a</td>';
        }
    }
    /**
     * make links
     */
    elseif (preg_match($regExUrl, $value, $url)) {
        $urli = $value;
        if (mb_strlen($value) >= 80) {
            $urli = substr($value, 0, 80) . '...';
        }

        $thisRow .= '<td class="datapoint_td">';
        $thisRow .= preg_replace($regExUrl, '<a href="' . $url[0] . '" target="_blank">' . $urli . '</a> ', $value);
        $thisRow .= '</td>';
    }
    /**
     * make 0,1 human readable
     */
    elseif ($enum !== false) {
        switch ($value) {
            case '0':
                $realValue = '<span class="enum_no">&#10799;</span>';
                break;
            case '1':
                $realValue = '<span class="enum_yes">&#10003;</span>';
                break;
            default:
                $realValue = 'n/a';
        }

        $thisRow .= '<td class="datapoint_td" style="text-align: center">' . $realValue . '</td>';
    } else {
        $thisRow .= '<td class="datapoint_td">' . substr(strip_tags($value), 0, 100) . '</td>';
    }

    /**
     *  parse log entries
     */
    if ($key === 'log_entry') {
        if (mb_strlen($value) >= 100) {
            $thisRow = '<td class="datapoint_td">' . substr(strip_tags($value), 0, 100) . '...</td>';
        } else {
            $thisRow = '<td class="datapoint_td">' . $value . '</td>';
        }
    }

    return $thisRow;
}
