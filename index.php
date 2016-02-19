<?php
/**
 * Front page
 *
 * No description
 *
 * @package EDTB\Main
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

/** @var pagetitle */
$pagetitle = "ED ToolBox";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

if (isset($_GET["import_done"]))
{
	?>
	<div class="entries">
		<div class="entries_inner">
			<script type="text/javascript">
				update_map();
			</script>
			<?php
			echo notice("Succesfully added " . number_format($_GET["num"]) . " visited systems to the database.<br /><br />You may now continue using ED ToolBox.", "Logs imported");
			?>
		</div>
	</div>
	<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
	exit;
}
?>
<div class="entries">
	<div class="entries_inner" id="scrollable">
	</div>
</div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
