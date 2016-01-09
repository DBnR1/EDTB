<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2016 Mauri Kujala (contact@edtb.xyz)
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

$pagetitle = "Screenshot Gallery";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
?>
<!-- stuff for spgm pic gallery -->
<script type="text/javascript" src="/source/spgm/spgm.js"></script>
<script type="text/javascript" src="/source/spgm/contrib/overlib410/overlib.js"></script>
<link rel="stylesheet" href="/source/spgm/css/style.css" />
<link rel="Stylesheet" href="/source/spgm/flavors/default/spgm_style.css" />
<script>
YUI().use('pjax', function (Y) {
	var pjax2 = new Y.Pjax({container: '.entries', linkSelector: '.td-galitem-title', contentSelector: '.entries_inner'});
	pjax2.on('navigate', function (e) {
		$(".se-pre-con").show();
	});
	pjax2.on(['error', 'load'], function (e) {
		$(".se-pre-con").fadeOut("slow");
	});
});
</script>

<div class="entries">
	<div class="entries_inner">
		<?php
		if (is_dir($settings['old_screendir']) && $settings['old_screendir'] != "C:\Users")
		{
			?>
			<table id="wrapper">
				<tr>
					<td id="center">
						<?php require_once("source/spgm/spgm.php"); ?>
					</td>
				</tr>
			</table>
			<?php
		}
		else
		{
			echo notice('Your screenshot directory is empty or gallery is disabled.<br />Set the variable "old_screendir" in the <a href="/admin/ini_editor.php">Customize ED ToolBox</a> page to enable gallery.');
		}
		?>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
