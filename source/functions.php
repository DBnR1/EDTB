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
	if ($onlyedsm !== true)
	{
		$coord_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_visited_systems.system_name,
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
	}
	else
	{
		$coord_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_visited_systems.system_name,
																edtb_systems.x, edtb_systems.y, edtb_systems.z
																FROM user_visited_systems
																LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
																WHERE edtb_systems.x != ''
																ORDER BY user_visited_systems.visit DESC
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}

	$results = mysqli_num_rows($coord_res);
	$last_system = array();

	if ($results > 0)
	{
		$coord_arr = mysqli_fetch_assoc($coord_res);
		$last_system["name"] = $coord_arr["system_name"];
		$last_system["x"] = $coord_arr["x"];
		$last_system["y"] = $coord_arr["y"];
		$last_system["z"] = $coord_arr["z"];

		if ($last_system["x"] == "")
		{
			$last_system["x"] = $coord_arr["own_x"];
			$last_system["y"] = $coord_arr["own_y"];
			$last_system["z"] = $coord_arr["own_z"];
		}
	}
	else
	{
		$last_system["name"] = "";
		$last_system["x"] = "";
		$last_system["y"] = "";
		$last_system["z"] = "";
	}

	return $last_system;
}

/**
 * Calculating coordinates
 */

require_once("Vendor/trilateration.php");

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

	$old = $settings["data_notify_age"]*24*60*60;
	$since = time()-$old;

	if (empty($time))
	{
		return false;
	}

	if ($time < $since)
	{
		return true;
	}
	else
	{
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

	$whoa = array(	$first_name . " so called " . $last_name,
					$first_name . " " . $last_name);

	// Insults from museangel.net, katoninetales.com, mandatory.com with some of my own thrown in for good measure
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
 * Parse data for Data Point
 *
 * @param string $key field name
 * @param string $value field value
 * @param float $d_x x coordinate
 * @param float $d_y y coordinate
 * @param float $d_z z coordinate
 * @param bool $dist
 * @param string $table table name
 * @param bool $enum
 * @return string $this_row parsed html td tag
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function set_data($key, $value, $d_x, $d_y, $d_z, &$dist, $table, $enum)
{
	global $curSys;

	$distance = "";
	$this_row = "";

	// Regular Expression filter for links
	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

	if ($value == "")
	{
		$value = "n/a";
	}

	if ($dist !== false)
	{
		// figure out what coords to calculate from
		$usable_coords = usable_coords();
		$usex = $usable_coords["x"];
		$usey = $usable_coords["y"];
		$usez = $usable_coords["z"];
		$exact = $usable_coords["current"] === true ? "" : " *";

		if (valid_coordinates($d_x, $d_y, $d_z))
		{
			$distance = number_format(sqrt(pow(($d_x-($usex)), 2)+pow(($d_y-($usey)), 2)+pow(($d_z-($usez)), 2)), 2);
			$this_row .= '<td style="padding:10px;white-space:nowrap;vertical-align:middle">' . $distance . '' . $exact . '</td>';
		}
		else
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle">n/a</td>';
		}

		$dist = false;
	}
	// make a link for systems with an id
	if ($key == "system_id" && $value != "0")
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle"><a href="/System.php?system_id=' . $value . '">' . $value . '</a></td>';
	}
	// make a link for systems with system name
	elseif (strpos($key, "system_name") !== false && $value != "0" || $key == "name" && $table == "edtb_systems")
	{
		// check if system has screenshots
		$screenshots = has_screenshots($value) ? '<a href="/Gallery.php?spgmGal=' . urlencode(strip_invalid_dos_chars($value)) . '" title="View image gallery"><img src="/style/img/image.png" class="icon" alt="Gallery" style="vertical-align:top;margin-left:5px;margin-right:0" /></a>' : "";

		// check if system is logged
		$loglink = is_logged($value) ? '<a href="log.php?system=' . urlencode($value) . '" style="color:inherit" title="System has log entries"><img src="/style/img/log.png" class="icon" alt="Log" style="vertical-align:top;margin-left:5px;margin-right:0" /></a>' : "";

		$this_row .= '<td style="padding:10px;vertical-align:middle"><a href="/System.php?system_name=' . urlencode($value) . '">' . $value . '' . $loglink.$screenshots . '</a></td>';
	}
	// number format some values
	elseif (strpos($key, "price") !== false || strpos($key, "ls") !== false || strpos($key, "population") !== false || strpos($key, "distance") !== false)
	{
		if (is_numeric($value) && $value != null)
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle">' . number_format($value) . '</td>';
		}
		else
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle">n/a</td>';
		}
	}
	// make links
	elseif (preg_match($reg_exUrl, $value, $url))
	{
		if (mb_strlen($value) >= 80)
		{
			$urli = substr($value, 0, 80) . "...";
		}
		else
		{
			$urli = $value;
		}
		$this_row .= '<td style="padding:10px;vertical-align:middle">' . preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $value) . '</td>';
	}
	// make 0,1 human readable
	elseif ($enum !== false)
	{
		$real_value = "n/a";
		if ($value == "0")
		{
			$real_value = "<span class='enum_no'>&#10799;</span>";
		}

		if ($value == "1")
		{
			$real_value = "<span class='enum_yes'>&#10003;</span>";
		}

		$this_row .= '<td style="padding:10px;text-align:center;vertical-align:middle">' .  $real_value . '</td>';
	}
	else
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle">' . substr(strip_tags($value), 0, 100) . '</td>';
	}

	// parse log entries
	if ($key == "log_entry")
	{
		if (mb_strlen($value) >= 100)
		{
			$this_row = '<td style="padding:10px;vertical-align:middle">' . substr(strip_tags($value), 0, 100) . '...</td>';
		}
		else
		{
			$this_row = '<td style="padding:10px;vertical-align:middle">' . $value . '</td>';
		}
	}

	return $this_row;
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

    foreach ($arBytes as $arItem)
    {
        if ($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2))) . " " . $arItem["UNIT"];
            break;
        }
    }
    return $result;
}

/**
 * Return the correct starport icon
 *
 * @param string $type starport type
 * @param bool $planetary 0|1
 * @param string $style overrides the style
 * @return string $station_icon html img tag for the starport icon
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_station_icon($type, $planetary = "0", $style = "")
{
	switch ($type)
	{
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
	switch ($allegiance)
	{
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

	if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
	{
		$usable["x"] = $curSys["x"];
		$usable["y"] = $curSys["y"];
		$usable["z"] = $curSys["z"];

		$usable["current"] = true;
	}
	else
	{
		$last_coords = last_known_system();

		$usable["x"] = $last_coords["x"];
		$usable["y"] = $last_coords["y"];
		$usable["z"] = $last_coords["z"];

		$usable["current"] = false;
	}

	if (!valid_coordinates($usable["x"], $usable["y"], $usable["z"]))
	{
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
	if (is_numeric($x) && is_numeric($y) && is_numeric($z))
	{
		return true;
	}
	else
	{
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
	global $settings;
    
    $system_name = strip_invalid_dos_chars($system_name);

	if (empty($system_name))
	{
		return false;
	}

	if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/screenshots/" . $system_name))
	{
		return true;
	}
	else
	{
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
	global $settings;

	if (empty($system))
	{
		return false;
	}

	if ($is_id !== false)
	{
		$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																				FROM user_log
																				WHERE system_id = '" . $system . "'
																				AND system_id != ''
																				LIMIT 1"));
	}
	else
	{
		$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																				FROM user_log
																				WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
																				AND system_name != ''
																				LIMIT 1"));
	}

	if ($logged > 0)
	{
		return true;
	}
	else
	{
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

	foreach ($settings["tts_override"] as $find => $replace)
	{
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
	$count = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																		id
																		FROM edtb_systems
																		WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
																		LIMIT 1"));
	if ($count == 0)
	{
		$count = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																			id
																			FROM user_systems_own
																			WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
																			LIMIT 1"));
	}

	if ($count > 0)
	{
		return true;
	}
	else
	{
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

	if ($icon !== false)
	{
		return "/style/img/ranks/" . $type . "/rank-" . $rank . ".png";
	}
	else
	{
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

	if (array_key_exists(strtolower($name), $ships))
	{
		$ship_name = $ships[strtolower($name)];
	}
	else
	{
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
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	(SELECT
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

	if (valid_coordinates($target_x, $target_y, $target_z))
	{
		$dist = number_format(sqrt(pow(($target_x-($usex)), 2)+pow(($target_y-($usey)), 2)+pow(($target_z-($usez)), 2)), 2);
		$distance = $dist . ' ly' . $exact;
	}
	else
	{
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
 * @return string $value if $update = false
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function edtb_common($name, $field, $update = false, $value = "")
{
	if ($update !== true)
	{
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT " . $field . "
															FROM edtb_common
															WHERE name = '" . $name . "'
															LIMIT 1")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$arr = mysqli_fetch_assoc($res);

		$value = $arr[$field];

		return $value;
	}
	else
	{
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
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

	if (isset($settings["old_screendir"]) && $settings["old_screendir"] != "C:\Users" && $settings["old_screendir"] != "C:\Users\\")
	{
		if (is_dir($settings["old_screendir"]) && is_writable($settings["old_screendir"]))
		{
			$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT visit
																FROM user_visited_systems
																WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $gallery_name) . "'
																ORDER BY visit DESC
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
			$arr = mysqli_fetch_assoc($res);

			$visit_time = strtotime($arr["visit"]);

			if (!$screenshots = scandir($settings["old_screendir"]))
			{
				$error = error_get_last();
				write_log("Error: " . $error["message"], __FILE__, __LINE__);
			}
			else
			{
				$gallery_name = strip_invalid_dos_chars($gallery_name);

				$newscreendir = $settings["new_screendir"] . "/" . $gallery_name;

				$added = 0;
				foreach ($screenshots as $file)
				{
					if (substr($file, -3) == "bmp")
					{
						$filetime = filemtime($settings["old_screendir"] . "/" . $file);
						$filetime = $filetime + ($system_time*60*60);

						if ($filetime > $visit_time)
						{
							if (!is_dir($newscreendir))
							{
								if (!mkdir($newscreendir, 0775, true))
								{
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
							if (file_exists($old_file_bmp))
							{
								exec("\"" . $settings["install_path"] . "/bin/ImageMagick/convert\" \"" . $old_file_bmp . "\" \"" . $new_file_jpg . "\"", $out);

								if (!empty($out))
								{
									$error = json_encode($out);
									write_log("Error: " . $error, __FILE__, __LINE__);
								}
							}

							if ($settings["keep_og"] == "false")
							{
								if (!unlink($old_file_bmp))
								{
									$error = error_get_last();
									write_log("Error: " . $error["message"], __FILE__, __LINE__);
								}
							}
							else
							{
								if (!is_dir($settings["old_screendir"] . "/originals"))
								{
									if (!mkdir($settings["old_screendir"] . "/originals", 0775, true))
									{
										$error = error_get_last();
										write_log("Error: " . $error["message"], __FILE__, __LINE__);
										break;
									}
								}
								if (file_exists($old_file_og))
								{
									$old_file_og = $settings["old_screendir"] . "/originals/" . $filetime  . "_" .  $file;
								}

								if (!rename($old_file_bmp, $old_file_og))
								{
									$error = error_get_last();
									write_log("Error: " . $error["message"], __FILE__, __LINE__);
								}
							}
							// move to new screenshot folder
							if (file_exists($new_file_jpg))
							{
								if (!rename($new_file_jpg, $new_screenshot))
								{
									$error = error_get_last();
									write_log("Error: " . $error["message"], __FILE__, __LINE__);
								}
							}
							$added++;

							/**
							 * add no more than 15 at a time
							 */

							if ($added > 15)
							{
								break;
							}
						}
						else
						{
							$old_file_bmp = $settings["old_screendir"] . "/" . $file;
							$old_file_og = $settings["old_screendir"] . "/originals/" . $file;
							if (!is_dir($settings["old_screendir"] . "/originals"))
							{
								if (!mkdir($settings["old_screendir"] . "/originals", 0775, true))
								{
									$error = error_get_last();
									write_log("Error: " . $error["message"], __FILE__, __LINE__);
									break;
								}
							}
							if (file_exists($old_file_og))
							{
								$old_file_og = $settings["old_screendir"] . "/originals/" . $filetime . "_" .  $file;
							}
							if (!rename($old_file_bmp, $old_file_og))
							{
								$error = error_get_last();
								write_log("Error: " . $error["message"], __FILE__, __LINE__);
							}
						}
					}
				}
			}
			// make thumbnails for the gallery
			if ($added > 0)
			{
				$thumbdir = $newscreendir . "/thumbs";

				if (!is_dir($thumbdir))
				{
					if (!mkdir($thumbdir, 0775, true))
					{
						$error = error_get_last();
						write_log("Error: " . $error["message"], __FILE__, __LINE__);
						//break;
					}
				}
				exec("\"" . $settings["install_path"] . "/bin/ImageMagick/mogrify\" -resize " . $settings["thumbnail_size"] . " -background #333333 -gravity center -extent " . $settings["thumbnail_size"] . " -format jpg -quality 95 -path \"" . $thumbdir . "\" \"" . $newscreendir . "/\"*.jpg", $out3);

				if (!empty($out3))
				{
					$error = json_encode($out3);
					write_log("Error: ". $error, __FILE__, __LINE__);
				}
			}
		}
		else
		{
			write_log("Error: " . $settings["old_screendir"] . " is not writable", __FILE__, __LINE__);
		}
	}
}

/**
 * Count how many jumps user has made since last known
 * coordinates and return a "fuzziness" factor
 *
 * @return array|bool $value range in ly to use for reference systems
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function fuzziness()
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT system_name
														FROM user_visited_systems
														ORDER BY visit DESC
														LIMIT 30")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$count = mysqli_num_rows($res);

	if ($count > 0)
	{
		$last_known = last_known_system(true);
		$last_known_name = $last_known["name"];

		if (!empty($last_known_name))
		{
			$num = 0;
			$value = array();

			while ($arr = mysqli_fetch_assoc($res))
			{
				$visited_system_name = $arr["system_name"];

				if ($visited_system_name == $last_known_name)
				{
					break;
				}
				else
				{
					$num++;
				}
			}

			$num = $num == 0 ? 1 : $num;
			$fuzziness = $num * 40 + 20; // assuming a range of 40 ly per jump (+ 20 ly just to be on the safe side)

			$value["fuzziness"] = $fuzziness;
			$value["system_name"] = $last_known_name;
			$value["x"] = $last_known["x"];
			$value["y"] = $last_known["y"];
			$value["z"] = $last_known["z"];

			return $value;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

/**
 * Calculate optimal reference systems for trilateration
 * Experimental
 *
 * @param bool $standard return standard references
 * @return array $references name => coordinates
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function reference_systems($standard = false)
{
	$start_point = fuzziness();

	if ($start_point !== false && $standard !== true)
	{
		$start_name = $start_point["system_name"];
		$start_x = $start_point["x"];
		$start_y = $start_point["y"];
		$start_z = $start_point["z"];

		$fuzziness = $start_point["fuzziness"];

		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, x, y, z
															FROM edtb_systems
															WHERE x BETWEEN (" . $start_x . " - " . $fuzziness . ") AND (" . $start_x . " + " . $fuzziness . ")
															AND y BETWEEN (" . $start_y . " - " . $fuzziness . ") AND (" . $start_y . " + " . $fuzziness . ")
															AND z BETWEEN (" . $start_z . " - " . $fuzziness . ") AND (" . $start_z . " + " . $fuzziness . ")
															AND sqrt(pow((x-(" . $start_x . ")), 2)+pow((y-(" . $start_y . ")), 2)+pow((z-(" . $start_z . ")), 2)) < " . $fuzziness . "
															AND name != '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $start_name) . "'
															ORDER BY sqrt(pow((x-(" . $start_x . ")), 2)+pow((y-(" . $start_y . ")), 2)+pow((z-(" . $start_z . ")), 2)) DESC")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$num = mysqli_num_rows($res);

		if ($num <= 4)
		{
			$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, x, y, z
																FROM edtb_systems
																WHERE x NOT BETWEEN (" . $start_x . " - " . $fuzziness . ") AND (" . $start_x . " + " . $fuzziness . ")
																AND y NOT BETWEEN (" . $start_y . " - " . $fuzziness . ") AND (" . $start_y . " + " . $fuzziness . ")
																AND z NOT BETWEEN (" . $start_z . " - " . $fuzziness . ") AND (" . $start_z . " + " . $fuzziness . ")
																AND sqrt(pow((x-(" . $start_x . ")), 2)+pow((y-(" . $start_y . ")), 2)+pow((z-(" . $start_z . ")), 2)) > " . $fuzziness . "
																AND name != '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $start_name) . "'
																ORDER BY sqrt(pow((x-(" . $start_x . ")), 2)+pow((y-(" . $start_y . ")), 2)+pow((z-(" . $start_z . ")), 2)) ASC LIMIT 300")
																or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		}

		$i = 0;
		$pool = array();
		while ($arr = mysqli_fetch_assoc($res))
		{
			$pool[$i]["name"] = $arr["name"];
			$pool[$i]["x"] = $arr["x"];
			$pool[$i]["y"] = $arr["y"];
			$pool[$i]["z"] = $arr["z"];

			$i++;
		}

		Utility::orderBy($pool, 'z DESC');
		$references[$pool[0]["name"]] = $pool[0]["x"] . "," . $pool[0]["y"] . "," . $pool[0]["z"];

		Utility::orderBy($pool, 'z ASC');
		$references[$pool[0]["name"]] = $pool[0]["x"] . "," . $pool[0]["y"] . "," . $pool[0]["z"];

		Utility::orderBy($pool, 'x DESC');
		$references[$pool[0]["name"]] = $pool[0]["x"] . "," . $pool[0]["y"] . "," . $pool[0]["z"];

		Utility::orderBy($pool, 'x ASC');
		$references[$pool[0]["name"]] = $pool[0]["x"] . "," . $pool[0]["y"] . "," . $pool[0]["z"];
	}
	/**
	 *  If start point is not set, use standard set of references
	 */
	else
	{
		$references = array(	"Sadr" => "-1794.69,53.6875,365.844",
								"HD 1" => "-888.375,99.3125,-489.75",
								"Cant" => "126.406,-249.031,87.7812",
								"Nox"  => "38.8438,-17.7812,-63.875");
	}

	return $references;
}

/**
 * Make log entries
 *
 * @param resource $log_res
 * @param string $type
 * @return string $logdata
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function make_log_entries($log_res, $type)
{
	global $system_time;

	$this_system = "";
	$this_id = "";
	$i = 0;
	while ($log_arr = mysqli_fetch_assoc($log_res))
	{
		if ($this_id != $log_arr["id"])
		{
			$system_name = $log_arr["system_name"] == "" ? $log_arr["log_system_name"] : $log_arr["system_name"];
			$log_station_name = $log_arr["station_name"];
			$log_text = $log_arr["log_entry"];
			$date = date_create($log_arr["stardate"]);
			$log_added = date_modify($date, "+1286 years");
			$distance = $log_arr["distance"] != "" ? number_format($log_arr["distance"], 1) : "";

			if ($this_system != $system_name && $type == "system" || $this_system != $system_name && $type == "log")
			{
				$add = $distance != 0 ? " (distance " . $distance . " ly" . $exact . ")" : "";

				$sortable = "";
				if ($i == 0)
				{
					if (isset($_GET["slog_sort"]) && $_GET["slog_sort"] != "undefined")
					{
						if ($_GET['slog_sort'] == 'asc') $sssort = 'desc';
						if ($_GET['slog_sort'] == 'desc') $sssort = 'asc';
					}
					else
					{
						$sssort = 'asc';
					}

					$sortable = '<span class="right"><a href="/index.php?slog_sort=' . $sssort . '" title="Sort by date asc/desc"><img class="icon" src="/style/img/sort.png" alt="Sort" style="margin-right:0" /></a></span>';
				}
				if ($type == "log")
				{
					$sortable = "";
				}

				// check if system has screenshots
				$screenshots = has_screenshots($system_name) ? '<a href="/Gallery.php?spgmGal=' . urlencode(strip_invalid_dos_chars($system_name)) . '" title="View image gallery"><img src="/style/img/image.png" alt="Gallery" style="margin-left:5px;margin-right:3px;vertical-align:top" /></a>' : "";

				$logdata .= '<header><h2><img class="icon" src="/style/img/system_log.png" alt="log" />System log for <a href="/System.php?system_name=' . urlencode($system_name) . '">' . $system_name . '</a>' . $screenshots . $add . $sortable . '</h2></header>';
				$logdata .= '<hr>';
			}
			elseif ($type == "general" && $i == 0)
			{
				$sortable = "";
				if (isset($_GET["glog_sort"]) && $_GET["glog_sort"] != "undefined")
				{
					if ($_GET['glog_sort'] == 'asc') $gssort = 'desc';
					if ($_GET['glog_sort'] == 'desc') $gssort = 'asc';
				}
				else
				{
					$gssort = 'asc';
				}

				$sortable = '<span class="right"><a href="/index.php?glog_sort=' . $gssort . '" title="Sort by date asc/desc"><img class="icon" src="/style/img/sort.png" alt="Sort" style="margin-right:0" /></a></span>';

				$logdata .= '<header><h2><img class="icon" src="/style/img/log.png" alt="log" />Commander\'s Log' . $sortable . '</h2></header>';
				$logdata .= '<hr>';
			}

			// check if log is pinned
			$pinned = $log_arr["pinned"] == "1" ? '<img class="icon" src="/style/img/pinned.png" alt="Pinned" />' : "";

			// check if log is personal
			$personal = $log_arr["type"] == "personal" ? '<img class="icon" src="/style/img/user.png" alt="Personal" />' : "";

			$log_title = !empty($log_arr["title"]) ? '&nbsp;&ndash;&nbsp;' . $log_arr["title"] : "";

			// check if log has audio
			$audio = $log_arr["audio"] != "" ? '<a href="javascript:void(0)" onclick="$(\'#' . $log_arr["id"] . '\').fadeToggle(\'fast\')" title="Listen to audio logs"><img class="icon" src="/style/img/audio.png" alt="Audio" /></a>' : "";

			$logdata .= '<h3>' . $pinned . $personal . $audio;
			$logdata .= '<a href="javascript:void(0)" onclick="toggle_log_edit(\'' . $log_arr["id"] . '\')" style="color:inherit" title="Edit entry">';
			$logdata .= date_format($log_added, "j M Y, H:i");

			if (!empty($log_station_name))
			{
				$logdata .= '&nbsp;[Station: ' . htmlspecialchars($log_station_name) . ']';
			}

			$logdata .= $log_title;
			$logdata .= '</a></h3>';
			$logdata .= '<pre class="entriespre" style="margin-bottom:20px">';

			if (!empty($audio))
			{
				$logdata .= '<div class="audio" id="' . $log_arr["id"] . '" style="display:none">';

				$audio_files = explode(", ", $log_arr["audio"]);

				foreach ($audio_files as $audio_file)
				{
					$file = $_SERVER["DOCUMENT_ROOT"] . "/audio_logs/" . $audio_file;
					$file_src = "/audio_logs/" . $audio_file;

					if (file_exists($file))
					{
						$timestamp = filemtime($file)+($system_time*60*60);
						$record_date = date("Y-m-d H:i:s", $timestamp);
						$date = date_create($record_date);
						$record = date_modify($date, "+1286 years");
						$record_added = date_format($record, "j M Y, H:i");
						$added_ago = get_timeago($timestamp);

						$logdata .= '<div style="margin-bottom:4px;margin-top:6px;margin-left:3px">';
						$logdata .= 'Added: ' . $record_added . ' (' . $added_ago . ')';
						$logdata .= '</div>';
						$logdata .= '<div>';
						$logdata .= '<audio controls>';
						$logdata .= '<source src="' . $file_src . '" type="audio/mp3">';
						$logdata .= 'Your browser does not support the audio element.';
						$logdata .= '</audio>';
						$logdata .= '</div>';
					}
				}
				$logdata .= '</div>';
			}

			$logdata .= $log_text;
			$logdata .= '</pre>';
		}

		$this_system = $system_name;
		$this_id = $log_arr["id"];
		$i++;
	}

	return $logdata;
}