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
 * Nearest systems & stations
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
*/

$pagetitle = "Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

$addtolink = "";
$addtolink2 = "";
$system = isset($_GET["system"]) ? $_GET["system"] : "";
$text = "Nearest";

$add = "";
$hidden_inputs = "";

//  determine what coordinates to use
if (!empty($system))
{
	$sys_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, id, x, y, z
															FROM edtb_systems
															WHERE id = '" . $system . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$sys_arr = mysqli_fetch_assoc($sys_res);

	$sys_name = $sys_arr["name"];
	$sys_id = $sys_arr["id"];

	$usex = $sys_arr["x"];
	$usey = $sys_arr["y"];
	$usez = $sys_arr["z"];

	$text .= " (to <a href='system.php?system_id=" . $sys_id . "'>" . $sys_name . "</a>) ";
	$addtolink .= "&system=" . $system . "";
	$addtolink2 .= "&system=" . $system . "";
	$hidden_inputs .= '<input type="hidden" name="system" value="' . $sys_id . '" />';
}
elseif (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]) && empty($system))
{
	$usex = $curSys["x"];
	$usey = $curSys["y"];
	$usez = $curSys["z"];
}
else
{
	// get last known coordinates
	$last_coords = last_known_system();

	$usex = $last_coords["x"];
	$usey = $last_coords["y"];
	$usez = $last_coords["z"];

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

if ($power != "")
{
	$stations = false;

    $add .= " AND edtb_systems.power = '" . $power . "'";
	$text .= " " . $power . " systems";
	$hidden_inputs .= '<input type="hidden" name="power" value="' . $power . '" />';
	$addtolink .= "&power=" . urlencode($power) . "";
	$addtolink2 .= "&power=" . urlencode($power) . "";
}

if ($only != "")
{
	$stations = true;

	if ($only != "all")
	{
		$add .= " AND edtb_stations.allegiance = '" . $only . "'";
	}
	else
	{
		$add .= " AND edtb_stations.allegiance = 'None'";
	}

	if ($only != "all" && $only != "Independent")
	{
		$text .= " systems with " . $only . " controlled stations";
	}
	elseif ($only == "Independent")
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

if ($system_allegiance != "")
{
	$stations = false;

    $add .= " AND edtb_systems.allegiance = '" . $system_allegiance . "'";
	$text .= " " . str_replace('None', 'Non-allied', $system_allegiance) . " systems";
	$hidden_inputs .= '<input type="hidden" name="system_allegiance" value="' . $system_allegiance . '" />';
	$addtolink .= "&system_allegiance=" . $system_allegiance . "";
}

// if we're searching facilities
if (!empty($facility))
{
	$stations = true;

	$add .= " AND edtb_stations." . $facility . " = '1'";
	$f_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name
														FROM edtb_facilities
														WHERE code = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $facility) . "'
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
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

if ($pad != "")
{
	$stations = true;

    $add .= " AND edtb_stations.max_landing_pad_size = '" . $pad . "'";
	$padsize = $pad == "L" ? "Large" : "Medium";
	$text .= "  stations with " . $padsize . " sized landing pads";
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
														edtb_stations.id AS station_id, edtb_stations.faction AS station_faction,
														edtb_stations.government AS station_government, edtb_stations.allegiance AS station_allegiance,
														edtb_stations.state AS station_state, edtb_stations.black_market, edtb_stations.commodities_market,
														edtb_stations.refuel, edtb_stations.repair, edtb_stations.rearm,
														edtb_stations.outfitting, edtb_stations.shipyard,
														edtb_stations.import_commodities, edtb_stations.export_commodities,
														edtb_stations.prohibited_commodities, edtb_stations.economies, edtb_stations.shipyard_updated_at,
														edtb_stations.outfitting_updated_at, edtb_stations.selling_ships,
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
														WHERE edtb_systems.x != ''" . $add . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2)),
														-edtb_stations.ls_from_star DESC
														LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
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
														WHERE edtb_systems.x != ''" . $add . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
														LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
}

// if we're searching modules
if (!empty($group_id))
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

	$gnres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT group_name
														FROM edtb_modules
														WHERE group_id = '" . $group_id . "'
														LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]));
	$gnarr = mysqli_fetch_assoc($gnres);

	$group_name = $gnarr["group_name"];
	$group_name = substr($group_name, -1) == "s" ? $group_name : "" . $group_name . "s";

	if (!empty($rating))
	{
		$ratings = " " . $_GET["rating"] . " rated ";
		$hidden_inputs .= '<input type="hidden" name="rating" value="' . $rating . '" />';
		$addtolink .= "&rating=" . $rating . "";
	}

	if (!empty($class))
	{
		$classes = " class " . $_GET["class"] . " ";
		$hidden_inputs .= '<input type="hidden" name="class" value="' . $class . '" />';
		$addtolink .= "&class=" . $class . "";
	}

	if (!empty($class) && !empty($rating))
	{
		$pres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT price
															FROM edtb_modules
															WHERE group_id = '" . $group_id . "'
															AND rating = '" . $rating . "'
															AND class = '" . $class . "'
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		$parr = mysqli_fetch_assoc($pres);
		$modules_price = number_format($parr["price"]);
		$price = " (normal price " . $modules_price . " CR) ";
	}

	$text .= " stations selling " . $ratings . "" . $classes . "" . $group_name . "" . $price . "";
	$hidden_inputs .= '<input type="hidden" name="group_id" value="' . $group_id . '" />';
	$addtolink .= "&group_id=" . $group_id . "";
	$addtolink2 .= "&group_id=" . $group_id . "";

	$module_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																FROM edtb_modules
																WHERE group_id = '" . $group_id . "'" . $class_add . "" . $rating_add . "
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$mod_count = mysqli_num_rows($module_res);

	if ($mod_count > 0)
	{
		$module_arr = mysqli_fetch_assoc($module_res);
		$modules_id = $module_arr["id"];

		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
															edtb_stations.ls_from_star, edtb_stations.max_landing_pad_size,
															edtb_stations.is_planetary, edtb_stations.type,
															edtb_stations.id AS station_id, edtb_stations.faction AS station_faction,
															edtb_stations.government AS station_government, edtb_stations.allegiance AS station_allegiance,
															edtb_stations.state AS station_state, edtb_stations.black_market, edtb_stations.commodities_market,
															edtb_stations.refuel, edtb_stations.repair, edtb_stations.rearm,
															edtb_stations.outfitting, edtb_stations.shipyard,
															edtb_stations.import_commodities, edtb_stations.export_commodities,
															edtb_stations.prohibited_commodities, edtb_stations.economies, edtb_stations.shipyard_updated_at,
															edtb_stations.outfitting_updated_at, edtb_stations.selling_ships,
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
															AND edtb_stations.selling_modules LIKE '-%" . $modules_id . "%-'" . $add . "
															ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
															LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$stations = true;
	}
	else
	{
		$res = "";
	}
}

// if we're searching ships
if (!empty($ship_name))
{
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
														edtb_stations.ls_from_star, edtb_stations.max_landing_pad_size,
														edtb_stations.is_planetary, edtb_stations.type,
														edtb_stations.id AS station_id, edtb_stations.faction AS station_faction,
														edtb_stations.government AS station_government, edtb_stations.allegiance AS station_allegiance,
														edtb_stations.state AS station_state, edtb_stations.black_market, edtb_stations.commodities_market,
														edtb_stations.refuel, edtb_stations.repair, edtb_stations.rearm,
														edtb_stations.outfitting, edtb_stations.shipyard,
														edtb_stations.import_commodities, edtb_stations.export_commodities,
														edtb_stations.prohibited_commodities, edtb_stations.economies, edtb_stations.shipyard_updated_at,
														edtb_stations.outfitting_updated_at, edtb_stations.selling_ships,
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
														AND edtb_stations.selling_ships LIKE '%\'" . $ship_name . "\'%'" . $add . "
														ORDER BY sqrt(pow((coordx-(" . $usex . ")),2)+pow((coordy-(" . $usey . ")),2)+pow((coordz-(" . $usez . ")),2))
														LIMIT 10") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);


	$p_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT price
														FROM edtb_ships
														WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $ship_name) . "'")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	$p_arr = mysqli_fetch_assoc($p_res);
	$ship_price = number_format($p_arr["price"]);

	if (isset($p_arr["price"]))
	{
		$s_price = " (normal price " . $ship_price . " CR)";
	}

	$stations = true;
	$text .= " stations selling the " . $ship_name . "" . $s_price . "";
	$hidden_inputs .= '<input type="hidden" name="ship_name" value="' . $ship_name . '" />';
	$addtolink .= "&ship_name=" . $ship_name . "";
	$addtolink2 .= "&ship_name=" . $ship_name . "";
}

if ($text == "Nearest")
{
	$text = "Nearest stations";
}

if (substr($text, 0, 11) == "Nearest (to" && $stations === true)
{
	$text = str_replace("Nearest ", "Nearest stations ", $text);
}

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
<div class="entries">
	<div class="entries_inner">
		<table id="nscontent" style="margin-top:16px;width:100%">
			<tr>
				<td class="heading" style="width:25%;white-space:nowrap">Nearest stations</td>
				<td class="heading" style="width:25%;white-space:nowrap">Nearest Allegiances</td>
				<td class="heading" style="width:25%;white-space:nowrap">Nearest Powers</td>
				<td class="heading" style="width:25%;white-space:nowrap">Selling Modules</td>
				<td class="heading" style="width:25%;white-space:nowrap">Ships & Facilities</td>
			</tr>
			<tr>
				<!-- station allegiances -->
				<td class="transparent" style="vertical-align:top;width:25%;white-space:nowrap">
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?allegiance=Empire<?php echo $addtolink2;?>" title="Empire"><img src="style/img/empire.png" alt="Empire" /></a>&nbsp;
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?allegiance=Alliance<?php echo $addtolink2;?>" title="Alliance"><img src="style/img/alliance.png" alt="Alliance" /></a>&nbsp;
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?allegiance=Federation<?php echo $addtolink2;?>" title="Federation"><img src="style/img/federation.png" alt="Federation" /></a>&nbsp;
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?allegiance=Independent<?php echo $addtolink2;?>" title="Independent"><img src="style/img/system.png" alt="Independent" /></a>
					<!-- search systems and stations-->
					<div style="text-align:left">
						<div style="width:180px;margin-top:35px">
							<input class="textbox" type="text" name="system_name" placeholder="System (optional)" id="system_21" style="width:180px" oninput="showResult(this.value, '11', 'no', 'no', 'yes')" /><br />
							<input class="textbox" type="text" name="station_name" placeholder="Station (optional)" id="station_21" style="width:180px" oninput="showResult(this.value, '12', 'no', 'yes', 'yes')" />
							<div class="suggestions" id="suggestions_11" style="margin-left:0;margin-top:-36px;min-width:168px"></div>
							<div class="suggestions" id="suggestions_12" style="margin-left:0;min-width:168px"></div>
						</div>
					</div>
				</td>
				<!-- allegiances -->
				<td class="transparent" style="vertical-align:top;width:25%;white-space:nowrap">
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?system_allegiance=Empire<?php echo $addtolink2;?>" title="Empire"><img src="style/img/empire.png" alt="Empire" /></a>&nbsp;
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?system_allegiance=Alliance<?php echo $addtolink2;?>" title="Alliance"><img src="style/img/alliance.png" alt="Alliance" /></a>&nbsp;
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?system_allegiance=Federation<?php echo $addtolink2;?>" title="Federation"><img src="style/img/federation.png" alt="Federation" /></a>&nbsp;
					<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?system_allegiance=None<?php echo $addtolink2;?>" title="None allied"><img src="style/img/system.png" alt="None allied" /></a>
					<br /><br />
				</td>
				<!-- powers -->
				<td class="transparent" style="vertical-align:top;width:25%;white-space:nowrap">
					<?php
					$p_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name
																		FROM edtb_powers
																		ORDER BY name")
																		or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

					while ($p_arr = mysqli_fetch_assoc($p_res))
					{
						$power_name = $p_arr["name"];

						if (isset($power))
						{
							$addtolink = str_replace("&power=", "", $addtolink);
							$addtolink = str_replace("?power=", "", $addtolink);
							$addtolink = str_replace(urlencode($power), "", $addtolink);
						}
						echo '<a data-replace="true" data-target="#nscontent" href="/nearest_systems.php?power=' . urlencode($power_name). '' . $addtolink . '" title="' . $power_name . '">' . $power_name . '</a><br />';
					}
					?>
				</td>
				<!-- modules -->
				<td class="transparent" style="vertical-align:top;width:25%;white-space:nowrap">
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
						<?php
						echo $hidden_inputs;
						if (isset($group_id) && $group_id != "0")
						{
							$modi = " AND group_id = '" . $group_id . "'";
						}
						?>
						<select class="selectbox" name="group_id" style="width:222px" onchange="getCR($('select[name=group_id]').val(),'')">
								<optgroup label="Module"><option value="0">Module</option>
								<?php
								$mod_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT group_id, group_name, category_name
																						FROM edtb_modules
																						ORDER BY category_name, group_name")
																						or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

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
						<select class="selectbox" name="class" style="width:222px" id="class" onchange="getCR($('select[name=group_id]').val(),$('select[name=class]').val())">
								<option value="0">Class</option>
								<?php
								$mod_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT class
																						FROM edtb_modules WHERE class != ''" . $modi . "
																						ORDER BY class")
																						or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

								while ($mod_arr = mysqli_fetch_assoc($mod_res))
								{
									$selected = $_GET["class"] == $mod_arr["class"] ? " selected='selected'" : "";
									echo '<option value="' . $mod_arr["class"] . '"' . $selected . '>Class ' . $mod_arr["class"] . '</option>';
								}
								?>
						</select><br />
						<select class="selectbox" name="rating" style="width:222px" id="rating">
								<option value="0">Rating</option>
								<?php
								$mod_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT DISTINCT rating
																						FROM edtb_modules
																						WHERE rating != ''" . $modi . "
																						ORDER BY rating")
																						or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

								while ($mod_arr = mysqli_fetch_assoc($mod_res))
								{
									$selected = $_GET["rating"] == $mod_arr["rating"] ? " selected='selected'" : "";
									echo '<option value="' . $mod_arr["rating"] . '"' . $selected . '>Rating ' . $mod_arr["rating"] . '</option>';
								}
								?>
						</select><br />
						<input class="button" type="submit" value="Search" style="width:222px;margin-top:5px" />
					</form>
				</td>
				<!-- ships & facilities -->
				<td class="transparent" style="vertical-align:top;width:25%;white-space:nowrap">
					<!-- ships -->
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" id="ships" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
						<?php
						echo $hidden_inputs;
						?>
						<select class="selectbox" name="ship_name" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
								<option value="0">Sells Ships</option>
								<?php

								$ship_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name
																						FROM edtb_ships
																						ORDER BY name")
																						or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

								while ($ship_arr = mysqli_fetch_assoc($ship_res))
								{
									$selected = $_GET["ship_name"] == $ship_arr["name"] ? " selected='selected'" : "";
									echo '<option value="' . $ship_arr["name"] . '"' . $selected . '>' . $ship_arr["name"] . '</option>';
								}
								?>
						</select><br />
						<!--<input id="ship_submit" class="button" type="submit" value="Search" style="width:180px" />-->
					</form>
					<!-- facilities -->
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" id="facilities" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
						<?php
						echo $hidden_inputs;
						?>
						<select class="selectbox" name="facility" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
								<option value="0">Has Facilities</option>
								<?php
								$facility_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT name, code
																							FROM edtb_facilities
																							ORDER BY name")
																							or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

								while ($facility_arr = mysqli_fetch_assoc($facility_res))
								{
									$selected = $_GET["facility"] == $facility_arr["code"] ? " selected='selected'" : "";
									echo '<option value="' . $facility_arr["code"] . '"' . $selected . '>' . $facility_arr["name"] . '</option>';
								}
								?>
						</select><br />
					</form>
					<!-- landing pads -->
					<form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" name="go" id="landingpads" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
						<?php
						echo $hidden_inputs;
						?>
						<select class="selectbox" name="pad" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
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
				<td class="heading" colspan="5"><?php echo $text?></td>
			</tr>
			<tr>
				<td class="ns_nearest" colspan="5">
					<table id="nearest_systems">
						<tr>
							<td class="light" colspan="7"><strong>System</strong></td>
							<?php
							if ($stations !== false)
							{
							?>
								<td class="light" colspan="3"><strong>Station</strong></td>
							<?php
							}
							?>
						</tr>
						<tr>
							<td class="dark">Allegiance</td>
							<td class="dark">Distance</td>
							<td class="dark">Name</td>
							<td class="dark">Pop.</td>
							<td class="dark">Economy</td>
							<td class="dark">Government</td>
							<td class="dark">Security</td>
							<?php
							if ($stations !== false)
							{
							?>
								<td class="dark">Name</td>
								<td class="dark">LS From Star</td>
								<td class="dark">Landing Pad</td>
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

								// check if system has screenshots
								$screenshots = has_screenshots($system) ? '<a href="/gallery.php?spgmGal=' . urlencode($system) . '" title="View image gallery"><img src="/style/img/image.png" alt="Gallery" style="margin-left:5px;vertical-align:top" /></a>' : "";

								// check if system is logged
								$loglink = is_logged($system_id, true) ? '<a href="log.php?system=' . urlencode($system) . '" style="color:inherit" title="System has log entries"><img src="/style/img/log.png" style="margin-left:5px" /></a>' : "";

								$ss_coordx = $arr["coordx"];
								$ss_coordy = $arr["coordy"];
								$ss_coordz = $arr["coordz"];

								$distance = sqrt(pow(($ss_coordx-($usex)), 2)+pow(($ss_coordy-($usey)), 2)+pow(($ss_coordz-($usez)), 2));

								// get allegiance icon
								$pic = get_allegiance_icon($allegiance);

								if ($system != $last_system)
								{
									echo '<tr><td class="transparent" style="text-align:center"><img src="style/img/' . $pic . '" alt="' . $allegiance . '" /></td>';

									echo '<td class="transparent">';
									echo number_format($distance,2);
									echo ' ly' . $is_unknown . '</td>';

									echo '<td class="transparent"><a href="system.php?system_id=' . $system_id . '">' . $system . '</a>' . $loglink.$screenshots . '</td>';
									echo '<td class="transparent">' . $sys_population . '</td>';
									echo '<td class="transparent">' . $sys_economy . '</td>';
									echo '<td class="transparent">' . $sys_government . '</td>';
									echo '<td class="transparent">' . $sys_security . '</td>';
								}
								else
								{
									echo '<tr><td class="transparent" colspan="7" style="height:45px">&nbsp;</td>';
								}

								if (!empty($station_name))
								{
									$station_ls_from_star = $arr["ls_from_star"] == 0 ? "n/a" : number_format($arr["ls_from_star"]);
									$station_max_landing_pad_size = $arr["max_landing_pad_size"];
									$station_is_planetary = $arr["is_planetary"];
									$station_type = $arr["type"];

									$icon = get_station_icon($station_type, $station_is_planetary);

									$station_id = $arr["station_id"];
									$station_faction = $arr["station_faction"] == "" ? "" : "<strong>Faction:</strong> " . $arr["station_faction"] . "<br />";
									$station_government = $arr["station_government"] == "" ? "" : "<strong>Government:</strong> " . $arr["station_government"] . "<br />";
									$station_allegiance = $arr["station_allegiance"] == "" ? "" : "<strong>Allegiance:</strong> " . $arr["station_allegiance"] . "<br />";

									$station_state = $arr["station_state"] == "" ? "" : "<strong>State:</strong> " . $arr["station_state"] . "<br />";
									$station_type_d = $arr["type"] == "" ? "" : "<strong>Type:</strong> " . $arr["type"] . "<br />";
									$station_economies = $arr["station_economies"] == "" ? "" : "<strong>Economies:</strong> " . $arr["station_economies"] . "<br />";

									$station_import_commodities = $arr["import_commodities"] == "" ? "" : "<br /><strong>Import commodities:</strong> " . $arr["import_commodities"] . "<br />";
									$station_export_commodities = $arr["export_commodities"] == "" ? "" : "<strong>Export commodities:</strong> " . $arr["export_commodities"] . "<br />";
									$station_prohibited_commodities = $arr["prohibited_commodities"] == "" ? "" : "<strong>Prohibited commodities:</strong> " . $arr["prohibited_commodities"] . "<br />";

									$station_selling_ships = $arr["selling_ships"] == "" ? "" : "<br /><strong>Selling ships:</strong> " . str_replace("'", "", $arr["selling_ships"]) . "<br />";

									$station_shipyard = $arr["shipyard"];
									$station_outfitting = $arr["outfitting"];
									$station_commodities_market = $arr["commodities_market"];
									$station_black_market = $arr["black_market"];
									$station_refuel = $arr["refuel"];
									$station_repair = $arr["repair"];
									$station_rearm = $arr["rearm"];

									$station_includes = array(  "shipyard" => $station_shipyard,
																"outfitting" => $station_outfitting,
																"commodities market" => $station_commodities_market,
																"black market" => $station_black_market,
																"refuel" => $station_refuel,
																"repair" => $station_repair,
																"restock" => $station_rearm);

									$i = 0;
									$station_services = "";
									foreach ($station_includes as $name => $included)
									{
										if ($included == 1)
										{
											if ($i != 0)
											{
												$station_services .= ", ";
											}
											else
											{
												$station_services .= "<strong>Facilities:</strong> ";
											}

											$station_services .= $name;
										$i++;
										}
									}
									$station_services .= "<br />";

									$outfitting_updated_at = $arr["outfitting_updated_at"] == "0" ? "" : "<br /><strong>Outfitting last updated:</strong> " . get_timeago($arr["outfitting_updated_at"], true, true) . "<br />";

									$shipyard_updated_at = $arr["shipyard_updated_at"] == "0" ? "" : "<strong>Shipyard last updated:</strong> " . get_timeago($arr["shipyard_updated_at"], true, true) . "<br />";

									$info = $station_type_d.$station_faction.$station_government.$station_allegiance.$station_state.$station_economies.$station_services.$station_import_commodities.$station_export_commodities.$station_prohibited_commodities.$outfitting_updated_at.$shipyard_updated_at.$station_selling_ships;

									$info = str_replace("['", "", $info);
									$info = str_replace("']", "", $info);
									$info = str_replace("', '", ", ", $info);

									// get allegiance icon
									$station_allegiance_icon = get_allegiance_icon($arr["station_allegiance"]);
									$station_allegiance_icon = '<img src="/style/img/' . $station_allegiance_icon . '" alt="' . $arr["station_allegiance"] . '" style="width:19px;height:19px;margin-right:5px" />';

									/*
									*	notify user if data is old
									*/

									$station_disp_name = $station_name;

									if (!empty($group_id) || !empty($ship_name))
									{
										if (data_is_old($arr["outfitting_updated_at"]) || data_is_old($arr["shipyard_updated_at"]))
										{
											$station_disp_name = '<span class="old_data">' . $station_name . '</span>';
										}
									}

									echo '<td class="transparent">' . $station_allegiance_icon.$icon . '<a href="javascript:void(0)" onclick="$(\'#si_statinfo_' . $station_id . '\').fadeToggle(\'fast\')" title="Additional information">' . $station_disp_name . '<div class="stationinfo_ns" id="si_statinfo_' . $station_id . '">' . $info . '</div></td>';
									echo '<td class="transparent">' . $station_ls_from_star . '</td>';
									echo '<td class="transparent">' . $station_max_landing_pad_size . '</td>';
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
