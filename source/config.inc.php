<?php
/**
 * Config file
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

/** set default timezone to utc */
date_default_timezone_set('UTC');

/** @require ini config */
require_once("config_ini.inc.php");
/** @require server config */
require_once($settings["install_path"] . "/data/server_config.inc.php");
/** @require MySQL */
require_once("MySQL.php");

/**
 *  if the user_settings table doesn't exist, add it
 */
if (!mysqli_query($GLOBALS["___mysqli_ston"], "DESCRIBE `user_settings`"))
{
	/** @require db migration class  */
	require_once("dbMigrate.php");

	$create = new db_create;

	$create->table("user_settings","
	  `id` mediumint(8) unsigned NOT null,>>
	  `variable` varchar(60) NOT null,>>
	  `value` text NOT null
	","
	  ADD PRIMARY KEY (`id`);
	  MODIFY `id` mediumint(8) NOT null AUTO_INCREMENT;
	  ADD UNIQUE(`variable`);
	", __FILE__,  __LINE__);

	$create->run_sql("INSERT IGNORE INTO elite_log.`user_settings` (`id`, `variable`, `value`) VALUES
	(null, 'log_range', '45'),
	(null, 'rare_range', '30'),
	(null, 'edsm_export', 'false'),
	(null, 'edsm_api_key', ''),
	(null, 'edsm_cmdr_name', ''),
	(null, 'edsm_standard_comments', ''),
	(null, 'dropdown', '10,25,35,50,75,100,150,300,500,700'),
	(null, 'maxdistance', '50'),
	(null, 'tts_override', \"1>> one \n2>> two \n3>> tree \n4>> four \n5>> five \n6>> six \n7>> seven \n8>> eight \n9>> niner \n0>> zero \n- >> dash \n&>> and \n:>>. &mdash;\"),
	(null, 'angry_droid', 'true'),
	(null, 'thumbnail_size', '265x149'),
	(null, 'galnet_articles', '4'),
	(null, 'ext_links', \"ED ToolBox>>http://edtb.xyz\nEDDB>>https://eddb.io\nInara>>http://inara.cz\nCoriolis>>http://coriolis.io\nElite Dangerous>>http://www.elitedangerous.com\nFrontier Forums>>https://forums.frontier.co.uk/subscription.php\nDiscounts thread>>https://forums.frontier.co.uk/showthread.php?t=155377\nCommunity Goals thread>>https://forums.frontier.co.uk/showthread.php?t=187372\"),
	(null, 'dist_systems', 'Sol>>Sol'),
	(null, 'galnet_excludes', 'GalNet Weekly, A Week in Powerplay, GalNet Focus, Weekly Conflict Report'),
	(null, 'nowplaying_file', ''),
	(null, 'nowplaying_vlc_url', ''),
	(null, 'nowplaying_vlc_password', ''),
	(null, 'data_view_table', \"edtb_systems>>Systems\nedtb_stations>>Stations\nuser_visited_systems>>Visited Systems\nedtb_rares>>Rares\nedtb_commodities>>Commodities\nedtb_ships>>Ships\nuser_poi>>Points of Interest\nuser_poi_categories>>POI Categories\\nuser_bm_categories>>Bookmark Categories\nuser_log>>Log Entries\nx_mining_sites>>Mining Sites\nx_saved_posts>>Saved Posts\"),
	(null, 'data_view_default_table', 'edtb_systems'),
	(null, 'data_view_ignore', 'edtb_systems>>x,y,z,simbad_ref,updated_at
	edtb_stations>>economies,selling_modules,shipyard_updated_at,outfitting_updated_at
	user_poi>>x,y,z
	edtb_rares>>station_type,system_allegiance,station_allegiance'),
	(null, 'default_map', 'galaxy_map'),
	(null, 'galmap_show_rares', 'true'),
	(null, 'galmap_show_bookmarks', 'true'),
	(null, 'galmap_show_visited_systems', 'true'),
	(null, 'galmap_show_pois', 'true'),
	(null, 'nmap_show_rares', 'true'),
	(null, 'nmap_show_bookmarks', 'true'),
	(null, 'nmap_show_visited_systems', 'true'),
	(null, 'nmap_show_pois', 'true'),
	(null, 'keep_og', 'true'),
	(null, 'data_notify_age', '10'),
	(null, 'show_ship_status', 'true'),
	(null, 'show_cmdr_status', 'true'),
	(null, 'show_cqc_rank', 'false')
	", __FILE__,  __LINE__);
}
/**
 * Expand the $settings global variable with stuff from the database
 */

$settings_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT SQL_CACHE user_settings.variable, user_settings.value, edtb_settings_info.type
															FROM user_settings
															LEFT JOIN edtb_settings_info ON edtb_settings_info.variable = user_settings.variable")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

while ($settings_arr = mysqli_fetch_assoc($settings_res))
{
	$variable = $settings_arr["variable"];
	$value = $settings_arr["value"];

	if ($settings_arr["type"] == "array")
	{
		// split by new line
		$values = preg_split("/\r\n|\r|\n|" . PHP_EOL . "/", $value);

		foreach ($values as $arvalue)
		{
			if (!empty($arvalue))
			{
				$count = 0;
				$parts = explode(">>", $arvalue);

				$var = $parts[0];
				$val = $parts[1];

				$values_s = explode(",", $val);
				$count = count($values_s);

				if ($count > 1)
				{
					$i = 0;
					foreach	($values_s as $val_f)
					{
						$settings[$variable][$var][$i] = $val_f;
						$i++;
					}
				}
				else
				{
					$settings[$variable][$var] = $val;
				}
			}
		}
	}
	elseif ($settings_arr["type"] == "csl")
	{
		$values = explode(",", $value);

		$i = 0;
		foreach ($values as $arvalue)
		{
			$settings[$variable][$i] = trim($arvalue);
			$i++;
		}
	}
	else
	{
		$settings[$variable] = $value;
	}
}

$maplink = $settings["default_map"] == "galaxy_map" ? "/GalMap.php" : "/Map.php";
$dropdown = $settings["dropdown"];
array_push($dropdown, $settings["maxdistance"]);

/**
 * Links for the navigation panel
 */

$links = array( "ED ToolBox--log.png--true" => "/",
				"System Information--info.png--true" => "/System.php",
				"Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map--grid.png--true" => $maplink,
				"Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks--poi.png--false" => "/Poi.php",
				"Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations--find.png--false" => "/NearestSystems.php",
				"Data Point--dataview.png--false" => "/DataPoint.php",
				"Galnet News--news.png--false" => "/GalNet.php",
				"Screenshot Gallery--gallery.png--false" => "/Gallery.php",
				"System Log--log.png--true" => "/");

/** @var galnet_feed feed url for galnet news page */
$galnet_feed = "http://feed43.com/8865261068171800.xml";

/** @var base_dir path to EDTB */
$base_dir = $settings["install_path"] . "/EDTB/";

$settings["new_screendir"] = $settings["install_path"] . "/EDTB/screenshots";

/** @var agent user agent for FD api */
$settings["agent"] = "Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D257";
/** @var cookie_file cookie file for FD api */
$settings["cookie_file"] = $settings["install_path"] . "\EDTB\cache\cookies";
/** @var curl_exe path to curl executable file */
$settings["curl_exe"] = $settings["install_path"] . "\bin\curl.exe";

global $settings;

/**
 * parse data from companion json
 */

$profile_file = $_SERVER["DOCUMENT_ROOT"] . "/profile.json";

if (file_exists($profile_file))
{
	$profile_file = file_get_contents($profile_file);
	$profile = json_decode($profile_file, true);

	$api["commander"] = $profile["commander"];
	$api["ship"] = $profile["ship"];
	$api["stored_ships"] = $profile["ships"];
}

global $api;
