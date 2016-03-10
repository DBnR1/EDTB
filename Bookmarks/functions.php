<?php
/**
 * Back-end functions for pois and bookmarks
 *
 * This script parses the netLog file to determine the user's current location and fetches
 * related information from the database and puts that information to global variable $curSys
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

/**
 * Make items
 *
 * @param array $arr
 * @param string $type
 * @param int $i
 * @return string
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function makeitem($arr, $type, &$i)
{
    global $usex, $usey, $usez, $system_time;

    $item_id = $arr["id"];
    $item_text = $arr["text"];
    $item_name = $arr["item_name"];
    $item_system_name = $arr["system_name"];
    $item_system_id = $arr["system_id"];
    $item_cat_name = $arr["catname"];
    $item_added_on = $arr["added_on"];

    $item_added_ago = "";

    if (!empty($item_added_on)) {
        $item_added_ago = get_timeago($item_added_on, false);

        $item_added_on = new DateTime(date("Y-m-d\TH:i:s\Z", ($item_added_on + $system_time * 60 * 60)));
        $item_added_on = date_modify($item_added_on, "+1286 years");
        $item_added_on = $item_added_on->format("j M Y, H:i");
    }

    $item_coordx = $arr["item_coordx"];
    $item_coordy = $arr["item_coordy"];
    $item_coordz = $arr["item_coordz"];

    $distance = "n/a";
    if (valid_coordinates($item_coordx, $item_coordy, $item_coordz)) {
        $distance = number_format(sqrt(pow(($item_coordx-($usex)), 2)+pow(($item_coordy-($usey)), 2)+pow(($item_coordz-($usez)), 2)), 1) . " ly";
    }

    /**
     * if visited, change border color
     */
    $visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "   SELECT id
                                                                            FROM user_visited_systems
                                                                            WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $item_system_name) . "'
                                                                            LIMIT 1"));

    $style_override = $visited ? ' style="border-left: 3px solid #3da822"' : "";

    $tdclass = $i % 2 ? "dark" : "light";

    /**
     * provide crosslinks to screenshot gallery, log page, etc
     */
    $item_crosslinks = crosslinks($item_system_name);

    echo '<tr>';
    echo '<td class="' . $tdclass . '" style="min-width:420px;max-width:500px">';
    echo '<div class="poi"' . $style_override . '>';
    echo '<a href="javascript:void(0)" onclick="update_values(\'/Bookmarks/get' . $type . 'EditData.php?' . $type . '_id=' . $item_id . '\',\'' . $item_id . '\');tofront(\'add' . $type . '\')" style="color:inherit" title="Click to edit entry">';

    echo $distance . ' &ndash;';

    if (!empty($item_system_id)) {
        echo '</a>&nbsp;<a title="System information" href="/System?system_id=' . $item_system_id . '" style="color:inherit">';
    } elseif ($item_system_name != "") {
        echo '</a>&nbsp;<a title="System information" href="/System?system_name=' . urlencode($item_system_name) . '" style="color:inherit">';
    } else {
        echo '</a>&nbsp;<a href="#" style="color:inherit">';
    }

    if (empty($item_name)) {
        echo $item_system_name;
    } else {
        echo $item_name;
    }

    echo '</a>' . $item_crosslinks . '<span class="right" style="margin-left:5px">' . $item_cat_name . '</span><br />';

    if (!empty($item_added_on)) {
        echo 'Added: ' . $item_added_on . ' (' . $item_added_ago . ')<br /><br />';
    }

    echo nl2br($item_text);
    echo '</div>';
    echo '</td>';
    echo '</tr>';
    $i++;
}

/**
 * Make item table
 *
 * @param resource $res
 * @param string $type
 * @return string
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function maketable($res, $type)
{
    global $curSys;

    $num = mysqli_num_rows($res);

    echo '<table>';

    if ($num > 0) {
        if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
            echo '<tr>';
            echo '<td class="dark" style="min-width:420px;max-width:500px">';
            echo '<p><strong>No coordinates for current location, last known location used.</strong></p>';
            echo '</td>';
            echo '</tr>';
        }

        $i = 0;
        $to_last = array();
        while ($arr = mysqli_fetch_assoc($res)) {
            echo makeitem($arr, $type, $i);
        }
    } else {
        if ($type == "Poi") {
            ?>
            <tr>
                <td class="dark" style="min-width:420px;max-width:500px">
                    <strong>No points of interest.<br />Click the "Points of Interest" text to add one.</strong>
                </td>
            </tr>
            <?php
        } else {
            ?>
            <tr>
                <td class="dark" style="min-width:420px;max-width:500px">
                    <strong>No bookmarks.<br />Click the allegiance icon on the top left corner to add one.</strong>
                </td>
            </tr>
            <?php
        }
    }

    echo '</table>';
}
