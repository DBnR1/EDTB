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
 * PHP
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
*/

$pagetitle = "System Information";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
?>
<div class="entries">
	<div class="entries_inner" id="system_page" style="overflow:hidden !important">
		<h2 id="si_name"></h2><hr>
		<table class="system_table">
			<tr>
				<td style="vertical-align:top">
					<!-- STATIONS -->
					<div class="systeminfo_stations" id="si_stations"></div>
				</td>
				<td style="width:1px;vertical-align:top">
					<!-- SYSTEM INFO -->
					<div class="systeminfo_system" id="si_detailed"></div>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
