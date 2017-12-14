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
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require config */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';
/** @require curSys */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/curSys.php';

use \EDTB\source\System;

/**
 * System Info
 */

if (isset($_GET['sys'])) {
    $numVisits = System::numVisits($curSys['name']);
    $escSysname = $mysqli->real_escape_string($curSys['name']);

    $vaText .= 'No system data.';

    if (!empty($curSys['name'])) {
        $vaSystem = str_replace('.', '', $curSys['name']);

        $vaText = 'The ' . ttsOverride($vaSystem) . " system.\n\r";

        $vaAllegiance = $curSys['allegiance'] === 'None' ? 'No additional data available. ' : $curSys['allegiance'];
        $vaAllegiance = $vaAllegiance === '' ? 'No additional data available. ' : $vaAllegiance;

        /**
         * Marvin goes on a bit of a rant
         */
        $rant = '';
        if ($settings['angry_droid'] === 'true') {
            /**
             * fetch allegiance rants
             */
            $allegianceRants = glob('Rants/Allegiance_*');

            /**
             * loop trough files
             */
            foreach ($allegianceRants as $alleg) {
                $allegiance = str_replace([
                    'Rants/Allegiance_',
                    '.txt'
                ], '', $alleg);

                $rants = [];

                /**
                 * if current allegiance matches
                 */
                if ($curSys['allegiance'] === $allegiance) {
                    $rantss = file($alleg);
                    // loop trough rants
                    foreach ($rantss as $ranta) {
                        if (!empty($ranta) && $ranta{0} !== ';') {
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

        $vaGovernment = $curSys['government'] === 'None' ? '' : ' ' . $curSys['government'];
        $vaPower = '';

        if (!empty($curSys['power']) && !empty($curSys['power_state'])) {
            $vaPowerText = [];
            $vaPowerText[] = $curSys['power'];

            /**
             * Another rant incoming...
             */
            if ($settings['angry_droid'] === 'true') {
                /**
                 * fetch power rants
                 */
                $powerRants = glob('Rants/Power_*');

                /**
                 * loop trough files
                 */
                foreach ($powerRants as $powers) {
                    $power = str_replace([
                        'Rants/Power_',
                        '.txt'
                    ], '', $powers);

                    // if current power matches
                    if ($curSys['power'] === $power) {
                        $powerRantss = file($powers);

                        $powerRantsText = [];
                        // loop trough rants
                        foreach ($powerRantss as $powerRanta) {
                            if (!empty($powerRanta)) {
                                if ($powerRanta === 'random') {
                                    $powerRantsText[] = randomInsult($power);
                                } elseif ($powerRanta{0} !== ';') {
                                    $powerRantsText[] = $powerRanta;
                                }
                            }
                        }

                        // randomize
                        shuffle($powerRantsText);

                        $vaPowerText[] = !empty($powerRantsText[0]) ? $powerRantsText[0] : $curSys['power'];
                        break;
                    }
                }
            }

            if ($curSys['power_state'] === 'Contested') {
                $vaPower = ' system that is currently contested';
            } else {
                $vaPowerStateText = $curSys['power_state'] === 'Control' ? 'controlled' : $curSys['power_state'];

                array_filter($vaPowerText);
                shuffle($vaPowerText);

                $vaPower = $curSys['power_state'] === 'None' ? '' : ' ' . strtolower($vaPowerStateText) . ' by ' . $vaPowerText[0];
            }
        }

        /**
         * Round value for population
         */
        $round = 0;
        if ($curSys['population'] >= 1000000000) {
            $round = -6;
        } elseif ($curSys['population'] >= 10000000 && $curSys['population'] < 1000000000) {
            $round = -5;
        } elseif ($curSys['population'] >= 1000000 && $curSys['population'] < 10000000) {
            $round = -4;
        } elseif ($curSys['population'] >= 100000 && $curSys['population'] < 1000000) {
            $round = -3;
        } elseif ($curSys['population'] >= 10000 && $curSys['population'] < 100000) {
            $round = -3;
        } elseif ($curSys['population'] >= 1000 && $curSys['population'] < 10000) {
            $round = -2;
        } elseif ($curSys['population'] >= 100 && $curSys['population'] < 1000) {
            $round = -1;
        }

        $vaPop = '';
        if ($curSys['population'] != 0) {
            $pop = number_format(round($curSys['population'], $round));
            $vaPop = $curSys['population'] === 'None' ? '. It is unpopulated.' : ', with a population of about ' . $pop . '.';
        }

        $article = '';
        if ($vaAllegiance !== 'No additional data available. ') {
            $article = 'A';
            if (preg_match('/([aeiouAEIOU])/', $vaAllegiance{0})) {
                $article = 'An';
            }
        }

        $vaText .= $article . ' ' . $vaAllegiance . strtolower($vaGovernment) . $vaPower . $vaPop;

        $vaText .= ' ' . $rant;

        $query = "  SELECT name, ls_from_star
                    FROM edtb_stations
                    WHERE system_id = '" . $curSys['id'] . "'
                    ORDER BY -ls_from_star DESC, name";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $count = $result->num_rows;

        if ($count > 0) {
            $c = 0;
            while ($arra = $result->fetch_object()) {
                if ($c === 0) {
                    $firstStationName = $arra->name;
                    $firstStationLsFrom_star = $arra->ls_from_star;
                } else {
                    break;
                }
                $c++;
            }
        }

        $result->close();

        if ($count === 1) {
            if ($firstStationLsFrom_star != 0) {
                $vaText .= " The systems' only spaceport is " . $firstStationName . ' ' . number_format(round($firstStationLsFrom_star)) . ' light seconds away.';
            } else {
                $vaText .= " The systems' only spaceport is " . $firstStationName . '.';
            }
        } elseif ($count > 1) {
            if ($firstStationLsFrom_star != 0) {
                $vaText .= ' It has ' . $count . ' spaceports, the nearest one is ' . $firstStationName . ' ' . number_format(round($firstStationLsFrom_star)) . ' light seconds away.';
            } else {
                $vaText .= ' It has ' . $count . ' spaceports.';
            }
        }

        if ($numVisits == 1) {
            $inputs = [];
            $inputs[] = ' We have not visited this system before.';
            $inputs[] = ' This is our first time visiting this system.';
            shuffle($inputs);

            $vaText .= $inputs[0];
        } else {
            $query = "  SELECT visit
                        FROM user_visited_systems
                        WHERE system_name = '$escSysname'
                        ORDER BY visit ASC
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $visArr = $result->fetch_object();

            $firstVis = get_timeago(strtotime($visArr->visit));
            $result->close();

            if ($numVisits == 2) {
                $vaText .= ' We have visited this system once before. That was ' . $firstVis . '.';
            } else {
                $vaText .= ' We have visited this system ' . $numVisits . ' times before. Our first visit was ' . $firstVis . '.';
            }
        }
    }
    echo $vaText;

    exit;
}

/**
 * Nearest Station
 */

if (isset($_GET['cs'])) {
    $ambiguity = '';
    if (validCoordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
        $usex = $curSys['x'];
        $usey = $curSys['y'];
        $usez = $curSys['z'];
    } else {
        $lastCoords = lastKnownSystem();

        $usex = $lastCoords['x'];
        $usey = $lastCoords['y'];
        $usez = $lastCoords['z'];
        $lastSystem = $lastCoords['name'];

        $add2 = 'I am unable to determine the coordinates of our current location. Our last known location is the ' . ttsOverride($lastSystem) . ' system. ';
        $ambiguity = ' some ';
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
                ORDER BY sqrt(pow((coordx-(" . $usex . ')), 2)+pow((coordy-(' . $usey . ')), 2)+pow((coordz-(' . $usez . ')), 2)),
                -edtb_stations.ls_from_star DESC
                LIMIT 1';

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    echo $add2;

    $csObj = $result->fetch_object();

    $csSystem = ttsOverride($csObj->system_name);
    $csAllegiance = $csObj->allegiance;

    $ssCoordx = $csObj->coordx;
    $ssCoordy = $csObj->coordy;
    $ssCoordz = $csObj->coordz;

    $csDistance = sqrt((($ssCoordx - $usex) ** 2) + (($ssCoordy - $usey) ** 2) + (($ssCoordz - $usez) ** 2));

    $csStationName = $csObj->station_name;
    $csMaxLandingPad_size = $csObj->max_landing_pad_size === 'L' ? 'large' : 'medium';
    $csLsFromStar = $csObj->ls_from_star;
    $csType = $csObj->type;
    $csShipyard = $csObj->shipyard;
    $csOutfitting = $csObj->outfitting;
    $csCommoditiesMarket = $csObj->commodities_market;
    $csBlackMarket = $csObj->black_market;
    $csRefuel = $csObj->refuel;
    $csRepair = $csObj->repair;
    $csRearm = $csObj->rearm;

    $result->close();

    $csFacilities = [
        'a shipyard' => $csShipyard,
        'outfitting' => $csOutfitting,
        'a commodities market' => $csCommoditiesMarket,
        'a black market' => $csBlackMarket,
        'refuel' => $csRefuel,
        'repair' => $csRepair,
        'restock' => $csRearm
    ];

    $count = 0;
    foreach ($csFacilities as $csName => $csIncluded) {
        if ($csIncluded == 1) {
            $count++;
        }
    }

    $csServices = '';
    $i = 0;
    foreach ($csFacilities as $csName => $csIncluded) {
        if ($csIncluded == 1) {
            if ($i == $count-1) {
                $csServices .= ', and ';
            } elseif ($i != 0 && $i != $count-1) {
                $csServices .= ', ';
            } else {
                $csServices .= ', and is equipped with ';
            }

            $csServices .= $csName;
            $i++;
        }
    }

    $article = '';
    if (!empty($csType)) {
        $article = 'a';
        if (preg_match('/([aeiouAEIOU])/', $csType{0})) {
            $article = 'an';
        }
    }

    if ($csDistance == 0) {
        echo 'The nearest spaceport is in this system. ';
    } else {
        echo 'The nearest spaceport is in the ' . $csSystem . ' system, ' . $ambiguity . number_format($csDistance, 1) . ' light years away.';
    }

    echo ' ' . $csStationName;
    if (!empty($csType)) {
        $csType = str_ireplace('Unknown Planetary', 'unknown planetary port', $csType);
        echo ' is ' . $article . ' ' . $csType;
    }
    if ($csLsFromStar != 0) {
        echo ' ' . number_format($csLsFromStar) . ' light seconds away from the main star';
    }

    echo '. It has ' . $csMaxLandingPad_size . ' sized landing pads';

    echo $csServices;

    exit;
}

/**
 * Random Musings
 */

if (isset($_GET['rm'])) {
    $query = "  SELECT id, text
                FROM edtb_musings
                WHERE used = '0'
                ORDER BY rand()
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $obj = $result->fetch_object();

    $rmId = $obj->id;
    $rmText = $obj->text;
    echo $rmText;

    $result->close();

    $query = "  UPDATE edtb_musings
                SET used = '1'
                WHERE id = '$rmId'
                LIMIT 1";

    $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    exit;
}

/**
 * current system short
 */

if (isset($_GET['sys_short'])) {
    $sysShort = 'unknown';
    if (!empty($curSys['name'])) {
        $sysShort = $curSys['name'];
    }

    echo ttsOverride($sysShort);

    exit;
}

/**
 * distance to X
 */

if (isset($_GET['dist'])) {
    $to = $_GET['dist'];

    $distance = '';

    $to = str_replace('system', '', $to);

    if (!validCoordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
        $distance = "How can I calculate distances if I don't even know where we are?";
    } else {
        if (System::exists($to)) {
            $escTo = $mysqli->real_escape_string($to);
            $query = '  SELECT
                        sqrt(pow((IFNULL(edtb_systems.x, user_systems_own.x)-(' . $curSys['x'] . ')),2)+pow((IFNULL(edtb_systems.y, user_systems_own.y)-(' . $curSys['y'] . ')),2)+pow((IFNULL(edtb_systems.z, user_systems_own.z)-(' . $curSys['z'] . ")),2))
                        AS distance
                        FROM edtb_systems
                        LEFT JOIN user_systems_own ON edtb_systems.name = user_systems_own.name
                        WHERE edtb_systems.name = '$escTo'
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $result->fetch_object();

            $distance = $obj->distance === '' ? 'Not available' : number_format($obj->distance, 1);

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

if (isset($_GET['curSys']) || isset($_GET['cSys'])) {
    $search = $_GET['curSys'] ?? $_GET['cSys'];

    $info = '';

    if (array_key_exists($search, $curSys)) {
        $info = $curSys[$search] === '' ? 'None' : $curSys[$search];
    } else {
        $info = '' . $search . ' is not recognised';
    }

    echo $info;

    exit;
}
