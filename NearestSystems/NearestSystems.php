<?php
/**
 * Nearest systems & stations
 * Front-end file for Nearest systems & stations
 *
 * This page displays the nearest systems and stations based on the user's location
 * or a specified location. Results can be filtered by system/station allegiance,
 * system power, type of modules or ships sold at the station + more.
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

/** @var string pagetitle */
$pagetitle = "Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");

/** @var string power_params parameters to add to Power links */
$power_params = "";

/** @var string allegiance_params parameters to add to Allegiance links */
$allegiance_params = "";

$system = isset($_GET["system"]) ? $_GET["system"] : "";
$text = "Nearest";

$add = "";
$hidden_inputs = "";

/**
 * determine what coordinates to use
 */
if (!empty($system)) {
    $query = "  SELECT name, id, x, y, z
                FROM edtb_systems
                WHERE id = '$system'
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
    $sys_obj = $result->fetch_object();

    $sys_name = $sys_obj->name;
    $sys_id = $sys_obj->id;

    $usex = $sys_obj->x;
    $usey = $sys_obj->y;
    $usez = $sys_obj->z;

    $result->close();

    $text .= " (to <a href='System.php?system_id=" . $sys_id . "'>" . $sys_name . "</a>) ";
    $power_params .= "&system=" . $system;
    $allegiance_params .= "&system=" . $system;
    $hidden_inputs .= '<input type="hidden" name="system" value="' . $sys_id . '" />';
} elseif (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]) && empty($system)) {
    $usex = $curSys["x"];
    $usey = $curSys["y"];
    $usez = $curSys["z"];
} else {
    // get last known coordinates
    $last_coords = last_known_system();

    $usex = $last_coords["x"];
    $usey = $last_coords["y"];
    $usez = $last_coords["z"];

    $is_unknown = " *";
}

/**
 * If we still don't have valid coordinates, center on Sol
 */
if (!valid_coordinates($usex, $usey, $usez)) {
    $usex = "0";
    $usey = "0";
    $usez = "0";

    $is_unknown = " *";
}

/**
 * get url parameters
 */
$ship_name = isset($_GET["ship_name"]) ? $_GET["ship_name"] : "";
$facility = isset($_GET["facility"]) ? $_GET["facility"] : "";
$only = isset($_GET["allegiance"]) ? $_GET["allegiance"] : "";
$system_allegiance = isset($_GET["system_allegiance"]) ? $_GET["system_allegiance"] : "";
$group_id = isset($_GET["group_id"]) ? $_GET["group_id"] : "";
$power = isset($_GET["power"]) ? $_GET["power"] : "";
$pad = isset($_GET["pad"]) ? $_GET["pad"] : "";
$station_type = isset($_GET["station_type"]) ? $_GET["station_type"] : "";
$stations = true;

/**
 * specific power
 */
if ($power != "") {
    $stations = false;

    $add .= " AND edtb_systems.power = '$power'";
    $text .= " " . $power . " systems";
    $hidden_inputs .= '<input type="hidden" name="power" value="' . $power . '" />';
    $power_params .= "&power=" . urlencode($power);
    $allegiance_params .= "&power=" . urlencode($power);
}

/**
 * specific station allegiance
 */
if ($only != "") {
    $stations = true;

    if ($only != "all") {
        $add .= " AND edtb_stations.allegiance = '$only'";
    } else {
        $add .= " AND edtb_stations.allegiance = 'None'";
    }

    if ($only != "all" && $only != "Independent") {
        $text .= " systems with " . $only . " controlled stations";
    } elseif ($only == "Independent") {
        $text .= " systems with Independent stations";
    } else {
        $text .= " systems with non-allied stations";
    }

    $hidden_inputs .= '<input type="hidden" name="allegiance" value="' . $only . '" />';
    $power_params .= "&allegiance=" . $only;
}

/**
 * specific system allegiance
 */
if ($system_allegiance != "") {
    $stations = false;

    $add .= " AND edtb_systems.allegiance = '$system_allegiance'";
    $text .= " " . str_replace('None', 'Non-allied', $system_allegiance) . " systems";
    $hidden_inputs .= '<input type="hidden" name="system_allegiance" value="' . $system_allegiance . '" />';
    $power_params .= "&system_allegiance=" . $system_allegiance;
}

/**
 * if we're searching facilities
 */
if (!empty($facility)) {
    $stations = true;

    $add .= " AND edtb_stations." . $facility . " = '1'";

    $esc_facility = $mysqli->real_escape_string($facility);
    $query = "  SELECT name
                FROM edtb_facilities
                WHERE code = '$esc_facility'
                LIMIT 1";

    $f_obj = $result->fetch_object();
    $f_name = $f_obj->name;

    $result->close();

    if (preg_match('/([aeiouAEIOU])/', $f_name{0})) {
        $article = "an";
    } else {
        $article = "a";
    }

    $text .= " stations with " . $article . " " . $f_name . " facility";
    $hidden_inputs .= '<input type="hidden" name="facility" value="' . $facility . '" />';
    $power_params .= "&facility=" . $facility;
    $allegiance_params .= "&facility=" . $facility;
}

/**
 * landing pad size
 */
if ($pad != "") {
    $stations = true;

    $add .= " AND edtb_stations.max_landing_pad_size = '$pad'";
    $padsize = $pad == "L" ? "Large" : "Medium";
    $text .= "  stations with " . $padsize . " sized landing pads";
    $hidden_inputs .= '<input type="hidden" name="pad" value="' . $pad . '" />';
    $power_params .= "&pad=" . $pad;
    $allegiance_params .= "&pad=" . $pad;
}

/**
 * station type
 */
if ($station_type != "") {
    $stations = true;

    switch ($station_type)
    {
        case "planetary":
            $add .= " AND edtb_stations.is_planetary = '1'";
            $text .= " (planetary only)";
            break;
        case "space":
            $add .= " AND edtb_stations.is_planetary = '0'";
            $text .= " (space ports only)";
            break;
        case "all":
            $add .= "";
            $text .= "";
            break;
        default:
            $add .= "";
            $text .= "";
    }

    $hidden_inputs .= '<input type="hidden" name="station_type" value="' . $station_type . '" />';
    $power_params .= "&station_type=" . $station_type;
    $allegiance_params .= "&station_type=" . $station_type;
}
/**
 * nearest stations....
 */
if ($stations !== false) {

    $main_query = " SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
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
                    ORDER BY sqrt(pow((coordx-(" . $usex . ")), 2)+pow((coordy-(" . $usey . ")), 2)+pow((coordz-(" . $usez . ")), 2)),
                    -edtb_stations.ls_from_star DESC
                    LIMIT 10";
}

/**
 * ...or nearest systems
 */
else {
    $main_query = " SELECT edtb_systems.name AS system, edtb_systems.allegiance,
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
                    ORDER BY sqrt(pow((coordx-(" . $usex . ")), 2)+pow((coordy-(" . $usey . ")), 2)+pow((coordz-(" . $usez . ")), 2))
                    LIMIT 10";
}

/**
 * if we're searching modules
 */
if (!empty($group_id)) {
    $class = isset($_GET["class"]) ? $_GET["class"] : "";
    $rating = isset($_GET["rating"]) ? $_GET["rating"] : "";

    $class_add = "";
    if ($class != "" && $class != "0") {
        $class_add = " AND class = '" . $_GET["class"] . "'";
    }

    $rating_add = "";
    if ($rating != "" && $rating != "0") {
        $rating_add = " AND rating = '" . $_GET["rating"] . "'";
    }

    $query = "  SELECT group_name
                FROM edtb_modules
                WHERE group_id = '$group_id'
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
    $g_obj = $result->fetch_object();

    $group_name = $g_obj->group_name;
    $group_name = substr($group_name, -1) == "s" ? $group_name : "" . $group_name . "s";

    $result->close();

    if (!empty($rating)) {
        $ratings = " " . $_GET["rating"] . " rated ";
        $hidden_inputs .= '<input type="hidden" name="rating" value="' . $rating . '" />';
        $power_params .= "&rating=" . $rating;
    }

    if (!empty($class)) {
        $classes = " class " . $_GET["class"] . " ";
        $hidden_inputs .= '<input type="hidden" name="class" value="' . $class . '" />';
        $power_params .= "&class=" . $class;
    }

    if (!empty($class) && !empty($rating)) {
        $query = "  SELECT price
                    FROM edtb_modules
                    WHERE group_id = '$group_id'
                    AND rating = '$rating'
                    AND class = '$class'
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $p_obj = $result->fetch_object();

        $modules_price = number_format($p_obj->price);
        $price = " (normal price " . $modules_price . " CR) ";

        $result->close();
    }

    $text .= " stations selling " . $ratings . $classes . $group_name . $price;
    $hidden_inputs .= '<input type="hidden" name="group_id" value="' . $group_id . '" />';
    $power_params .= "&group_id=" . $group_id;
    $allegiance_params .= "&group_id=" . $group_id;

    $query = "  SELECT id
                FROM edtb_modules
                WHERE group_id = '$group_id'" . $class_add . $rating_add . "
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $mod_count = $result->num_rows;

    if ($mod_count > 0) {
        $p_obj = $result->fetch_object();
        $modules_id = $module_obj->id;

        $main_query = " SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
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
                        ORDER BY sqrt(pow((coordx-(" . $usex . ")), 2)+pow((coordy-(" . $usey . ")), 2)+pow((coordz-(" . $usez . ")), 2))
                        LIMIT 10";

        $stations = true;
        $result->close();
    } else {
        $res = "";
    }
}

/**
 * if we're searching ships
 */
if (!empty($ship_name)) {
    $main_query = " SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
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
                    ORDER BY sqrt(pow((coordx-(" . $usex . ")), 2)+pow((coordy-(" . $usey . ")), 2)+pow((coordz-(" . $usez . ")), 2))
                    LIMIT 10";

    $esc_ship_name = $mysqli->real_escape_string($ship_name);
    $query = "  SELECT price
                FROM edtb_ships
                WHERE name = '$esc_ship_name'";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $price_obj = $result->fetch_object();

    $ship_price = number_format($price_obj->price);

    if (isset($price_obj->price)) {
        $s_price = " (normal price " . $ship_price . " CR)";
    }

    $result->close();

    $stations = true;
    $text .= " stations selling the " . $ship_name . $s_price;
    $hidden_inputs .= '<input type="hidden" name="ship_name" value="' . $ship_name . '" />';
    $power_params .= "&ship_name=" . $ship_name;
    $allegiance_params .= "&ship_name=" . $ship_name;
}

if ($text == "Nearest") {
    $text = "Nearest stations";
}

if (substr($text, 0, 11) == "Nearest (to" && $stations === true) {
    $text = str_replace("Nearest ", "Nearest stations ", $text);
}

/**
 * replace all but the first occurance of "key" with "value"
 */
$replaces = array(  "stations" => "",
                    "selling" => "and",
                    "with" => "and"
                    );

foreach ($replaces as $replace => $with) {
    $pos = strpos($text, $replace);
    if ($pos !== false) {
        $text = substr($text, 0, $pos + 1) . str_replace($replace, $with, substr($text, $pos + 1));
    }
}

/**
 * replace all but the last occurance of "systems"
 */
$pos = substr_count($text, 'systems');
if ($pos > 1) {
    $text = preg_replace('/\.(\s|$)/', 'systems$1', $text);
    $text = substr_replace($text, '', strpos($text, 'systems'), 7);
}

$main_result = $mysqli->query($main_query) or write_log($mysqli->error, __FILE__, __LINE__);
$count = $main_result->num_rows;
?>
<script>
    $("a.send").click(function()
    {
        $.get("/action/shipControls.php?send=" + $(this).data("send"));
    });
</script>
<div class="entries">
    <div class="entries_inner">
        <div class="stationinfo_ns" id="si_statinfo"></div>
        <div class="info" id="sysinfo" style="position:fixed">
            Send system name to Elite Dangerous client.<br />Have the textbox in the Galaxy Map targeted before clicking.
        </div>
        <table style="margin-bottom:0;width:100%">
            <tr>
                <td class="heading" style="white-space:nowrap;width:20%">Nearest stations</td>
                <td class="heading" style="white-space:nowrap;width:20%">Nearest Allegiances</td>
                <td class="heading" style="white-space:nowrap;width:20%">Nearest Powers</td>
                <td class="heading" style="white-space:nowrap;width:20%">Selling Modules</td>
                <td class="heading" style="white-space:nowrap;width:20%">Ships &amp; Facilities</td>
            </tr>
            <tr>
                <!-- station allegiances -->
                <td class="transparent" style="vertical-align:top;width:20%;white-space:nowrap">
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?allegiance=Empire<?php echo $allegiance_params?>" title="Empire">
                        <img src="/style/img/empire.png" class="allegiance_icon" alt="Empire" />
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?allegiance=Alliance<?php echo $allegiance_params?>" title="Alliance">
                        <img src="/style/img/alliance.png" class="allegiance_icon" alt="Alliance" />
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?allegiance=Federation<?php echo $allegiance_params?>" title="Federation">
                        <img src="/style/img/federation.png" class="allegiance_icon" alt="Federation" />
                    </a>&nbsp;
                        <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?allegiance=Independent<?php echo $allegiance_params?>" title="Independent">
                    <img src="/style/img/system.png" class="allegiance_icon" alt="Independent" />
                    </a>
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
                <td class="transparent" style="vertical-align:top;width:20%;white-space:nowrap">
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?system_allegiance=Empire<?php echo $allegiance_params?>" title="Empire">
                        <img src="/style/img/empire.png" class="allegiance_icon" alt="Empire" />
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?system_allegiance=Alliance<?php echo $allegiance_params?>" title="Alliance">
                        <img src="/style/img/alliance.png" class="allegiance_icon" alt="Alliance" />
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?system_allegiance=Federation<?php echo $allegiance_params?>" title="Federation">
                        <img src="/style/img/federation.png" class="allegiance_icon" alt="Federation" />
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent" href="/NearestSystems/?system_allegiance=None<?php echo $allegiance_params?>" title="None allied">
                        <img src="/style/img/system.png" class="allegiance_icon" alt="None allied" />
                    </a>
                    <br /><br />
                </td>
                <!-- powers -->
                <td class="transparent" style="vertical-align:top;width:20%;white-space:nowrap">
                    <?php
                    $query = "SELECT name FROM edtb_powers ORDER BY name";
                    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                    while ($power_obj = $result->fetch_object()) {
                        $power_name = $power_obj->name;

                        if (isset($power)) {
                            $power_params = str_replace("&power=", "", $power_params);
                            $power_params = str_replace("?power=", "", $power_params);
                            $power_params = str_replace(urlencode($power), "", $power_params);
                        }
                        echo '<a data-replace="true" data-target="#nscontent" href="/NearestSystems/?power=' . urlencode($power_name) . $power_params . '" title="' . $power_name . '">' . $power_name . '</a><br />';
                    }

                    $result->close();
                    ?>
                </td>
                <!-- modules -->
                <td class="transparent" style="vertical-align:top;width:20%;white-space:nowrap">
                    <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" name="go">
                        <?php
                        echo $hidden_inputs;
                        if (isset($group_id) && $group_id != "0") {
                            $modi = " AND group_id = '$group_id'";
                        }
                        ?>
                        <select title="Module" class="selectbox" name="group_id" style="width:222px" onchange="getCR($('select[name=group_id]').val(), '')">
                            <optgroup label="Module"><option value="0">Module</option>
                            <?php
                            $query = "  SELECT DISTINCT group_id, group_name, category_name
                                        FROM edtb_modules
                                        ORDER BY category_name, group_name";

                            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                            $cur_cat = "";
                            while ($mod_obj = $result->fetch_object()) {
                                $cat_name = $mod_obj->category_name;

                                if ($cur_cat != $cat_name) {
                                    echo '</optgroup><optgroup label="' . $cat_name . '">';
                                }

                                $selected = $_GET["group_id"] == $mod_obj->group_id ? " selected='selected'" : "";
                                echo '<option value="' . $mod_obj->group_id . '"' . $selected . '>' . $mod_obj->group_name . '</option>';

                                $cur_cat = $cat_name;
                            }

                            $result->close();
                            ?>
                        </select><br />
                        <select title="Class" class="selectbox" name="class" style="width:222px" id="class" onchange="getCR($('select[name=group_id]').val(), $('select[name=class]').val())">
                            <option value="0">Class</option>
                            <?php
                            $query = "  SELECT DISTINCT class
                                        FROM edtb_modules WHERE class != ''" . $modi . "
                                        ORDER BY class";

                            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                            while ($mod_obj = $result->fetch_object()) {
                                $selected = $_GET["class"] == $mod_obj->class ? " selected='selected'" : "";
                                echo '<option value="' . $mod_obj->class . '"' . $selected . '>Class ' . $mod_obj->class . '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br />
                        <select title="Rating" class="selectbox" name="rating" style="width:222px" id="rating">
                            <option value="0">Rating</option>
                            <?php
                            $query = "  SELECT DISTINCT rating
                                        FROM edtb_modules
                                        WHERE rating != ''" . $modi . "
                                        ORDER BY rating";

                            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                            while ($mod_obj = $result->fetch_object()) {
                                $selected = $_GET["rating"] == $mod_obj->rating ? " selected='selected'" : "";
                                echo '<option value="' . $mod_obj->rating . '"' . $selected . '>Rating ' . $mod_obj->rating . '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br />
                        <input class="button" type="submit" value="Search" style="width:222px;margin-top:5px" />
                    </form>
                </td>
                <!-- ships & facilities -->
                <td class="transparent" style="vertical-align:top;width:20%;white-space:nowrap">
                    <!-- ships -->
                    <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" name="go" id="ships" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
                        <?php
                        echo $hidden_inputs;
                        ?>
                        <select title="Ship" class="selectbox" name="ship_name" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
                            <option value="0">Sells Ships</option>
                            <?php
                            $query = "SELECT name FROM edtb_ships ORDER BY name";
                            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                            while ($ship_obj = $result->fetch_object()) {
                                $selected = $_GET["ship_name"] == $ship_obj->name ? " selected='selected'" : "";
                                echo '<option value="' . $ship_obj->name . '"' . $selected . '>' . $ship_obj->name . '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br />
                        <!--<input id="ship_submit" class="button" type="submit" value="Search" style="width:180px" />-->
                    </form>
                    <!-- facilities -->
                    <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" name="go" id="facilities" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
                        <?php
                        echo $hidden_inputs;
                        ?>
                        <select title="Facility" class="selectbox" name="facility" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
                            <option value="0">Has Facilities</option>
                            <?php
                            $query = "SELECT name, code FROM edtb_facilities ORDER BY name";
                            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

                            while ($facility_obj = $result->fetch_object()) {
                                $selected = $_GET["facility"] == $facility_obj->code ? " selected='selected'" : "";
                                echo '<option value="' . $facility_obj->code . '"' . $selected . '>' . $facility_obj->name . '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br />
                    </form>
                    <!-- landing pads -->
                    <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" name="go" id="landingpads" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
                        <?php
                        echo $hidden_inputs;
                        ?>
                        <select title="Landing pad" class="selectbox" name="pad" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
                            <?php
                            $selectedL = $_GET["pad"] == "L" ? ' selected="selected"' : "";
                            $selectedM = $_GET["pad"] == "M" ? ' selected="selected"' : "";
                            ?>
                            <option value="">Landing Pad Size</option>
                            <option value="L"<?php echo $selectedL?>>Large</option>
                            <option value="M"<?php echo $selectedM?>>Medium</option>
                            <option value="">All</option>
                        </select><br />
                    </form>
                    <!-- station type -->
                    <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" name="go" id="stationtype" data-push="true" data-target="#nscontent" data-include-blank-url-params="true" data-optimize-url-params="false">
                        <?php
                        echo $hidden_inputs;
                        ?>
                        <select title="Station type" class="selectbox" name="station_type" style="width:180px" onchange="$('.se-pre-con').show();this.form.submit()">
                            <?php
                            $selectedP = $_GET["station_type"] == "planetary" ? ' selected="selected"' : "";
                            $selectedS = $_GET["station_type"] == "space" ? ' selected="selected"' : "";
                            $selectedA = $_GET["station_type"] == "all" ? ' selected="selected"' : "";
                            ?>
                            <option value="all">Station Type</option>
                            <option value="planetary"<?php echo $selectedP?>>Planetary</option>
                            <option value="space"<?php echo $selectedS?>>Space</option>
                            <option value="all"<?php echo $selectedA?>>All</option>
                        </select><br />
                    </form>
                </td>
            </tr>
        </table>
        <table id="nscontent" style="margin-left:5px;margin-bottom:20px;width:100%">
            <tr>
                <td class="transparent" colspan="5">
                    <header><h2><img class="icon" src="/style/img/find.png" alt="Find" /><?php echo $text?></h2></header>
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="ns_nearest" colspan="5">
                    <table id="nearest_systems">
                        <tr>
                            <td class="heading" colspan="7"><strong>System</strong></td>
                            <?php
                            if ($stations !== false) {
                                ?>
                                <td class="heading" colspan="3"><strong>Station</strong></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td class="dark"><strong>Allegiance</strong></td>
                            <td class="dark"><strong>Distance</strong></td>
                            <td class="dark"><strong>Name</strong></td>
                            <td class="dark"><strong>Pop.</strong></td>
                            <td class="dark"><strong>Economy</strong></td>
                            <td class="dark"><strong>Government</strong></td>
                            <td class="dark"><strong>Security</strong></td>
                            <?php
                            if ($stations !== false) {
                                ?>
                                <td class="dark"><strong>Name</strong></td>
                                <td class="dark"><strong>LS From Star</strong></td>
                                <td class="dark"><strong>Landing Pad</strong></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                        if ($count > 0) {
                            $last_system = "";
                            $ii = 0;
                            $tdclass = "";
                            while ($obj = $main_result->fetch_object()) {
                                $system = $obj->system;
                                $system_id = $obj->system_id;
                                $sys_population = number_format($obj->population);
                                $sys_economy = empty($obj->economy) ? "n/a" : $obj->economy;
                                $sys_government = $obj->government;
                                $sys_security = empty($obj->security) ? "None" : $obj->security;
                                $allegiance = $obj->allegiance;

                                $station_name = $obj->station_name;

                                /**
                                 * provide crosslinks to screenshot gallery, log page, etc
                                 */
                                $ns_crosslinks = System::crosslinks($system);

                                $ss_coordx = $obj->coordx;
                                $ss_coordy = $obj->coordy;
                                $ss_coordz = $obj->coordz;

                                $distance = sqrt(pow(($ss_coordx-($usex)), 2)+pow(($ss_coordy-($usey)), 2)+pow(($ss_coordz-($usez)), 2));

                                /**
                                 * get allegiance icon for system
                                 */
                                $pic = get_allegiance_icon($allegiance);

                                if ($system != $last_system) {
                                    $tdclass = $tdclass == "light" ? "dark" : "light";
                                    ?>
                                    <tr>
                                        <td class="<?php echo $tdclass?>" style="text-align:center">
                                            <img src="/style/img/<?php echo $pic?>" class="allegiance_icon" alt="<?php echo $allegiance?>" style="margin:0" />
                                        </td>
                                        <td class="<?php echo $tdclass?>">
                                            <?php echo number_format($distance, 2)?> ly<?php echo $is_unknown?>
                                        </td>
                                        <td class="<?php echo $tdclass?>">
                                            <a class="send" href="javascript:void(0)" data-send="<?php echo $system?>" data-id="<?php echo $system_id?>">
                                                <img class="icon" src="/style/img/magic.png" alt="Send" style="margin-bottom:7px;margin-right:0" onmouseover="to_view('sysinfo', event)" onmouseout="$('#sysinfo').fadeToggle('fast')" />
                                            </a>
                                            <a href="/System?system_id=<?php echo $system_id?>">
                                                <?php echo $system?>
                                            </a>
                                            <?php echo $ns_crosslinks?>
                                        </td>
                                        <td class="<?php echo $tdclass?>"><?php echo $sys_population?></td>
                                        <td class="<?php echo $tdclass?>"><?php echo $sys_economy?></td>
                                        <td class="<?php echo $tdclass?>"><?php echo $sys_government?></td>
                                        <td class="<?php echo $tdclass?>"><?php echo $sys_security?></td>
                                    <?php
                                } else {
                                    ?>
                                    <tr><td class="transparent" colspan="7" style="height:45px">&nbsp;</td>
                                    <?php
                                }

                                if (!empty($station_name)) {
                                    $station_ls_from_star = $obj->ls_from_star == 0 ? "n/a" : number_format($obj->ls_from_star);
                                    $station_max_landing_pad_size = $obj->max_landing_pad_size;
                                    $station_max_landing_pad_size = $station_max_landing_pad_size == "L" ? "Large" : "Medium";
                                    $station_is_planetary = $obj->is_planetary;
                                    $station_type = $obj->type;

                                    $icon = get_station_icon($station_type, $station_is_planetary);

                                    $station_id = $obj->station_id;
                                    $station_faction = $obj->station_faction == "" ? "" : "<strong>Faction:</strong> " . $obj->station_faction . "<br />";
                                    $station_government = $obj->station_government == "" ? "" : "<strong>Government:</strong> " . $obj->station_government . "<br />";
                                    $station_allegiance = $obj->station_allegiance == "" ? "" : "<strong>Allegiance:</strong> " . $obj->station_allegiance . "<br />";

                                    $station_state = $obj->station_state == "" ? "" : "<strong>State:</strong> " . $obj->station_state . "<br />";
                                    $station_type_d = $obj->type == "" ? "" : "<strong>Type:</strong> " . $obj->type . "<br />";
                                    $station_economies = $obj->station_economies == "" ? "" : "<strong>Economies:</strong> " . $obj->station_economies . "<br />";

                                    $station_import_commodities = $obj->import_commodities == "" ? "" : "<br /><strong>Import commodities:</strong> " . $obj->import_commodities . "<br />";
                                    $station_export_commodities = $obj->export_commodities == "" ? "" : "<strong>Export commodities:</strong> " . $obj->export_commodities . "<br />";
                                    $station_prohibited_commodities = $obj->prohibited_commodities == "" ? "" : "<strong>Prohibited commodities:</strong> " . $obj->prohibited_commodities . "<br />";

                                    $station_selling_ships = $obj->selling_ships == "" ? "" : "<br /><strong>Selling ships:</strong> " . str_replace("'", "", $obj->selling_ships) . "<br />";

                                    $station_shipyard = $obj->shipyard;
                                    $station_outfitting = $obj->outfitting;
                                    $station_commodities_market = $obj->commodities_market;
                                    $station_black_market = $obj->black_market;
                                    $station_refuel = $obj->refuel;
                                    $station_repair = $obj->repair;
                                    $station_rearm = $obj->rearm;

                                    $station_includes = array(  "shipyard" => $station_shipyard,
                                                                "outfitting" => $station_outfitting,
                                                                "commodities market" => $station_commodities_market,
                                                                "black market" => $station_black_market,
                                                                "refuel" => $station_refuel,
                                                                "repair" => $station_repair,
                                                                "restock" => $station_rearm);

                                    $i = 0;
                                    $station_services = "";
                                    foreach ($station_includes as $name => $included) {
                                        if ($included == 1) {
                                            if ($i != 0) {
                                                $station_services .= ", ";
                                            } else {
                                                $station_services .= "<strong>Facilities:</strong> ";
                                            }

                                            $station_services .= $name;
                                            $i++;
                                        }
                                    }
                                    $station_services .= "<br />";

                                    $outfitting_updated_at = $obj->outfitting_updated_at == "0" ? "" : "<br /><strong>Outfitting last updated:</strong> " . get_timeago($obj->outfitting_updated_at, true, true) . "<br />";

                                    $shipyard_updated_at = $obj->shipyard_updated_at == "0" ? "" : "<strong>Shipyard last updated:</strong> " . get_timeago($obj->shipyard_updated_at, true, true) . "<br />";

                                    $info = $station_type_d . $station_faction . $station_government . $station_allegiance . $station_state . $station_economies . $station_services;
                                    $info .= $station_import_commodities . $station_export_commodities . $station_prohibited_commodities . $outfitting_updated_at . $shipyard_updated_at . $station_selling_ships;

                                    $info = str_replace("['", "", $info);
                                    $info = str_replace("']", "", $info);
                                    $info = str_replace("', '", ", ", $info);

                                    /**
                                     * get allegiance icon
                                     */
                                    $station_allegiance_icon = get_allegiance_icon($obj->station_allegiance);
                                    $station_allegiance_icon = '<img src="/style/img/' . $station_allegiance_icon . '" alt="' . $obj->station_allegiance . '" style="width:19px;height:19px;margin-right:5px" />';

                                    /**
                                     * notify user if data is old
                                     */
                                    $station_disp_name = $station_name;

                                    if (!empty($group_id) || !empty($ship_name)) {
                                        if (data_is_old($obj->outfitting_updated_at) || data_is_old($obj->shipyard_updated_at)) {
                                            $station_disp_name = '<span class="old_data">' . $station_name . '</span>';
                                        }
                                    }
                                    ?>
                                    <td class="<?php echo $tdclass?>">
                                        <?php echo $station_allegiance_icon . $icon?>
                                        <a href="javascript:void(0)" id="minfo<?php echo $station_id?>" title="Additional information">
                                            <?php echo $station_disp_name?>
                                        </a>
                                    </td>
                                    <td class="<?php echo $tdclass?>">
                                        <?php echo $station_ls_from_star?>
                                    </td>
                                    <td class="<?php echo $tdclass?>">
                                        <?php echo $station_max_landing_pad_size?>
                                    </td>
                                    <script>
                                            $(document).mouseup(function (e)
                                            {
                                                var containers = [];
                                                containers.push($("#si_statinfo"));

                                                $.each(containers, function(key, value)
                                                {
                                                    if (!$(value).is(e.target) && $(value).has(e.target).length === 0)
                                                    {
                                                        $(value).fadeOut("fast");
                                                    }
                                                });
                                            });

                                            $("#minfo<?php echo $station_id?>").click(function(e)
                                            {
                                                var statinfo_div = $("#si_statinfo");
                                                if (statinfo_div.is(":hidden"))
                                                {
                                                    statinfo_div.fadeToggle("fast");
                                                    statinfo_div.css(
                                                    {
                                                        left: e.pageX - 330,
                                                        top: e.pageY - 40
                                                    });
                                                    statinfo_div.html("<?php echo addslashes($info)?>");
                                                }
                                            });
                                        </script>
                                <?php
                                }
                                ?>
                                </tr>
                                <?php
                                $last_system = $system;
                                $ii++;
                            } // end of while
                        } else {
                            $colspan = $stations !== false ? "10" : "7";
                            ?>
                            <tr><td class="light" colspan="<?php echo $colspan?>">None found!</td></tr>
                            <?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
