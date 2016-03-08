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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @var string pagetitle */
$pagetitle = "Error Log";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
?>
<div class="entries">
    <div class="entries_inner">
        <h2>
            <img class="icon24" src="/style/img/log2_24.png" alt="Log" />Error log
        </h2>
        <hr>
        <?php
        // read logfile
        $logfile = $_SERVER["DOCUMENT_ROOT"] . "/edtb_log.txt";
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
            // reverse array and output data
            if (!empty($lines)) {
                foreach (array_reverse($lines) as $line_num => $line) {
                    // only show first 600 lines
                    if ($line_num <= 599) {
                        // Regular Expression filter for links
                        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

                        // split data and define variables
                        $data = explode("]", $line);
                        $time = str_replace("[", "", $data[0]);

                        $parts = explode(" on line ", $data[1]);
                        $error_line = $parts[1];
                        $error_line = empty($error_line) ? "n/a" : $error_line;

                        $file = str_replace("[", "", $parts[0]);
                        $file = str_replace($settings["install_path"] . "\EDTB\\", "", $file);
                        $file = empty($file) ? "n/a" : $file;

                        $error = array_slice($data, 2);
                        $error = implode("", $error);

                        if (preg_match($reg_exUrl, $error, $url)) {
                            $error = preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_blank'>" . $url[0] . "</a>", $error);
                        }

                        $tdclass = $line_num % 2 ? "dark" : "light";
                        ?>
                        <tr>
                            <td class="<?php echo $tdclass?>" style="padding:10px;width:1%;text-align:center">
                                <a class="copy" href="javascript:void(0);" title="Copy to clipboard" data-clipboard-text="<?php echo $line?>">
                                    <img class="icon" src="/style/img/clipboard.png" alt="Copy" style="margin-left:5px" />
                                </a>
                            </td>
                            <td class="<?php echo $tdclass?>" style="width:1%;text-align:center">
                                <?php echo $time?>
                            </td>
                            <td class="<?php echo $tdclass?>">
                                <?php echo $file?>
                            </td>
                            <td class="<?php echo $tdclass?>">
                                <?php echo $error_line?>
                            </td>
                            <td class="<?php echo $tdclass?>">
                                <?php echo strip_tags($error)?>
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
