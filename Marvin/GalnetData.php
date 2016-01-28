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
 * GalNet data for Marvin
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

/*
*	if data is older than 30 minutes, update
*/

$ga_last_update = edtb_common("last_galnet_update", "unixtime") + 30*60; // 30 minutes

if ($ga_last_update < time())
{
	$xml2 = xml2array($galnet_feed);

	$in = 1;
	foreach ($xml2["rss"]["channel"]["item"] as $dataga)
	{
		$gatitle = $dataga["title"];
		$ga_title = explode(" - ", $gatitle);
		$ga_title = $ga_title[0];

		$text = $dataga["description"];
		$text = str_replace('<p><sub><i>-- Delivered by <a href="http://feed43.com/">Feed43</a> service</i></sub></p>', "", $text);
		$text = str_replace('<br />', PHP_EOL, $text);
		$text = str_replace(' – ', ', ', $text);
		$text = trim(strip_tags($text));

		// exclude stuff
		$continue = true;
		foreach ($settings["galnet_excludes"] as $exclude)
		{
			$find = $exclude;
			$pos = strpos($ga_title, $find);

			if ($pos !== false)
			{
				$continue = false;
				break 1;
			}
		}

		if ($continue !== false)
		{
			// write articles into txt files for VoiceAttack
			$to_write = "" . $ga_title . "\n\r" . $text . "";

			if ($in <= 4)
			{
				/*
				*	write four of the latest articles to .txt files
				*/

				$newfile = "" . $_SERVER["DOCUMENT_ROOT"] . "/Marvin/galnet" . $in . ".txt";

				$old_file = "";
				if (file_exists($newfile))
				{
					$old_file = file_get_contents($newfile);
				}

				if (!file_put_contents($newfile, $to_write))
				{
					$error = error_get_last();
					write_log("Error: " . $error['message'] . "", __FILE__, __LINE__);
				}

				/*
				*	compare to the latest to see if new articles have been posted since last check
				*/

				$new_file = "-1";
				if (file_exists($newfile))
				{
					$new_file = file_get_contents($newfile);
				}

				if ($new_file != $old_file)
				{
					mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
																SET unixtime = UNIX_TIMESTAMP()
																WHERE name = 'last_galnet_new'
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
				}
			}
			$in++;
		}
	}

	/*
	*	update last_update time
	*/

	mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
												SET unixtime = UNIX_TIMESTAMP()
												WHERE name = 'last_galnet_update'
												LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
}

/*
*	fetch last check time and last new article time
*/

$last_galnet_check = edtb_common("last_galnet_check", "unixtime");
$last_galnet_new = edtb_common("last_galnet_new", "unixtime");

if ($last_galnet_new < $last_galnet_check)
{
	echo "No new GalNet articles have been published since you last asked " . get_timeago($last_galnet_check, false) . ".";
}
else
{
	echo "New GalNet articles have been published since you last asked. Would you like me to read them to you?";
}

/*
*	 update last check time
*/

mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
											SET unixtime = UNIX_TIMESTAMP()
											WHERE name = 'last_galnet_check'
											LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);
