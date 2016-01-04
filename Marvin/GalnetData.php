<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2015 Mauri Kujala (contact@edtb.xyz)
*
*    This program is free software; you can redistribute it and/or
*    modify it under the terms of the GNU General Public License
*    as published by the Free Software Foundation; either version 2
*    of the License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

// get latest GalNet articles
$ga_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT unixtime
														FROM edtb_common
														WHERE name = 'last_galnet_update'
														LIMIT 1");
$ga_arr = mysqli_fetch_assoc($ga_res);

$ga_last_update = $ga_arr["unixtime"] + 30*60; // 30 minutes

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
		$text = str_replace('<br /><br />', '<br />', $text);
		$text = str_replace('<br />', '.', $text);
		$text = str_replace(' – ', ', ', $text);
		$text = strip_tags($text);

		// exclude stuff
		$continue = true;
		foreach ($settings["galnet_excludes"] AS $exclude)
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
				$newfile = "" . $_SERVER["DOCUMENT_ROOT"] . "/Marvin/galnet" . $in . ".txt";
				$md5_old = md5_file($newfile);

				file_put_contents($newfile, $to_write);

				$md5_new = md5_file($newfile);

				if ($md5_old != $md5_new)
				{
					mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
																SET unixtime = UNIX_TIMESTAMP()
																WHERE name = 'last_galnet_new'
																LIMIT 1");
				}
			}
			$in++;
		}
	}
	mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
												SET unixtime = UNIX_TIMESTAMP()
												WHERE name = 'last_galnet_update'
												LIMIT 1");
}

$m_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT unixtime
													FROM edtb_common
													WHERE name = 'last_galnet_check'
													LIMIT 1");
$m_arr = mysqli_fetch_assoc($m_res);

$last_galnet_check = $m_arr["unixtime"];

$g_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT unixtime
													FROM edtb_common
													WHERE name = 'last_galnet_new'
													LIMIT 1");
$g_arr = mysqli_fetch_assoc($g_res);

$last_galnet_new = $g_arr["unixtime"];

if ($last_galnet_new < $last_galnet_check)
{
	echo "No new GalNet articles have been published since you last asked " . get_timeago($last_galnet_check) . ".";
}
else
{
	echo "New GalNet articles have been published since you last asked. Would you like me to read them to you?";
}

file_put_contents("" . $_SERVER["DOCUMENT_ROOT"] . "/Marvin/galnet_new.txt", $last_galnet_new);

mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
											SET unixtime = UNIX_TIMESTAMP()
											WHERE name = 'last_galnet_check'
											LIMIT 1");

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);