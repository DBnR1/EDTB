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
 * FD API data for Marvin
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
$info = "";

/*
*	commander data
*/

if (isset($_GET["cmdr"]))
{
	$search = $_GET["cmdr"];

	if (isset($api["commander"]))
	{
		if ($search == "balance")
		{
			$info = number_format($api["commander"]["credits"]);
		}
		elseif ($search == "rank" && isset($_GET["of"]))
		{
			$info = get_rank($_GET["of"], $api["commander"]["rank"][$_GET["of"]], false);
		}
	}
}

/*
*	ship data
*/

if (isset($_GET["ship"]))
{
	$search = $_GET["ship"];

	if (isset($api["ship"]))
	{
		if ($search == "name")
		{
			$info = $api["ship"]["name"];
		}
		elseif ($search == "health")
		{
			$info = number_format($api["ship"]["health"]["hull"]/10000, 1);
		}
		elseif ($search == "fuel")
		{
			$info = number_format($api["ship"]["fuel"]["main"]["level"]/$api["ship"]["fuel"]["main"]["capacity"]*100, 1);
		}
		elseif ($search == "cargo_capacity")
		{
			$info = $api["ship"]["cargo"]["capacity"];
		}
		elseif ($search == "cargo_used")
		{
			$info = $api["ship"]["cargo"]["qty"];
		}
		elseif ($search == "value")
		{
			$info = number_format($api["ship"]["value"]["total"]);
		}
	}
}

echo $info;

((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

exit;
