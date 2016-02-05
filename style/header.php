<?php
/**
 * Header file
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

// get action if any
$action = isset($_GET["action"]) ? $_GET["action"] : "";
?>
<!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<!-- icon, styles and custom fonts -->
            <link type="image/png" href="/style/img/icon.png" rel="icon" />
            <link type="text/css" href="/style/style.css?ver=<?php echo $settings["edtb_version"]?>" rel="stylesheet" />

			<!-- jquery -->
            <script type="text/javascript" src="/source/Vendor/jquery-2.2.0.min.js"></script>
			<!-- wiselinks -->
			<script type="text/javascript" src="/source/Vendor/wiselinks-1.2.2.min.js"></script>
			<!-- clipboard -->
			<script type="text/javascript" src="/source/Vendor/clipboard.min.js"></script>

			<?php
			if ($_SERVER["PHP_SELF"] == "/GalMap.php")
			{
			?>
				<!-- Three.js -->
				<script src="/source/Vendor/three.min.js"></script>
				<!-- ED3D-Galaxy-Map -->
				<link href="/source/Vendor/ED3D-Galaxy-Map/css/styles.css?ver=<?php echo $settings["edtb_version"]?>" rel="stylesheet" type="text/css" />
				<script src="/source/Vendor/ED3D-Galaxy-Map/js/ed3dmap.js"></script>
			<?php
			}
			?>

			<!-- markitup -->
			<script type="text/javascript" src="/source/Vendor/markitup/sets/html/set.js"></script>
			<script type="text/javascript" src="/source/Vendor/markitup/jquery.markitup.js"></script>

			<?php
			if ($_SERVER["PHP_SELF"] == "/Map.php")
			{
			?>
				<!-- highcharts (map)-->
				<script type="text/javascript" src="/source/Vendor/Highcharts/js/highcharts.js"></script>
				<script type="text/javascript" src="/source/Vendor/Highcharts/js/highcharts-3d.js"></script>
			<?php
			}
			?>

			<!-- own js -->
            <script type="text/javascript" src="/source/javascript.js"></script>

            <title>CMDR <?php echo $settings["cmdr_name"]; ?>'s ToolBox</title>
        </head>
        <body onload="startTime()">
			<div class="se-pre-con" id="loading"><svg width="100" height="100" viewbox="0 0 40 40"><path d="m5,8l5,8l5,-8z" class="l1 d1" /><path d="m5,8l5,-8l5,8z"   class="l1 d2" /><path d="m10,0l5,8l5,-8z"  class="l1 d3" /><path d="m15,8l5,-8l5,8z"  class="l1 d4" /><path d="m20,0l5,8l5,-8z"  class="l1 d5" /><path d="m25,8l5,-8l5,8z"  class="l1 d6" /><path d="m25,8l5,8l5,-8z"  class="l1 d7" /><path d="m30,16l5,-8l5,8z" class="l1 d8" /><path d="m30,16l5,8l5,-8z" class="l1 d9" /><path d="m25,24l5,-8l5,8z" class="l1 d10" /><path d="m25,24l5,8l5,-8z" class="l1 d11" /><path d="m20,32l5,-8l5,8z" class="l1 d13" /><path d="m15,24l5,8l5,-8z" class="l1 d14" /><path d="m10,32l5,-8l5,8z" class="l1 d15" /><path d="m5,24l5,8l5,-8z"  class="l1 d16" /><path d="m5,24l5,-8l5,8z"  class="l1 d17" /><path d="m0,16l5,8l5,-8z"  class="l1 d18" /><path d="m0,16l5,-8l5,8z"  class="l1 d19" /><path d="m10,16l5,-8l5,8z" class="l2 d0" /><path d="m15,8l5,8l5,-8z"  class="l2 d3" /><path d="m20,16l5,-8l5,8z" class="l2 d6"  /><path d="m20,16l5,8l5,-8z" class="l2 d9" /><path d="m15,24l5,-8l5,8z" class="l2 d12" /><path d="m10,16l5,8l5,-8z" class="l2 d15" /></svg></div>
			<div class="leftpanel">
				<div class="leftpanel-top">
					<!-- current system name will be rendered here -->
					<div class="leftpanel-title" id="t1"></div>
					<!-- date and clock will be rendered here -->
					<div id="datetime">
						<div class="leftpanel-date" id="date"></div>
						<div class="leftpanel-clock" id="hrs"></div>
					</div>
					<!-- links to external resources -->
					<div id="ext_links" class="leftpanel-ext_links">
						<?php
						// External links
						foreach ($settings["ext_links"] as $name => $link_href)
						{
							echo '<a href="' .  $link_href . '" target="_BLANK" onclick="$(\'#ext_links\').fadeToggle(\'fast\')">';
							echo '	<div class="leftpanel-ext_links_link">' . $name . '</div>';
							echo '</a>';
						}
						?>
					</div>
				</div>
				<div class="leftpanel-systeminfo">
					<!-- system info will be rendered here -->
					<!-- <div id="systeminfo" onclick="update_values('/get/getSystemEditData.php');tofront('editsystem')"></div> -->
					<div id="systeminfo"></div>
				</div>
				<!-- stations for the current system will be rendered here -->
				<div class="leftpanel-stations" id="stations"></div>

				<div class="leftpanel-links">
					<div class="links">
						<?php
						// set nav links
						$i = 0;
						$count = count($links);
						foreach ($links as $name => $link_href)
						{
							$names = explode("--", $name);
							$name = $names[0];
							$pic = $names[1];
							$reload = $names[2];

							$class = $pagetitle == $name ? "active" : "link";

							//
							if ($pagetitle == "System Log" && $name == "ED ToolBox")
							{
								$class = "active";
							}

							if ($reload != "true")
							{
								$aclass = ' data-push="true"';
								$onclick = ' onclick="setActive(\'' . $i . '\', \'' . $count . '\')"';
							}
							else
							{
								$aclass = '';
								$onclick = '';
							}

							if ($name != "System Log")
							{
								echo '<a' . $aclass . $onclick . ' href="' .  $link_href . '"><div id="link_' . $i . '" class="' . $class . '"><img src="/style/img/' . $pic . '" alt="pic" class="icon" />' . $name . '</div></a>';
							}
							$i++;
						}
						?>
					</div>
				</div>
				<div class="leftpanel-sessionlog">
					<?php
					// session log
					// get old session log
					if (!$sessionlog = file_get_contents($settings["install_path"] . "/data/sessionlog.txt"))
					{
						$error = error_get_last();
						write_log("Error: " . $error["message"], __FILE__, __LINE__);
					}
					?>
					<div class="seslog" id="seslog">
						<textarea class="seslogtext" cols="40" rows="13" id="logtext" oninput="showsave()"><?php echo $sessionlog?></textarea>
						<span id="seslogsuccess"></span>
					</div>
					<!-- currently playing from foobar2000 // -->
					<div id="nowplaying"></div>
				</div>
			</div>
			<div class="rightpanel-top">
				<!-- elite emblem and add logs -->
				<a href="javascript:void(0)" id="toggle" onclick="toggle_log('')" title="Add log entry">
					<img src="/style/img/elite.png" alt="Add log" class="elite_emb" />
				</a>

				<!-- page title and search systems & stations -->
				<div class="rightpanel-pagetitle">
					<a href="javascript:void(0)" onclick="tofront('search_system');$('#system_22').focus()" title="Search for a system" id="pagetitle">
						CMDR <?php echo $settings["cmdr_name"];?>
					</a>
					<?php
					/**
					 * User ranks from FD API
					 */

					if (isset($api["commander"]) && $settings["show_cmdr_status"] == "true")
					{
						if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/cache/cmdr_ranks_status.html"))
						{
							$status_ranks_cache = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/cache/cmdr_ranks_status.html");
						}
						else
						{
							$status_ranks_cache = "";
						}
						echo '<div class="status_ranks" id="cmdr_click"><div id="cmdr_status">' . $status_ranks_cache . '</div></div>';
					}

					/**
					 * EDSM comment link
					 */

					if (!empty($settings["edsm_api_key"]) && !empty($settings["edsm_cmdr_name"]))
					{
						?>
						<span>
							<a id="edsm_cmnt_pic" href="javascript:void(0)" title="Add private comment to EDSM">
								<img src="/style/img/comment.png" class="icon24" alt="EDSM" id="edsm_click" />
							</a>
						</span>
						<?php
					}
					?>
				</div>
				<!-- EDSM comment -->
				<?php
				if (!empty($settings["edsm_api_key"]) && !empty($settings["edsm_cmdr_name"]))
				{
					?>
					<div class="edsm_comment" id="edsm_comment">
						<form method="get" action="/" data-push="true">
							<input type="textbox" id="comment2" class="textbox" name="comment" placeholder="Private EDSM comment for this system" style="width:350px" />
							<br />
							<div class="button" onclick="edsm_comment($('#comment2').val(), true)" style="margin-top:6px;margin-bottom:6px">Send comment</div>
						</form>
						<?php
						if (!empty($settings["edsm_standard_comments"]))
						{
							echo '<br />&nbsp;OR choose from standard set<form method="get" action="/">';
							echo '<select class="selectbox" id="comment1" name="comment" onchange="edsm_comment($(\'#comment1\').val(), true)">';
							echo '<option value="">Choose comment</option>';
							foreach ($settings["edsm_standard_comments"] as $name => $comment)
							{
								echo '<option value="' . $comment . '">';
								echo $name;
								echo '</option>';
							}
							echo '</select></form>';
						}
						?>
					</div>
					<?php
				}
				?>
				<!-- icons & ships status -->
				<div class="right" style="display:inline-block;margin-right:10px;margin-top:15px">
					<?php
					/**
					 * show ship status
					 */

					if (isset($api["ship"]) && $settings["show_ship_status"] == "true")
					{
						if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/cache/ship_status.html"))
						{
							$ship_cache = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/cache/ship_status.html");
						}
						else
						{
							$ship_cache = "";
						}
						echo '<div class="status_ship" id="ship_status">' . $ship_cache . '</div>';
					}
					?>
					<!-- notifications appear here -->
					<span id="notifications"></span>
					<?php
					/**
					 * show refresh button
					 */

					if (isset($api["commander"]) || isset($api["ship"]))
					{
						echo '<a id="api_refresh" href="javascript:void(0)" onclick="refresh_api()" title="Refresh API data"><img src="/style/img/refresh_24.png" alt="Refresh" class="icon24" /></a>';
					}
					?>
					<a href="javascript:void(0)" title="About ED ToolBox" id="about_click">
						<img src="/style/img/about.png" style="height:26px;width:26px" alt="About" />
					</a>
					<a href="javascript:void(0)" title="Settings Panel" id="settings_click">
						<img src="/style/img/settings.png" style="height:26px;width:26px" alt="Settings" />
					</a>
				</div>

				<!-- notices for new releases or old data -->
				<div class="settings_panel" id="notice">
					It has been a while since you last update system and station data.<br />As a result, any data you see here may be outdated.<br /><br />
					Right-click the EDTB manager icon on your system tray and select<br />"Update system and station data".
				</div>
				<div class="settings_panel" id="notice_new"></div>

				<div class="settings_panel" id="settings" style="width:227px">
					<a href="/admin/settings.php" title="Settings editor">
						<div class="link" style="width:90%">
							<img src="/style/img/settings.png" alt="Settings" class="icon" />Customize ED ToolBox
						</div>
					</a>
					<a href="/admin/ini_editor.php" title="Edit ini file">
						<div class="link" style="width:90%">
							<img src="/style/img/vareditor.png" alt="INI" class="icon" />Edit ini file
						</div>
					</a>
					<a href="/admin" title="Database manager (Adminer)" target="_BLANK">
						<div class="link" style="width:90%">
							<img src="/style/img/dataview.png" alt="DB Manager" class="icon" />Database Management
						</div>
					</a>
					<a href="/admin/sql.php" title="Run MySQL queries">
						<div class="link" style="width:90%">
							<img src="/style/img/sql.png" alt="MySQL" class="icon" />Run MySQL queries
						</div>
					</a>
					<a href="/admin/import.php" title="Import flight logs">
						<div class="link" style="width:90%">
							<img src="/style/img/import.png" alt="Import" class="icon" />Import Flight Logs
						</div>
					</a>
					<a href="/admin/api_login.php" title="Connect Companion API">
						<div class="link" style="width:90%">
							<img src="/style/img/api.png" alt="API" class="icon" />Connect Companion API
						</div>
					</a>
				</div>
				<div class="settings_panel" id="about">
					<table>
						<tr>
							<td colspan="3" class="light">ED ToolBox v.<?php echo $settings["edtb_version"]?></td>
						</tr>
						<tr>
							<td class="info_td" colspan="3" style="padding-bottom:5px;padding-top:5px">
								ED ToolBox is a companion tool for the
								<a href="http://www.frontier.co.uk/" target="_BLANK">Frontier Developments</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> game
								<a href="http://www.elitedangerous.com" target="_BLANK">Elite: Dangerous</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" />.<br />
								ED ToolBox is an unofficial tool and is in no way affiliated with Frontier Developments.
							</td>
						</tr>
						<tr>
							<td colspan="3" class="light">Acknowledgements</td>
						</tr>
						<tr>
							<td class="info_td" colspan="3" style="padding-bottom:10px;padding-top:5px">
								This tool and its usage rely heavily on open source resources. Here's a list of (hopefully) all of them:
							</td>
						</tr>
						<tr>
							<td class="info_td">
								<a href="http://eddb.io" target="_BLANK">EDDB.io</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (system and station data)
							</td>
							<td class="info_td">
								<a href="http://markitup.jaysalvat.com/home/" target="_BLANK">markItUp!</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (log editor)
							</td>
							<td class="info_td">
								<a href="http://sourceforge.net/projects/sql-edit-table/" target="_BLANK">MySQL Edit Table</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (Data Point)
							</td>
						</tr>
						<tr>
							<td class="info_td">
								<a href="http://www.phpfastcache.com/" target="_BLANK">phpFastCache</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (page caching)
							</td>
							<td class="info_td">
								<a href="https://codemirror.net/" target="_BLANK">CodeMirror</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (ini-file editor)
							</td>
							<td class="info_td">
								<a href="http://spgm.sourceforge.net/" target="_BLANK">SPGM</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (screenshot gallery)
							</td>
						</tr>
						<tr>
							<td class="info_td">
								<a href="https://jquery.com/" target="_BLANK">jQuery</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (js library)
							</td>
							<td class="info_td">
								<a href="http://feed43.com/" target="_BLANK">Feed43</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (GalNet feed)
							</td>
							<td class="info_td">
								<a href="http://www.highcharts.com/" target="_BLANK">Highcharts</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (neighborhood map)
							</td>
						</tr>
						<tr>
							<td class="info_td">
								<a href="https://github.com/gbiobob/ED3D-Galaxy-Map" target="_BLANK">ED3D Galaxy Map</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (galaxy map)
							</td>
							<td class="info_td">
								<a href="http://threejs.org/" target="_BLANK">Three.js</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (js library)
							</td>
							<td class="info_td">
								<a href="http://www.imagemagick.org" target="_BLANK">ImageMagick®</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> (screenshot tools)
							</td>
						</tr>
						<tr>
							<td class="info_td" colspan="3">
								Icons made by <a href="http://www.freepik.com" title="Freepik">Freepik</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" />,
								<a href="http://www.flaticon.com/authors/designmodo" title="Designmodo">Designmodo</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" />, and
								<a href="http://www.flaticon.com/authors/dave-gandy" title="Dave Gandy">Dave Gandy</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> from
								<a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" /> are licensed by
								<a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0">CC BY 3.0</a>
								<img src="/style/img/external_link.png" class="ext_icon" alt="ext" />
							</td>
						</tr>
						<tr>
							<td class="info_td" colspan="3">
								ED ToolBox was created using assets and imagery from Elite Dangerous, with the permission of Frontier Developments plc,<br />
								for non-commercial purposes. It is not endorsed by nor reflects the views or opinions of Frontier Developments and no<br />
								employee of Frontier Developments was involved in the making of it.
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="rightpanel">
