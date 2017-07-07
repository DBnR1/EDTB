<?php
/**
 * Functions
 *
 * No description
 *
 * @package EDTB\Backend
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

/** @require config */
require_once __DIR__ . '/config.inc.php';
/** @require MySQL */
require_once __DIR__ . '/MySQL.php';
/** @require other functions */
require_once __DIR__ . '/functions_safe.php';
/** @require curSys */
//require_once("curSys.php"); // can't require curSys here, it interferes with the data update
/** @require mappings */
require_once __DIR__ . '/FDMaps.php';
/** @require utility */
require_once __DIR__ . '/Vendor/utility.php';
/** @require System class */
require_once __DIR__ . '/System.php';

/**
 * Last known system with valid coordinates
 *
 * @param bool $onlyedsm only include EDSM systems
 * @return array $lastSystem x, y, z, name
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function last_known_system($onlyedsm = false): array
{
    global $mysqli;

    if ($onlyedsm !== true) {
        $query = "  SELECT user_visited_systems.system_name,
                    edtb_systems.x, edtb_systems.y, edtb_systems.z,
                    user_systems_own.x AS own_x,
                    user_systems_own.y AS own_y,
                    user_systems_own.z AS own_z
                    FROM user_visited_systems
                    LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                    LEFT JOIN user_systems_own ON user_visited_systems.system_name = user_systems_own.name
                    WHERE edtb_systems.x != '' OR user_systems_own.x != ''
                    ORDER BY user_visited_systems.visit DESC
                    LIMIT 1";
    } else {
        $query = "  SELECT user_visited_systems.system_name,
                    edtb_systems.x, edtb_systems.y, edtb_systems.z
                    FROM user_visited_systems
                    LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                    WHERE edtb_systems.x != ''
                    ORDER BY user_visited_systems.visit DESC
                    LIMIT 1";
    }

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $results = $result->num_rows;
    $lastSystem = [];

    if ($results > 0) {
        $coordObj = $result->fetch_object();
        $lastSystem['name'] = $coordObj->system_name;
        $lastSystem['x'] = $coordObj->x;
        $lastSystem['y'] = $coordObj->y;
        $lastSystem['z'] = $coordObj->z;

        if ($lastSystem['x'] === '') {
            $lastSystem['x'] = $coordObj->own_x;
            $lastSystem['y'] = $coordObj->own_y;
            $lastSystem['z'] = $coordObj->own_z;
        }
    } else {
        $lastSystem['name'] = '';
        $lastSystem['x'] = '';
        $lastSystem['y'] = '';
        $lastSystem['z'] = '';
    }

    $result->close();

    return $lastSystem;
}

/**
 * Check if data is old
 *
 * @param int $time unix timestamp
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function data_is_old($time)
{
    global $settings;

    $old = $settings['data_notify_age'] * 24 * 60 * 60;
    $since = time()-$old;

    if (empty($time)) {
        return false;
    }

    if ($time < $since) {
        return true;
    }

    return false;
}

/**
 * Random insult generator... yes
 *
 * @param string $whoToInsult
 * @return string $insult
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function random_insult($whoToInsult)
{
    $who = explode(' ', $whoToInsult);
    $firstName = $who[0];
    $lastName = $who[1];

    $whoa = [
        $firstName . ' so called ' . $lastName,
        $firstName . ' ' . $lastName
    ];

    /**
     * Insults from museangel.net, katoninetales.com, mandatory.com with some of my own thrown in for good measure
     */
    $pool1 = [
        'moronic',
        'putrid',
        'disgusting',
        'cockered',
        'droning',
        'fobbing',
        'frothy',
        'smelly',
        'infectious',
        'puny',
        'roguish',
        'assinine',
        'tottering',
        'shitty',
        'villainous',
        'pompous',
        'elitist',
        'dirty'
    ];
    $pool2 = [
        'shit-kicking',
        'Federal',
        'butt-munching',
        'clap-ridden',
        'fart-eating',
        'clay-brained',
        'sheep-fucking'
    ];
    $pool3 = [
        'hemorrhoid',
        'assface',
        'whore',
        'kretin',
        'cumbucket',
        'fuckface',
        'asshole',
        'turd',
        'taint',
        'knob',
        'tit',
        'shart',
        'douche'
    ];

    // randomize
    shuffle($pool1);
    shuffle($pool2);
    shuffle($pool3);
    shuffle($whoa);

    $insult = 'the ' . $pool1[0] . ' ' . $pool2[0] . ' ' . $pool3[0] . ' ' . $whoa[0];

    return $insult;
}

/**
 * Return the correct starport icon
 *
 * @param string $type starport type
 * @param string $planetary 0|1
 * @param string $style overrides the style
 * @return string $stationIcon html img tag for the starport icon
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_station_icon($type, $planetary = '0', $style = '')
{
    switch ($type) {
        case 'Coriolis Starport':
            $stationIcon = '<img src="/style/img/spaceports/coriolis.png" class="icon" alt="' . $type . '" style="' . $style . '">';
            break;
        case 'Orbis Starport':
            $stationIcon = '<img src="/style/img/spaceports/orbis.png" class="icon" alt="' . $type . '" style="' . $style . '">';
            break;
        case 'Ocellus Starport':
            $stationIcon = '<img src="/style/img/spaceports/ocellus.png" class="icon" alt="' . $type . '" style="' . $style . '">';
            break;
        case ($planetary == '0'):
            $stationIcon = '<img src="/style/img/spaceports/spaceport.png" class="icon" alt="Starport" style="' . $style . '">';
            break;
        case ($planetary == '1'):
            $stationIcon = '<img src="/style/img/spaceports/planetary.png" class="icon" alt="Planetary" style="' . $style . '">';
            break;
        default:
            $stationIcon = '<img src="/style/img/spaceports/unknown.png" class="icon" alt="Unknown" style="' . $style . '">';
    }

    return $stationIcon;
}

/**
 * Return the correct allegiance icon
 *
 * @param string $allegiance
 * @return string $allegianceIcon name of allegiance icon
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_allegiance_icon($allegiance)
{
    switch ($allegiance) {
        case 'Empire':
            $allegianceIcon = 'empire.png';
            break;
        case 'Alliance':
            $allegianceIcon = 'alliance.png';
            break;
        case 'Federation':
            $allegianceIcon = 'federation.png';
            break;
        default:
            $allegianceIcon = 'system.png';
    }

    return $allegianceIcon;
}

/**
 * Return usable coordinates
 *
 * @return array of floats x, y, z and bool current
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function usable_coords()
{
    global $curSys;

    $usable = [];

    if (valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
        $usable['x'] = $curSys['x'];
        $usable['y'] = $curSys['y'];
        $usable['z'] = $curSys['z'];

        $usable['current'] = true;
    } else {
        $lastCoords = last_known_system();

        $usable['x'] = $lastCoords['x'];
        $usable['y'] = $lastCoords['y'];
        $usable['z'] = $lastCoords['z'];

        $usable['current'] = false;
    }

    if (!valid_coordinates($usable['x'], $usable['y'], $usable['z'])) {
        $usable['x'] = '0';
        $usable['y'] = '0';
        $usable['z'] = '0';

        $usable['current'] = false;
    }
    return $usable;
}

/**
 * Validate coordinates
 *
 * @param float $x
 * @param float $y
 * @param float $z
 * @return bool
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function valid_coordinates($x, $y, $z)
{
    if (is_numeric($x) && is_numeric($y) && is_numeric($z)) {
        return true;
    }

    return false;
}

/**
 * Replace output text for text-to-speech overrides
 *
 * @param string $text
 * @return string $text
 * @author Travis @ https://github.com/padthaitofuhot
 */
function tts_override($text)
{
    global $settings;

    if (is_array($settings['tts_override'])) {
        foreach ($settings['tts_override'] as $find => $replace) {
            $text = str_ireplace($find, $replace, $text);
        }
    }

    return $text;
}

/**
 * Return rank icon/name
 *
 * @param string $type
 * @param string $rank
 * @param bool $icon
 * @return string path to icon or rank name
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_rank($type, $rank, $icon = true)
{
    global $ranks;

    if ($icon !== false) {
        return '/style/img/ranks/' . $type . '/rank-' . $rank . '.png';
    }

    return $ranks[$type][$rank];
}

/**
 * Return proper ship name
 *
 * @param string $name
 * @return string $shipName
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function ship_name($name)
{
    global $ships;

    $shipName = $name;
    if (array_key_exists(strtolower($name), $ships)) {
        $shipName = $ships[strtolower($name)];
    }

    return $shipName;
}

/**
 * Return distance from current to $system
 *
 * @param string|int $system
 * @return string $distance
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function get_distance($system)
{
    global $mysqli;

    /**
     * fetch target coordinates
     */
    $escSys = $mysqli->real_escape_string($system);

    $query = "  (SELECT
                edtb_systems.x AS target_x,
                edtb_systems.y AS target_y,
                edtb_systems.z AS target_z
                FROM edtb_systems
                WHERE edtb_systems.name = '$escSys')
                UNION
                (SELECT
                user_systems_own.x AS target_x,
                user_systems_own.y AS target_y,
                user_systems_own.z AS target_z
                FROM user_systems_own
                WHERE user_systems_own.name = '$escSys')
                LIMIT 1";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    $obj = $result->fetch_object();

    $targetX = $obj->target_x;
    $targetY = $obj->target_y;
    $targetZ = $obj->target_z;

    $result->close();

    // figure out what coords to calculate from
    $usableCoords = usable_coords();
    $usex = $usableCoords['x'];
    $usey = $usableCoords['y'];
    $usez = $usableCoords['z'];
    $exact = $usableCoords['current'] === true ? '' : ' *';

    if (valid_coordinates($targetX, $targetY, $targetZ)) {
        $dist = number_format(sqrt((($targetX - $usex) ** 2) + (($targetY - $usey) ** 2) + (($targetZ - $usez) ** 2)), 2);
        $distance = $dist . ' ly' . $exact;
    } else {
        $distance = '';
    }

    return $distance;
}

/**
 * Fetch or update data from edtb_common
 *
 * @param string $name
 * @param string $field
 * @param bool $update
 * @param string $value
 * @return string|null $value if $update = false
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function edtb_common($name, $field, $update = false, $value = '')
{
    global $mysqli;

    if ($update !== true) {
        $query = '  SELECT ' . $field . "
                    FROM edtb_common
                    WHERE name = '$name'
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $obj = $result->fetch_object();

        $value = $obj->{$field};

        $result->close();

        return $value;
    }

    $escVal = $mysqli->real_escape_string($value);
    $stmt = '   UPDATE edtb_common
                SET ' . $field . " = '$escVal'
                WHERE name = '$name'
                LIMIT 1";

    $mysqli->query($stmt) or write_log($mysqli->error, __FILE__, __LINE__);

    return null;
}

/**
 * Remove invalid dos characters
 *
 * @param string $sourceString directory/file name to check for invalid chars
 * @return string $retValue
 * @author David Marshall
 */
function strip_invalid_dos_chars($sourceString)
{
    $invalidChars = array('*','\\','/',':','?','"','<','>','|'); // Invalid chars according to Windows 10

    return str_replace($invalidChars, '_', $sourceString);
}
