<?php
/**
 * Header class
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

/** @require installer script */
require_once$_SERVER['DOCUMENT_ROOT'] . '/Install/install_script.php';
/** @require config */
require_once$_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require MySQL */
require_once$_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';
/** @require functions */
require_once$_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require curSys */
require_once$_SERVER['DOCUMENT_ROOT'] . '/source/curSys.php';

use \EDTB\style\Theme;

/**
 * Header
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class Header extends Theme
{
    /** @var string $pageTitle */
    public $pageTitle = '';

    /**
     * Display the header
     */
    public function displayHeader()
    {
        global $settings;
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <!-- icon, styles and custom fonts -->
            <link type="image/png" href="/style/img/icon.png" rel="icon" />
            <link type="text/css" href="/style/style.css?ver=<?= $settings['edtb_version']?>" rel="stylesheet" />

            <?php
            if (Theme::sidebarStyle() === 'narrow') {
                ?>
                <link type="text/css" href="/style/style_narrow.css?ver=<?= $settings['edtb_version']?>" rel="stylesheet" />
                <?php
            }
            ?>

            <!-- jquery -->
            <script src="/source/Vendor/jquery-2.2.0.min.js"></script>
            <!-- wiselinks -->
            <script src="/source/Vendor/wiselinks-1.2.2.min.js"></script>
            <!-- clipboard -->
            <script src="/source/Vendor/clipboard.min.js"></script>
            <!-- audio recorder -->
            <script src="/source/Vendor/Recordmp3js/recordmp3.js"></script>
            <script src="/source/Vendor/adamwdraper-Numeral-js-7487acb/numeral.js"></script>

            <!-- markitup -->
            <script src="/source/Vendor/markitup/sets/html/set.js"></script>
            <script src="/source/Vendor/markitup/jquery.markitup.js"></script>

            <!-- own js -->
            <script src="/source/javascript.js"></script>

            <!-- global variable for clock -->
            <script>
                var gmt = "<?= $settings['game_time']?>";
            </script>

            <title>CMDR <?= $settings['cmdr_name']?>'s ToolBox</title>
        </head>
        <body onload="startTime()">
        <div class="se-pre-con" id="loading">
            <svg width="100" height="100" viewbox="0 0 40 40">
                <path d="m5,8l5,8l5,-8z" class="l1 d1" />
                <path d="m5,8l5,-8l5,8z" class="l1 d2" />
                <path d="m10,0l5,8l5,-8z" class="l1 d3" />
                <path d="m15,8l5,-8l5,8z" class="l1 d4" />
                <path d="m20,0l5,8l5,-8z" class="l1 d5" />
                <path d="m25,8l5,-8l5,8z" class="l1 d6" />
                <path d="m25,8l5,8l5,-8z" class="l1 d7" />
                <path d="m30,16l5,-8l5,8z" class="l1 d8" />
                <path d="m30,16l5,8l5,-8z" class="l1 d9" />
                <path d="m25,24l5,-8l5,8z" class="l1 d10" />
                <path d="m25,24l5,8l5,-8z" class="l1 d11" />
                <path d="m20,32l5,-8l5,8z" class="l1 d13" />
                <path d="m15,24l5,8l5,-8z" class="l1 d14" />
                <path d="m10,32l5,-8l5,8z" class="l1 d15" />
                <path d="m5,24l5,8l5,-8z" class="l1 d16" />
                <path d="m5,24l5,-8l5,8z" class="l1 d17" />
                <path d="m0,16l5,8l5,-8z" class="l1 d18" />
                <path d="m0,16l5,-8l5,8z" class="l1 d19" />
                <path d="m10,16l5,-8l5,8z" class="l2 d0" />
                <path d="m15,8l5,8l5,-8z" class="l2 d3" />
                <path d="m20,16l5,-8l5,8z" class="l2 d6" />
                <path d="m20,16l5,8l5,-8z" class="l2 d9" />
                <path d="m15,24l5,-8l5,8z" class="l2 d12" />
                <path d="m10,16l5,8l5,-8z" class="l2 d15" />
            </svg>
        </div>
        <div class="leftpanel">
            <div class="leftpanel-top">
                <!-- current system name will be rendered here -->
                <div class="leftpanel-title" id="t1"></div>
                <!-- date and clock will be rendered here -->
                <div id="datetime">
                    <?php
                    if (Theme::sidebarStyle() !== 'narrow') {
                        ?>
                        <div class="leftpanel-date" id="date"></div>
                        <div class="leftpanel-clock" id="hrs"></div>
                        <?php
                    } else {
                        ?>
                        <div class="leftpanel-clock" id="hrsns"></div>
                        <?php
                    }
                    ?>
                </div>
                <!-- links to external resources -->
                <div id="ext_links" class="leftpanel-ext_links">
                    <?php
                    /**
                     * External links
                     */
                    foreach ($settings['ext_links'] as $name => $linkHref) {
                        echo '<a href="' .  $linkHref . '" target="_blank" onclick="$(\'#ext_links\').fadeToggle(\'fast\')">';
                        echo '<div class="leftpanel-ext_links_link">' . $name . '</div>';
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

            <!-- navigation links -->
            <div class="leftpanel-links">
                <div class="links">
                    <?php
                    /**
                     * set main navigation links
                     */
                    $this->navLinks();
                    ?>
                </div>
            </div>
            <?php
            /**
             *  minimize or maximize left panel
             */
            if (Theme::sidebarStyle() === 'narrow') {
                $minm .= '<a href="javascript:void(0)" onclick="minmax(\'normal\')" title="Maximize left panel">';
                $minm .= '<img class="minmax" src="/style/img/minmax.png" alt="Max">';
                $minm .= '</a>';
            } else {
                $minm .= '<a href="javascript:void(0)" onclick="minmax(\'narrow\')" title="Minimize left panel">';
                $minm .= '<img class="minmax" src="/style/img/minmax.png" alt="Min">';
                $minm .= '</a>';
            }
            ?>
            <div class="leftpanel-sessionlog">
                <?php
                if (Theme::sidebarStyle() !== 'narrow') {
                    /**
                     * session log
                     */
                    // get old session log
                    if (!$sessionlog = file_get_contents($settings['install_path'] . '/data/sessionlog.txt')) {
                        $error = error_get_last();
                        write_log('Error: ' . $error['message'], __FILE__, __LINE__);
                    }
                    ?>
                    <div class="seslog" id="seslog">
                        <textarea title="Session log" class="seslogtext" cols="40" rows="13" id="logtext" oninput="showsave()"><?= $sessionlog?></textarea>
                        <span id="seslogsuccess"><?= $minm?></span>
                        <span id="old_val" style="display: none"><?= $minm?></span>
                    </div>
                    <!-- currently playing from foobar2000/VLC // -->
                    <div id="nowplaying"></div>
                    <?php
                } else {
                    ?>
                    <div class="seslog" id="seslog">
                        <span id="seslogsuccess"><?= $minm?></span>
                        <span id="old_val" style="display: none"><?= $minm?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        /**
         * top panel
         */
        $this->topPanel();

        echo '<div class="rightpanel">';
    }

    /**
     * Main navigation links
     */
    private function navLinks()
    {
        global $settings;
        /**
         * Links for the navigation panel
         */
        $maplink = $settings['default_map'] === 'galaxy_map' ? '/GalMap' : '/Map';

        $links = [
                'ED ToolBox--log.png--true' => '/',
                'System Information--info.png--true' => '/System',
                        'Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map--grid.png--true' => $maplink,
                        'Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks--poi.png--true' => '/Bookmarks',
                        'Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations--find.png--false' => '/NearestSystems',
                        'Data Point--dataview.png--false' => '/DataPoint',
                        'Galnet News--news.png--false' => '/GalNet',
                        'Screenshot Gallery--gallery.png--false' => '/Gallery',
                        'System Log--log.png--true' => '/'
                        ];

        $i = 0;
        $count = count($links);
        foreach ($links as $name => $linkHref) {
            $names = explode('--', $name);
            $name = $names[0];
            $pic = $names[1];
            $reload = $names[2];

            $class = $this->pageTitle === $name ? 'active' : 'link';

            //
            if ($this->pageTitle === 'System Log' && $name === 'ED ToolBox') {
                $class = 'active';
            }

            $aclass = '';
            $onclick = '';
            if ($reload !== 'true') {
                $aclass = ' data-push="true"';
                $onclick = ' onclick="setActive(\'' . $i . '\', \'' . $count . '\')"';
            }

            if ($name !== 'System Log') {
                if (Theme::sidebarStyle() === 'narrow') {
                    // offset the log icon to make it appear centered
                    $styling = $pic === 'log.png' ? ' style="margin-left: 6px"' : '';

                    echo '<a' . $aclass . $onclick . ' href="' .  $linkHref . '">';
                    echo '<div id="link_' . $i . '" class="' . $class . '">';
                    echo '<img src="/style/img/' . $pic . '" alt="pic" class="icon"' . $styling . '>';
                    echo '</div>';
                    echo '</a>';
                } else {
                    echo '<a' . $aclass . $onclick . ' href="' .  $linkHref . '">';
                    echo '<div id="link_' . $i . '" class="' . $class . '">';
                    echo '<img src="/style/img/' . $pic . '" alt="pic" class="icon">' . $name;
                    echo '</div>';
                    echo '</a>';
                }
            }
            $i++;
        }
    }

    /**
     * the top panel
     */
    private function topPanel()
    {
        global $settings, $api;
        ?>
        <div class="rightpanel-top">
            <!-- elite emblem and add logs -->
            <a href="javascript:void(0)" id="toggle" title="Add log entry">
                <img src="/style/img/elite.png" alt="Add log" class="elite_emb">
            </a>

            <!-- page title and search systems & stations -->
            <div class="rightpanel-pagetitle">
                <span class="titletext">
                    <a href="javascript:void(0)" onclick="tofront('search_system');$('#system_22').focus()" title="Search for a system" id="pagetitle">
                        CMDR <?= $settings['cmdr_name']?>
                    </a>
                </span>
                <?php
                /**
                 * User ranks from FD API
                 */
                if (isset($api['commander']) && $settings['show_cmdr_status'] === 'true') {
                    $statusRanksCache = '';
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/cache/cmdr_ranks_status.html')) {
                        $statusRanksCache = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cache/cmdr_ranks_status.html');
                    }

                    echo '<div class="status_ranks" id="cmdr_click"><div id="cmdr_status">' . $statusRanksCache . '</div></div>';
                }

                /**
                 * EDSM comment link
                 */
                if (!empty($settings['edsm_api_key']) && !empty($settings['edsm_cmdr_name'])) {
                    ?>
                    <div style="display:inline-block; margin-left: 5px">
                        <a id="edsm_cmnt_pic" href="javascript:void(0)" title="Add private comment to EDSM">
                            <img src="/style/img/comment.png" class="icon24" alt="EDSM" id="edsm_click">
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
            <!-- EDSM comment -->
            <?php
            if (!empty($settings['edsm_api_key']) && !empty($settings['edsm_cmdr_name'])) {
                ?>
                <div class="edsm_comment" id="edsm_comment">
                    <form method="get" action="/" data-push="true">
                        <input type="text" id="comment2" class="textbox" name="comment" placeholder="Private EDSM comment for this system" style="width: 350px">
                        <br>
                        <div class="button" onclick="edsm_comment($('#comment2').val(), true)" style="margin-top: 6px; margin-bottom: 6px">Send comment</div>
                    </form>
                    <?php
                    if (!empty($settings['edsm_standard_comments'])) {
                        echo '<br>&nbsp;OR choose from standard set<form method="get" action="/">';
                        echo '<select class="selectbox" id="comment1" name="comment" onchange="edsm_comment($(\'#comment1\').val(), true)">';
                        echo '<option value="">Choose comment</option>';

                        foreach ($settings['edsm_standard_comments'] as $name => $comment) {
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
            <div class="right" style="display: inline-block; margin-right: 10px;  margin-top: 14px;font-size:0;height:60px;width:auto;white-space: nowrap">
                <?php
                /**
                 * show ship status
                 */
                if (isset($api['ship']) && $settings['show_ship_status'] === 'true') {
                    $shipCache = '';
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/cache/ship_status.html')) {
                        $shipCache = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cache/ship_status.html');
                    }

                    echo '<div class="status_ship" id="ship_status">' . $shipCache . '</div>';
                }
                ?>
                <!-- notifications appear here -->
                <div id="notifications" style="display:inline-block;margin-right: 6px"></div>

                <div style="display:inline-block">
                    <?php
                    /**
                     * show refresh button
                     */
                    if (isset($api['commander']) || isset($api['ship'])) {
                        ?>
                        <a id="api_refresh" href="javascript:void(0)" onclick="refresh_api()" title="Refresh API data">
                            <img class="icon24" src="/style/img/refresh_24.png" alt="Refresh" style="margin-right: 10px">
                        </a>
                        <?php
                    }
                    ?>
                    <a href="javascript:void(0)" title="About ED ToolBox" id="about_click">
                        <img class="icon24" src="/style/img/about.png" style="height: 26px; width:26px;margin-right: 10px" alt="About">
                    </a>
                    <a href="javascript:void(0)" title="Settings Panel" id="settings_click">
                        <img class="icon24" src="/style/img/settings.png" style="height: 26px; width: 26px" alt="Settings">
                    </a>
                </div>
            </div>

            <!-- notices for new releases or old data -->
            <div class="settings_panel" id="notice">
                It has been a while since you last update system and station data.<br>As a result, any data you see here may be outdated.<br><br>
                Right-click the EDTB manager icon on your system tray and select<br>"Update system and station data".
            </div>
            <div class="settings_panel" id="notice_new"></div>

            <!-- settings panel -->
            <?php
            $this->settingsPanel();
            ?>

            <!-- about ED ToolBox -->
            <?php
            /**
             * about ED ToolBox
             */
            $this->about();

            echo '</div>';
    }

    /**
     * Settings panel links
     */
    private function settingsPanel()
    {
        $settingLinks =
                         [
                            [  'href' => '/Admin',
                                    'icon' => 'settings.png',
                                    'text' => 'Customize ED ToolBox'],
                            [  'href' => '/Admin/ini_editor.php',
                                    'icon' => 'vareditor.png',
                                    'text' => 'Edit ini file'],
                            [  'href' => '/Admin/db_manager.php',
                                    'icon' => 'dataview.png',
                                    'text' => 'Database Management'],
                            [  'href' => '/Admin/SQL.php',
                                    'icon' => 'sql.png',
                                    'text' => 'Run MySQL queries'],
                            [  'href' => '/Admin/Import.php',
                                    'icon' => 'import.png',
                                    'text' => 'Import Flight Logs'],
                            [  'href' => '/Admin/API_login.php',
                                    'icon' => 'api.png',
                                    'text' => 'Connect Companion API'],
                            [ 'href' => '/Admin/Log.php',
                                   'icon' => 'log2.png',
                                   'text' => 'View Error Log']
                         ];

        echo '<div class="settings_panel" id="settings" style="width: 227px">';

        foreach ($settingLinks as $title)
        {
            ?>
            <a href="<?= $title['href']?>" title="<?= $title['text']?>">
                <div class="link" style="width:90%; text-align: left">
                    <img class="icon" src="/style/img/<?= $title['icon']?>" alt="<?= $title['icon']?>"><?= $title['text']?>
                </div>
            </a>
            <?php
        }

        echo '</div>';
    }

    /**
     * About ED ToolBox
     */
    private function about()
    {
        global $settings;
        ?>
        <div class="settings_panel" id="about">
            <table>
                <tr>
                    <td colspan="3" class="light">ED ToolBox v.<?= $settings['edtb_version']?></td>
                </tr>
                <tr>
                    <td class="info_td" colspan="3" style="padding-bottom: 5px; padding-top: 5px">
                        ED ToolBox is a companion tool for the
                        <a href="http://www.frontier.co.uk/" target="_blank">Frontier Developments</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> game
                        <a href="http://www.elitedangerous.com" target="_blank">Elite: Dangerous</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext">.<br>
                        ED ToolBox is an unofficial tool and is in no way affiliated with Frontier Developments.
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="light">Acknowledgements</td>
                </tr>
                <tr>
                    <td class="info_td" colspan="3" style="padding-bottom: 10px; padding-top: 5px">
                        This tool and its usage rely heavily on open source resources. Here's a list of (hopefully) all of them:
                    </td>
                </tr>
                <tr>
                    <td class="info_td">
                        <a href="http://eddb.io" target="_blank">EDDB.io</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (system and station data)
                    </td>
                    <td class="info_td">
                        <a href="http://markitup.jaysalvat.com/home/" target="_blank">markItUp!</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (log editor)
                    </td>
                    <td class="info_td">
                        <a href="http://sourceforge.net/projects/sql-edit-table/" target="_blank">MySQL Edit Table</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (Data Point)
                    </td>
                </tr>
                <tr>
                    <td class="info_td">
                        <a href="http://www.phpfastcache.com/" target="_blank">phpFastCache</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (page caching)
                    </td>
                    <td class="info_td">
                        <a href="https://codemirror.net/" target="_blank">CodeMirror</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (ini-file editor)
                    </td>
                    <td class="info_td">
                        <a href="http://spgm.sourceforge.net/" target="_blank">SPGM</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (screenshot gallery)
                    </td>
                </tr>
                <tr>
                    <td class="info_td">
                        <a href="https://jquery.com/" target="_blank">jQuery</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (js library)
                    </td>
                    <td class="info_td">
                        <a href="http://feed43.com/" target="_blank">Feed43</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (GalNet feed)
                    </td>
                    <td class="info_td">
                        <a href="http://www.highcharts.com/" target="_blank">Highcharts</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (neighborhood map)
                    </td>
                </tr>
                <tr>
                    <td class="info_td">
                        <a href="https://github.com/gbiobob/ED3D-Galaxy-Map" target="_blank">ED3D Galaxy Map</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (galaxy map)
                    </td>
                    <td class="info_td">
                        <a href="http://threejs.org/" target="_blank">Three.js</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (js library)
                    </td>
                    <td class="info_td">
                        <a href="http://www.imagemagick.org" target="_blank">ImageMagick®</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> (screenshot tools)
                    </td>
                </tr>
                <tr>
                    <td class="info_td" colspan="3">
                        Icons made by <a href="http://www.freepik.com" title="Freepik">Freepik</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext">,
                        <a href="http://www.flaticon.com/authors/designmodo" title="Designmodo">Designmodo</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext">, and
                        <a href="http://www.flaticon.com/authors/dave-gandy" title="Dave Gandy">Dave Gandy</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> from
                        <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext"> are licensed by
                        <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0">CC BY 3.0</a>
                        <img class="ext_icon" src="/style/img/external_link.png" alt="ext">
                    </td>
                </tr>
                <tr>
                    <td class="info_td" colspan="3">
                        ED ToolBox was created using assets and imagery from Elite Dangerous, with the permission of Frontier Developments plc,<br>
                        for non-commercial purposes. It is not endorsed by nor reflects the views or opinions of Frontier Developments and no<br>
                        employee of Frontier Developments was involved in the making of it.
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}
