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

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

// get action if any
$action = isset($_GET["action"]) ? $_GET["action"] : "";
?>
<!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<!-- icon, styles and custom fonts -->
            <link type="image/png" href="/style/img/icon.png" rel="icon" />
            <link type="text/css" href="/style/style.css" rel="stylesheet" />
			<link href="/source/ED3D-Galaxy-Map/css/styles.css" rel="stylesheet" type="text/css" />

			<!-- Three.js -->
			<script src="/source/three.min.js"></script>
			<!-- ED3D-Galaxy-Map -->
			<script src="/source/ED3D-Galaxy-Map/js/ed3dmap.js"></script>
			<!-- jquery -->
            <script type="text/javascript" src="/source/jquery-2.1.4.min.js"></script>
			<!-- wiselinks -->
			<script type="text/javascript" src="/source/wiselinks-1.2.2.min.js"></script>
			<!-- markitup -->
			<script type="text/javascript" src="/source/markitup/sets/html/set.js"></script>
			<script type="text/javascript" src="/source/markitup/jquery.markitup.js"></script>
			<!-- own js -->
            <script type="text/javascript" src="/source/javascript.js"></script>

            <title>CMDR <?php echo $settings["cmdr_name"]; ?>'s ToolBox</title>
        </head>
        <body onload="startTime();">
			<div class="se-pre-con" id="loading"><svg width="100" height="100" viewbox="0 0 40 40"><path d="m5,8l5,8l5,-8z" class="l1 d1" /><path d="m5,8l5,-8l5,8z"   class="l1 d2" /><path d="m10,0l5,8l5,-8z"  class="l1 d3" /><path d="m15,8l5,-8l5,8z"  class="l1 d4" /><path d="m20,0l5,8l5,-8z"  class="l1 d5" /><path d="m25,8l5,-8l5,8z"  class="l1 d6" /><path d="m25,8l5,8l5,-8z"  class="l1 d7" /><path d="m30,16l5,-8l5,8z" class="l1 d8" /><path d="m30,16l5,8l5,-8z" class="l1 d9" /><path d="m25,24l5,-8l5,8z" class="l1 d10" /><path d="m25,24l5,8l5,-8z" class="l1 d11" /><path d="m20,32l5,-8l5,8z" class="l1 d13" /><path d="m15,24l5,8l5,-8z" class="l1 d14" /><path d="m10,32l5,-8l5,8z" class="l1 d15" /><path d="m5,24l5,8l5,-8z"  class="l1 d16" /><path d="m5,24l5,-8l5,8z"  class="l1 d17" /><path d="m0,16l5,8l5,-8z"  class="l1 d18" /><path d="m0,16l5,-8l5,8z"  class="l1 d19" /><path d="m10,16l5,-8l5,8z" class="l2 d0" /><path d="m15,8l5,8l5,-8z"  class="l2 d3" /><path d="m20,16l5,-8l5,8z" class="l2 d6"  /><path d="m20,16l5,8l5,-8z" class="l2 d9" /><path d="m15,24l5,-8l5,8z" class="l2 d12" /><path d="m10,16l5,8l5,-8z" class="l2 d15" /></svg></div>
			<div class="leftpanel">
				<div class="leftpanel-top">
					<!-- current system name will be rendered here -->
					<div class="leftpanel-title" id="t1"></div>
					<!-- date and clock will be rendered here -->
					<div id="datetime" onclick="$('#ext_links').fadeToggle('fast');">
						<div class="leftpanel-clock" id="hrs"></div><br />
						<div class="leftpanel-date" id="date"></div>
					</div>
					<!-- links to external resources -->
					<div id="ext_links" class="leftpanel-ext_links">
						<?php
						// links
						foreach ($settings["ext_links"] as $name => $link_href)
						{
							echo '<a href="' .  $link_href . '" target="_BLANK" onclick="$(\'#ext_links\').fadeToggle(\'fast\');"><div class="leftpanel-ext_links_link">' . $name . '</div></a>';
						}
						?>
					</div>
				</div>
				<div class="leftpanel-systeminfo">
					<!-- system info will be rendered here -->
					<!-- <div id="systeminfo" onclick="update_values('/get/getSystemEditData.php');tofront('editsystem');"></div> -->
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

							if ($pagetitle == $name)
							{
								$class = "active";
							}
							else
							{
								$class = "link";
							}
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
								echo '<a' . $aclass . '' . $onclick . ' href="' .  $link_href . '"><div id="link_' . $i . '" class="' . $class . '"><img src="/style/img/' . $pic . '" alt="pic" />&nbsp;&nbsp;' . $name . '</div></a>';
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
					$sessionlog = file_get_contents("" . $settings["install_path"] . "/data/sessionlog.txt");
					?>
					<div class="seslog" id="seslog">
						<textarea class="seslogtext" cols="40" rows="13" id="logtext" oninput="showsave()"><?php echo $sessionlog?></textarea>
						<span id="seslogsuccess"></span>
					</div>
					<!-- currently playing from foobar2000 // -->
					<div id="nowplaying"></div>
				</div>
			</div>
			<div class="rightpanel">
				<div class="rightpanel-top">
					<a href="javascript:void(0);" id="toggle" onclick="get_cs('system_1', 'false', 'system_id');tofront('addlog');$('.addstations').toggle();$('#html').markItUp(mySettings);$('#html').attr('id', 'test');$('#log_form').trigger('reset');" title="Add log entry">
						<img src="/style/img/elite.png" alt="Add log" class="elite_emb" />
					</a>

					<span class="rightpanel-pagetitle">
						<a href="javascript:void(0);" onclick="tofront('search_system');$('#system_22').focus();" title="Search for a system"><?php echo str_replace("&nbsp;&nbsp;", "&nbsp;", $pagetitle)?></a>
					</span>
					<span style="float:right;margin-right:10px;margin-top:15px;">
						<?php
						/*
						*	Display notification if user hasn't updated data in a while
						*/

						$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT unixtime
																			FROM edtb_common
																			WHERE name = 'last_data_update'
																			LIMIT 1")
																			or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
						$arr = mysqli_fetch_assoc($res);
						$last_update = $arr["unixtime"];
						$now = time() - (7*24*60*60); // 7 days

						if ($now > $last_update)
						{
							?>
							<a href="javascript:void(0);" title="Notice" onclick="$('#notice').fadeToggle('fast')"><img src="/style/img/notice.png" style="vertical-align:middle;height:26px;width:26px;" alt="Notice" /></a>
							<?php
						}
						?>
						<a href="javascript:void(0);" title="About ED ToolBox" onclick="$('#about').fadeToggle('fast')"><img src="/style/img/about.png" style="vertical-align:middle;height:26px;width:26px;" alt="About" /></a>
						<a href="javascript:void(0);" title="Settings Panel" onclick="$('#settings').fadeToggle('fast')"><img src="/style/img/settings.png" style="vertical-align:middle;height:26px;width:26px;" alt="Settings" /></a>
					</span>

					<div class="settings_panel" id="notice">
						It has been a while since you last update system and station data.<br />As a result, any data you see here may be outdated.<br /><br />
						Right-click the EDTB manager icon on your system tray and select<br />"Update system and station data".
					</div>

					<div class="settings_panel" id="settings">
						<a href="/admin/ini_editor.php" title="Variable editor"><div class="link" style="width:190px;"><img src="/style/img/vareditor.png" alt="ve" style="vertical-align:middle;" />&nbsp;&nbsp;Customize ED ToolBox</div></a>
						<a href="/admin" title="Database manager (Adminer)" target="_BLANK"><div class="link" style="width:190px;"><img src="/style/img/dataview.png" alt="db" style="vertical-align:middle;" />&nbsp;&nbsp;Database Management</div></a>
						<a href="/admin/import.php" title="Import flight logs"><div class="link" style="width:190px;"><img src="/style/img/import.png" alt="import" style="vertical-align:middle;" />&nbsp;&nbsp;Import Flight Logs</div></a>
					</div>
					<div class="settings_panel" id="about">
						<table>
							<tr>
								<td colspan="3" class="station_info_price_category">What is ED ToolBox?</td>
							</tr>
							<tr>
								<td class="info_td" colspan="3" style="padding-bottom:5px;padding-top:5px;">ED ToolBox is a companion tool for the <a href="http://www.frontier.co.uk/" target="_BLANK">Frontier Developments&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> game <a href="http://www.elitedangerous.com" target="_BLANK">Elite: Dangerous&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a>.<br />ED ToolBox is an unofficial tool and is in no way affiliated with Frontier Developments.</td>
							</tr>
							<tr>
								<td colspan="3" class="station_info_price_category">Acknowledgements</td>
							</tr>
							<tr>
								<td class="info_td" colspan="3" style="padding-bottom:10px;padding-top:5px;">This tool and its usage rely heavily on open source resources. Here's a list of (hopefully) all of them:</td>
							</tr>
							<tr>
								<td class="info_td"><a href="http://eddb.io" target="_BLANK">EDDB.io&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (system and station data)</td>
								<td class="info_td"><a href="http://markitup.jaysalvat.com/home/" target="_BLANK">markItUp!&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (log editor)</td>
								<td class="info_td"><a href="http://sourceforge.net/projects/sql-edit-table/" target="_BLANK">MySQL Edit Table&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (database editor)</td>
							</tr>
							<tr>
								<td class="info_td"><a href="http://www.phpfastcache.com/" target="_BLANK">phpFastCache&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (page caching)</td>
								<td class="info_td"><a href="https://codemirror.net/" target="_BLANK">CodeMirror&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (ini-file editor)</td>
								<td class="info_td"><a href="http://spgm.sourceforge.net/" target="_BLANK">SPGM&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (screenshot gallery)</td>
							</tr>
							<tr>
								<td class="info_td"><a href="https://jquery.com/" target="_BLANK">jQuery&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (js library)</td>
								<td class="info_td"><a href="http://feed43.com/" target="_BLANK">Feed43&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (GalNet feed)</td>
								<td class="info_td"><a href="http://www.highcharts.com/" target="_BLANK">Highcharts&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (neighborhood map)</td>
							</tr>
							<tr>
								<td class="info_td"><a href="https://github.com/gbiobob/ED3D-Galaxy-Map" target="_BLANK">ED3D Galaxy Map&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (galaxy map)</td>
								<td class="info_td"><a href="http://threejs.org/" target="_BLANK">Three.js&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (js library)</td>
								<td class="info_td"><a href="http://www.imagemagick.org" target="_BLANK">ImageMagick®&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> (screenshot tools)</td>
							</tr>
							<tr>
								<td class="info_td" colspan="3">Icons made by <a href="http://www.freepik.com" title="Freepik">Freepik&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a>, <a href="http://www.flaticon.com/authors/designmodo" title="Designmodo">Designmodo&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a>, and <a href="http://www.flaticon.com/authors/dave-gandy" title="Dave Gandy">Dave Gandy&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> from <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a> are licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0">CC BY 3.0&nbsp;<img src="/style/img/external_link.png" style="vertical-align:middle;margin-bottom:3px;" alt="ext" /></a>
								</td>
							</tr>
							<tr>
								<td class="info_td" colspan="3">
								ED ToolBox was created using assets and imagery from Elite Dangerous, with the permission of Frontier Developments plc, <br />
								for non-commercial purposes. It is not endorsed by nor reflects the views or opinions of Frontier Developments and no <br />
								employee of Frontier Developments was involved in the making of it.
								</td>
							</tr>
						</table>
					</div>
				</div>
