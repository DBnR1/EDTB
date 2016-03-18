<?php
/**
 * Back-end functions for pois and bookmarks
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
 * Class PoiBm
 */
class PoiBm
{
    public $usex, $usey, $usez;
    public $time_difference = 0;

    /**
     * PoiBm constructor.
     */
    public function __construct()
    {
        global $server, $user, $pwd, $db;

        $this->mysqli = new mysqli($server, $user, $pwd, $db);

        if ($this->mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->mysqli->connect_error;
        }
    }

    /**
     * Make item table
     *
     * @param mysqli_result $res
     * @param string $type
     * @return string
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public function make_table($res, $type)
    {
        global $curSys;

        $num = $res->num_rows;

        echo '<table>';

        if ($num > 0) {
            if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
                echo '<tr>';
                echo '<td class="dark poi_minmax">';
                echo '<p><strong>No coordinates for current location, last known location used.</strong></p>';
                echo '</td>';
                echo '</tr>';
            }

            $i = 0;
            $to_last = [];
            while ($obj = $res->fetch_object()) {
                echo $this->make_item($obj, $type, $i);
            }
        } else {
            if ($type == "Poi") {
                ?>
                <tr>
                    <td class="dark poi_minmax">
                        <strong>No points of interest.<br/>Click the "Points of Interest" text to add one.</strong>
                    </td>
                </tr>
                <?php
            } else {
                ?>
                <tr>
                    <td class="dark poi_minmax">
                        <strong>No bookmarks.<br/>Click the allegiance icon on the top left corner to add one.</strong>
                    </td>
                </tr>
                <?php
            }
        }

        echo '</table>';
    }

    /**
     * Make items
     *
     * @param object $obj
     * @param string $type
     * @param int $i
     * @return string
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function make_item($obj, $type, &$i)
    {
        $item_id = $obj->id;
        $item_text = $obj->text;
        $item_name = $obj->item_name;
        $item_system_name = $obj->system_name;
        $item_system_id = $obj->system_id;
        $item_cat_name = $obj->catname;
        $item_added_on = $obj->added_on;

        $item_added_ago = "";

        if (!empty($item_added_on)) {
            $item_added_ago = get_timeago($item_added_on, false);

            $item_added_on = new DateTime(date("Y-m-d\TH:i:s\Z", ($item_added_on + $this->time_difference * 60 * 60)));
            $item_added_on = date_modify($item_added_on, "+1286 years");
            $item_added_on = $item_added_on->format("j M Y, H:i");
        }

        $item_coordx = $obj->item_coordx;
        $item_coordy = $obj->item_coordy;
        $item_coordz = $obj->item_coordz;

        $distance = "n/a";
        if (valid_coordinates($item_coordx, $item_coordy, $item_coordz)) {
            $distance = number_format(sqrt(pow(($item_coordx - ($this->usex)), 2) + pow(($item_coordy - ($this->usey)), 2) + pow(($item_coordz - ($this->usez)), 2)), 1) . " ly";
        }

        /**
         * if visited, change border color
         */
        $visited = System::num_visits($item_system_name);
        $style_override = $visited ? ' style="border-left: 3px solid #3da822"' : "";

        $tdclass = $i % 2 ? "dark" : "light";

        /**
         * provide crosslinks to screenshot gallery, log page, etc
         */
        $item_crosslinks = System::crosslinks($item_system_name);

        echo '<tr>';
        echo '<td class="' . $tdclass . ' poi_minmax">';
        echo '<div class="poi"' . $style_override . '>';
        echo '<a href="javascript:void(0)" onclick="update_values(\'/Bookmarks/get' . $type . 'EditData.php?' . $type . '_id=' . $item_id . '\', \'' . $item_id . '\');tofront(\'add' . $type . '\')" style="color:inherit" title="Click to edit entry">';

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
     * Add, update or delete poi from the database
     *
     * @param object $data
     */
    public function add_poi($data)
    {
        $p_system = $data->{"poi_system_name"};
        $p_name = $data->{"poi_name"};
        $p_x = $data->{"poi_coordx"};
        $p_y = $data->{"poi_coordy"};
        $p_z = $data->{"poi_coordz"};

        if (valid_coordinates($p_x, $p_y, $p_z)) {
            $addc = ", x = '$p_x', y = '$p_y', z = '$p_z'";
            $addb = ", '$p_x', '$p_y', '$p_z'";
        } else {
            $addc = ", x = null, y = null, z = null";
            $addb = ", null, null, null";
        }

        $p_entry = $data->{"poi_text"};
        $p_id = $data->{"poi_edit_id"};
        $category_id = $data->{"category_id"};

        $esc_name = $this->mysqli->real_escape_string($p_name);
        $esc_sysname = $this->mysqli->real_escape_string($p_system);
        $esc_entry= $this->mysqli->real_escape_string($p_entry);

        if ($p_id != "") {
            $stmt = "   UPDATE user_poi SET
                        poi_name = '$esc_name',
                        system_name = '$esc_sysname',
                        text = '$esc_entry',
                        category_id = '$category_id'" . $addc . "
                        WHERE id = '$p_id'";
        } elseif (isset($_GET["deleteid"])) {
            $stmt = "   DELETE FROM user_poi
                        WHERE id = '" . $_GET["deleteid"] . "'
                        LIMIT 1";
        } else {
            $stmt = "   INSERT INTO user_poi (poi_name, system_name, text, category_id, x, y, z, added_on)
                        VALUES
                        ('$esc_name',
                        '$esc_sysname',
                        '$esc_entry',
                        '$category_id'" . $addb . ",
                        UNIX_TIMESTAMP())";
        }

        $this->mysqli->query($stmt) or write_log($this->mysqli->error, __FILE__, __LINE__);
    }

    /**
     * Add, update or delete bookmarks
     *
     * @param object $data
     */
    public function add_bm($data)
    {
        $bm_system_id = $data->{"bm_system_id"};
        $bm_system_name = $data->{"bm_system_name"};
        $bm_catid = $data->{"bm_catid"};
        $bm_entry = $data->{"bm_text"};
        $bm_id = $data->{"bm_edit_id"};

        $esc_entry = $this->mysqli->real_escape_string($bm_entry);
        $esc_sysname = $this->mysqli->real_escape_string($bm_system_name);

        if ($bm_id != "") {
            $query = "  UPDATE user_bookmarks SET
                        comment = '$esc_entry',
                        system_name = '$esc_sysname',
                        category_id = '$bm_catid'
                        WHERE id = '$bm_id' LIMIT 1";
        } elseif (isset($_GET["deleteid"])) {
            $query = "  DELETE FROM user_bookmarks
                        WHERE id = '" . $_GET["deleteid"] . "'
                        LIMIT 1";
        } else {
            $query = "  INSERT INTO user_bookmarks (system_id, system_name, comment, category_id, added_on)
                        VALUES
                        ('$bm_system_id',
                        '$esc_sysname',
                        '$esc_entry',
                        '$bm_catid',
                        UNIX_TIMESTAMP())";
        }

        $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
    }
}
