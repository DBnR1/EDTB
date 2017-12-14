<?php
/**
 * Ajax backend file to fetch Wikipedia data
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

/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = urlencode($_GET['search']);
    $text = '';

    /**
     * first try the dismbiguation
     */
    $url = 'https://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&redirects=&exsectionformat=plain&titles=' .
        strtolower($search) . '_(disambiguation)';

    if ($result = file_get_contents($url)) {
        $jsonData = json_decode($result);
        /** @var array $titles */
        $titles = $jsonData->{'query'}->{'pages'};

        $count = 0;
        foreach ($titles as $title) {
            $titleExtract = $title->{'extract'};

            preg_match_all("/\<p>.*?\<\/p>/", $titleExtract, $matches);

            /** @var array $matches */
            foreach ($matches as $match) {
                /** @var array $match */
                foreach ($match as $titleM) {
                    $titleMO = $titleM;
                    $titleM = str_replace([
                        '<p>',
                        '</p>'
                    ], '', $titleM);

                    if (strpos($titleM, ' was ') === false) {
                        $titleLink = explode(',', $titleM);
                    } else {
                        $titleLink = explode(' was ', $titleM);
                    }

                    if (strpos($titleM, ' is ') !== false) {
                        $titleLink = explode(' is ', $titleM);
                    }

                    $titleLink = explode('(', $titleLink[0]);
                    $titleFirst = str_replace(' ', '_', strip_tags(trim($titleLink[0])));

                    if (strpos($titleM, 'refer') === false) {
                        $titleRest = str_replace($titleM,
                            '<ul><li><a href="https://en.wikipedia.org/wiki/' . $titleFirst . '" target="_blank">' . $titleM .
                            '<img src="/style/img/external_link.png" class="ext_link" alt="ext" style="margin-left: 3px"></a></li></ul>',
                            $titleM);
                    } else {
                        $titleRest = str_replace($titleM, '<ul><li>' . $titleM . '</li></ul>', $titleM);
                    }

                    echo $titleRest;

                    $also = ' also';

                    break;
                }
            }

            if ($count === 0 && strpos($titleRest, 'refer') === false) {
                $text = '<div class="searchtitle">' . $_GET['search'] . ' may' . $also . ' refer to:</div>';
            }

            if ($count === 0 && strpos($titleRest, 'include') !== false) {
                $text = '';
            }

            echo $text;
            echo '<ul>';

            preg_match_all("/\<li>.*?\\n/", $titleExtract, $matches);

            foreach ($matches as $match) {
                $i = 0;
                foreach ($match as $titleM) {
                    $titleM = str_replace([
                        '<li>',
                        '</li>'
                    ], '', $titleM);
                    $titleLink = explode(',', $titleM);
                    $titleLink = preg_split('/\(\d/', $titleLink[0]);
                    $titleFirst = str_replace(' ', '_', strip_tags(trim($titleLink[0])));

                    $titleRest = str_replace($titleM,
                        '<li><a href="https://en.wikipedia.org/wiki/' . $titleFirst . '" target="_blank">' . $titleM .
                        '</a><img src="/style/img/external_link.png" class="ext_link" alt="ext" style="margin-left: 3px"></li>',
                        $titleM);

                    echo $titleRest;

                    if ($i === 15) {
                        break 2;
                    }

                    $i++;
                }
            }
            $count++;
        }
    } else {
        write_log('Error: Failed to contact Wikipedia', __FILE__, __LINE__);
    }

    /**
     * if that yields no results, try the direct approach
     */
    if ($i === 0) {
        $url = 'https://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exsectionformat=plain&titles=' .
            strtolower($search);

        if ($result = file_get_contents($url)) {
            $jsonData = json_decode($result);
            $titles = $jsonData->{'query'}->{'pages'};

            foreach ($titles as $title) {
                $titleExtract = $title->{'extract'};

                preg_match_all("/\<li>.*?\<\/li>/", $titleExtract, $matches);

                foreach ($matches as $match) {
                    foreach ($match as $titleM) {
                        $titleM = str_replace([
                            '<li>',
                            '</li>'
                        ], '', $titleM);
                        $titleLink = explode(',', $titleM);
                        $titleLink = preg_split('/\(\d/', $titleLink[0]);
                        $titleFirst = str_replace(' ', '_', strip_tags(trim($titleLink[0])));

                        $titleRest = str_replace($titleM,
                            '<li><a href="https://en.wikipedia.org/wiki/' . $titleFirst . '" target="_blank">' . $titleM .
                            '</a><img src="/style/img/external_link.png" class="ext_link" alt="ext" style="margin-left: 3px"></li>',
                            $titleM);

                        echo $titleRest;

                        if ($i === 15) {
                            break 2;
                        }

                        $i++;
                    }
                }
            }
        } else {
            write_log('Error: Failed to contact Wikipedia', __FILE__, __LINE__);
        }
    }

    /**
     * if nothing's still found, give up
     */
    if ($i === 0) {
        echo '<li>Nothing found...</li>';
    }

    echo '</ul>';
} else {
    echo 'No search string set.';
}
