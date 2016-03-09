<?php
/**
 * Import old netLog files
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
$pagetitle = "Import Log Files";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

$batch_limit = 104857600; // 100 MB
$batches_left = isset($_GET["batches_left"]) ? $_GET["batches_left"] : "";

$imported_logs_file = $_SERVER["DOCUMENT_ROOT"] . "/cache/imported_logs.txt";

echo '<div class="entries"><div class="entries_inner">';

if (is_dir($settings["log_dir"])) {
    $logfiles2 = glob($settings["log_dir"] . "/netLog*");
    $logfiles = array();
    $total_size = 0;

    $imported_files = array();
    if (file_exists($imported_logs_file)) {
        $imported_files = file($imported_logs_file, FILE_IGNORE_NEW_LINES);
    }
    //print_r($imported_files);
    foreach ($logfiles2 as $file) {
        //if (!array_search($file, $imported_files))
        if (!in_array($file, $imported_files)) {
            $size = filesize($file);
            $total_size += $size;

            if ($total_size < $batch_limit) {
                $logfiles[] = $file;
            }
            $total_logfiles[] = $file;
        }
    }
    $num = count($total_logfiles);

    if ($num == 0) {
        $text = 'No unimported netLog files located.';
        echo notice($text, "Import Logs");
    } elseif ($total_size < $batch_limit && $batches_left == "") {
        $text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . '.';
        $text .= 'Do you want to import them?<br /><br /><a href="/admin/Import.php?import">Import logs</a>';
        echo notice($text, "Import Logs");
    } else {
        $batches = ceil($total_size / $batch_limit);
        $numss = $_GET["num"];
        if ($batches_left == "1") {
            $text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . '.<br />';
            $text .= 'Due to the size of the logs, they need to be imported in batches of ' . FileSizeConvert($batch_limit) . '.<br />';
            $text .= 'Do you want to import them?<br /><br />';
            $text .= '<div id="text" style="text-align:center">';
            $text .= '<a href="import.php?import&num=' . $numss . '" onclick="$(\'#loadin\').show();$(\'#text\').hide()">Import logs, last batch</a></div>';
            $text .= '<div id="loadin" style="text-align:center;display:none"><img src="/style/img/loading.gif" alt="Loading..." /></div>';
            echo notice($text, "Import Logs");
        } elseif ($batches_left == "") {
            $text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . '.<br />';
            $text .= 'Due to the size of the logs, they need to be imported in batches of ' . FileSizeConvert($batch_limit) . '.<br />';
            $text .= 'Do you want to import them?<br /><br /><div id="text" style="text-align:center">';
            $text .= '<a href="import.php?import&batches_left=' . $batches . '&num=' . $numss . '" onclick="$(\'#loadin\').show();$(\'#text\').hide()">';
            $text .= 'Import logs, patch 1 of ' . $batches . '</a></div>';
            $text .= '<div id="loadin" style="text-align:center;display:none"><img src="/style/img/loading.gif" alt="Loading..." /></div>';
            echo notice($text, "Import Logs");
        } else {
            $text = $num . ' netLog files totaling ' . FileSizeConvert($total_size) . ' remaining.<br />';
            $text .= 'Do you want to import the next batch?<br /><br />';
            $text .= '<div id="text" style="text-align:center;">';
            $text .= '<a href="import.php?import&batches_left=' . $batches_left . '&num=' . $numss . '" onclick="$(\'#loadin\').show();$(\'#text\').hide()">';
            $text .= 'Import logs, ' . $batches . ' batches left</a></div>';
            $text .= '<div id="loadin" style="text-align:center;display:none"><img src="/style/img/loading.gif" alt="Loading..." /></div>';
            echo notice($text, "Import Logs");
        }
    }

    if (isset($_GET["import"])) {
        $i = 0;
        $current_sys = "";
        foreach ($logfiles as $newest_file) {
            if (!in_array($newest_file, $imported_files)) {
                // read first line to get date
                $fline = fgets(fopen($newest_file, 'r'));

                $sub = substr($fline, 0, 8);
                $sub = explode("-", $sub);

                $year = "20" . $sub[0];
                $month = $sub[1];
                $day = $sub[2];

                // read file to an array
                $filr = file($newest_file);
                $lines = $filr;

                foreach ($lines as $line_num => $line) {
                    $pos = strrpos($line, "System:");
                    if ($pos !== false) {
                        preg_match_all("/\((.*?)\) B/", $line, $matches);
                        $cssystemname = $matches[1][0];

                        preg_match_all("/\{(.*?)\} System:/", $line, $matches2);
                        $visited_time = $matches2[1][0];
                        $visited_on = $year . "-" . $month . "-" . $day . " " . $visited_time;

                        if ($current_sys != $cssystemname) {
                            // check if the visit is already improted
                            $res = mysqli_query($GLOBALS["___mysqli_ston"], "   SELECT id
                                                                                FROM user_visited_systems
                                                                                WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $cssystemname) . "'
                                                                                AND visit = '" . $visited_on . "'
                                                                                LIMIT 1")
                                                                                or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

                            $exists = mysqli_num_rows($res);

                            if ($exists == 0) {
                                mysqli_query($GLOBALS["___mysqli_ston"], "  INSERT INTO user_visited_systems (system_name, visit)
                                                                            VALUES (
                                                                            '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $cssystemname) . "',
                                                                            '" . $visited_on . "')")
                                                                            or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

                                if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) >= 1) {
                                    $i++;
                                }
                            }
                        }
                        $current_sys = $cssystemname;
                    }
                }

                /**
                 *  Write filename to .txt so we won't process this file again
                 */
                $ffd = fopen($imported_logs_file, "a");

                fwrite($ffd, $newest_file . PHP_EOL);
                fclose($ffd);
            }
        }

        if (!isset($_GET["batches_left"])) {
            $num_tot = $_GET["num"] + $i ;
            $nums = isset($_GET["num"]) ? $num_tot : $i;
            if (!headers_sent()) {
                exit(header('Location: /index.php?import_done&num=' . $nums));
            } else {
                ?>
                <script>
                    location.replace("/index.php?import_done&num=<?php echo $nums;?>");
                </script>
                <?php
                exit();
            }
        } else {
            $nums = $_GET["num"] + $i;
            $batches_left = $_GET["batches_left"] - 1;
            if (!headers_sent()) {
                exit(header('Location: /admin/import.php?batches_left=' . $batches_left . '&num=' . $nums));
            } else {
                ?>
                <script>
                    location.replace("/admin/import.php?batches_left=<?php echo $batches_left;?>&num=<?php echo $nums;?>");
                </script>
                <?php
                exit();
            }
        }
    }
} else {
    echo 'Could not locate ' . $settings["log_dir"] . ', check your settings.';
}
echo '</div></div>';

require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
