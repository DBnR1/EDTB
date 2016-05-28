<?php
/**
 * System data for Marvin
 *
 * No description
 *
 * @package EDTB\Marvin
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/** @require functions */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");
/** @require config */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
/** @require MySQL */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/MySQL.php");
/** @require curSys */
require_once($_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

use \EDTB\source\System;

/**
 * System Info
 */

if (isset($_GET["sys"])) {
    $num_visits = System::num_visits($curSys["name"]);
    $esc_sysname = $mysqli->real_escape_string($curSys["name"]);

    $va_text .= "No system data.";

    if (!empty($curSys["name"])) {
        $va_system = str_replace(".", "", $curSys["name"]);

        $va_text = "The " . tts_override($va_system) . " system.\n\r";

        $va_allegiance = $curSys["allegiance"] == "None" ? "No additional data available. " : $curSys["allegiance"];
        $va_allegiance = $va_allegiance == "" ? "No additional data available. " : $va_allegiance;

        /**
         * Marvin goes on a bit of a rant
         */
        $rant = "";
        if ($settings["angry_droid"] == "true") {
            /**
             * fetch allegiance rants
             */
            $allegiance_rants = glob("Rants/Allegiance_*");

            /**
             * loop trough files
             */
            foreach ($allegiance_rants as $alleg) {
                $allegiance = str_replace("Rants/Allegiance_", "", $alleg);
                $allegiance = str_replace(".txt", "", $allegiance);

                $rants = [];

                /**
                 * if current allegiance matches
                 */
                if ($curSys["allegiance"] == $allegiance) {
                    $rantss = file($alleg);
                    // loop trough rants
                    foreach ($rantss as $ranta) {
                        if (!empty($ranta) && $ranta{0} != ";") {
                            $rants[] = $ranta;
                        }
                    }

                    // randomize
                    shuffle($rants);

                    $rant = $rants[0];
                    break;
                }
            }
        }

        $va_government = $curSys["government"] == "None" ? "" : " " . $curSys["government"];
        $va_power = "";

        if (!empty($curSys["power"]) && !empty($curSys["power_state"])) {
            $va_power_text = [];
            $va_power_text[] = $curSys["power"];

            /**
             * Another rant incoming...
             */
            if ($settings["angry_droid"] == "true") {
                /**
                 * fetch power rants
                 */
                $power_rants = glob("Rants/Power_*");

                /**
                 * loop trough files
                 */
                foreach ($power_rants as $powers) {
                    $power = str_replace("Rants/Power_", "", $powers);
                    $power = str_replace(".txt", "", $power);

                    // if current power matches
                    if ($curSys["power"] == $power) {
                        $power_rantss = file($powers);

                        $power_rants_text = [];
                        // loop trough rants
                        foreach ($power_rantss as $power_ranta) {
                            if (!empty($power_ranta)) {
                                if ($power_ranta == "random") {
                                    $power_rants_text[] = random_insult($power);
                                } elseif ($power_ranta{0} != ";") {
                                    $power_rants_text[] = $power_ranta;
                                }
                            }
                        }

                        // randomize
                        shuffle($power_rants_text);

                        $va_power_text[] = !empty($power_rants_text[0]) ? $power_rants_text[0] : $curSys["power"];
                        break;
                    }
                }
            }

            if ($curSys["power_state"] == "Contested") {
                $va_power = " system that is currently contested";
            } else {
                $va_power_state_text = $curSys["power_state"] == "Control" ? "controlled" : $curSys["power_state"];

                array_filter($va_power_text);
                shuffle($va_power_text);

                $va_power = $curSys["power_state"] == "None" ? "" : " " . strtolower($va_power_state_text) . " by " . $va_power_text[0];
            }
        }

        /**
         * Round value for population
         */
        if ($curSys["population"] >= 1000000000) {
            $round = -6;
        } elseif ($curSys["population"] >= 10000000 && $curSys["population"] < 1000000000) {
            $round = -5;
        } elseif ($curSys["population"] >= 1000000 && $curSys["population"] < 10000000) {
            $round = -4;
        } elseif ($curSys["population"] >= 100000 && $curSys["population"] < 1000000) {
            $round = -3;
        } elseif ($curSys["population"] >= 10000 && $curSys["population"] < 100000) {
            $round = -3;
        } elseif ($curSys["population"] >= 1000 && $curSys["population"] < 10000) {
            $round = -2;
        } elseif ($curSys["population"] >= 100 && $curSys["population"] < 1000) {
            $round = -1;
        } else {
            $round = 0;
        }

        $va_pop = "";
        if ($curSys["population"] != 0) {
            $pop = number_format(round($curSys["population"], $round));
            $va_pop = $curSys["population"] == "None" ? ". It is unpopulated." : ", with a population of about " . $pop . ".";
        }

        $article = "";
        if ($va_allegiance != "No additional data available. ") {
            if (preg_match('/([aeiouAEIOU])/', $va_allegiance{0})) {
                $article = "An";
            } else {
                $article = "A";
            }
        }

        $va_text .= $article . " " . $va_allegiance . strtolower($va_government) . $va_power . $va_pop;

        $va_text .= " " . $rant;

        $query = "  SELECT name, ls_from_star
                    FROM edtb_stations
                    WHERE system_id = '" . $curSys["id"] . "'
                    ORDER BY -ls_from_star DESC, name";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $count = $result->num_rows;

        if ($count > 0) {
            $c = 0;
            while ($arra = $result->fetch_object()) {
                if ($c == 0) {
                    $first_station_name = $arra->name;
                    $first_station_ls_from_star = $arra->ls_from_star;
                } else {
                    break;
                }
                $c++;
            }
        }

        $result->close();

        if ($count == 1) {
            if ($first_station_ls_from_star != 0) {
                $va_text .= " The systems' only spaceport is " . $first_station_name . " " . number_format(round($first_station_ls_from_star)) . " light seconds away.";
            } else {
                $va_text .= " The systems' only spaceport is " . $first_station_name . ".";
            }
        } elseif ($count > 1) {
            if ($first_station_ls_from_star != 0) {
                $va_text .= " It has " . $count . " spaceports, the nearest one is " . $first_station_name . " " . number_format(round($first_station_ls_from_star)) . " light seconds away.";
            } else {
                $va_text .= " It has " . $count . " spaceports.";
            }
        }

        if ($num_visits == 1) {
            $inputs = [];
            $inputs[] = " We have not visited this system before.";
            $inputs[] = " This is our first time visiting this system.";
            shuffle($inputs);

            $va_text .= $inputs[0];
        } else {
            $query = "  SELECT visit
                        FROM user_visited_systems
                        WHERE system_name = '$esc_sysname'
                        ORDER BY visit ASC
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $vis_arr = $result->fetch_object();

            $first_vis = get_timeago(strtotime($vis_arr->visit));
            $result->close();

            if ($num_visits == 2) {
                $va_text .= " We have visited this system once before. That was " . $first_vis . ".";
            } else {
                $va_text .= " We have visited this system " . $num_visits . " times before. Our first visit was " . $first_vis . ".";
            }
        }
    }
    echo $va_text;

    exit;
}

/**
 * Nearest Station
 */

if (isset($_GET["cs"])) {
    $ambiguity = "";
    if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
        $usex = $curSys["x"];
        $usey = $curSys["y"];
        $usez = $curSys["z"];
    } else {
        $last_coords = last_known_system();

        $usex = $last_coords["x"];
        $usey = $last_coords["y"];
        $usez = $last_coords["z"];
        $last_system = $last_coords["name"];

        $add2 = "I am unable to determine the coordinates of our current location. Our last known location is the " . tts_override($last_system) . " system. ";
        $ambiguity = " some ";
    }

    $query = "  SELECT
                edtb_stations.system_id AS system_id,
                edtb_stations.name AS station_name,
                edtb_stations.max_landing_pad_size,
                edtb_stations.ls_from_star,
                edtb_stations.type,
                edtb_stations.shipyard,
                edtb_stations.outfitting,
                edtb_stations.commodities_market,
                edtb_stations.black_market,
                edtb_stations.refuel,
                edtb_stations.repair,
                edtb_stations.rearm,
                edtb_systems.allegiance AS allegiance,
                edtb_systems.id AS system_id,
                edtb_systems.x AS coordx,
                edtb_systems.y AS coordy,
                edtb_systems.z AS coordz,
                edtb_systems.name as system_name
                FROM edtb_stations
                LEFT JOIN edtb_systems on edtb_stations.system_id = edtb_systems.id
                WHERE edtb_systems.x != ''
                ORDER BY sqrt(pow((coordx-(" . $usex . ")), 2)+pow((coordy-(" . $usey . ")), 2)+pow((coordz-(" . $usez . ")), 2)),
                -edtb_stations.ls_from_star DESC
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    echo $add2;

    $cs_obj = $result->fetch_object();

    $cs_system = tts_override($cs_obj->system_name);
    $cs_allegiance = $cs_obj->allegiance;

    $ss_coordx = $cs_obj->coordx;
    $ss_coordy = $cs_obj->coordy;
    $ss_coordz = $cs_obj->coordz;

    $cs_distance = sqrt(pow(($ss_coordx-($usex)), 2)+pow(($ss_coordy-($usey)), 2)+pow(($ss_coordz-($usez)), 2));

    $cs_station_name = $cs_obj->station_name;
    $cs_max_landing_pad_size = $cs_obj->max_landing_pad_size == "L" ? "large" : "medium";
    $cs_ls_from_star = $cs_obj->ls_from_star;
    $cs_type = $cs_obj->type;
    $cs_shipyard = $cs_obj->shipyard;
    $cs_outfitting = $cs_obj->outfitting;
    $cs_commodities_market = $cs_obj->commodities_market;
    $cs_black_market = $cs_obj->black_market;
    $cs_refuel = $cs_obj->refuel;
    $cs_repair = $cs_obj->repair;
    $cs_rearm = $cs_obj->rearm;

    $result->close();

    $cs_facilities = array( "a shipyard" => $cs_shipyard,
        "outfitting" => $cs_outfitting,
        "a commodities market" => $cs_commodities_market,
        "a black market" => $cs_black_market,
        "refuel" => $cs_refuel,
        "repair" => $cs_repair,
        "restock" => $cs_rearm);

    $count = 0;
    foreach ($cs_facilities as $cs_name => $cs_included) {
        if ($cs_included == 1) {
            $count++;
        }
    }

    $cs_services = "";
    $i = 0;
    foreach ($cs_facilities as $cs_name => $cs_included) {
        if ($cs_included == 1) {
            if ($i == $count-1) {
                $cs_services .= ", and ";
            } elseif ($i != 0 && $i != $count-1) {
                $cs_services .= ", ";
            } else {
                $cs_services .= ", and is equipped with ";
            }

            $cs_services .= $cs_name;
            $i++;
        }
    }

    $article = "";
    if (!empty($cs_type)) {
        if (preg_match('/([aeiouAEIOU])/', $cs_type{0})) {
            $article = "an";
        } else {
            $article = "a";
        }
    }

    if ($cs_distance == 0) {
        echo 'The nearest spaceport is in this system. ';
    } else {
        echo 'The nearest spaceport is in the ' . $cs_system . ' system, ' . $ambiguity . number_format($cs_distance, 1) . ' light years away.';
    }

    echo ' ' . $cs_station_name;
    if (!empty($cs_type)) {
        $cs_type = str_ireplace("Unknown Planetary", "unknown planetary port", $cs_type);
        echo ' is ' . $article . ' ' . $cs_type;
    }
    if ($cs_ls_from_star != 0) {
        echo ' ' . number_format($cs_ls_from_star) . ' light seconds away from the main star';
    }

    echo '. It has ' . $cs_max_landing_pad_size . ' sized landing pads';

    echo $cs_services;

    exit;
}

/**
 * Random Musings
 */

if (isset($_GET["rm"])) {
    $query = "  SELECT id, text
                FROM edtb_musings
                WHERE used = '0'
                ORDER BY rand()
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $obj = $result->fetch_object();

    $rm_id = $obj->id;
    $rm_text = $obj->text;
    echo $rm_text;

    $result->close();

    $query = "  UPDATE edtb_musings
                SET used = '1'
                WHERE id = '$rm_id'
                LIMIT 1";

    $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    exit;
}

/**
 * current system short
 */

if (isset($_GET["sys_short"])) {
    $sys_short = "unknown";
    if (!empty($curSys["name"])) {
        $sys_short = $curSys["name"];
    }

    echo tts_override($sys_short);

    exit;
}

/**
 * distance to X
 */

if (isset($_GET["dist"])) {
    $to = $_GET["dist"];

    $distance = "";

    $to = str_replace("system", "", $to);

    if (!valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"])) {
        $distance = "How can I calculate distances if I don't even know where we are?";
    } else {
        if (System::exists($to)) {
            $esc_to = $mysqli->real_escape_string($to);
            $query = "  SELECT
                        sqrt(pow((IFNULL(edtb_systems.x, user_systems_own.x)-(" . $curSys["x"] . ")),2)+pow((IFNULL(edtb_systems.y, user_systems_own.y)-(" . $curSys["y"] . ")),2)+pow((IFNULL(edtb_systems.z, user_systems_own.z)-(" . $curSys["z"] . ")),2))
                        AS distance
                        FROM edtb_systems
                        LEFT JOIN user_systems_own ON edtb_systems.name = user_systems_own.name
                        WHERE edtb_systems.name = '$esc_to'
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $result->fetch_object();

            $distance = $obj->distance == "" ? "Not available" : number_format($obj->distance, 1);

            $result->close();
        } else {
            $distance = "I'm sorry, I didn't get that.";
        }
    }

    echo $distance;

    exit;
}

/**
 * curSys access, added the cSys variable because VA has oddly short limit on the url
 */

if (isset($_GET["curSys"]) || isset($_GET["cSys"])) {
    $search = isset($_GET["curSys"]) ? $_GET["curSys"] : $_GET["cSys"];

    $info = "";

    if (array_key_exists($search, $curSys)) {
        $info = $curSys[$search] == "" ? "None" : $curSys[$search];
    } else {
        $info = "" . $search . " is not recognised";
    }

    echo $info;

    exit;
}
