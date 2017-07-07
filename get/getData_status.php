<?php
/**
 * Ajax backend file to fetch profile data
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

/** @var string new is the system new */
$new = 'false';
if (isset($_GET['newsys'])) {
    $new = $_GET['newsys'] === 'true' ? 'true' : 'false';
}

/** @var string override override the default minimum time between refreshes */
$override = 'false';
if (isset($_GET['override'])) {
    $override = $_GET['override'] === 'true' ? 'true' : 'false';
}

/** @var string force_update */
$forceUpdate = 'false';
if (isset($_GET['force_update'])) {
    $forceUpdate = $_GET['force_update'] === 'true' ? 'true' : 'false';
}

/** @require api update */
require_once $_SERVER['DOCUMENT_ROOT'] . '/action/updateAPIdata.php';
/** @require config */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';

/**
 * show user status
 */

$data['cmdr_status'] = 'false';
$data['cmdr_balance_status'] = 'false';
if (isset($api['commander']) && $settings['show_cmdr_status'] === 'true') {
    $data['cmdr_status'] = '';
    $data['cmdr_balance_status'] = '';

    if ($api['commander'] !== 'no_data') {
        $cmdrCredits = number_format($api['commander']->{'credits'});

        /**
         * get icons for cmdr ranks
         */
        $cmdrRankCombat = $api['commander']->{'rank'}->{'combat'};
        $cmdrRankCombatIcon = '<a href="#" title="Combat rank: ' . get_rank('combat', $cmdrRankCombat, false) . '">';
        $cmdrRankCombatIcon .= '<img src="' . get_rank('combat', $cmdrRankCombat + 1) . '" alt="combat" class="status_img" style="margin-right: 6px">';
        $cmdrRankCombatIcon .= '</a>';

        $cmdrRankTrade = $api['commander']->{'rank'}->{'trade'};
        $cmdrRankTradeIcon = '<a href="#" title="Trade rank: ' . get_rank('trade', $cmdrRankTrade, false) . '">';
        $cmdrRankTradeIcon .= '<img src="' . get_rank('trade', $cmdrRankTrade + 1) . '" alt="trade" class="status_img" style="margin-right: 6px">';
        $cmdrRankTradeIcon .= '</a>';

        $cmdrRankExplore = $api['commander']->{'rank'}->{'explore'};
        $cmdrRankExploreIcon = '<a href="#" title="Explorer rank: ' . get_rank('explore', $cmdrRankExplore, false) . '">';
        $cmdrRankExploreIcon .= '<img src="' . get_rank('explore', $cmdrRankExplore + 1) . '" alt="explorer" class="status_img">';
        $cmdrRankExploreIcon .= '</a>';

        $cmdrRankCqc = '';
        $cmdrRankCqcIcon = '';

        if ($settings['show_cqc_rank'] === 'true') {
            $cmdrRankCqc = $api['commander']->{'rank'}->{'cqc'};
            $cmdrRankCqcIcon = '<a href="#" title="CQC rank: ' . get_rank('cqc', $cmdrRankCqc, false) . '">';
            $cmdrRankCqcIcon .= '<img src="' . get_rank('cqc', $cmdrRankCqc + 1) . '" class="status_img" alt="cqc" style="margin-right: 6px">';
            $cmdrRankCqcIcon .= '</a>';
        }

        /**
         * additional info
         */
        $cmdrRankFed = $api['commander']->{'rank'}->{'federation'};
        $fedRank = get_rank('federation', $cmdrRankFed, false);

        $cmdrRankEmpire = $api['commander']->{'rank'}->{'empire'};
        $empireRank = get_rank('empire', $cmdrRankEmpire, false);

        $additional = '<div id="cmdr_status_mi" style="display: none">';
        $additional .= '<strong>Federation rank:</strong> ' . $fedRank . '<br>';
        $additional .= '<strong>Empire rank:</strong> ' . $empireRank;
        $additional .= '</div>';

        $data['cmdr_status'] = $cmdrRankCombatIcon . $cmdrRankTradeIcon . $cmdrRankExploreIcon . $cmdrRankCqcIcon . $additional;

        $data['cmdr_balance_status'] = '<img src="/style/img/rare.png" class="balance_pic" alt="Cr">' . $cmdrCredits . ' CR';
    }
}

/**
 * show ship status
 */

$data['ship_status'] = 'false';
if (isset($api['ship']) && $settings['show_ship_status'] === 'true') {
    if ($api['ship'] === 'no_data') {
        $data['ship_status'] = '<a href="/Admin/API_login.php">No data, reconnect API</a>';
    } else {
        /**
         * basic ship info
         */
        $shipName = $api['ship']->{'name'};
        $shipHealth = number_format($api['ship']->{'health'}->{'hull'} / 10000, 1);

        $shipFuel = number_format($api['ship']->{'fuel'}->{'main'}->{'level'} / $api['ship']->{'fuel'}->{'main'}->{'capacity'} * 100, 1);
        $shipCargoCap = $api['ship']->{'cargo'}->{'capacity'};
        $shipCargoUsed = $api['ship']->{'cargo'}->{'qty'};

        /**
         * additional ship info
         */
        $shipValue = number_format($api['ship']->{'value'}->{'total'});
        $shipHullValue = number_format($api['ship']->{'value'}->{'hull'});
        $shipModulesValue = number_format($api['ship']->{'value'}->{'modules'});

        if (isset($api['stored_ships']) && is_array($api['stored_ships'])) {
            $storedShips = '<br><br><strong>Stored ships</strong><br>';
            foreach ($api['stored_ships'] as $shipId => $storedShip) {
                if ($shipId != $api['commander']->{'currentShipId'}) {
                    $shipName = ship_name($storedShip->{'name'});
                    $dockedAtStation = $storedShip->{'station'}->{'name'};
                    $dockedAtSystem = $storedShip->{'starsystem'}->{'name'};

                    $distance = get_distance($dockedAtSystem);

                    $storedShips .= $shipName . ' (' . $distance . ')<br>';
                    $storedShips .= $dockedAtStation . ' at <a href="/System?system_name=' . urlencode($dockedAtSystem) . '">';
                    $storedShips .= $dockedAtSystem . '</a><br><br>';
                }
            }
        }

        $additional = '<div id="ship_status_mi" style="display: none">';
        $additional .= '<strong>Ship value:</strong> ' . $shipValue .' CR<br>';
        $additional .= 'Hull: ' . $shipHullValue . ' CR<br>';
        $additional .= 'Modules: ' . $shipModulesValue . ' CR' . $storedShips;
        $additional .= '</div>';

        $data['ship_status'] = '<img src="/style/img/ship.png" class="icon" alt="Ship hull">' . $shipHealth . ' %';
        $data['ship_status'] .= '<img src="/style/img/fuel.png" class="icon24" style="margin-left: 6px; margin-bottom: 4px" alt="Ship fuel">' . $shipFuel . ' %';
        $data['ship_status'] .= '<img src="/style/img/cargo.png" class="icon24" style="margin-left: 6px" alt="Ship cargo">' . $shipCargoUsed . '/' . $shipCargoCap;
        $data['ship_status'] .= $additional;
    }
}

/**
 * write to cache if changed
 */
$cmdrRanksFile = $_SERVER['DOCUMENT_ROOT'] . '/cache/cmdr_ranks_status.html';
$data['cmdr_ranks_update'] = 'false';
$cmdrRankCache = file_get_contents($cmdrRanksFile);

$cmdrBalanceFile = $_SERVER['DOCUMENT_ROOT'] . '/cache/cmdr_balance_status.html';
$data['cmdr_balance_update'] = 'false';
$cmdrBalanceCache = file_get_contents($cmdrBalanceFile);

$shipStatusFile = $_SERVER['DOCUMENT_ROOT'] . '/cache/ship_status.html';
$data['ship_status_update'] = 'false';
$shipStatusCache = file_get_contents($shipStatusFile);

if ($forceUpdate === 'true') {
    $data['cmdr_ranks_update'] = 'true';
    $data['cmdr_balance_update'] = 'true';
    $data['ship_status_update'] = 'true';
} else {
    if ($cmdrRankCache != $data['cmdr_status']) {
        if (!file_put_contents($cmdrRanksFile, $data['cmdr_status'])) {
            $error = error_get_last();
            write_log('Error: ' . $error['message'], __FILE__, __LINE__);
        }
        $data['cmdr_ranks_update'] = 'true';
    }

    if ($cmdrBalanceCache != $data['cmdr_balance_status']) {
        if (!file_put_contents($cmdrBalanceFile, $data['cmdr_balance_status'])) {
            $error = error_get_last();
            write_log('Error: ' . $error['message'], __FILE__, __LINE__);
        }
        $data['cmdr_balance_update'] = 'true';
    }

    if ($shipStatusCache != $data['ship_status']) {
        if (!file_put_contents($shipStatusFile, $data['ship_status'])) {
            $error = error_get_last();
            write_log('Error: ' . $error['message'], __FILE__, __LINE__);
        }
        $data['ship_status_update'] = 'true';
    }
}
echo json_encode($data);
