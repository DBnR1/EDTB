<?php
/**
 * GalNet data for Marvin
 *
 * No description
 *
 * @package EDTB\Marvin
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

/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require config */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';

/**
 * if data is older than 30 minutes, update
 */

$gaLastUpdate = edtbCommon('last_galnet_update', 'unixtime') + 30 * 60; // 30 minutes

if ($gaLastUpdate < time()) {
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

    $in = 1;
    foreach ($feed as $dataga) {
        $gatitle = $dataga['title'];
        $gaTitle = explode(' - ', $gatitle);
        $gaTitle = $gaTitle[0];

        $text = $dataga['content'];
        $text = str_replace('<br>', PHP_EOL, $text);
        $text = str_replace(' – ', ', ', $text);
        $text = trim(strip_tags($text));
        $text = html_entity_decode($text);

        // exclude stuff
        $continue = true;
        foreach ($settings['galnet_excludes'] as $exclude) {
            $find = $exclude;
            $pos = strpos($gaTitle, $find);

            if ($pos !== false) {
                $continue = false;
                break 1;
            }
        }

        if ($continue !== false) {
            /**
             * write articles into txt files for VoiceAttack
             */
            $toWrite = $gaTitle . "\n\r" . $text;

            if ($in <= $settings['galnet_articles']) {
                /**
                 * write x of the latest articles to .txt files
                 */
                $newfile = $_SERVER['DOCUMENT_ROOT'] . '/Marvin/galnet' . $in . '.txt';

                $oldFile = '';
                if (file_exists($newfile)) {
                    $oldFile = file_get_contents($newfile);
                }

                if (!file_put_contents($newfile, $toWrite)) {
                    $error = error_get_last();
                    write_log('Error: ' . $error['message'], __FILE__, __LINE__);
                }

                /**
                 * compare to the latest to see if new articles have been posted since last check
                 */
                $newFile = '-1';
                if (file_exists($newfile)) {
                    $newFile = file_get_contents($newfile);
                }

                if ($newFile != $oldFile) {
                    edtbCommon('last_galnet_new', 'unixtime', true, time());
                }
            }
            $in++;
        }
    }

    /**
     * update last_update time
     */
    edtbCommon('last_galnet_update', 'unixtime', true, time());
}

/**
 * fetch last check time and last new article time
 */
$lastGalnetCheck = edtbCommon('last_galnet_check', 'unixtime');
$lastGalnetNew = edtbCommon('last_galnet_new', 'unixtime');

if ($lastGalnetNew < $lastGalnetCheck) {
    echo 'No new GalNet articles have been published since you last asked ' . get_timeago($lastGalnetCheck, false) . '.';
} else {
    echo 'New GalNet articles have been published since you last asked. Would you like me to read them to you?';
}

/**
 *  update last check time
 */
edtbCommon('last_galnet_check', 'unixtime', true, time());
