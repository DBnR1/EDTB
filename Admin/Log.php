<?php
/**
 * Log viewer
 *
 * No description
 *
 * @package EDTB\Admin
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

/** @require Theme class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Theme.php';

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->pageTitle = 'Error Log';

/**
 * display the header
 */
$header->displayHeader();
?>
<div class="entries">
    <div class="entries_inner">
        <h2>
            <img class="icon24" src="/style/img/log2_24.png" alt="Log">Error log
        </h2>
        <hr>
        <?php
        /**
         * read logfile
         */
        $logfile = $_SERVER['DOCUMENT_ROOT'] . '/edtb_log.txt';
        $lines = file($logfile);
        ?>
        <table>
            <thead>
                <tr>
                    <td class="heading">&nbsp;</td>
                    <td class="heading"><strong>Time</strong></td>
                    <td class="heading"><strong>File</strong></td>
                    <td class="heading"><strong>Line</strong></td>
                    <td class="heading"><strong>Message</strong></td>
                </tr>
            </thead>
            <tbody>
                <?php
                /**
                 * reverse the array and output data
                 */
                if (!empty($lines)) {
                    foreach (array_reverse($lines) as $lineNum => $line) {
                        /**
                         * only show first 600 lines
                         */
                        if ($lineNum <= 599) {
                            // Regular Expression filter for links
                            $regExUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

                            /**
                             * split data and define variables
                             */
                            $data = explode(']', $line);
                            $time = str_replace('[', '', $data[0]);

                            $parts = explode(' on line ', $data[1]);
                            $errorLine = $parts[1];
                            $errorLine = empty($errorLine) ? 'n/a' : $errorLine;

                            $file = str_replace('[', '', $parts[0]);
                            $file = str_replace($settings['install_path'] . "\\EDTB\\", '', $file);
                            $file = empty($file) ? 'n/a' : $file;

                            $error = array_slice($data, 2);
                            $error = implode('', $error);

                            if (preg_match($regExUrl, $error, $url)) {
                                $error = preg_replace($regExUrl, '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>', $error);
                            } else {
                                $error = strip_tags($error);
                            }

                            $tdclass = $lineNum % 2 ? 'dark' : 'light';
                            ?>
                            <tr>
                                <td class="<?= $tdclass?>" style="padding: 10px; width:1%;text-align: center">
                                    <a class="copy" href="javascript:void(0);" title="Copy to clipboard" data-clipboard-text="<?= $line?>">
                                        <img class="icon" src="/style/img/clipboard.png" alt="Copy" style="margin-left: 5px">
                                    </a>
                                </td>
                                <td class="<?= $tdclass?>" style="width:1%;text-align: center">
                                    <?= $time?>
                                </td>
                                <td class="<?= $tdclass?>">
                                    <?= $file?>
                                </td>
                                <td class="<?= $tdclass?>">
                                    <?= $errorLine?>
                                </td>
                                <td class="<?= $tdclass?>">
                                    <?= strip_tags($error, '<a>')?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                } else {
                    ?>
                    <tr>
                        <td class="dark" colspan="5">
                            The log file is empty.
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    var clipboard = new Clipboard('.copy');
</script>
<?php
/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->displayFooter();

