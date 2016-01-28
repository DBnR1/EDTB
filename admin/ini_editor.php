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
 * Variable editor
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$notify = "";
$pagetitle = "Variable Editor";

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

if (isset($_POST["code"]))
{
	$code = $_POST["code"];
	if (file_put_contents($ini_file, $code))
	{
		$notify = "<div class='notify_success'>Settings succesfully edited.</div>";
	}
	else
	{
		$notify = "<div class='notify_deleted'>Edit unsuccesfull.</div>";
	}
}

$ini = file_get_contents($ini_file);
?>
<!-- codemirror -->
<link type="text/css" rel="stylesheet" href="/source/Vendor/codemirror/lib/codemirror.css">
<script type="text/javascript" src="/source/Vendor/codemirror/lib/codemirror.js"></script>
<script type="text/javascript" src="/source/Vendor/codemirror/mode/properties/properties.js"></script>
<?php echo $notify;?>
<div class="entries">
	<div class="entries_inner" style="margin-bottom:20px">
	<h2>
		<img src="/style/img/settings.png" alt="Settings" style="width:20px;height:20px;margin-right:6px" />Edit .ini file
		<span style="margin-left:20px;font-size:11px">[&nbsp;<a href="/admin/settings.php">Settings</a>&nbsp;]&nbsp;[&nbsp;<a href="/admin/sql.php">Execute SQL</a>&nbsp;]</span>
	</h2>
	<hr>
		<form method="post" action="ini_editor.php">
			<textarea id="codes" name="code"><?php echo $ini?></textarea>
			<input type="submit" class="button" value="Submit changes" />
		</form>
		<script type="text/javascript">
			var editor = CodeMirror.fromTextArea(document.getElementById("codes"),
			{
				lineNumbers: true,
				mode: "text/x-ini"
			});
		</script>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
