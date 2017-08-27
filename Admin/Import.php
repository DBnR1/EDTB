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
$header->pageTitle = 'Import Log Files';

/**
 * display the header
 */
$header->displayHeader();

/** @var int $batchLimit */
$batchLimit = 104857600; // 100 MB
$batchesLeft = $_GET['batches_left'] ?? '';

$importedLogsFile = $_SERVER['DOCUMENT_ROOT'] . '/cache/imported_logs.txt';

echo '<div class="entries"><div class="entries_inner">';

if (is_dir($settings['log_dir'])) {
    $logfiles2 = glob($settings['log_dir'] . '/netLog*');
    $logfiles = [];
    $totalSize = 0;

    /**
     * read already imported files to an array
     */
    $importedFiles = [];
    if (file_exists($importedLogsFile)) {
        $importedFiles = file($importedLogsFile, FILE_IGNORE_NEW_LINES);
    }

    $totalLogfiles = [];
    foreach ($logfiles2 as $file) {
        if (!in_array($file, $importedFiles, true)) {
            $size = filesize($file);
            $totalSize += $size;

            if ($totalSize < $batchLimit) {
                $logfiles[] = $file;
            }
            $totalLogfiles[] = $file;
        }
    }
    $num = count($totalLogfiles);

    if ($num === 0) {
        $text = 'No unimported netLog files located.';
        echo notice($text, 'Import Logs');
    } elseif ($totalSize < $batchLimit && $batchesLeft === '') {
        $text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($totalSize) . '. ';
        $text .= 'Do you want to import them?<br><br><a href="/Admin/Import.php?import">Import logs</a>';
        echo notice($text, 'Import Logs');
    } else {
        $batches = ceil($totalSize / $batchLimit);
        $numss = $_GET['num'];
        if ($batchesLeft === '1') {
            $text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($totalSize) . '.<br>';
            $text .= 'Due to the size of the logs, they need to be imported in batches of ' . FileSizeConvert($batchLimit) . '.<br>';
            $text .= 'Do you want to import them?<br><br>';
            $text .= '<div id="text" style="text-align: center">';
            $text .= '<a href="Import.php?import&num=' . $numss . '" onclick="$(\'#loadin\').show();$(\'#text\').hide()">Import logs, last batch</a></div>';
            $text .= '<div id="loadin" style="text-align: center; display: none"><img src="/style/img/loading.gif" alt="Loading..."></div>';
            echo notice($text, 'Import Logs');
        } elseif ($batchesLeft === '') {
            $text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($totalSize) . '.<br>';
            $text .= 'Due to the size of the logs, they need to be imported in batches of ' . FileSizeConvert($batchLimit) . '.<br>';
            $text .= 'Do you want to import them?<br><br><div id="text" style="text-align: center">';
            $text .= '<a href="import.php?import&batches_left=' . $batches . '&num=' . $numss . '" onclick="$(\'#loadin\').show();$(\'#text\').hide()">';
            $text .= 'Import logs, patch 1 of ' . $batches . '</a></div>';
            $text .= '<div id="loadin" style="text-align: center; display: none"><img src="/style/img/loading.gif" alt="Loading..."></div>';
            echo notice($text, 'Import Logs');
        } else {
            $text = $num . ' netLog files totaling ' . FileSizeConvert($totalSize) . ' remaining.<br>';
            $text .= 'Do you want to import the next batch?<br><br>';
            $text .= '<div id="text" style="text-align: center">';
            $text .= '<a href="import.php?import&batches_left=' . $batchesLeft . '&num=' . $numss . '" onclick="$(\'#loadin\').show();$(\'#text\').hide()">';
            $text .= 'Import logs, ' . $batches . ' batches left</a></div>';
            $text .= '<div id="loadin" style="text-align: center; display: none"><img src="/style/img/loading.gif" alt="Loading..."></div>';
            echo notice($text, 'Import Logs');
        }
    }

    if (isset($_GET['import'])) {
        $i = 0;
        $currentSys = '';
        foreach ($logfiles as $newestFile) {
            if (!in_array($newestFile, $importedFiles, true)) {
                // read file to an array
                $filr = file($newestFile);
                $lines = $filr;

                // read first line to get date
                $fline = fgets(fopen($newestFile, 'rb'));

                if ($fline[0] === '=') {
                    $sub = substr($filr[2], 0, 10);
                    $sub = explode('-', $sub);
                    $year = $sub[0];
                } else {
                    $sub = substr($fline, 0, 8);
                    $sub = explode('-', $sub);
                    $year = '20' . $sub[0];
                }

                $month = $sub[1];
                $day = $sub[2];

                /**
                 * Prepare statement an bind
                 */
                $stmt = $mysqli->prepare('INSERT INTO user_visited_systems (system_name, visit) VALUES (?, ?)');
                $stmt->bind_param('ss', $escSys, $visitedOn);

                foreach ($lines as $lineNum => $line) {
                    $pos = strrpos($line, 'System:');
                    if ($pos !== false) {
                        /**
                         * Regular expression filter to find the system name
                         */
                        preg_match_all("/\System:\"(.*?)\"/", $line, $matches);
                        $cssystemname = $matches[1][0];

                        if (empty($cssystemname)) {
                            preg_match_all("/\((.*?)\) B/", $line, $matches2);
                            $cssystemname = $matches2[1][0];
                        }

                        if ($currentSys !== $cssystemname) {
                            preg_match_all("/\{(\d\d)\:(\d\d)\:(\d\d)(.*?)\} System:/", $line, $matches3);

                            if (is_array($matches3)) {
                                $visitedTime = $matches3[1][0] . ':' . $matches3[2][0] . ':' . $matches3[3][0];
                            } else {
                                preg_match_all("/\{(.*?)\} System:/", $line, $matches2);
                                $visitedTime = $matches2[1][0];
                            }

                            $visitedOn = $year . '-' . $month . '-' . $day . ' ' . $visitedTime;

                            $escSys = $mysqli->real_escape_string($cssystemname);

                            /**
                             * check if the visit is already improted
                             */
                            $query = "  SELECT id
                                        FROM user_visited_systems
                                        WHERE system_name = '$escSys'
                                        AND visit = '$visitedOn'
                                        LIMIT 1";

                            $result = $mysqli->query($query);

                            $exists = $result->num_rows;
                            $result->close();

                            if ($exists === 0) {
                                $stmt->execute();

                                if ($mysqli->affected_rows >= 1) {
                                    $i++;
                                }
                            }
                        }
                        $currentSys = $cssystemname;
                    }
                }

                $stmt->close();

                /**
                 *  Write filename to .txt so we won't process this file again
                 */
                $ffd = fopen($importedLogsFile, 'ab');

                fwrite($ffd, $newestFile . PHP_EOL);
                fclose($ffd);
            }
        }

        if (!isset($_GET['batches_left'])) {
            $numTot = $_GET['num'] + $i ;
            $nums = isset($_GET['num']) ? $numTot : $i;
            if (!headers_sent()) {
                exit(header('Location: /index.php?import_done&num=' . $nums));
            }

            ?>
            <script>
                location.replace("/index.php?import_done&num=<?= $nums?>");
            </script>
        <?php
            exit();
        }

        $nums = $_GET['num'] + $i;
        $batchesLeft = $_GET['batches_left'] - 1;
        if (!headers_sent()) {
            exit(header('Location: /Admin/import.php?batches_left=' . $batchesLeft . '&num=' . $nums));
        }

        ?>
        <script>
            location.replace("/Admin/import.php?batches_left=<?= $batchesLeft?>&num=<?= $nums?>");
        </script>
    <?php
        exit();
    }
} else {
    echo 'Could not locate ' . $settings['log_dir'] . ', check your settings.';
}
echo '</div></div>';

/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->displayFooter();
