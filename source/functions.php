<?php
/**
 * Functions
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
require_once("config.inc.php");
/** @require MySQL */
require_once("MySQL.php");
/** @require other functions */
require_once("functions_safe.php");
/** @require curSys */
//require_once("curSys.php");
/** @require mappings */
require_once("FDMaps.php");
/** @require utility */
require_once("Vendor/utility.php");

/**
 * Last known system with valid coordinates
 *
 * @param bool $onlyedsm only include EDSM systems
 * @return array $last_system x, y, z, name
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function last_known_system($onlyedsm = false)
{
    if ($onlyedsm !== true) {
        $coord_res = mysqli_query($GLOBALS["___mysqli_ston"], " SELECT user_visited_systems.system_name,
                                                                edtb_systems.x, edtb_systems.y, edtb_systems.z,
                                                                user_systems_own.x AS own_x,
                                                                user_systems_own.y AS own_y,
                                                                user_systems_own.z AS own_z
                                                                FROM user_visited_systems
                                                                LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                                                                LEFT JOIN user_systems_own ON user_visited_systems.system_name = user_systems_own.name
                                                                WHERE edtb_systems.x != '' OR user_systems_own.x != ''
                                                                ORDER BY user_visited_systems.visit DESC
                                                                LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
    } else {
        $coord_res = mysqli_query($GLOBALS["___mysqli_ston"], " SELECT user_visited_systems.system_name,
                                                                edtb_systems.x, edtb_systems.y, edtb_systems.z
                                                                FROM user_visited_systems
                                                                LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                                                                WHERE edtb_systems.x != ''
                                                                ORDER BY user_visited_systems.visit DESC
                                                                LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
    }

    $results = mysqli_num_rows($coord_res);
    $last_system = array();

    if ($results > 0) {
        $coord_arr = mysqli_fetch_assoc($coord_res);
        $last_system["name"] = $coord_arr["system_name"];
        $last_system["x"] = $coord_arr["x"];
        $last_system["y"] = $coord_arr["y"];
        $last_system["z"] = $coord_arr["z"];

        if ($last_system["x"] == "") {
            $last_system["x"] = $coord_arr["own_x"];
            $last_system["y"] = $coord_arr["own_y"];
            $last_system["z"] = $coord_arr["own_z"];
        }
    } else {
        $last_system["name"] = "";
        $last_system["x"] = "";
        $last_system["y"] = "";
        $last_system["z"] = "";
    }

    return $last_system;
}

/**
 * Generate array from XML
 * https://gist.github.com/laiello/8189351
 */
require_once("Vendor/xml2array.php");

/**
 * Check if data is old
 *
 * @param int $time unix timestamp
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function data_is_old($time)
{
    global $settings;

    $old = $settings["data_notify_age"] * 24 * 60 * 60;
    $since = time()-$old;

    if (empty($time)) {
        return false;
    }

    if ($time < $since) {
        return true;
    } else {
        return false;
    }
}

/**
 * Random insult generator... yes
 *
 * @param string $who_to_insult
 * @return string $insult
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function random_insult($who_to_insult)
{
    $who = explode(" ", $who_to_insult);
    $first_name = $who[0];
    $last_name = $who[1];

    $whoa = array(  $first_name . " so called " . $last_name,
                    $first_name . " " . $last_name);

    /**
     * Insults from museangel.net, katoninetales.com, mandatory.com with some of my own thrown in for good measure
     */
    $pool1 = array("moronic", "putrid", "disgusting", "cockered", "droning", "fobbing", "frothy", "smelly", "infectious", "puny", "roguish", "assinine", "tottering", "shitty", "villainous", "pompous", "elitist", "dirty");
    $pool2 = array("shit-kicking", "Federal", "butt-munching", "clap-ridden", "fart-eating", "clay-brained", "sheep-fucking");
    $pool3 = array("hemorrhoid", "assface", "whore", "kretin", "cumbucket", "fuckface", "asshole", "turd", "taint", "knob", "tit", "shart", "douche");

    // randomize
    shuffle($pool1);
    shuffle($pool2);
    shuffle($pool3);
    shuffle($whoa);

    $insult = "the " . $pool1[0] . " " . $pool2[0] . " " . $pool3[0] . " " . $whoa[0];

    return $insult;
}

/**
 * Converts bytes into human readable file size.
 *
 * @param string $bytes
 * @return string human readable file size (2,87 ??)
 * @author Mogilev Arseny
 */
function FileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
    $arBytes = array(
        0 => array(
            "UNIT" => "TB",
            "VALUE" => pow(1024, 4)
        ),
        1 => array(
            "UNIT" => "GB",
            "VALUE" => pow(1024, 3)
        ),
        2 => array(
            "UNIT" => "MB",
            "VALUE" => pow(1024, 2)
        ),
        3 => array(
            "UNIT" => "KB",
            "VALUE" => 1024
        ),
        4 => array(
            "UNIT" => "B",
            "VALUE" => 0
        ),
    );

    foreach ($arBytes as $arItem) {
        if ($bytes >= $arItem["VALUE"]) {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
            break;
        }
    }
    return $result;
}

/**
 * Return the correct starport icon
 *
 * @param string $type starport type
 * @param string $planetary 0|1
 * @param string $style overrides the style
 * @return string $station_icon html img tag for the starport icon
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_station_icon($type, $planetary = "0", $style = "")
{
    switch ($type) {
        case "Coriolis Starport":
            $station_icon = '<img src="/style/img/spaceports/coriolis.png" class="icon" alt="Coriolis Starport" style="' . $style . '" />';
            break;
        case "Orbis Starport":
            $station_icon = '<img src="/style/img/spaceports/orbis.png" class="icon" alt="Orbis Starport" style="' . $style . '" />';
            break;
        case "Ocellus Starport":
            $station_icon = '<img src="/style/img/spaceports/ocellus.png" class="icon" alt="Ocellus Starport" style="' . $style . '" />';
            break;
        case ($planetary == "0"):
            $station_icon = '<img src="/style/img/spaceports/spaceport.png" class="icon" alt="Starport" style="' . $style . '" />';
            break;
        case ($planetary == "1"):
            $station_icon = '<img src="/style/img/spaceports/planetary.png" class="icon" alt="Planetary" style="' . $style . '" />';
            break;
        default:
            $station_icon = '<img src="/style/img/spaceports/unknown.png" class="icon" alt="Unknown" style="' . $style . '" />';
    }

    return $station_icon;
}

/**
 * Return the correct allegiance icon
 *
 * @param string $allegiance
 * @return string $allegiance_icon name of allegiance icon
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_allegiance_icon($allegiance)
{
    switch ($allegiance) {
        case "Empire":
            $allegiance_icon = "empire.png";
            break;
        case "Alliance":
            $allegiance_icon = "alliance.png";
            break;
        case "Federation":
            $allegiance_icon = "federation.png";
            break;
        default:
            $allegiance_icon = "system.png";
    }

    return $allegiance_icon;
}

/**
 * Return usable coordinates
 *
 * @return array of floats x, y, z and bool current
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function usable_coords()
{
    global $curSys;

    $usable = array();

    if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
        $usable["x"] = $curSys["x"];
        $usable["y"] = $curSys["y"];
        $usable["z"] = $curSys["z"];

        $usable["current"] = true;
    } else {
        $last_coords = last_known_system();

        $usable["x"] = $last_coords["x"];
        $usable["y"] = $last_coords["y"];
        $usable["z"] = $last_coords["z"];

        $usable["current"] = false;
    }

    if (!valid_coordinates($usable["x"], $usable["y"], $usable["z"])) {
        $usable["x"] = "0";
        $usable["y"] = "0";
        $usable["z"] = "0";

        $usable["current"] = false;
    }
    return $usable;
}

/**
 * Validate coordinates
 *
 * @param float $x
 * @param float $y
 * @param float $z
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function valid_coordinates($x, $y, $z)
{
    if (is_numeric($x) && is_numeric($y) && is_numeric($z)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check if system is mapped in System map
 *
 * @param string $system_name
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function is_mapped($system_name)
{
    if (empty($system_name)) {
        return false;
    }

    $res = mysqli_query($GLOBALS["___mysqli_ston"], "   SELECT id
                                                        FROM user_system_map
                                                        WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
                                                        LIMIT 1");
    $num = mysqli_num_rows($res);

    if ($num > 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check if system has screenshots
 *
 * @param string $system_name
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function has_screenshots($system_name)
{
    $system_name = strip_invalid_dos_chars($system_name);

    if (empty($system_name)) {
        return false;
    }

    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/screenshots/" . $system_name)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Check if system is logged
 *
 * @param string $system
 * @param bool $is_id
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function is_logged($system, $is_id = false)
{
    if (empty($system)) {
        return false;
    }

    if ($is_id !== false) {
        $logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "    SELECT id
                                                                                FROM user_log
                                                                                WHERE system_id = '" . $system . "'
                                                                                AND system_id != ''
                                                                                LIMIT 1"));
    } else {
        $logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "    SELECT id
                                                                                FROM user_log
                                                                                WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
                                                                                AND system_name != ''
                                                                                LIMIT 1"));
    }

    if ($logged > 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Replace output text for text-to-speech overrides
 *
 * @param string $text
 * @return string $text
 * @author Travis @ https://github.com/padthaitofuhot
 */
function tts_override($text)
{
    global $settings;

    foreach ($settings["tts_override"] as $find => $replace) {
        $text = str_ireplace($find, $replace, $text);
    }

    return $text;
}

/**
 * Check if a system exists in our database
 *
 * @param string $system_name
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function system_exists($system_name)
{
    $count = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], " SELECT
                                                                        id
                                                                        FROM edtb_systems
                                                                        WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
                                                                        LIMIT 1"));
    if ($count == 0) {
        $count = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], " SELECT
                                                                            id
                                                                            FROM user_systems_own
                                                                            WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
                                                                            LIMIT 1"));
    }

    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * Return rank icon/name
 *
 * @param string $type
 * @param string $rank
 * @param bool $icon
 * @return string path to icon or rank name
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_rank($type, $rank, $icon = true)
{
    global $ranks;

    if ($icon !== false) {
        return "/style/img/ranks/" . $type . "/rank-" . $rank . ".png";
    } else {
        return $ranks[$type][$rank];
    }
}

/**
 * Return proper ship name
 *
 * @param string $name
 * @return string $ship_name
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function ship_name($name)
{
    global $ships;

    if (array_key_exists(strtolower($name), $ships)) {
        $ship_name = $ships[strtolower($name)];
    } else {
        $ship_name = $name;
    }

    return $ship_name;
}

/**
 * Return distance from current to $system
 *
 * @param string|int $system
 * @param bool $is_id
 * @return string $distance
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_distance($system, $is_id = false)
{
    // fetch target coordinates
    $res = mysqli_query($GLOBALS["___mysqli_ston"], "   (SELECT
                                                        edtb_systems.x AS target_x,
                                                        edtb_systems.y AS target_y,
                                                        edtb_systems.z AS target_z
                                                        FROM edtb_systems
                                                        WHERE edtb_systems.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "')
                                                        UNION
                                                        (SELECT
                                                        user_systems_own.x AS target_x,
                                                        user_systems_own.y AS target_y,
                                                        user_systems_own.z AS target_z
                                                        FROM user_systems_own
                                                        WHERE user_systems_own.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "')
                                                        LIMIT 1")
                                                        or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

    $arr = mysqli_fetch_assoc($res);

    $target_x = $arr["target_x"];
    $target_y = $arr["target_y"];
    $target_z = $arr["target_z"];

    // figure out what coords to calculate from
    $usable_coords = usable_coords();
    $usex = $usable_coords["x"];
    $usey = $usable_coords["y"];
    $usez = $usable_coords["z"];
    $exact = $usable_coords["current"] === true ? "" : " *";

    if (valid_coordinates($target_x, $target_y, $target_z)) {
        $dist = number_format(sqrt(pow(($target_x-($usex)), 2)+pow(($target_y-($usey)), 2)+pow(($target_z-($usez)), 2)), 2);
        $distance = $dist . ' ly' . $exact;
    } else {
        $distance = '';
    }

    return $distance;
}

/**
 * Fetch or update data from edtb_common
 *
 * @param string $name
 * @param string $field
 * @param bool $update
 * @param string $value
 * @return string|null $value if $update = false
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function edtb_common($name, $field, $update = false, $value = "")
{
    if ($update !== true) {
        $res = mysqli_query($GLOBALS["___mysqli_ston"], "   SELECT " . $field . "
                                                            FROM edtb_common
                                                            WHERE name = '" . $name . "'
                                                            LIMIT 1")
                                                            or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

        $arr = mysqli_fetch_assoc($res);

        $value = $arr[$field];

        return $value;
    } else {
        $res = mysqli_query($GLOBALS["___mysqli_ston"], "   UPDATE edtb_common
                                                            SET " . $field . " = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $value) . "'
                                                            WHERE name = '" . $name . "'
                                                            LIMIT 1")
                                                            or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
    }
}

/**
 * Remove invalid dos characters
 *
 * @param string $source_string directory/file name to check for invalid chars
 * @return string $ret_value
 * @author David Marshall <contact@edtb.xyz>
 */
function strip_invalid_dos_chars($source_string)
{
    $invalid_chars = array('*','\\','/',':','?','"','<','>','|'); // Invalid chars according to Windows 10
    $ret_value = str_replace($invalid_chars, "_", $source_string);
    return $ret_value;
}

/**
 * Convert screenshots to jpg and move to screenhot folder
 *
 * @param string $gallery_name name of the gallery to create
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function make_gallery($gallery_name)
{
    global $settings, $system_time;

    if (isset($settings["old_screendir"]) && $settings["old_screendir"] != "C:\\Users" && $settings["old_screendir"] != "C:\\Users\\") {
        if (is_dir($settings["old_screendir"]) && is_writable($settings["old_screendir"])) {
            $res = mysqli_query($GLOBALS["___mysqli_ston"], "   SELECT visit
                                                                FROM user_visited_systems
                                                                WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $gallery_name) . "'
                                                                ORDER BY visit DESC
                                                                LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);

            $visit_time = isset($arr["visit"]) ? strtotime($arr["visit"]) : time();

            if (!$screenshots = scandir($settings["old_screendir"])) {
                $error = error_get_last();
                write_log("Error: " . $error["message"], __FILE__, __LINE__);
            } else {
                $gallery_name = strip_invalid_dos_chars($gallery_name);

                $newscreendir = $settings["new_screendir"] . "/" . $gallery_name;

                $added = 0;
                foreach ($screenshots as $file) {
                    if (substr($file, -3) == "bmp") {
                        $filetime = filemtime($settings["old_screendir"] . "/" . $file);
                        $filetime = $filetime + ($system_time * 60 * 60);

                        if ($filetime > $visit_time) {
                            if (!is_dir($newscreendir)) {
                                if (!mkdir($newscreendir, 0775, true)) {
                                    $error = error_get_last();
                                    write_log("Error: " . $error["message"], __FILE__, __LINE__);
                                    break;
                                }
                            }
                            $old_file_bmp = $settings["old_screendir"] . "/" . $file;
                            $old_file_og = $settings["old_screendir"] . "/originals/" . $file;
                            $edited = date("Y-m-d_H-i-s", filemtime($old_file_bmp));
                            $new_filename = $edited . "-" . $gallery_name . ".jpg";
                            $new_file_jpg = $settings["old_screendir"] . "/" . $new_filename;
                            $new_screenshot = $newscreendir . "/" . $new_filename;

                            // convert from bmp to jpg
                            if (file_exists($old_file_bmp)) {
                                exec("\"" . $settings["install_path"] . "/bin/ImageMagick/convert\" \"" . $old_file_bmp . "\" \"" . $new_file_jpg . "\"", $out);

                                if (!empty($out)) {
                                    $error = json_encode($out);
                                    write_log("Error: " . $error, __FILE__, __LINE__);
                                }
                            }

                            if ($settings["keep_og"] == "false") {
                                if (!unlink($old_file_bmp)) {
                                    $error = error_get_last();
                                    write_log("Error: " . $error["message"], __FILE__, __LINE__);
                                }
                            } else {
                                if (!is_dir($settings["old_screendir"] . "/originals")) {
                                    if (!mkdir($settings["old_screendir"] . "/originals", 0775, true)) {
                                        $error = error_get_last();
                                        write_log("Error: " . $error["message"], __FILE__, __LINE__);
                                        break;
                                    }
                                }
                                if (file_exists($old_file_og)) {
                                    $old_file_og = $settings["old_screendir"] . "/originals/" . $filetime  . "_" .  $file;
                                }

                                if (!rename($old_file_bmp, $old_file_og)) {
                                    $error = error_get_last();
                                    write_log("Error: " . $error["message"], __FILE__, __LINE__);
                                }
                            }
                            // move to new screenshot folder
                            if (file_exists($new_file_jpg)) {
                                if (!rename($new_file_jpg, $new_screenshot)) {
                                    $error = error_get_last();
                                    write_log("Error: " . $error["message"], __FILE__, __LINE__);
                                }
                            }
                            $added++;

                            /**
                             * add no more than 15 at a time
                             */
                            if ($added > 15) {
                                break;
                            }
                        } else {
                            $old_file_bmp = $settings["old_screendir"] . "/" . $file;
                            $old_file_og = $settings["old_screendir"] . "/originals/" . $file;
                            if (!is_dir($settings["old_screendir"] . "/originals")) {
                                if (!mkdir($settings["old_screendir"] . "/originals", 0775, true)) {
                                    $error = error_get_last();
                                    write_log("Error: " . $error["message"], __FILE__, __LINE__);
                                    break;
                                }
                            }
                            if (file_exists($old_file_og)) {
                                $old_file_og = $settings["old_screendir"] . "/originals/" . $filetime . "_" .  $file;
                            }
                            if (!rename($old_file_bmp, $old_file_og)) {
                                $error = error_get_last();
                                write_log("Error: " . $error["message"], __FILE__, __LINE__);
                            }
                        }
                    }
                }
            }
            // make thumbnails for the gallery
            if ($added > 0) {
                $thumbdir = $newscreendir . "/thumbs";

                if (!is_dir($thumbdir)) {
                    if (!mkdir($thumbdir, 0775, true)) {
                        $error = error_get_last();
                        write_log("Error: " . $error["message"], __FILE__, __LINE__);
                        //break;
                    }
                }
                exec("\"" . $settings["install_path"] . "/bin/ImageMagick/mogrify\" -resize " . $settings["thumbnail_size"] . " -background #333333 -gravity center -extent " . $settings["thumbnail_size"] . " -format jpg -quality 95 -path \"" . $thumbdir . "\" \"" . $newscreendir . "/\"*.jpg", $out3);

                if (!empty($out3)) {
                    $error = json_encode($out3);
                    write_log("Error: " . $error, __FILE__, __LINE__);
                }
            }
        } else {
            write_log("Error: " . $settings["old_screendir"] . " is not writable", __FILE__, __LINE__);
        }
    }
}

/**
 * Return links to screenshots, system log or system map
 *
 * @param string $system
 * @param bool $show_screens
 * @param bool $show_system
 * @param bool $show_logs
 * @param bool $show_map
 * @return string $return
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function crosslinks($system, $show_screens = true, $show_system = false, $show_logs = true, $show_map = true)
{
    $return = "";
    // check if system has screenshots
    if ($show_screens === true && has_screenshots($system)) {
        $return .= '<a href="/Gallery.php?spgmGal=' . urlencode(strip_invalid_dos_chars($system)) . '" title="View image gallery">';
        $return .= '<img src="/style/img/image.png" class="icon" alt="Gallery" style="margin-left:5px;margin-right:0;vertical-align:top" />';
        $return .= '</a>';
    }

    // check if system is logged
    if ($show_logs === true && is_logged($system)) {
        $return .= '<a href="/Log?system=' . urlencode($system) . '" style="color:inherit" title="System has log entries">';
        $return .= '<img src="/style/img/log.png" class="icon" style="margin-left:5px;margin-right:0" />';
        $return .= '</a>';
    }

    // check if system is mapped
    if ($show_map === true && is_mapped($system)) {
        $return .= '<a href="/SystemMap/?system=' . urlencode($system) . '" style="color:inherit" title="System map">';
        $return .= '<img src="/style/img/grid.png" class="icon" style="margin-left:5px;margin-right:0" />';
        $return .= '</a>';
    }

    // show link if system exists
    if ($show_system === true && system_exists($system)) {
        $return .= '<a href="/System?system_name=' . urlencode($system) . '" style="color:inherit" title="System info">';
        $return .= '<img src="/style/img/info.png" class="icon" alt="Info" style="margin-left:5px;margin-right:0" />';
        $return .= '</a>';
    }

    return $return;
}
