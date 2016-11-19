<?php
/**
 * Ajax backend file for the left column data
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

/**
 * System title for the left column
 */
$data["system_title"] .= "";

$pic = get_allegiance_icon($curSys["allegiance"]);

$data["system_title"] .= '<div class="leftpanel-add-data">';
$data["system_title"] .= '<a href="javascript:void(0)" id="toggle" onclick="setbm(\'' . addslashes($curSys["name"]) . '\', \'' . $curSys["id"] . '\');tofront(\'addBm\');$(\'#bm_text\').focus()" title="Bookmark system">';
$data["system_title"] .= '<img src="/style/img/' . $pic . '" class="allegiance_icon" alt="' . $curSys["allegiance"] . '" />';
$data["system_title"] .= '</a>';
$data["system_title"] .= '</div>';

if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
    $data["system_title"] .= '<div class="leftpanel-title-text"><span id="ltitle">';

    $bookmarked = 0;
    if ($curSys["id"] != "-1") {
        $b_query = "SELECT id
                    FROM user_bookmarks
                    WHERE system_id = '" . $curSys["id"] . "'
                    AND system_id != ''
                    LIMIT 1";
    } else {
        $b_query = "SELECT id
                    FROM user_bookmarks
                    WHERE system_name = '$esc_cursys_name'
                    LIMIT 1";
    }
    $bookmarked = $mysqli->query($b_query)->num_rows;

    $p_query = "SELECT id
                FROM user_poi
                WHERE system_name = '$esc_cursys_name'
                AND system_name != ''
                LIMIT 1";

    $poid = $mysqli->query($p_query)->num_rows;

    $class = $bookmarked > 0 ? "bookmarked" : "title";
    $class = $poid > 0 ? "poid" : $class;

    $data["system_title"] .= '<a class="' . $class . '" href="javascript:void(0)" id="system_title" onclick="tofront(\'distance\');get_cs(\'system_2\', \'coords_2\');$(\'#system_6\').focus()" onmouseover="slide()" onmouseout="slideout()" title="Calculate distances">';

    if (isset($curSys["name"]) && !empty($curSys["name"])) {
        $data["system_title"] .= htmlspecialchars($curSys["name"]);
        $data["system_title"] .= "</a>";
        $data["system_title"] .= '</span><span style="margin-left: 10px;"><button class="btn" data-clipboard-target="#system_title"><img src="/style/img/clipboard.png" alt="Copy" width="13" align="right"></button></span>';
    } else {
        $data["system_title"] .= "Location unavailable";
        $data["system_title"] .= "</a>";

        $data["system_title"] .= '<img class="icon20" src="/style/img/help.png" alt="Help" style="margin-left:6px" onclick="$(\'#location_help\').fadeToggle(\'fast\')" />';
        $data["system_title"] .= '</span>';
        $data["system_title"] .= '<div class="info" id="location_help" style="position:fixed;left:60px;top:40px">';
        $data["system_title"] .= 'If you\'re having trouble getting ED ToolBox to<br />show your current location, check the<br />';
        $data["system_title"] .= '<a href="http://edtb.xyz/?q=common-issues#location_unavailable" target="_blank">Common issues</a> page at EDTB.xyz for help.';
        $data["system_title"] .= '</div>';
    }

    $data["system_title"] .= '</div>';
} else {
    $data["system_title"] .= '<div style="display:none" id="system_title">' . $curSys["name"] . '</div>';
}

/**
 * User balance from FD API
 */
if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
    $status_balance_cache = "";
    if (isset($api["commander"]) && $settings["show_cmdr_status"] == "true") {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/cache/cmdr_balance_status.html")) {
            $status_balance_cache = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/cache/cmdr_balance_status.html");
        }
    }
}

/**
 * System information for the left column
 */
$data["system_info"] = "";

if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
    if (!empty($curSys["allegiance"])) {
        $population_s = $curSys["population"] == "0" ? "" : " - Population: " . number_format($curSys["population"]);
        $population_s = $curSys["population"] == "None" ? "" : $curSys["population"];
        $population_s = $curSys["government"] == "" ? "" : " - " . $curSys["government"];

        $data["system_info"] .= '<div class="subtitle" id="t2">' . $curSys["allegiance"] . $government_s . $population_s . '</div>';

        $data["system_info"] .= '<div class="text" id="t3">';
        if (!empty($curSys["economy"])) {
            $data["system_info"] .= '&boxur; Economy: ' . $curSys["economy"] . '<span style="margin-left:10px">';
        }
        $data["system_info"] .= '<span id="balance_st">' . $status_balance_cache . '</span>';
        $data["system_info"] .= '</span></div>';
    } else {
        $data["system_info"] .= '<div class="subtitle" id="t2">Welcome</div>';
        $data["system_info"] .= '<div class="text" id="t3">';
        $data["system_info"] .= '&boxur; CMDR ' . $settings["cmdr_name"] . '<span style="margin-left:10px">';
        $data["system_info"] .= '<span id="balance_st">' . $status_balance_cache . '</span>';
        $data["system_info"] .= '</span></div>';
    }
}

/**
 * link to calculate coordinates
 */
if (empty($curSys["coordinates"]) && !empty($curSys["name"])) {
    if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
        $calc_coord .= '<span style="margin-bottom:6px;height:40px">';
        $calc_coord .= '<a href="javascript:void(0)" onclick="set_reference_systems(false);tofront(\'calculate\');get_cs(\'target_system\')" title="No coordinates found, click here to calculate">';
        $calc_coord .= '<img src="/style/img/calculator.png" class="icon24" alt="Calculate" />';
        $calc_coord .= '&nbsp;*&nbsp;No coordinates, click to calculate them.</a></span><br /><br />&nbsp';
    } else {
        $calc_coord .= '<span style="margin-bottom:6px;text-align:center">';
        $calc_coord .= '<a href="javascript:void(0)" onclick="set_reference_systems(false);tofront(\'calculate\');get_cs(\'target_system\')" title="No coordinates found, click here to calculate">';
        $calc_coord .= '<img src="/style/img/calculator.png" class="icon24" alt="Calculate" style="margin-left:11px;margin-top:3px" />';
        $calc_coord .= '</a></span>';
    }
}

/**
 * Stations for the left column
 */
if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
    $query = "  SELECT SQL_CACHE
                id, name, ls_from_star, max_landing_pad_size, faction, government, allegiance,
                state, type, import_commodities, export_commodities,
                prohibited_commodities, economies, selling_ships, shipyard,
                outfitting, commodities_market, black_market, refuel, repair, rearm, is_planetary
                FROM edtb_stations
                WHERE system_id = '" . $curSys["id"] . "'
                ORDER BY -ls_from_star DESC, name
                LIMIT 5";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
    $count = $result->num_rows;

    if ($count > 0) {
        $c = 0;
        while ($station_obj = $result->fetch_object()) {
            $station_name = $station_obj->name;

            if ($c == 0) {
                $first_station_name = $station_obj->name;
                $first_station_ls_from_star = $station_obj->ls_from_star;
            }

            $ls_from_star = $station_obj->ls_from_star;
            $max_landing_pad_size = $station_obj->max_landing_pad_size == "" ? "" : "<strong>Landing pad:</strong> " . $station_obj->max_landing_pad_size . "<br />";
            $station_id = $station_obj->id;

            $faction = $station_obj->faction == "" ? "" : "<strong>Faction:</strong> " . $station_obj->faction . "<br />";
            $government = $station_obj->government == "" ? "" : "<strong>Government:</strong> " . $station_obj->government . "<br />";
            $allegiance = $station_obj->allegiance == "" ? "" : "<strong>Allegiance:</strong> " . $station_obj->allegiance . "<br />";

            $state = $station_obj->state == "" ? "" : "<strong>State:</strong> " . $station_obj->state . "<br />";
            $s_type = $station_obj->type;
            $type = $station_obj->type == "" ? "" : "<strong>Type:</strong> " . $station_obj->type . "<br />";
            $economies = $station_obj->economies == "" ? "" : "<strong>Economies:</strong> " . $station_obj->economies . "<br />";

            $import_commodities = $station_obj->import_commodities == "" ? "" : "<br /><strong>Import commodities:</strong> " . $station_obj->import_commodities . "<br />";
            $export_commodities = $station_obj->export_commodities == "" ? "" : "<strong>Export commodities:</strong> " . $station_obj->export_commodities . "<br />";
            $prohibited_commodities = $station_obj->prohibited_commodities == "" ? "" : "<strong>Prohibited commodities:</strong> " . $station_obj->prohibited_commodities . "<br />";

            $selling_ships = $station_obj->selling_ships == "" ? "" : "<br /><strong>Selling ships:</strong> " . str_replace("'", "", $station_obj->selling_ships) . "<br />";

            $shipyard = $station_obj->shipyard;
            $outfitting = $station_obj->outfitting;
            $commodities_market = $station_obj->commodities_market;
            $black_market = $station_obj->black_market;
            $refuel = $station_obj->refuel;
            $repair = $station_obj->repair;
            $rearm = $station_obj->rearm;
            $is_planetary = $station_obj->is_planetary;

            $icon = get_station_icon($s_type, $is_planetary, "margin:3px;margin-left:0px;margin-right:6px");

            $includes = array(  "shipyard" => $shipyard,
                                "outfitting" => $outfitting,
                                "commodities market" => $commodities_market,
                                "black market" => $black_market,
                                "refuel" => $refuel,
                                "repair" => $repair,
                                "restock" => $rearm);

            $i = 0;
            $services = "";
            foreach ($includes as $name => $included) {
                if ($included == 1) {
                    if ($i != 0) {
                        $services .= ", ";
                    } else {
                        $services .= "<strong>Facilities:</strong> ";
                    }

                    $services .= $name;

                    $i++;
                }
            }
            $services .= "<br />";

            $info = $type . $max_landing_pad_size . $faction . $government . $allegiance . $state . $economies . $services . $import_commodities . $export_commodities . $prohibited_commodities . $selling_ships;

            $info = str_replace("['", "", $info);
            $info = str_replace("']", "", $info);
            $info = str_replace("', '", ", ", $info);

            //$info = $info == "" ? "Edit station information" : $info;

            // $station_data .= '<div><a href="javascript:void(0)" onclick="update_values(\'/get/getStationEditData.php?station_id=' . $station_id . '\',\'' . $station_id . '\');tofront(\'addstation\')" style="color:inherit" onmouseover="$(\'#statinfo_' . $station_id . '\').toggle()" onmouseout="$(\'#statinfo_' . $station_id . '\').toggle()">' . $station_name;
            $station_data .= '<div>' . $icon  . '<a href="javascript:void(0)" style="color:inherit" onmouseover="$(\'#statinfo_' . $station_id . '\').fadeToggle(\'fast\')" onmouseout="$(\'#statinfo_' . $station_id . '\').toggle()">' . $station_name;

            if (!empty($ls_from_star)) {
                $station_data .= ' (' . number_format($ls_from_star) . ' ls)';
            }

            $station_data .= "</a>&nbsp;<a href='javascript:void(0)' title='Add to new log as station' onclick='addstation(\"" . $station_name . "\")'><img src='/style/img/right.png' alt='Add to log' class='addstations' /></a>";

            $station_data .= '<div class="stationinfo" id="statinfo_' . $station_id . '">' . $info . '</div></div>';

            $c++;
        }
    } else {
        $station_data .= $calc_coord;
        $station_data .= 'No station data available';
    }
    $result->close();
} else {
    $station_data .= $calc_coord;
}

/**
 * if system coords are user calculated, show calc button
 */
//$query = "  SELECT id, edsm_message
//            FROM user_systems_own
//            WHERE name = '$esc_cursys_name'
//            LIMIT 1";
//
//$system_user_calculated = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
//
//$is_user_calculated = $system_user_calculated->num_rows;
//
//if ($is_user_calculated > 0 && !empty($curSys["name"])) {
//    $c_obj = $system_user_calculated->fetch_object();
//    $edsm_ms = $c_obj->edsm_message;
//    $system_user_calculated->close();
//
//    $parts = explode(":::", $edsm_ms);
//
//    $msg_num = $parts[0];
//
//    /**
//     * ask for more distances
//     */
//    if ($msg_num != "102" && $msg_num != "104") {
//        if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
//            $station_data .= '<span style="float:right;margin-right:2px;margin-top:6px">';
//        } else {
//            $station_data .= '<span style="float:right;margin-top:3px;text-align:center;white-space:nowrap">';
//        }
//        $station_data .= '<a href="javascript:void(0)" onclick="set_reference_systems(false, true);tofront(\'calculate\');get_cs(\'target_system\')" title="Supply more distances">';
//        $station_data .= '<img class="icon24" src="/style/img/calculator2.png" alt="Calculate" />';
//        $station_data .= '</a><a href="javascript:void(0)" onclick="set_reference_systems(false);tofront(\'calculate\');get_cs(\'target_system\')" title="Review distances">';
//        $station_data .= '<img class="icon24" src="/style/img/calculator.png" alt="Calculate" />';
//        $station_data .= '</a></span>';
//    } else {
//        /**
//         *  show review distances
//         */
//        if (!isset($_COOKIE["style"]) || $_COOKIE["style"] != "narrow") {
//            $station_data .= '<span style="float:right;margin-right:8px;margin-top:6px">';
//        } else {
//            $station_data .= '<span style="float:right;margin-top:3px;margin-right:13px;text-align:center">';
//        }
//        $station_data .= '<a href="javascript:void(0)" onclick="set_reference_systems(false);tofront(\'calculate\');get_cs(\'target_system\')" title="Review distances">';
//        $station_data .= '<img class="icon24" src="/style/img/calculator.png" alt="Calculate" />';
//        $station_data .= '</a></span>';
//    }
//}

$data["station_data"] = $station_data;
