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

if (isset($_GET["search"]) && $_GET["search"] != "")
{
	$search = addslashes($_GET["search"]);

	echo '<div class="searchtitle">' . $_GET["search"] . ' may refer to:</div><ul>';

	/*
	*	first try the dismbiguation
	*/
	$url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&redirects=&exsectionformat=plain&titles=" . strtolower($search) . "_(disambiguation)";

	$result = file_get_contents($url);
	$json_data = json_decode($result, true);
	$titles = $json_data["query"]["pages"];

	foreach ($titles as $title)
	{
		$title_extract = $title["extract"];

		preg_match_all("/\<li>.*?\<\/li>/", $title_extract, $matches);

		foreach ($matches as $match)
		{
			$i = 0;
			foreach ($match as $title_m)
			{
				$title_m = str_replace("<li>", "", $title_m);
				$title_m = str_replace("</li>", "", $title_m);
				$title_link = explode(',', $title_m);
				$title_link = explode('(', $title_link[0]);
				$title_first = str_replace(" ", "_", strip_tags(trim($title_link[0])));

				$title_rest = str_replace($title_m, '<li><a href="https://en.wikipedia.org/wiki/' . $title_first . '" target="_BLANK">' . $title_m . '</a>&nbsp;<img src="/style/img/external_link.png" alt="ext" style="vertical-align:middle;" /></li>', $title_m);

				echo $title_rest;

				if ($i == 15)
					break 2;

				$i++;
			}
		}
	}

	/*
	*	if that yealds no results, try the direct approach
	*/

	if ($i == 0)
	{
		$url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&redirects=&exsectionformat=plain&titles=" . strtolower($search) . "";

		$result = file_get_contents($url);
		$json_data = json_decode($result, true);
		$titles = $json_data["query"]["pages"];

		foreach ($titles as $title)
		{
			$title_extract = $title["extract"];

			preg_match_all("/\<li>.*?\<\/li>/", $title_extract, $matches);

			foreach ($matches as $match)
			{
				foreach ($match as $title_m)
				{
					$title_m = str_replace("<li>", "", $title_m);
					$title_m = str_replace("</li>", "", $title_m);
					$title_link = explode(',', $title_m);
					$title_link = explode('(', $title_link[0]);
					$title_first = str_replace(" ", "_", strip_tags(trim($title_link[0])));

					$title_rest = str_replace($title_m, '<li><a href="https://en.wikipedia.org/wiki/' . $title_first . '" target="_BLANK">' . $title_m . '</a>&nbsp;<img src="/style/img/external_link.png" alt="ext" style="vertical-align:middle;" /></li>', $title_m);

					echo $title_rest;

					if ($i == 15)
						break 2;

					$i++;
				}
			}
		}
	}

	/*
	*	if nothing's still found, give up
	*/

	if ($i == 0)
	{
		echo '<li>Nothing found...</li>';
	}

	echo '</ul>';
}
else
{
	echo 'No search string set.';
}

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);