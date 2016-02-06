<?php
/**
 * Ajax backend file to send private comments to EDSM
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

if (isset($_GET["comment"]) && isset($_GET["system_name"]))
{
	$comment = $_GET["comment"];
	$system = $_GET["system_name"];
}
else
{
	write_log("Error: EDSM comment or system not set", __FILE__, __LINE__);
	exit;
}

/**
 * submit to EDSM
 */

if (!empty($settings["edsm_api_key"]) && !empty($settings["edsm_cmdr_name"]))
{
	if (!$cmnt_string = file_get_contents("http://www.edsm.net/api-logs-v1/set-comment?commanderName=" . urlencode($settings["edsm_cmdr_name"]) . "&apiKey=" . $settings["edsm_api_key"] . "&systemName=" . urlencode($system) . "&comment=" . urlencode($comment)))
	{
		$error = error_get_last();
		write_log("Error: " . $error['message'], __FILE__, __LINE__);
	}
}
else
{
	write_log("Error: EDSM API key or commander name not set", __FILE__, __LINE__);
	exit;
}
