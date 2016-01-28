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
 * Ajax backend file to add or edit bookmarks
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
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
													WHERE id = '" . $bm_id . "' LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	elseif (isset($_GET["deleteid"]))
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	DELETE FROM user_bookmarks
													WHERE id = '" . $_GET["deleteid"] . "'
													LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
	else
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_bookmarks (system_id, system_name, comment, category_id, added_on)
													VALUES
													('" . $bm_system_id . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_system_name) . "',
													'" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_entry) . "',
													'" . $bm_catid . "',
													UNIX_TIMESTAMP())") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}
?>
<div class="input" id="addBm">
	<form method="post" id="bm_form" action="bookmark.php">
		<div class="input-inner">
			<div class="suggestions" id="suggestions_3" style="margin-top:79px;margin-left:14px"></div>
			<table>
				<tr>
					<td class="heading" colspan="2">Add/edit bookmark
						<span class="right">
							<a href="javascript:void(0)" onclick="tofront('addBm')" title="Close form">
								<img src="/style/img/close.png" alt="X" style="width:16px;height:16px" />
							</a>
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="dark">
					<input type="hidden" name="bm_edit_id" id="bm_edit_id">
					<input type="hidden" name="bm_system_id" id="bm_system_id">
					<div>
						<input class="textbox" type="text" name="bm_system_name" placeholder="System name" id="bm_system_name" style="width:469px" oninput="showResult(this.value, '3', 'no', 'no', 'no', 'yes')" />
					</div>
					</td>
				</tr>
				<tr>
					<td class="dark">
						<input class="textbox" type="text" name="bm_text" id="bm_text" placeholder="Comment (optional)" style="width:326px" />
					</td>
					<td class="dark">
					<select class="selectbox" name="bm_catid" id="bm_catid" style="width:140px">
						<option value="0">Category (optional)</option>
						<?php
						$cat_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, name FROM user_bm_categories ORDER BY name");

						while ($cat_arr = mysqli_fetch_assoc($cat_res))
						{
							echo '<option value="' . $cat_arr["id"] . '">' . $cat_arr["name"] . '</option>';
						}
						?>
					</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="dark">
						<a href="/poi.php" data-replace="true" data-target=".entries"><div class="button" onclick="update_data('bm_form', '/add/bookmark.php?do', true);tofront('null', true)">Add Bookmark</div></a>
						<span id="delete_bm"></span>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>
