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

$pagetitle = "Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

$addtolink = "";
$addtolink2 = "";
$system = isset($_GET["system"]) ? $_GET["system"] : "";
$text = "Nearest";

//  determine what coordinates to use
if ($system != "")
{
	$sys_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, id, x, y, z
															FROM edtb_systems
															WHERE id = '" . $system . "'
															LIMIT 1");
	$sys_arr = mysqli_fetch_assoc($sys_res);

	$sys_name = $sys_arr["name"];
	$sys_id = $sys_arr["id"];

	$usex = $sys_arr["x"];
	$usey = $sys_arr["y"];
	$usez = $sys_arr["z"];

	$text .= " (to <a href='system.php?system_id=" . $sys_id . "'>" . $sys_name . "</a>) ";
	$addtolink .= "&system=" . $system . "";
	$addtolink2 .= "&system=" . $system . "";
}
else if (is_numeric($coordx) && $system == "")
{
	$usex = $coordx;
	$usey = $coordy;
	$usez = $coordz;
}
else
{
	// get last known coordinates
	$last_coords = last_known_system();

	$usex = $last_coords["x"];
	$usey = $last_coords["y"];
	$usez = $last_coords["z"];


	//$stations = true;
	$is_unknown = " *";
}

$ship_name = isset($_GET["ship_name"]) ? $_GET["ship_name"] : "";
$facility = isset($_GET["facility"]) ? $_GET["facility"] : "";
$only = isset($_GET["allegiance"]) ? $_GET["allegiance"] : "";
$system_allegiance = isset($_GET["system_allegiance"]) ? $_GET["system_allegiance"] : "";
$group_id = isset($_GET["group_id"]) ? $_GET["group_id"] : "";
$power = isset($_GET["power"]) ? $_GET["power"] : "";
$pad = isset($_GET["pad"]) ? $_GET["pad"] : "";
$stations = true;
$hidden_inputs = "";

$add3 = "";
if ($power != "")
{
    $add3 = " AND edtb_systems.power = '" . $power . "'";
	$stations = false;
	$text .= " " . $power . " systems";
	$hidden_inputs .= '<input type="hidden" name="power" value="' . $power . '" />';
	$addtolink .= "&power=" . urlencode($power) . "";
	$addtolink2 .= "&power=" . urlencode($power) . "";
}

$add = "";
if ($only != "")
{
	if ($only != "all")
	{
		$add = " AND edtb_stations.allegiance = '" . $only . "'";
	}
	else
	{
		$add = " AND edtb_stations.allegiance = 'None'";
	}
	$stations = true;
	if ($only != "all" && $only != "Independent")
	{
		$text .= " systems with " . $only . " controlled stations";
	}
	else if ($only == "Independent")
	{
		$text .= " systems with Independent stations";
	}
	else
	{
		$text .= " systems with non-allied stations";
	}
	$hidden_inputs .= '<input type="hidden" name="allegiance" value="' . $only . '" />';
	$addtolink .= "&allegiance=" . $only . "";
}

$add2 = "";
if ($system_allegiance != "")
{
    $add2 = " AND edtb_systems.allegiance = '" . $system_allegiance . "'";
	$stations = false;
	$text .= " " . str_replace('None', 'Non-allied', $system_allegiance) . " systems";
	$hidden_inputs .= '<input type="hidden" name="system_allegiance" value="' . $system_allegiance . '" />';
	$addtolink .= "&system_allegiance=" . $system_allegiance . "";
}

// if we're searching facilities
if ($facility != "" && $facility != "0")
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
														edtb_stations.ls_from_star, edtb_stations.max_landing_pad_size,
														edtb_stations.is_planetary, edtb_stations.type,
														edtb_systems.allegiance AS allegiance,
														edtb_systems.name AS system,
														edtb_systems.x AS coordx,
														edtb_systems.y AS coordy,
														edtb_systems.z AS coordz,
														edtb_systems.population,
														edtb_systems.government,
														edtb_systems.security,
														edtb_systems.economy
														FROM edtb_stations
														LEFT JOIN edtb_systems on edtb_stations.system_id = edtb_systems.id
														WHERE edtb_systems.x != ''
														AND edtb_stations." . $facility . " = '1'" . $add . "" . $add2 . "" . $add3 . "" . $add4 . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
														LIMIT 10");
	$stations = true;
	$f_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name
														FROM edtb_facilities
														WHERE code = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $facility) . "'
														LIMIT 1");
	$f_arr = mysqli_fetch_assoc($f_res);
	$f_name = $f_arr["name"];

	if (preg_match('/([aeiouAEIOU])/', $f_name{0}))
	{
		$article = "an";
	}
	else
	{
		$article = "a";
	}
	$text .= " stations with " . $article . " " . $f_name . " facility";
	$hidden_inputs .= '<input type="hidden" name="facility" value="' . $facility . '" />';
	$addtolink .= "&facility=" . $facility . "";
	$addtolink2 .= "&facility=" . $facility . "";
}

$add4 = "";
if ($pad != "")
{
    $add4 = " AND edtb_stations.max_landing_pad_size = '" . $pad . "'";
	$stations = true;
	$padsize = $pad == "L" ? "Large" : "Medium";
	$text .= "  stationswith " . $padsize . " sized landing pads";
	$hidden_inputs .= '<input type="hidden" name="pad" value="' . $pad . '" />';
	$addtolink .= "&pad=" . $pad . "";
	$addtolink2 .= "&pad=" . $pad . "";
}

// nearest stations
if ($stations !== false)
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
														edtb_stations.ls_from_star, edtb_stations.max_landing_pad_size,
														edtb_stations.is_planetary, edtb_stations.type,
														edtb_systems.allegiance AS allegiance,
														edtb_systems.name AS system,
														edtb_systems.x AS coordx,
														edtb_systems.y AS coordy,
														edtb_systems.z AS coordz,
														edtb_systems.population,
														edtb_systems.government,
														edtb_systems.security,
														edtb_systems.economy
														FROM edtb_stations
														LEFT JOIN edtb_systems on edtb_stations.system_id = edtb_systems.id
														WHERE edtb_systems.x != ''" . $add . "" . $add2 . "" . $add3 . "" . $add4 . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2)),
														edtb_stations.ls_from_star
														LIMIT 10");
}
else
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_systems.name AS system, edtb_systems.allegiance,
														edtb_systems.id AS system_id,
														edtb_systems.x AS coordx,
														edtb_systems.y AS coordy,
														edtb_systems.z AS coordz,
														edtb_systems.population,
														edtb_systems.government,
														edtb_systems.security,
														edtb_systems.economy
														FROM edtb_systems
														WHERE edtb_systems.x != ''" . $add . "" . $add2 . "" . $add3 . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
														LIMIT 10");
}

// if we're searching modules
if ($group_id != "" && $group_id != "0")
{
	$class = isset($_GET["class"]) ? $_GET["class"] : "";
	$rating = isset($_GET["rating"]) ? $_GET["rating"] : "";

	$class_add = "";
	if ($class != "" && $class != "0")
	{
		$class_add = " AND class = '" . $_GET["class"] . "'";
	}

	$rating_add = "";
	if ($rating != "" && $rating != "0")
	{
		$rating_add = " AND rating = '" . $_GET["rating"] . "'";
	}

	$gnres = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT group_name FROM edtb_modules WHERE group_id = '" . $group_id . "' LIMIT 1");
	$gnarr = mysqli_fetch_assoc($gnres);

	$group_name = $gnarr["group_name"];
	$group_name = substr($group_name, -1) == "s" ? $group_name : "" . $group_name . "s";

	if ($rating != "" && $rating != "0")
	{
		$ratings = " " . $_GET["rating"] . " rated ";
		$hidden_inputs .= '<input type="hidden" name="rating" value="' . $rating . '" />';
	}
	if ($class != "" && $class != "0")
	{
		$classes = " class " . $_GET["class"] . " ";
		$hidden_inputs .= '<input type="hidden" name="class" value="' . $class . '" />';
	}
	$text .= " stations selling " . $ratings . "" . $classes . "" . $group_name . "";
	$hidden_inputs .= '<input type="hidden" name="group_id" value="' . $group_id . '" />';
	$addtolink .= "&group_id=" . $group_id . "";
	$addtolink2 .= "&group_id=" . $group_id . "";

	$module_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																FROM edtb_modules
																WHERE group_id = '" . $group_id . "'" . $class_add . "" . $rating_add . "
																LIMIT 1");
	$mod_count = mysqli_num_rows($module_res);

	if ($mod_count > 0)
	{
		$module_arr = mysqli_fetch_assoc($module_res);
		$modules_id = $module_arr["id"];

		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
															edtb_stations.ls_from_star, edtb_stations.max_landing_pad_size,
															edtb_stations.is_planetary, edtb_stations.type,
															edtb_systems.allegiance AS allegiance,
															edtb_systems.name AS system,
															edtb_systems.x AS coordx,
															edtb_systems.y AS coordy,
															edtb_systems.z AS coordz,
															edtb_systems.population,
															edtb_systems.government,
															edtb_systems.security,
															edtb_systems.economy
															FROM edtb_stations
															LEFT JOIN edtb_systems on edtb_stations.system_id = edtb_systems.id
															WHERE edtb_systems.x != ''
															AND edtb_stations.selling_modules LIKE '-%" . $modules_id . "%-'" . $add . "" . $add2 . "" . $add3 . "" . $add4 . "
															ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
															LIMIT 10");
		$stations = true;
	}
	else
	{
		$res = "";
	}
}

// if we're searching ships
if ($ship_name != "" && $ship_name != "0")
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
														edtb_stations.ls_from_star, edtb_stations.max_landing_pad_size,
														edtb_stations.is_planetary, edtb_stations.type,
														edtb_systems.allegiance AS allegiance,
														edtb_systems.name AS system,
														edtb_systems.x AS coordx,
														edtb_systems.y AS coordy,
														edtb_systems.z AS coordz,
														edtb_systems.population,
														edtb_systems.government,
														edtb_systems.security,
														edtb_systems.economy
														FROM edtb_stations
														LEFT JOIN edtb_systems on edtb_stations.system_id = edtb_systems.id
														WHERE edtb_systems.x != ''
														AND edtb_stations.selling_ships LIKE '%\'" . $ship_name . "\'%'" . $add . "" . $add2 . "" . $add3 . "" . $add4 . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
														LIMIT 10");
	$stations = true;
	$text .= " stations selling the " . $ship_name . "";
	$hidden_inputs .= '<input type="hidden" name="ship_name" value="' . $ship_name . '" />';
	$addtolink .= "&ship_name=" . $ship_name . "";
	$addtolink2 .= "&ship_name=" . $ship_name . "";
}

if ($text == "Nearest")
	$text = "Nearest stations";

/*
*	replace all but the first occurance of "key" with "value"
*/

$replaces = array(	"stations" => "",
					"selling" => "and",
					"with" => "and"
					);

foreach ($replaces as $replace => $with)
{
	$pos = strpos($text, $replace);
	if ($pos !== false)
	{
		$text = substr($text, 0, $pos + 1) . str_replace($replace, $with, substr($text, $pos + 1));
	}
}

/*
*	replace all but the last occurance of "systems"
*/

$pos = substr_count($text, 'systems');
if ($pos > 1)
{
	$text = preg_replace('/\.(\s|$)/', 'systems$1', $text);
	$text = substr_replace($text, '', strpos($text,'systems'), 7);
}

$count = mysqli_num_rows($res);
?>
<script>
YUI().use('pjax', function (Y) {
	var pjax2 = new Y.Pjax({container: '.entries_inner', linkSelector: '.nslink', contentSelector: '#nscontent'});
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
		<table id="nscontent" style="margin-top:16px;width:100%">
			<tr>
				<td class="systeminfo_station_name" style="width:25%;white-space:nowrap;">Nearest stations</td>
				<td class="systeminfo_station_name" style="width:25%;white-space:nowrap;">Nearest Allegiances</td>
				<td class="systeminfo_station_name" style="width:25%;white-space:nowrap;">Nearest Powers</td>
				<td class="systeminfo_station_name" style="width:25%;white-space:nowrap;">Selling Modules</td>
				<td class="systeminfo_station_name" style="width:25%;white-space:nowrap;">Ships & Facilities</td>
			</tr>
			<tr>
				<!-- station allegiances -->
				<td class="station_info_price_info_t" style="vertical-align:top;width:25%;white-space:nowrap;">
					<a class="nslink" href="/nearest_systems.php?allegiance=Empire<?php echo $addtolink2;?>" title="Empire"><img src="style/img/empire.png" alt="All" style="vertical-align:middle;" /></a>&nbsp;
					<a class="nslink" href="/nearest_systems.php?allegiance=Alliance<?php echo $addtolink2;?>" title="Alliance"><img src="style/img/alliance.png" alt="All" style="vertical-align:middle;" /></a>&nbsp;
					<a class="nslink" href="/nearest_systems.php?allegiance=Federation<?php echo $addtolink2;?>" title="Federation"><img src="style/img/federation.png" alt="All" style="vertical-align:middle;" /></a>&nbsp;
					<a class="nslink" href="/nearest_systems.php?allegiance=Independent<?php echo $addtolink2;?>" title="Independent"><img src="style/img/system.png" alt="All" style="vertical-align:middle;" /></a>
					<!-- search systems and stations-->
					<div style="text-align:left;">
						<div style="width:180px;margin-top:35px;">
							<input class="textbox" type="text" name="system_name" placeholder="System (optional)" id="system_21" style="width:180px;" oninput="showResult(this.value, '11', 'no', 'no', 'yes')" autofocus="autofocus" /><br />
							<input class="textbox" type="text" name="station_name" placeholder="Station (optional)" id="station_21" style="width:180px;" oninput="showResult(this.value, '12', 'no', 'yes', 'yes')" />
							<div class="suggestions" id="suggestions_11" style="margin-left:0px;margin-top:-36px;min-width:168px;"></div>
							<div class="suggestions" id="suggestions_12" style="margin-left:0px;min-width:168px;"></div>
						</div>
					</div>
				</td>
				<!-- allegiances -->
				<td class="station_info_price_info_t" style="vertical-align:top;width:25%;white-space:nowrap;">
					<a class="nslink" href="/nearest_systems.php?system_allegiance=Empire<?php echo $addtolink2;?>" title="Empire"><img src="style/img/empire.png" alt="All" style="vertical-align:middle;" /></a>&nbsp;
					<a class="nslink" href="/nearest_systems.php?system_allegiance=Alliance<?php echo $addtolink2;?>" title="Alliance"><img src="style/img/alliance.png" alt="All" style="vertical-align:middle;" /></a>&nbsp;
					<a class="nslink" href="/nearest_systems.php?system_allegiance=Federation<?php echo $addtolink2;?>" title="Federation"><img src="style/img/federation.png" alt="All" style="vertical-align:middle;" /></a>&nbsp;
					<a class="nslink" href="/nearest_systems.php?system_allegiance=None<?php echo $addtolink2;?>" title="None allied"><img src="style/img/system.png" alt="None allied" style="vertical-align:middle;" /></a>
					<br /><br />
				</td>
				<!-- powers -->
				<td class="station_info_price_info_t" style="vertical-align:top;width:25%;white-space:nowrap;">
					<?php
					$p_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT name FROM edtb_powers ORDER BY name");

					while ($p_arr = mysqli_fetch_assoc($p_res))
					{
						$power_name = $p_arr["name"];

						if (isset($power))
						{
							$addtolink = str_replace("&power=", "", $addtolink);
							$addtolink = str_replace("?power=", "", $addtolink);
							$addtolink = str_replace(urlencode($power), "", $addtolink);
						}
						echo '<a class="nslink" href="/nearest_systems.php?power=' . urlencode($power_name). '' . $addtolink . '" title="' . $power_name . '">' . $power_name . '</a><br />';
					}
					?>
				</td>
				<!-- modules -->
				<td class="station_info_price_info_t" style="vertical-align:top;width:25%;white-space:nowrap;">
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go">
						<?php
						echo $hidden_inputs;
						if (isset($group_id) && $group_id != "0")
						{
							$modi = " AND group_id = '" . $group_id . "'";
						}
						?>
						<select class="selectbox" name="group_id" style="width:222px;" onchange="getCR($('select[name=group_id]').val());">
								<optgroup label="Module"><option value="0">Module</option>
								<?php
								$mod_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT group_id, group_name, category_name
																						FROM edtb_modules
																						ORDER BY category_name, group_name");

								$cur_cat = "";
								while ($mod_arr = mysqli_fetch_assoc($mod_res))
								{
									$cat_name = $mod_arr["category_name"];

									if ($cur_cat != $cat_name)
									{
										echo '</optgroup><optgroup label="' . $cat_name . '">';
									}
									$selected = $_GET["group_id"] == $mod_arr["group_id"] ? " selected='selected'" : "";
									echo '<option value="' . $mod_arr["group_id"] . '"' . $selected . '>' . $mod_arr["group_name"] . '</option>';

									$cur_cat = $cat_name;
								}
								?>
						</select><br />
						<select class="selectbox" name="class" style="width:222px;" id="class">
								<option value="0">Class</option>
								<?php
								$mod_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT class
																						FROM edtb_modules WHERE class != ''" . $modi . "
																						ORDER BY class");

								while ($mod_arr = mysqli_fetch_assoc($mod_res))
								{
									$selected = $_GET["class"] == $mod_arr["class"] ? " selected='selected'" : "";
									echo '<option value="' . $mod_arr["class"] . '"' . $selected . '>Class ' . $mod_arr["class"] . '</option>';
								}
								?>
						</select><br />
						<select class="selectbox" name="rating" style="width:222px;" id="rating">
								<option value="0">Rating</option>
								<?php
								$mod_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT rating
																						FROM edtb_modules
																						WHERE rating != ''" . $modi . "
																						ORDER BY rating");

								while ($mod_arr = mysqli_fetch_assoc($mod_res))
								{
									$selected = $_GET["rating"] == $mod_arr["rating"] ? " selected='selected'" : "";
									echo '<option value="' . $mod_arr["rating"] . '"' . $selected . '>Rating ' . $mod_arr["rating"] . '</option>';
								}
								?>
						</select><br />
						<input class="button" type="submit" value="Search" style="width:222px;" />
					</form>
				</td>
				<!-- ships & facilities -->
				<td class="station_info_price_info_t" style="vertical-align:top;width:25%;white-space:nowrap;">
					<!-- ships -->
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" id="ships">
						<?php
						echo $hidden_inputs;
						?>
						<select class="selectbox" name="ship_name" style="width:180px;" onchange="this.form.submit();">
								<option value="0">Sells Ships</option>
								<?php

								$ship_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name
																						FROM edtb_ships
																						ORDER BY name");

								while ($ship_arr = mysqli_fetch_assoc($ship_res))
								{
									$selected = $_GET["ship_name"] == $ship_arr["name"] ? " selected='selected'" : "";
									echo '<option value="' . $ship_arr["name"] . '"' . $selected . '>' . $ship_arr["name"] . '</option>';
								}
								?>
						</select><br />
						<!--<input id="ship_submit" class="button" type="submit" value="Search" style="width:180px;" />-->
					</form>
					<!-- facilities -->
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" id="ships">
						<?php
						echo $hidden_inputs;
						?>
						<select class="selectbox" name="facility" style="width:180px;" onchange="this.form.submit();">
								<option value="0">Has Facilities</option>
								<?php
								$facility_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, code
																							FROM edtb_facilities
																							ORDER BY name");

								while ($facility_arr = mysqli_fetch_assoc($facility_res))
								{
									$selected = $_GET["facility"] == $facility_arr["code"] ? " selected='selected'" : "";
									echo '<option value="' . $facility_arr["code"] . '"' . $selected . '>' . $facility_arr["name"] . '</option>';
								}
								?>
						</select><br />
						<!--<input id="ship_submit" class="button" type="submit" value="Search" style="width:180px;" />-->
					</form>
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" id="ships">
						<?php
						echo $hidden_inputs;
						?>
						<select class="selectbox" name="pad" style="width:180px;" onchange="this.form.submit();">
							<?php
							$selectedL = $_GET["pad"] == "L" ? " selected='selected'" : "";
							$selectedM = $_GET["pad"] == "M" ? " selected='selected'" : "";
							?>
							<option value="">Landing Pad Size</option>
							<option value="L"<?php echo $selectedL;?>>Large</option>
							<option value="M"<?php echo $selectedM;?>>Medium</option>
							<option value="">All</option>
						</select><br />
					</form>
				</td>
			</tr>
			<tr>
				<td class="systeminfo_station_name" colspan="5"><?php echo $text?></td>
			</tr>
			<tr>
				<td class="station_info_price_info_t" colspan="5">
					<table>
						<tr>
							<td class="station_info_price_category" colspan="7">System</td>

							<?php
							if ($stations !== false)
							{
							?>
								<td class="station_info_price_category" colspan="3">Station</td>
							<?php
							}
							?>
						</tr>
						<tr>
							<td class="station_info_price_info2">Allegiance</td>
							<td class="station_info_price_info2">Distance</td>
							<td class="station_info_price_info2">Name</td>
							<td class="station_info_price_info2">Pop.</td>
							<td class="station_info_price_info2">Economy</td>
							<td class="station_info_price_info2">Government</td>
							<td class="station_info_price_info2">Security</td>
							<?php
							if ($stations !== false)
							{
							?>
								<td class="station_info_price_info2">Name</td>
								<td class="station_info_price_info2">LS From Star</td>
								<td class="station_info_price_info2">Landing Pad</td>
							<?php
							}
							?>
						</tr>
						<?php
						if ($count > 0)
						{
							$last_system = "";
							while ($arr = mysqli_fetch_assoc($res))
							{
								$system = $arr["system"];
								$system_id = $arr["system_id"];
								$sys_population = number_format($arr["population"]);
								$sys_economy = $arr["economy"];
								$sys_government = $arr["government"];
								$sys_security = $arr["security"];
								$allegiance = $arr["allegiance"];

								$station_name = $arr["station_name"];

								$ss_coordx = $arr["coordx"];
								$ss_coordy = $arr["coordy"];
								$ss_coordz = $arr["coordz"];

								$distance = sqrt(pow(($ss_coordx-($usex)), 2)+pow(($ss_coordy-($usey)), 2)+pow(($ss_coordz-($usez)), 2));

								$pic = "system.png";

								if ($allegiance != "")
								{
									$pic = $allegiance == "Empire" ? "empire.png" : $pic;
									$pic = $allegiance == "Alliance" ? "alliance.png" : $pic;
									$pic = $allegiance == "Federation" ? "federation.png" : $pic;
								}

								if ($system != $last_system)
								{
									echo '<tr><td class="station_info_price_info_t" style="text-align:center;"><img src="style/img/' . $pic . '" alt="' . $allegiance . '" style="vertical-align:middle;" /></td>';

									echo '<td class="station_info_price_info_t">';
									echo number_format($distance,2);
									echo ' ly' . $is_unknown . '</td>';

									echo '<td class="station_info_price_info_t"><a href="system.php?system_id=' . $system_id . '">' . $system . '</a></td>';
									echo '<td class="station_info_price_info_t">' . $sys_population . '</td>';
									echo '<td class="station_info_price_info_t">' . $sys_economy . '</td>';
									echo '<td class="station_info_price_info_t">' . $sys_government . '</td>';
									echo '<td class="station_info_price_info_t">' . $sys_security . '</td>';
								}
								else
								{
									echo '<tr><td class="station_info_price_info_t" colspan="7" style="height:45px;">&nbsp;</td>';
								}

								if ($station_name != "")
								{
									$station_ls_from_star = $arr["ls_from_star"] == 0 ? "n/a" : number_format($arr["ls_from_star"]);
									$station_max_landing_pad_size = $arr["max_landing_pad_size"];
									$station_is_planetary = $arr["is_planetary"];
									$station_type = $arr["type"];

									/*$planetary = $station_is_planetary == "1" ? '<img src="/style/img/planetary.png" alt="planetary" style="margin-right:6px;vertical-align:middle;" />' : '<img src="/style/img/spaceport.png" alt="" style="margin-right:6px;vertical-align:middle;" />';*/

									$icon = get_station_icon($station_type, $station_is_planetary);

									echo '<td class="station_info_price_info_t">' . $icon . '' . $station_name . '</td>';
									echo '<td class="station_info_price_info_t">' . $station_ls_from_star . '</td>';
									echo '<td class="station_info_price_info_t">' . $station_max_landing_pad_size . '</td>';
								}

								echo '</tr>';
								$last_system = $system;
							}
						}
						else
						{
							echo '<tr><td>None found!</td></tr>';
						}
						?>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");