<?php
/*
*  ED ToolBox, a companion web app for the video game Elite Dangerous
*  (C) 1984 - 2016 Frontier Developments Plc.
*  ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/**
 * Import old netLog files
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$pagetitle = "Import Log Files";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

$batch_limit = 104857600; // 100 MB
$batches_left = isset($_GET["batches_left"]) ? $_GET["batches_left"] : "";

echo '<div class="entries"><div class="entries_inner">';

if (is_dir($settings["log_dir"]))
{
	$logfiles2 = glob("" . $settings["log_dir"] . "/netLog*");
	$logfiles = array();
	$total_size = 0;

	foreach ($logfiles2 as $file)
	{
		$size = filesize($file);
        $total_size += $size;

		if ($total_size < $batch_limit)
		{
			$logfiles[] = $file;
		}
	}
	$num = count($logfiles2);

	if ($total_size < $batch_limit && $batches_left == "")
	{
		$text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . '. Do you want to import them?<br /><br /><a href="import.php?import">Import logs</a>';
		echo notice($text, "Import Logs");
	}
	else
	{
		$batches = ceil($total_size / $batch_limit);
		$numss = $_GET["num"];
		if ($batches_left == "1")
		{
			$text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . '. Due to the size of the logs, they need to be imported in batches of ' . FileSizeConvert($batch_limit) . '.<br />Do you want to import them?<br /><br /><div id="text" style="text-align:center;"><a href="import.php?import&num=' . $numss . '" onclick="document.getElementById(\'loadin\').style.display=\'block\';document.getElementById(\'text\').style.display=\'none\';">Import logs, last batch</a></div><div id="loadin" style="text-align:center;display:none;"><img src="/style/img/loading.gif" alt="Loading" \></div>';
			echo notice($text, "Import Logs");
		}
		elseif ($batches_left == "")
		{
			$text = 'Located ' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . '. Due to the size of the logs, they need to be imported in batches of ' . FileSizeConvert($batch_limit) . '.<br />Do you want to import them?<br /><br /><div id="text" style="text-align:center;"><a href="import.php?import&batches_left=' . $batches . '&num=' . $numss . '"  onclick="document.getElementById(\'loadin\').style.display=\'block\';document.getElementById(\'text\').style.display=\'none\';">Import logs, patch 1 of ' . $batches . '</a></div><div id="loadin" style="text-align:center;display:none;"><img src="/style/img/loading.gif" alt="Loading" \></div>';
			echo notice($text, "Import Logs");
		}
		else
		{
			$text = '' . $num . ' netLog files totaling ' . FileSizeConvert($total_size) . ' remaining.<br />Do you want to import the next batch?<br /><br /><div id="text" style="text-align:center;"><a href="import.php?import&batches_left=' . $batches_left . '&num=' . $numss . '" onclick="document.getElementById(\'loadin\').style.display=\'block\';document.getElementById(\'text\').style.display=\'none\';">Import logs, ' . $batches . ' batches left</a></div><div id="loadin" style="text-align:center;display:none;"><img src="/style/img/loading.gif" alt="Loading" \></div>';
			echo notice($text, "Import Logs");
		}
	}

	if (isset($_GET["import"]))
	{
		$i = 0;
		$current_sys = "";
		foreach ($logfiles as $newest_file)
		{
			// read first line to get date
			$fline = fgets(fopen($newest_file, 'r'));

			$sub = substr($fline, 0, 8);
			$sub = explode("-", $sub);

			$year = "20". $sub[0] . "";
			$month = $sub[1];
			$day = $sub[2];

			// read file to an array
			$filr = file("" . $newest_file . "");
			$lines = $filr;

			foreach ($lines as $line_num => $line)
			{
				$pos = strrpos($line, "System:");
				if ($pos !== false)
				{
					preg_match_all("/\((.*?)\) B/", $line, $matches);
					$cssystemname = $matches[1][0];

					preg_match_all("/\{(.*?)\} System:/", $line, $matches2);
					$visited_time = $matches2[1][0];
					$visited_on = "" . $year . "-" . $month . "-" . $day . " " . $visited_time . "";

					if ($current_sys != $cssystemname)
					{
						// check if the visit is already improted
						$exists = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id
																							FROM user_visited_systems
																							WHERE system_name = '" . $cssystemname . "'
																							AND visit = '" . $visited_on . "'
																							LIMIT 1"));

						if ($exists == 0)
						{
							mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_visited_systems (system_name, visit)
																		VALUES (
																		'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $cssystemname) . "',
																		'" . $visited_on . "')")
																		or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

							if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) >= 1)
							{
								$i++;
							}
						}
					}
					$current_sys = $cssystemname;
				}
			}
			$temp_filename = str_replace("netLog", "imported_netLog", $newest_file);

			rename($newest_file, $temp_filename);
		}

		if (!isset($_GET["batches_left"]))
		{
			$temp_logfiles = glob("" . $settings["log_dir"] . "/imported_netLog*");

			foreach ($temp_logfiles as $temp_logfile)
			{
				$new_file = str_replace("imported_", "", $temp_logfile);
				rename($temp_logfile, $new_file);
			}

			$num_tot = $_GET["num"]+ $i ;
			$nums = isset($_GET["num"]) ? $num_tot : $i;
			header('Location: /index.php?import_done&num=' . $nums . '');
		}
		else
		{
			$nums = $_GET["num"] + $i;
			$batches_left = $_GET["batches_left"] - 1;
			header('Location: /admin/import.php?batches_left=' . $batches_left . '&num=' . $nums . '');
		}
	}
}
else
{
	echo 'Could not locate ' . $settings["log_dir"] . ', check your settings.';
}
echo '</div></div>';

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
