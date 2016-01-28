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
 * Run SQL statements
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

$notify = "";
$pagetitle = "SQL";

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

if (isset($_POST["code"]))
{
	$code = $_POST["code"];

	$blacklist = array(
				"DROP",
				"DELETE",
				"ROUTINE",
				"EXECUTE",
				"DATABASE",
				"SERVER",
				"EMPTY",
				"TRUNCATE",
				"TRIGGER");

	$continue = true;

	$pattern = '/"(.*?)"/';
	$haystack = preg_replace($pattern, "", $code);
	$pattern = "/'(.*?)'/";
	$haystack = preg_replace($pattern, "", $haystack);
	$pattern = "/`(.*?)`/";
	$haystack = preg_replace($pattern, "", $haystack);

	//write_log($haystack);

	foreach ($blacklist as $find)
	{
		if (strripos($haystack, $find))
		{
			$continue = false;
			$notify = '<div class="notify_deleted">Query contains a forbidden command.</div>';
			break;
		}
	}

	if ($continue !== false)
	{
		$queries = explode(">>BREAK<<", $code);

		foreach ($queries as $query)
		{
			if (!mysqli_query($GLOBALS["___mysqli_ston"], "" . $query . ""))
			{
				$error = mysqli_error($GLOBALS["___mysqli_ston"]);
				$notify = '<div class="notify_deleted">Execution failed:<br />' . $error . '</div>';
			}
			else
			{
				$error = mysqli_info($GLOBALS["___mysqli_ston"]);
				$notify = '<div class="notify_success">Query succesfully executed.<br />' . $error . '</div>';
			}
		}
	}
}
?>
<!-- codemirror -->
<link type="text/css" rel="stylesheet" href="/source/Vendor/codemirror/lib/codemirror.css">
<script type="text/javascript" src="/source/Vendor/codemirror/lib/codemirror.js"></script>

<script type="text/javascript" src="/source/Vendor/codemirror/addon/placeholder.js"></script>
<script type="text/javascript" src="/source/Vendor/codemirror/mode/sql/sql.js"></script>

<div class="entries">
	<div class="entries_inner" style="margin-bottom:20px">
	<h2>
		<img src="/style/img/settings.png" alt="Settings" style="width:20px;height:20px;margin-right:6px" />Execute SQL<span style="margin-left:20px;font-size:11px">[&nbsp;<a href="/admin/ini_editor.php">Edit .ini file</a>&nbsp;]&nbsp;[&nbsp;<a href="/admin/settings.php">Settings</a>&nbsp;]</span>
	</h2>
	<hr>
	<?php echo $notify;?>
	<div style="padding:5px;margin-bottom:10px">
	You can use this form to perform sql statements. Certain commands, such as<br />
	<strong>DELETE</strong>, <strong>TRUNCATE</strong> and <strong>DROP</strong> are not available here.<br /><br />

	For more complete database management, use the included db manager (<a href="/admin/">Adminer</a>)<br />
	or a database manager of your choice.
	</div>
		<form method="post" action="sql.php">
			<textarea id="codes" name="code">
<?php
if (isset($_POST["code"]))
{
	echo $code;
}
else
{
	echo '/*
* 	     SQL statement goes here...
*	 To do multiple statements, use
*				  >>BREAK<<
*	    to separate statements
*/
';
}
?>
</textarea>
			<input type="submit" class="button" value="Submit" />
		</form>
		<script type="text/javascript">
			var editor = CodeMirror.fromTextArea(document.getElementById("codes"),
			{
				lineNumbers: true,
				mode: "text/x-mysql"
			});
		</script>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
