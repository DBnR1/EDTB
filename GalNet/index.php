<?php
/**
 * Galnet news
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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
*/

/** @require phpfastcache */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/Vendor/phpfastcache/phpfastcache.php';

/** @require Theme class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Theme.php';

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->page_title = 'Galnet News';

/**
 * display the header
 */
$header->display_header();

/**
 * get cached content
 */
$html = __c('files')->get('galnet');

if ($html === null) {
    ob_start();
    ?>
    <div class="entries">
        <div class="entries_inner">
            <h2><img class="icon24" src="/style/img/galnet.png" alt="GalNet" style="margin-right:6px"/>Latest Galnet News</h2>
            <hr>
            <?php
            $rss = new DOMDocument();
            $rss->load(GALNET_FEED);
            $feed = [];

            /** @var DOMDocument $node */
            foreach ($rss->getElementsByTagName('item') as $node) {
                $item = [
                    'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                    'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                    'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                    'content' => $node->getElementsByTagName('encoded')->item(0)->nodeValue

                ];
                $feed[] = $item;
            }

            $i = 0;
            foreach ($feed as $data) {
                $title = $data['title'];
                $link = $data['link'];
                $text = $data['content'];

                // exclude stuff
                $continue = true;

                foreach ($settings['galnet_excludes'] as $exclude) {
                    $find = $exclude;
                    $pos = strpos($title, $find);

                    if ($pos !== false) {
                        $continue = false;
                        break 1;
                    }
                }

                if ($continue !== false) {
                    ?>
                    <h3>
                        <a href="javascript:void(0)" onclick="$('#<?= $i ?>').fadeToggle()">
                            <img class="icon" src="/style/img/plus.png" alt="expand" style="padding-bottom:3px"/><?= $title ?>
                        </a>
                    </h3>
                    <div id="<?= $i ?>" style="display:none;padding-left:22px;max-width:800px">
                        <?= $text ?>
                        <br><br>
                        <span style="margin-bottom:15px">
                            <a href="<?= $link ?>" target="_blank">
                                Read on news.galnet.fr
                            </a><img class="ext_icon" src="/style/img/external_link.png" style="margin-bottom:3px" alt="ext"/>
                        </span>
                    </div>
                    <?php
                    $i++;
                }
            }
            ?>
        </div>
    </div>
    <?php
    $html = ob_get_contents();
    // Save to Cache for 30 minutes
    __c('files')->set('galnet', $html, 1800);

    /**
     * initiate page footer
     */
    $footer = new Footer();

    /**
     * display the footer
     */
    $footer->display_footer();

    exit;
}
echo $html;

/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->display_footer();
