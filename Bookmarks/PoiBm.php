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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
*/

namespace EDTB\Bookmarks;

use EDTB\source\System;
use mysqli_result;

/**
 * Show bookmarks and points of interest
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class PoiBm
{

    /** @var float $usex , $usey, $usez x, y and z coords to use for calculations */
    public $usex, $usey, $usez;

    /** @var int $timeDifference local time difference from UTC */
    public $timeDifference = 0;

    /**
     * PoiBm constructor.
     */
    public function __construct()
    {
        global $server, $user, $pwd, $db;

        /**
         * Connect to MySQL database
         */
        $this->mysqli = new \mysqli($server, $user, $pwd, $db);

        /**
         * check connection
         */
        if ($this->mysqli->connect_errno) {
            echo 'Failed to connect to MySQL: ' . $this->mysqli->connect_error;
        }
    }

    /**
     * Make item table
     *
     * @param mysqli_result $res
     * @param string $type
     *
     * @return void
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public function makeTable($res, $type)
    {
        global $curSys;

        $num = $res->num_rows;

        echo '<table>';

        if ($num > 0) {
            if (!validCoordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
                echo '<tr>';
                echo '<td class="dark poi_minmax">';
                echo '<p><strong>No coordinates for current location, last known location used.</strong></p>';
                echo '</td>';
                echo '</tr>';
            }

            $i = 0;
            $toLast = [];
            $categs = [];

            echo '<tr><td valign="top" style="position: relative; min-width: 400px; max-width: 480px;"><div style="top: 0; left: 0; width: 100%;">';

            while ($obj = $res->fetch_object()) {
                if (!in_array($obj->catname, $categs, true)) {
                    $categs[] = $obj->catname;
                    if (isset($obj->catname) && $obj->catname !== '') {
                        echo '<div style="font-family: Sintony, sans-serif; color: #fffffa; font-size: 13px;padding: 6px;vertical-align: middle;background-color: #2e3436;
					        line-height: 1.5;opacity: 0.9;cursor:pointer;" 
							onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.9\')" 
							id="btn-' . str_replace(' ', '', $obj->catname) . '" class="categ-btns" >' . $obj->catname . '</div>';
                    } else {
                        echo '<div style="font-family: Sintony, sans-serif; color: #fffffa; font-size: 13px;padding: 6px;vertical-align: middle;background-color: #2e3436;
					        line-height: 1.5;opacity: 0.9;cursor:pointer;" 
							onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.9\')" 
							id="btn-' . $type . '" class="categ-btns" >Uncategorized</div>';
                    }
                }
            }

            echo '</div></td><td style="vertical-align: text-top;" id="categ-panels">' . $this->generateColumnsPanels($categs, $res, $type, $i) . '</td></tr>';
        } else {
            if ($type === 'Poi') {
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
     * @param $categs
     * @param $res
     * @param $type
     * @param $i
     *
     * @return string
     */
    private function generateColumnsPanels($categs, $res, $type, $i): string
    {
        $panelss = '';

        for ($b = 0, $bMax = count($categs); $b < $bMax; $b++) {
            if (is_array($categs)) {
                $polishedCategs = str_replace(' ', '', $categs[$b]);
            }

            $polishedCategs = (isset($polishedCategs) && $polishedCategs !== '') ? $polishedCategs : $type;

            $panelss .= '<div id="panel-' . $polishedCategs . '" style="display:none; height: auto;" 
							            class="categ-panels"><table>' . $this->generateColumnsPanelsData($res, $type, $categs[$b], $i) . '</table></div>';
        }

        return $panelss;
    }

    /**
     * @param mysqli_result $res
     * @param $type
     * @param $catName
     * @param $i
     *
     * @return string
     */
    public function generateColumnsPanelsData($res, $type, $catName, $i): string
    {
        mysqli_data_seek($res, 0);
        $itemsToAdd = '<table class="panel-table">';
        while ($obj = $res->fetch_object()) {
            if ($obj->catname === $catName) {
                $itemsToAdd .= $this->makeItem($obj, $type, $i);
            }
        }

        return $itemsToAdd;
    }

    /**
     * Make items
     *
     * @param object $obj
     * @param string $type
     * @param int $i
     *
     * @return string
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function makeItem($obj, $type, &$i): string
    {
        $itemId = $obj->id;
        $itemText = $obj->text;
        $itemName = $obj->item_name;
        $itemSystemName = $obj->system_name;
        $itemSystemId = $obj->system_id;
        $itemCatName = $obj->catname;
        $itemAddedOn = $obj->added_on;

        $toBeReturned = '';

        $itemAddedAgo = '';
        if (!empty($itemAddedOn)) {
            $itemAddedAgo = get_timeago($itemAddedOn, false);

            $itemAddedOn = new \DateTime(date("Y-m-d\TH:i:s\Z", $itemAddedOn + $this->timeDifference * 60 * 60));
            $itemAddedOn = date_modify($itemAddedOn, '+1286 years');
            $itemAddedOn = $itemAddedOn->format('j M Y, H:i');
        }

        $itemCoordx = $obj->item_coordx;
        $itemCoordy = $obj->item_coordy;
        $itemCoordz = $obj->item_coordz;

        $distance = 'n/a';
        if (validCoordinates($itemCoordx, $itemCoordy, $itemCoordz)) {
            $distance = number_format(sqrt((($itemCoordx - $this->usex) ** 2) + (($itemCoordy - $this->usey) ** 2) + (($itemCoordz - $this->usez) ** 2)), 1) . ' ly';
        }

        /**
         * if visited, change border color
         */
        $visited = System::numVisits($itemSystemName);
        $styleOverride = $visited ? ' style="border-left: 3px solid #3da822"' : '';

        $tdclass = $i % 2 ? 'dark' : 'light';

        /**
         * provide crosslinks to screenshot gallery, log page, etc
         */
        $itemCrosslinks = System::crosslinks($itemSystemName);

        $toBeReturned .= '<tr>';
        $toBeReturned .= '<td class="' . $tdclass . ' poi_minmax">';
        $toBeReturned .= '<div class="poi"' . $styleOverride . '>';
        $toBeReturned .= '<a href="javascript:void(0)" onclick="update_values(\'/Bookmarks/get' . $type . 'EditData.php?' . $type . '_id=' . $itemId . '\', \'' . $itemId . '\');tofront(\'add' . $type . '\')" style="color: inherit" title="Click to edit entry">';

        $toBeReturned .= $distance . ' &ndash;';

        if (!empty($itemSystemId)) {
            $toBeReturned .= '</a>&nbsp;<a title="System information" href="/System?system_id=' . $itemSystemId . '" style="color: inherit">';
        } elseif ($itemSystemName !== '') {
            $toBeReturned .= '</a>&nbsp;<a title="System information" href="/System?system_name=' . urlencode($itemSystemName) . '" style="color: inherit">';
        } else {
            $toBeReturned .= '</a>&nbsp;<a href="#" style="color: inherit">';
        }

        if (empty($itemName)) {
            $toBeReturned .= $itemSystemName;
        } else {
            $toBeReturned .= $itemName;
        }

        $toBeReturned .= '</a>' . $itemCrosslinks . '<span class="right" style="margin-left: 5px">' . $itemCatName . '</span><br>';

        if (!empty($itemAddedOn)) {
            $toBeReturned .= 'Added: ' . $itemAddedOn . ' (' . $itemAddedAgo . ')<br><br>';
        }

        $toBeReturned .= nl2br($itemText);
        $toBeReturned .= '</div>';
        $toBeReturned .= '</td>';
        $toBeReturned .= '</tr>';
        $i++;

        return $toBeReturned;
    }

    /**
     * Add, update or delete poi from the database
     *
     * @param object $data
     */
    public function addPoi($data)
    {
        $pSystem = $data->{'poi_system_name'};
        $pName = $data->{'poi_name'};
        $pX = $data->{'poi_coordx'};
        $pY = $data->{'poi_coordy'};
        $pZ = $data->{'poi_coordz'};

        if (validCoordinates($pX, $pY, $pZ)) {
            $addc = ", x = '$pX', y = '$pY', z = '$pZ'";
            $addb = ", '$pX', '$pY', '$pZ'";
        } else {
            $addc = ', x = null, y = null, z = null';
            $addb = ', null, null, null';
        }

        $pEntry = $data->{'poi_text'};
        $pId = $data->{'poi_edit_id'};
        $categoryId = $data->{'category_id'};

        $escName = $this->mysqli->real_escape_string($pName);
        $escSysname = $this->mysqli->real_escape_string($pSystem);
        $escEntry = $this->mysqli->real_escape_string($pEntry);

        if ($pId !== '') {
            $stmt = "   UPDATE user_poi SET
                        poi_name = '$escName',
                        system_name = '$escSysname',
                        text = '$escEntry',
                        category_id = '$categoryId'" . $addc . "
                        WHERE id = '$pId'";
        } elseif (isset($_GET['deleteid'])) {
            $stmt = "   DELETE FROM user_poi
                        WHERE id = '" . $_GET['deleteid'] . "'
                        LIMIT 1";
        } else {
            $stmt = "   INSERT INTO user_poi (poi_name, system_name, text, category_id, x, y, z, added_on)
                        VALUES
                        ('$escName',
                        '$escSysname',
                        '$escEntry',
                        '$categoryId'" . $addb . ',
                        UNIX_TIMESTAMP())';
        }

        $this->mysqli->query($stmt) or write_log($this->mysqli->error, __FILE__, __LINE__);
    }

    /**
     * Add, update or delete bookmarks
     *
     * @param object $data
     */
    public function addBm($data)
    {
        $bmSystemId = $data->{'bm_system_id'};
        $bmSystemName = $data->{'bm_system_name'};
        $bmCatid = $data->{'bm_catid'};
        $bmEntry = $data->{'bm_text'};
        $bmId = $data->{'bm_edit_id'};

        $escEntry = $this->mysqli->real_escape_string($bmEntry);
        $escSysname = $this->mysqli->real_escape_string($bmSystemName);

        if ($bmId !== '') {
            $query = "  UPDATE user_bookmarks SET
                        comment = '$escEntry',
                        system_name = '$escSysname',
                        category_id = '$bmCatid'
                        WHERE id = '$bmId' LIMIT 1";
        } elseif (isset($_GET['deleteid'])) {
            $query = "  DELETE FROM user_bookmarks
                        WHERE id = '" . $_GET['deleteid'] . "'
                        LIMIT 1";
        } else {
            $query = "  INSERT INTO user_bookmarks (system_id, system_name, comment, category_id, added_on)
                        VALUES
                        ('$bmSystemId',
                        '$escSysname',
                        '$escEntry',
                        '$bmCatid',
                        UNIX_TIMESTAMP())";
        }

        $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
    }
}
