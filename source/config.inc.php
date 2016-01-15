<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2016 Mauri Kujala (contact@edtb.xyz)
*
*    This program is free software; you can redistribute it and/or
*    modify it under the terms of the GNU General Public License
*    as published by the Free Software Foundation; either version 2
*    of the License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

$ini_dir = str_replace("/EDTB", "", $_SERVER['DOCUMENT_ROOT']);
$ini_file = "" . $ini_dir . "/data/edtoolbox.ini";
$settings = parse_ini_file($ini_file);
global $settings;

$dropdown = $settings["dropdown"];
array_push($dropdown, $settings["maxdistance"]);

// feed url for galnet news page
$galnet_feed = "http://feed43.com/8865261068171800.xml";

$base_dir = "" . $settings["install_path"] . "/EDTB/";

$settings["new_screendir"] = "" . $settings["install_path"] . "/EDTB/screenshots";

$maplink = $settings["default_map"] == "galaxy_map" ? "/galmap.php" : "/map.php";
// links for the navigation panel
$links = array( "ED ToolBox--log.png--true" => "/",
				"System Information--info.png--true" => "/system.php",
				"Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map--grid.png--true" => $maplink,
				"Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks--poi.png--false" => "/poi.php",
				"Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations--find.png--false" => "/nearest_systems.php",
				"Data Point--dataview.png--false" => "/datapoint.php",
				"Galnet News--news.png--false" => "/galnet.php",
				"Screenshot Gallery--gallery.png--false" => "/gallery.php",
				"System Log--log.png--true" => "/");

// reference systems for trilateration, 4 needed
$referencesystems = array(  "Avik" => "13.9688,-4.59375,-6",
                            "Annarthia" => "41.5,-252.25,37.625",
                            "Vorden" => "-170.281,1.28125,-19.7188",
                            "Wi" => "13.531250,25.687500,-48.156250");
