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

if (isset($_GET["do"]))
{
	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

	$data = json_decode($_REQUEST["input"], true);

	$bm_system_id = $data["bm_system_id"];
	$bm_system_name = $data["bm_system_name"];
	$bm_catid = $data["bm_catid"];
	$bm_entry = $data["bm_text"];
	$bm_id = $data["bm_edit_id"];

	if ($bm_id != "")
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_bookmarks SET
													comment = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_entry) . "',
													system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_system_name) . "',
													category_id = '" . $bm_catid . "'
													WHERE id = '" . $bm_id . "' LIMIT 1");
	}
	else if (isset($_GET["deleteid"]))
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	DELETE FROM user_bookmarks WHERE id = '" . $_GET["deleteid"] . "' LIMIT 1");
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_bookmarks (system_id, system_name, comment, category_id, added_on)
													VALUES
													('" . $bm_system_id . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_system_name) . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_entry) . "',
													'" . $bm_catid . "',
													UNIX_TIMESTAMP())");
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit();
}
?>
<div class="input" id="addbm" style="text-align:center;">
	<div style="position:absolute;width:510px;margin-top:20px;margin-left:400px;">Add/edit bookmark<br />
		<form method="post" id="bm_form">
			<input type="hidden" name="bm_edit_id" id="bm_edit_id">
			<input type="hidden" name="bm_system_id" id="bm_system_id">
			<div>
				<input class="textbox" type="text" name="bm_system_name" placeholder="System name" id="bm_system_name" style="width:469px;" onkeyup="showResult(this.value, '3', 'no', 'no', 'no', 'yes')" autofocus="autofocus" />
				<div class="suggestions" id="suggestions_3" style="margin-left:15px;"></div>
			</div>
			<input class="textbox" type="text" name="bm_text" id="bm_text" placeholder="Comment (optional)" style="width:326px;" />
			<select class="selectbox" name="bm_catid" id="bm_catid" style="width:140px;">
				<option value="0">Category (optional)</option>
				<?php
				$cat_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM user_bm_categories");

				while ($cat_arr = mysqli_fetch_assoc($cat_res))
				{
					echo '<option value="' . $cat_arr["id"] . '">' . $cat_arr["name"] . '</option>';
				}
				?>
			</select>
		</form>
		<button onclick="update_data('bm_form', '/add/bookmark.php?do', true);tofront('null', true);">Add Bookmark</button>
		<span id="delete_bm"></span>
	</div>
</div>