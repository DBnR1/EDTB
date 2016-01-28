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
 * Settings
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

if (isset($_GET["do"]))
{
	require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

	$data = json_decode($_REQUEST["input"], true);

	foreach ($data as $var => $value)
	{
		mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE user_settings
													SET value = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $value) . "'
													WHERE variable = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $var) . "'
													LIMIT 1")
													or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}

	((is_null($___mysqli_res = mysqli_close($link))) ? false : $___mysqli_res);

	exit;
}

$pagetitle = "Settings";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

$cat_id = isset($_GET["cat_id"]) ? $_GET["cat_id"] : "2";

?>
<div class="notify_success" id="notify" style="display:none">Settings edited</div>
<div class="entries">
	<div class="entries_inner">
	<h2>
		<img src="/style/img/settings.png" alt="Settings" style="width:20px;height:20px;margin-right:6px" />Settings
		<span style="margin-left:20px;font-size:11px">[&nbsp;<a href="/admin/ini_editor.php">Edit .ini file</a>&nbsp;]&nbsp;[&nbsp;<a href="/admin/sql.php">Execute SQL</a>&nbsp;]</span>
	</h2>
	<hr>
	<?php
	echo'<ul class="pagination">';

	$cat_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id, name
															FROM edtb_settings_categories
															ORDER BY weight")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$i = 0;
	while ($cat_arr = mysqli_fetch_assoc($cat_res))
	{
		$id = $cat_arr["id"];
		$name = $cat_arr["name"];

		if ($id == $cat_id)
		{
			$active = " class='actives'";
			$current_category = $name;
		}
		else
		{
			$active = "";
		}

		if (($i % 5) == 0)
		{
			echo "</ul><br /><ul class='pagination' style='margin-top:-25px;'>";
		}

		echo '<li' . $active . '><a data-replace="true" data-target=".rightpanel" class="mtelink" href="settings.php?cat_id=' . $id . '">' . $name . '</a></li>';
		$i++;
	}

	echo '</ul>';

	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
														user_settings.id,
														edtb_settings_info.name,
														user_settings.variable,
														edtb_settings_info.type,
														edtb_settings_info.info,
														user_settings.value
														FROM user_settings
														LEFT JOIN edtb_settings_info ON edtb_settings_info.variable = user_settings.variable
														WHERE edtb_settings_info.category_id = '" . $cat_id . "'
														ORDER BY edtb_settings_info.weight")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	?>
	<form method="post" id="settings_form" action="settings.php">
		<table style="max-width:720px;margin-bottom:15px">
			<tr>
				<td class="heading">Edit <?php echo $current_category?></td>
			</tr>
			<?php
			$i = 0;
			while ($arr = mysqli_fetch_assoc($res))
			{
				$name = $arr["name"];
				$type = $arr["type"];
				$variable = $arr["variable"];
				$info = !empty($arr["info"]) ? '<div class="settings_info">' . $arr["info"] . '</div>' : "";
				$value = $arr["value"];

				if ($i % 2)
				{
					$tdclass = "dark";
				}
				else
				{
					$tdclass = "light";
				}

				if ($type == "numeric")
				{
					?>
					<tr>
						<td class="<?php echo $tdclass?>">
							<div>
								<?php echo $info?>
								<input class="textbox" type="text" name="<?php echo $variable?>" placeholder="<?php echo $name?>" value="<?php echo $value?>" style="width:100px" />
							</div>
						</td>
					</tr>
					<?php
				}
				elseif ($type == "textbox" || $type == "csl")
				{
					?>
					<tr>
						<td class="<?php echo $tdclass?>">
							<div>
								<?php echo $info?>
								<input class="textbox" type="text" name="<?php echo $variable?>" placeholder="<?php echo $name?>" value="<?php echo $value?>" style="width:520px" />
							</div>
						</td>
					</tr>
					<?php
				}
				elseif ($type == "array")
				{
					?>
					<tr>
						<td class="<?php echo $tdclass?>">
							<div>
								<?php echo $info?>
								<textarea class="textarea" name="<?php echo $variable?>" placeholder="<?php echo $name?>" style="width:520px;height:220px" /><?php echo $value?></textarea>
							</div>
						</td>
					</tr>
					<?php
				}
				elseif ($type == "tf")
				{
					if ($value == "true")
					{
						$t_sel = " selected='selected'";
						$f_sel = "";
					}
					elseif ($value == "false")
					{
						$t_sel = "";
						$f_sel = " selected='selected'";
					}
					?>
					<tr>
						<td class="<?php echo $tdclass?>">
							<div>
								<span class="settings_info"><?php echo $name?></span><br />
								<select class="selectbox" name="<?php echo $variable?>" style="width:100px">
									<option value="true"<?php echo $t_sel?>>Yes</option>
									<option value="false"<?php echo $f_sel?>>No</option>
								</select>
							</div>
						</td>
					</tr>
					<?php
				}
				elseif (substr($type, 0, 4) == "enum")
				{
					$values = str_replace("enum::", "", $type);

					$values = explode("&&", $values);
					?>
					<tr>
						<td class="<?php echo $tdclass?>">
							<div>
								<span class="settings_info"><?php echo $name?></span><br />
								<select class="selectbox" name="<?php echo $variable?>" style="width:auto">
									<?php
									foreach ($values as $val)
									{
										$parts = explode(">>", $val);

										$val_value = $parts[0];
										$val_name = $parts[1];

										$selected = $value == $val_value ? ' selected="selected"' : '';

										echo '<option value="' . $val_value . '"' . $selected . '>' . $val_name . '</option>';

									}
									?>
								</select>
							</div>
						</td>
					</tr>
					<?php
				}
				$i++;
			}
			$lclass = "dark";
			if ($tdclass == "dark")
			{
				$lclass = "light";
			}
			?>
				<tr>
					<td class="<?php echo $lclass?>">
						<a href="#" data-replace="true" data-target=".entries"><div class="button" onclick="update_data('settings_form', '/admin/settings.php?do', true);$('#notify').fadeToggle('fast')">Submit changes</div></a>
						<span id="delete_bm"></span>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
