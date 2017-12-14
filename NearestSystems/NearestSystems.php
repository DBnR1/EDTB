<?php
/**
 * Nearest systems & stations class
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

use \EDTB\source\System;

/**
 * Display nearest systems
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class NearestSystems
{
    /** @var string $system the system to use as a starting point */
    public $system;

    /** @var float $useX , $usey, $usez x, y and z coords to use for calculations */
    public $useX, $useY, $useZ;

    /** @var string $powerParams parameters to add to Power links */
    private $powerParams = '';

    /** @var string $allegianceParams parameters to add to Allegiance links */
    private $allegianceParams = '';

    /** @var string $text the info text */
    private $text = 'Nearest';

    /** @var string $addToQuery */
    private $addToQuery = '';

    /** @var string $hiddenInputs */
    private $hiddenInputs = '';

    /** @var bool $stations */
    private $stations = true;

    /** @var string $mainQuery */
    private $mainQuery;

    /**
     * NearestSystems constructor.
     */
    public function __construct()
    {
        global $server, $user, $pwd, $db;

        /**
         * connect to database
         */
        $this->mysqli = new mysqli($server, $user, $pwd, $db);

        if ($this->mysqli->connect_errno) {
            echo 'Failed to connect to MySQL: ' . $this->mysqli->connect_error;
        }

        /**
         * determine what coordinates to use
         */
        $this->system = isset($_GET['system']) ? $_GET['system'] + 0 : '';

        if (!empty($this->system)) {
            $query = "  SELECT name, id, x, y, z
                        FROM edtb_systems
                        WHERE id = '$this->system'
                        LIMIT 1";

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
            $sysObj = $result->fetch_object();

            $sysName = $sysObj->name;
            $sysId = $sysObj->id;

            $this->useX = $sysObj->x;
            $this->useY = $sysObj->y;
            $this->useZ = $sysObj->z;

            $result->close();

            $this->text .= ' (to <a href="/System?system_id=' . $sysId . '">' . $sysName . '</a>) ';
            $this->powerParams .= '&system=' . $this->system;
            $this->allegianceParams .= '&system=' . $this->system;
            $this->hiddenInputs .= '<input type="hidden" name="system" value="' . $sysId . '">';
        } elseif (validCoordinates($curSys['x'], $curSys['y'], $curSys['z']) && empty($this->system)) {
            $this->useX = $curSys['x'];
            $this->useY = $curSys['y'];
            $this->useZ = $curSys['z'];
        } else {
            // get last known coordinates
            $lastCoords = lastKnownSystem();

            $this->useX = $lastCoords['x'];
            $this->useY = $lastCoords['y'];
            $this->useZ = $lastCoords['z'];

            $this->is_unknown = ' *';
        }

        /**
         * If we still don't have valid coordinates, center on Sol
         */
        if (!validCoordinates($this->useX, $this->useY, $this->useZ)) {
            $this->useX = '0';
            $this->useY = '0';
            $this->useZ = '0';

            $this->is_unknown = ' *';
        }
    }

    /**
     *
     * @return string
     */
    public function nearest()
    {
        $this->getQueryParams();

        $this->getQuery();

        $this->filters();

        $this->content();
    }

    /**
     * get parameters for the main sql query based on url parameters
     */
    private function getQueryParams()
    {
        /**
         * get url parameters
         */
        $facility = $_GET['facility'] ?? '';
        $only = $_GET['allegiance'] ?? '';
        $systemAllegiance = $_GET['system_allegiance'] ?? '';
        $power = $_GET['power'] ?? '';
        $pad = $_GET['pad'] ?? '';
        $stationType = $_GET['station_type'] ?? '';

        /**
         * specific power
         */
        if ($power !== '') {
            $this->stations = false;

            $this->addToQuery .= " AND edtb_systems.power = '$power'";
            $this->text .= ' ' . $power . ' systems';
            $this->hiddenInputs .= '<input type="hidden" name="power" value="' . $power . '">';
            $this->powerParams .= '&power=' . urlencode($power);
            $this->allegianceParams .= '&power=' . urlencode($power);
        }

        /**
         * specific station allegiance
         */
        if ($only !== '') {
            $this->stations = true;

            if ($only !== 'all') {
                $this->addToQuery .= " AND edtb_stations.allegiance = '$only'";
            } else {
                $this->addToQuery .= " AND edtb_stations.allegiance = 'None'";
            }

            if ($only !== 'all' && $only !== 'Independent') {
                $this->text .= ' systems with ' . $only . ' controlled stations';
            } elseif ($only === 'Independent') {
                $this->text .= ' systems with Independent stations';
            } else {
                $this->text .= ' systems with non-allied stations';
            }

            $this->hiddenInputs .= '<input type="hidden" name="allegiance" value="' . $only . '">';
            $this->powerParams .= '&allegiance=' . $only;
        }

        /**
         * specific system allegiance
         */
        if ($systemAllegiance !== '') {
            $this->stations = false;

            $this->addToQuery .= " AND edtb_systems.allegiance = '$systemAllegiance'";
            $this->text .= ' ' . str_replace('None', 'Non-allied', $systemAllegiance) . ' systems';
            $this->hiddenInputs .= '<input type="hidden" name="system_allegiance" value="' . $systemAllegiance . '">';
            $this->powerParams .= '&system_allegiance=' . $systemAllegiance;
        }

        /**
         * if we're searching facilities
         */
        if (!empty($facility)) {
            $this->stations = true;

            $this->addToQuery .= ' AND edtb_stations.' . $facility . " = '1'";

            $escFacility = $this->mysqli->real_escape_string($facility);
            $query = "  SELECT name
                        FROM edtb_facilities
                        WHERE code = '$escFacility'
                        LIMIT 1";

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

            $fObj = $result->fetch_object();
            $fName = $fObj->name;

            $result->close();

            $article = 'a';
            if (preg_match('/([aeiouAEIOU])/', $fName{0})) {
                $article = 'an';
            }

            $this->text .= ' stations with ' . $article . ' ' . $fName . ' facility';
            $this->hiddenInputs .= '<input type="hidden" name="facility" value="' . $facility . '">';
            $this->powerParams .= '&facility=' . $facility;
            $this->allegianceParams .= '&facility=' . $facility;
        }

        /**
         * landing pad size
         */
        if ($pad !== '') {
            $this->stations = true;

            $this->addToQuery .= " AND edtb_stations.max_landing_pad_size = '$pad'";
            $padText = $pad === 'L' ? 'Large' : 'Medium';
            $this->text .= '  stations with ' . $padText . ' sized landing pads';
            $this->hiddenInputs .= '<input type="hidden" name="pad" value="' . $pad . '">';
            $this->powerParams .= '&pad=' . $pad;
            $this->allegianceParams .= '&pad=' . $pad;
        }

        /**
         * station type
         */
        if ($stationType !== '') {
            $this->stations = true;

            switch ($stationType) {
                case 'planetary':
                    $this->addToQuery .= " AND edtb_stations.is_planetary = '1'";
                    $this->text .= ' (planetary only)';
                    break;
                case 'space':
                    $this->addToQuery .= " AND edtb_stations.is_planetary = '0'";
                    $this->text .= ' (space ports only)';
                    break;
                case 'all':
                    $this->addToQuery .= '';
                    $this->text .= '';
                    break;
                default:
                    $this->addToQuery .= '';
                    $this->text .= '';
            }

            $this->hiddenInputs .= '<input type="hidden" name="station_type" value="' . $stationType . '">';
            $this->powerParams .= '&station_type=' . $stationType;
            $this->allegianceParams .= '&station_type=' . $stationType;
        }
    }

    /**
     * determine the MySQL query to use
     */
    private function getQuery()
    {
        /**
         * get url parameters
         */
        $shipName = $_GET['ship_name'] ?? '';
        $groupId = $_GET['group_id'] ?? '';
        /**
         * nearest stations....
         */
        if ($this->stations !== false) {
            $this->mainQuery = "   SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
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
                                    LEFT JOIN edtb_systems ON edtb_stations.system_id = edtb_systems.id
                                    WHERE edtb_systems.x != ''" . $this->addToQuery . '
                                    ORDER BY sqrt(pow((coordx-(' . $this->useX . ')), 2)+pow((coordy-(' . $this->useY .
                ')), 2)+pow((coordz-(' . $this->useZ . ')), 2)),
                                    -edtb_stations.ls_from_star DESC
                                    LIMIT 10';
        } /**
         * ...or nearest systems
         */ else {
            $this->mainQuery = "   SELECT edtb_systems.name AS system, edtb_systems.allegiance,
                                    edtb_systems.id AS system_id,
                                    edtb_systems.x AS coordx,
                                    edtb_systems.y AS coordy,
                                    edtb_systems.z AS coordz,
                                    edtb_systems.population,
                                    edtb_systems.government,
                                    edtb_systems.security,
                                    edtb_systems.economy
                                    FROM edtb_systems
                                    WHERE edtb_systems.x != ''" . $this->addToQuery . '
                                    ORDER BY sqrt(pow((coordx-(' . $this->useX . ')), 2)+pow((coordy-(' . $this->useY .
                ')), 2)+pow((coordz-(' . $this->useZ . ')), 2))
                                    LIMIT 10';
        }

        /**
         * if we're searching modules
         */
        if (!empty($groupId)) {
            $class = $_GET['class'] ?? '';
            $rating = $_GET['rating'] ?? '';

            $classAdd = '';
            if ($class !== '' && $class != '0') {
                $classAdd = " AND class = '" . $_GET['class'] . "'";
            }

            $ratingAdd = '';
            if ($rating !== '' && $rating != '0') {
                $ratingAdd = " AND rating = '" . $_GET['rating'] . "'";
            }

            $query = "  SELECT group_name
                        FROM edtb_modules
                        WHERE group_id = '$groupId'
                        LIMIT 1";

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
            $gObj = $result->fetch_object();

            $groupName = $gObj->group_name;
            $groupName = substr($groupName, -1) === 's' ? $groupName : '' . $groupName . 's';

            $result->close();

            if (!empty($rating)) {
                $ratings = ' ' . $_GET['rating'] . ' rated ';
                $this->hiddenInputs .= '<input type="hidden" name="rating" value="' . $rating . '">';
                $this->powerParams .= '&rating=' . $rating;
            }

            if (!empty($class)) {
                $classes = ' class ' . $_GET['class'] . ' ';
                $this->hiddenInputs .= '<input type="hidden" name="class" value="' . $class . '">';
                $this->powerParams .= '&class=' . $class;
            }

            if (!empty($class) && !empty($rating)) {
                $query = "  SELECT price
                        FROM edtb_modules
                        WHERE group_id = '$groupId'
                        AND rating = '$rating'
                        AND class = '$class'
                        LIMIT 1";

                $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
                $pObj = $result->fetch_object();

                $modulesPrice = number_format($pObj->price);
                $price = ' (normal price ' . $modulesPrice . ' CR) ';

                $result->close();
            }

            $this->text .= ' stations selling ' . $ratings . $classes . $groupName . $price;
            $this->hiddenInputs .= '<input type="hidden" name="group_id" value="' . $groupId . '">';
            $this->powerParams .= '&group_id=' . $groupId;
            $this->allegianceParams .= '&group_id=' . $groupId;

            $query = "  SELECT id
                        FROM edtb_modules
                        WHERE group_id = '$groupId'" . $classAdd . $ratingAdd . '
                        LIMIT 1';

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

            $modCount = $result->num_rows;

            if ($modCount > 0) {
                $moduleObj = $result->fetch_object();
                $modulesId = $moduleObj->id;

                $this->mainQuery = "   SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
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
                                        LEFT JOIN edtb_systems ON edtb_stations.system_id = edtb_systems.id
                                        WHERE edtb_systems.x != ''
                                        AND edtb_stations.selling_modules LIKE '-%" . $modulesId . "%-'" . $this->addToQuery . '
                                        ORDER BY sqrt(pow((coordx-(' . $this->useX . ')), 2)+pow((coordy-(' . $this->useY .
                    ')), 2)+pow((coordz-(' . $this->useZ . ')), 2))
                                        LIMIT 10';

                $this->stations = true;
                $result->close();
            }
        }

        /**
         * if we're searching ships
         */
        if (!empty($shipName)) {
            $this->mainQuery = "   SELECT edtb_stations.system_id AS system_id, edtb_stations.name AS station_name,
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
                                    LEFT JOIN edtb_systems ON edtb_stations.system_id = edtb_systems.id
                                    WHERE edtb_systems.x != ''
                                    AND edtb_stations.selling_ships LIKE '%\'" . $shipName . "\'%'" . $this->addToQuery . '
                                    ORDER BY sqrt(pow((coordx-(' . $this->useX . ')), 2)+pow((coordy-(' . $this->useY .
                ')), 2)+pow((coordz-(' . $this->useZ . ')), 2))
                                    LIMIT 10';

            $escShipName = $this->mysqli->real_escape_string($shipName);
            $query = "  SELECT price
                        FROM edtb_ships
                        WHERE name = '$escShipName'";

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

            $priceObj = $result->fetch_object();

            $shipPrice = number_format($priceObj->price);

            if (isset($priceObj->price)) {
                $sPrice = ' (normal price ' . $shipPrice . ' CR)';
            }

            $result->close();

            $this->stations = true;
            $this->text .= ' stations selling the ' . $shipName . $sPrice;
            $this->hiddenInputs .= '<input type="hidden" name="ship_name" value="' . $shipName . '">';
            $this->powerParams .= '&ship_name=' . $shipName;
            $this->allegianceParams .= '&ship_name=' . $shipName;
        }
    }

    /**
     * display the filters at the top of the page
     */
    private function filters()
    {
        ?>
        <div class="stationinfo_ns" id="si_statinfo"></div>
        <div class="info" id="sysinfo" style="position: fixed">
            Send system name to Elite Dangerous client.<br/>
            Have the textbox in the Galaxy Map targeted before clicking.
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
                <td class="transparent" style="vertical-align: top; width:20%;white-space: nowrap">
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?allegiance=Empire<?= $this->allegianceParams ?>" title="Empire">
                        <img src="/style/img/empire.png" class="allegiance_icon" alt="Empire"/>
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?allegiance=Alliance<?= $this->allegianceParams ?>" title="Alliance">
                        <img src="/style/img/alliance.png" class="allegiance_icon" alt="Alliance"/>
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?allegiance=Federation<?= $this->allegianceParams ?>"
                       title="Federation">
                        <img src="/style/img/federation.png" class="allegiance_icon" alt="Federation"/>
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?allegiance=Independent<?= $this->allegianceParams ?>"
                       title="Independent">
                        <img src="/style/img/system.png" class="allegiance_icon" alt="Independent"/>
                    </a>
                    <!-- search systems and stations-->
                    <div style="text-align: left">
                        <div style="width: 180px; margin-top: 35px">
                            <input class="textbox" type="text" name="system_name" placeholder="System (optional)"
                                   id="system_21" style="width: 180px"
                                   oninput="showResult(this.value, '11', 'no', 'no', 'yes')"/><br/>
                            <input class="textbox" type="text" name="station_name" placeholder="Station (optional)"
                                   id="station_21" style="width: 180px"
                                   oninput="showResult(this.value, '12', 'no', 'yes', 'yes')"/>
                            <div class="suggestions" id="suggestions_11"
                                 style="margin-left: 0; margin-top:-36px; min-width: 168px"></div>
                            <div class="suggestions" id="suggestions_12"
                                 style="margin-left: 0; min-width: 168px"></div>
                        </div>
                    </div>
                </td>
                <!-- allegiances -->
                <td class="transparent" style="vertical-align: top; width:20%; white-space: nowrap">
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?system_allegiance=Empire<?= $this->allegianceParams ?>"
                       title="Empire">
                        <img src="/style/img/empire.png" class="allegiance_icon" alt="Empire"/>
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?system_allegiance=Alliance<?= $this->allegianceParams ?>"
                       title="Alliance">
                        <img src="/style/img/alliance.png" class="allegiance_icon" alt="Alliance"/>
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?system_allegiance=Federation<?= $this->allegianceParams ?>"
                       title="Federation">
                        <img src="/style/img/federation.png" class="allegiance_icon" alt="Federation"/>
                    </a>&nbsp;
                    <a data-replace="true" data-target="#nscontent"
                       href="/NearestSystems/?system_allegiance=None<?= $this->allegianceParams ?>"
                       title="None allied">
                        <img src="/style/img/system.png" class="allegiance_icon" alt="None allied"/>
                    </a>
                    <br/><br/>
                </td>
                <!-- powers -->
                <td class="transparent" style="vertical-align: top; width:20%; white-space: nowrap">
                    <?php
                    $query = 'SELECT name FROM edtb_powers ORDER BY name';
                    $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

                    while ($powerObj = $result->fetch_object()) {
                        $powerName = $powerObj->name;

                        if (isset($power)) {
                            $this->powerParams = str_replace('&power=', '', $this->powerParams);
                            $this->powerParams = str_replace('?power=', '', $this->powerParams);
                            $this->powerParams = str_replace(urlencode($power), '', $this->powerParams);
                        }
                        echo '<a data-replace="true" data-target="#nscontent" href="/NearestSystems/?power=' .
                            urlencode($powerName) . $this->powerParams . '" title="' . $powerName . '">' . $powerName .
                            '</a><br>';
                    }

                    $result->close();
                    ?>
                </td>
                <!-- modules -->
                <td class="transparent" style="vertical-align: top; width:20%;white-space: nowrap">
                    <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>" name="go">
                        <?php
                        echo $this->hiddenInputs;
                        if (isset($groupId) && $groupId != '0') {
                            $modi = " AND group_id = '$groupId'";
                        }
                        ?>
                        <select title="Module" class="selectbox" name="group_id" style="width: 222px"
                                onchange="getCR($('select[name=group_id]').val(), '')">
                            <optgroup label="Module">
                                <option value="0">Module</option>
                                <?php
                                $query = '  SELECT DISTINCT group_id, group_name, category_name
                                            FROM edtb_modules
                                            ORDER BY category_name, group_name';

                                $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

                                $curCat = '';
                                while ($modObj = $result->fetch_object()) {
                                    $catName = $modObj->category_name;

                                    if ($curCat !== $catName) {
                                        echo '</optgroup><optgroup label="' . $catName . '">';
                                    }

                                    $selected = $_GET['group_id'] == $modObj->group_id ? " selected='selected'" : '';
                                    echo '<option value="' . $modObj->group_id . '"' . $selected . '>' . $modObj->group_name .
                                        '</option>';

                                    $curCat = $catName;
                                }

                                $result->close();
                                ?>
                        </select><br/>
                        <select title="Class" class="selectbox" name="class" style="width: 222px" id="class"
                                onchange="getCR($('select[name=group_id]').val(), $('select[name=class]').val())">
                            <option value="0">Class</option>
                            <?php
                            $query = "  SELECT DISTINCT class
                                        FROM edtb_modules WHERE class != ''" . $modi . '
                                        ORDER BY class';

                            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

                            while ($modObj = $result->fetch_object()) {
                                $selected = $_GET['class'] == $modObj->class ? " selected='selected'" : '';
                                echo '<option value="' . $modObj->class . '"' . $selected . '>Class ' . $modObj->class .
                                    '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br/>
                        <select title="Rating" class="selectbox" name="rating" style="width: 222px" id="rating">
                            <option value="0">Rating</option>
                            <?php
                            $query = "  SELECT DISTINCT rating
                                        FROM edtb_modules
                                        WHERE rating != ''" . $modi . '
                                        ORDER BY rating';

                            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

                            while ($modObj = $result->fetch_object()) {
                                $selected = $_GET['rating'] == $modObj->rating ? " selected='selected'" : '';
                                echo '<option value="' . $modObj->rating . '"' . $selected . '>Rating ' . $modObj->rating .
                                    '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br/>
                        <input class="button" type="submit" value="Search" style="width: 222px; margin-top: 5px"/>
                    </form>
                </td>
                <!-- ships & facilities -->
                <td class="transparent" style="vertical-align: top; width:20%;white-space: nowrap">
                    <!-- ships -->
                    <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>" name="go" id="ships"
                          data-push="true" data-target="#nscontent" data-include-blank-url-params="true"
                          data-optimize-url-params="false">
                        <?php
                        echo $this->hiddenInputs;
                        ?>
                        <select title="Ship" class="selectbox" name="ship_name" style="width: 180px"
                                onchange="$('.se-pre-con').show();this.form.submit()">
                            <option value="0">Sells Ships</option>
                            <?php
                            $query = 'SELECT name FROM edtb_ships ORDER BY name';
                            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

                            while ($shipObj = $result->fetch_object()) {
                                $selected = $_GET['ship_name'] === $shipObj->name ? " selected='selected'" : '';
                                echo '<option value="' . $shipObj->name . '"' . $selected . '>' . $shipObj->name . '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br/>
                    </form>
                    <!-- facilities -->
                    <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>" name="go" id="facilities"
                          data-push="true" data-target="#nscontent" data-include-blank-url-params="true"
                          data-optimize-url-params="false">
                        <?php
                        echo $this->hiddenInputs;
                        ?>
                        <select title="Facility" class="selectbox" name="facility" style="width: 180px"
                                onchange="$('.se-pre-con').show();this.form.submit()">
                            <option value="0">Has Facilities</option>
                            <?php
                            $query = 'SELECT name, code FROM edtb_facilities ORDER BY name';
                            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

                            while ($facilityObj = $result->fetch_object()) {
                                $selected = $_GET['facility'] == $facilityObj->code ? " selected='selected'" : '';
                                echo '<option value="' . $facilityObj->code . '"' . $selected . '>' . $facilityObj->name .
                                    '</option>';
                            }

                            $result->close();
                            ?>
                        </select><br/>
                    </form>
                    <!-- landing pads -->
                    <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>" name="go" id="landingpads"
                          data-push="true" data-target="#nscontent" data-include-blank-url-params="true"
                          data-optimize-url-params="false">
                        <?php
                        echo $this->hiddenInputs;
                        ?>
                        <select title="Landing pad" class="selectbox" name="pad" style="width: 180px"
                                onchange="$('.se-pre-con').show();this.form.submit()">
                            <?php
                            $selectedL = $_GET['pad'] === 'L' ? ' selected="selected"' : '';
                            $selectedM = $_GET['pad'] === 'M' ? ' selected="selected"' : '';
                            ?>
                            <option value="">Landing Pad Size</option>
                            <option value="L"<?= $selectedL ?>>Large</option>
                            <option value="M"<?= $selectedM ?>>Medium</option>
                            <option value="">All</option>
                        </select><br/>
                    </form>
                    <!-- station type -->
                    <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>" name="go" id="stationtype"
                          data-push="true" data-target="#nscontent" data-include-blank-url-params="true"
                          data-optimize-url-params="false">
                        <?php
                        echo $this->hiddenInputs;
                        ?>
                        <select title="Station type" class="selectbox" name="station_type" style="width: 180px"
                                onchange="$('.se-pre-con').show();this.form.submit()">
                            <?php
                            $selectedP = $_GET['station_type'] === 'planetary' ? ' selected="selected"' : '';
                            $selectedS = $_GET['station_type'] === 'space' ? ' selected="selected"' : '';
                            $selectedA = $_GET['station_type'] === 'all' ? ' selected="selected"' : '';
                            ?>
                            <option value="all">Station Type</option>
                            <option value="planetary"<?= $selectedP ?>>Planetary</option>
                            <option value="space"<?= $selectedS ?>>Space</option>
                            <option value="all"<?= $selectedA ?>>All</option>
                        </select><br/>
                    </form>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     *
     */
    private function content()
    {
        $mainResult = $this->mysqli->query($this->mainQuery) or write_log($this->mysqli->error, __FILE__, __LINE__);

        $this->text = $this->text === 'Nearest' ? 'Nearest stations' : $this->text;

        if (strpos($this->text, 'Nearest (to') === 0 && $this->stations === true) {
            $this->text = str_replace('Nearest ', 'Nearest stations ', $this->text);
        }

        /**
         * replace all but the first occurance of "key" with "value"
         */
        $replaces = [
            'stations' => '',
            'selling' => 'and',
            'with' => 'and'
        ];

        foreach ($replaces as $replace => $with) {
            $pos = strpos($this->text, $replace);
            if ($pos !== false) {
                $this->text = substr($this->text, 0, $pos + 1) . str_replace($replace, $with, substr($this->text, $pos + 1));
            }
        }

        /**
         * replace all but the last occurance of "systems"
         */
        $pos = substr_count($this->text, 'systems');
        if ($pos > 1) {
            $this->text = preg_replace('/\.(\s|$)/', 'systems$1', $this->text);
            $this->text = substr_replace($this->text, '', strpos($this->text, 'systems'), 7);
        }
        ?>
        <table id="nscontent" style="margin-left:5px;margin-bottom:20px;width:100%">
            <tr>
                <td class="transparent" colspan="5">
                    <header><h2><img class="icon" src="/style/img/find.png" alt="Find"/><?= $this->text ?></h2></header>
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="ns_nearest" colspan="5">
                    <table id="nearest_systems">
                        <tr>
                            <td class="heading" colspan="7"><strong>System</strong></td>
                            <?php
                            if ($this->stations !== false) {
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
                            if ($this->stations !== false) {
                                ?>
                                <td class="dark"><strong>Name</strong></td>
                                <td class="dark"><strong>LS From Star</strong></td>
                                <td class="dark"><strong>Landing Pad</strong></td>
                                <?php
                            }

                            $this->results($mainResult);
                            ?>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <script>
            $("a.send").click(function() {
                $.get("/action/shipControls.php?send=" + $(this).data("send"));
            });
        </script>
        <?php
    }

    /**
     * Display the results
     *
     * @param mysqli_result $mainResult
     */
    private function results($mainResult)
    {
        $count = $mainResult->num_rows;

        if ($count > 0) {
            $lastSystem = '';
            $ii = 0;
            $tdclass = '';
            while ($obj = $mainResult->fetch_object()) {
                $system = $obj->system;
                $systemId = $obj->system_id;
                $sysPopulation = number_format($obj->population);
                $sysEconomy = empty($obj->economy) ? 'n/a' : $obj->economy;
                $sysGovernment = $obj->government;
                $sysSecurity = empty($obj->security) ? 'None' : $obj->security;
                $allegiance = $obj->allegiance;

                $stationName = $obj->station_name;

                /**
                 * provide crosslinks to screenshot gallery, log page, etc
                 */
                $nsCrosslinks = System::crosslinks($system);

                $ssCoordx = $obj->coordx;
                $ssCoordy = $obj->coordy;
                $ssCoordz = $obj->coordz;

                $distance =
                    sqrt((($ssCoordx - $this->useX) ** 2) + (($ssCoordy - $this->useY) ** 2) + (($ssCoordz - $this->useZ) ** 2));

                /**
                 * get allegiance icon for system
                 */
                $pic = getAllegianceIcon($allegiance);

                if ($system !== $lastSystem) {
                    $tdclass = $tdclass === 'light' ? 'dark' : 'light';
                    ?>
                    <tr>
                    <td class="<?= $tdclass ?>" style="text-align: center">
                        <img src="/style/img/<?= $pic ?>" class="allegiance_icon"
                             alt="<?= $allegiance ?>" style="margin: 0"/>
                    </td>
                    <td class="<?= $tdclass ?>">
                        <?= number_format($distance, 2) ?> ly<?= $this->is_unknown ?>
                    </td>
                    <td class="<?= $tdclass ?>">
                        <a class="send" href="javascript:void(0)" data-send="<?= $system ?>"
                           data-id="<?= $systemId ?>">
                            <img class="icon" src="/style/img/magic.png" alt="Send"
                                 style="margin-bottom: 7px; margin-right: 0"
                                 onmouseover="to_view('sysinfo', event)"
                                 onmouseout="$('#sysinfo').fadeToggle('fast')"/>
                        </a>
                        <a href="/System?system_id=<?= $systemId ?>">
                            <?= $system ?>
                        </a>
                        <?= $nsCrosslinks ?>
                    </td>
                    <td class="<?= $tdclass ?>"><?= $sysPopulation ?></td>
                    <td class="<?= $tdclass ?>"><?= $sysEconomy ?></td>
                    <td class="<?= $tdclass ?>"><?= $sysGovernment ?></td>
                    <td class="<?= $tdclass ?>"><?= $sysSecurity ?></td>
                    <?php
                } else {
                    ?>
                    <tr>
                    <td class="transparent" colspan="7" style="height: 45px">&nbsp;</td>
                    <?php
                }

                /**
                 * display station info if necessary
                 */
                if (!empty($stationName)) {
                    $this->stationInfo($stationName, $obj, $tdclass);
                }
                ?>
                </tr>
                <?php
                $lastSystem = $system;
                $ii++;
            } // end of while
        } else {
            $colspan = $this->stations !== false ? '10' : '7';
            ?>
            <tr>
                <td class="light" colspan="<?= $colspan ?>">None found!</td>
            </tr>
            <?php
        }
    }

    /**
     * Show info about stations
     *
     * @param string $stationName
     * @param object $obj
     * @param string $tdclass
     */
    private function stationInfo($stationName, $obj, $tdclass)
    {
        $stationLsFromStar = $obj->ls_from_star == 0 ? 'n/a' : number_format($obj->ls_from_star);
        $stationMaxLandingPad_size = $obj->max_landing_pad_size;
        $stationMaxLandingPad_size = $stationMaxLandingPad_size === 'L' ? 'Large' : 'Medium';
        $stationIsPlanetary = $obj->is_planetary;
        $stationType = $obj->type;

        $icon = getStationIcon($stationType, $stationIsPlanetary);

        $stationId = $obj->station_id;
        $stationFaction = $obj->station_faction === '' ? '' : '<strong>Faction:</strong> ' . $obj->station_faction . '<br>';
        $stationGovernment =
            $obj->station_government === '' ? '' : '<strong>Government:</strong> ' . $obj->station_government . '<br>';
        $stationAllegiance =
            $obj->station_allegiance === '' ? '' : '<strong>Allegiance:</strong> ' . $obj->station_allegiance . '<br>';

        $stationState = $obj->station_state === '' ? '' : '<strong>State:</strong> ' . $obj->station_state . '<br>';
        $stationTypeD = $obj->type === '' ? '' : '<strong>Type:</strong> ' . $obj->type . '<br>';
        $stationEconomies =
            $obj->station_economies === '' ? '' : '<strong>Economies:</strong> ' . $obj->station_economies . '<br>';

        $stationImportCommodities = $obj->import_commodities === '' ? '' :
            '<br><strong>Import commodities:</strong> ' . $obj->import_commodities . '<br>';
        $stationExportCommodities =
            $obj->export_commodities === '' ? '' : '<strong>Export commodities:</strong> ' . $obj->export_commodities . '<br>';
        $stationProhibitedCommodities = $obj->prohibited_commodities === '' ? '' :
            '<strong>Prohibited commodities:</strong> ' . $obj->prohibited_commodities . '<br>';

        $stationSellingShips = $obj->selling_ships === '' ? '' :
            '<br><strong>Selling ships:</strong> ' . str_replace("'", '', $obj->selling_ships) . '<br>';

        $stationShipyard = $obj->shipyard;
        $stationOutfitting = $obj->outfitting;
        $stationCommoditiesMarket = $obj->commodities_market;
        $stationBlackMarket = $obj->black_market;
        $stationRefuel = $obj->refuel;
        $stationRepair = $obj->repair;
        $stationRearm = $obj->rearm;

        $stationIncludes = [
            'shipyard' => $stationShipyard,
            'outfitting' => $stationOutfitting,
            'commodities market' => $stationCommoditiesMarket,
            'black market' => $stationBlackMarket,
            'refuel' => $stationRefuel,
            'repair' => $stationRepair,
            'restock' => $stationRearm
        ];

        $i = 0;
        $stationServices = '';
        foreach ($stationIncludes as $name => $included) {
            if ($included == 1) {
                if ($i !== 0) {
                    $stationServices .= ', ';
                } else {
                    $stationServices .= '<strong>Facilities:</strong> ';
                }

                $stationServices .= $name;
                $i++;
            }
        }
        $stationServices .= '<br>';

        $outfittingUpdatedAt = $obj->outfitting_updated_at == '0' ? '' :
            '<br><strong>Outfitting last updated:</strong> ' . get_timeago($obj->outfitting_updated_at, true, true) . '<br>';

        $shipyardUpdatedAt = $obj->shipyard_updated_at == '0' ? '' :
            '<strong>Shipyard last updated:</strong> ' . get_timeago($obj->shipyard_updated_at, true, true) . '<br>';

        $info = $stationTypeD . $stationFaction . $stationGovernment . $stationAllegiance . $stationState . $stationEconomies .
            $stationServices;
        $info .= $stationImportCommodities . $stationExportCommodities . $stationProhibitedCommodities . $outfittingUpdatedAt .
            $shipyardUpdatedAt . $stationSellingShips;

        $info = str_replace("['", '', $info);
        $info = str_replace([
            "']",
            "', '"
        ], [
            '',
            ', '
        ], $info);

        /**
         * get allegiance icon
         */
        $stationAllegianceIcon = getAllegianceIcon($obj->station_allegiance);
        $stationAllegianceIcon = '<img src="/style/img/' . $stationAllegianceIcon . '" alt="' . $obj->station_allegiance .
            '" style="width: 19px;  height: 19px; margin-right: 5px" />';

        /**
         * notify user if data is old
         */
        $stationDispName = $stationName;

        if (!empty($groupId) || !empty($shipName) || dataIsOld($obj->outfitting_updated_at) ||
            dataIsOld($obj->shipyard_updated_at)) {
                $stationDispName = '<span class="old_data">' . $stationName . '</span>';
            }
        ?>
        <td class="<?= $tdclass ?>">
            <?= $stationAllegianceIcon . $icon ?>
            <a href="javascript:void(0)" id="minfo<?= $stationId ?>"
               title="Additional information">
                <?= $stationDispName ?>
            </a>
        </td>
        <td class="<?= $tdclass ?>">
            <?= $stationLsFromStar ?>
        </td>
        <td class="<?= $tdclass ?>">
            <?= $stationMaxLandingPad_size ?>
        </td>
        <script>
            $(document).mouseup(function(e) {
                var containers = [];
                containers.push($('#si_statinfo'));

                $.each(containers, function(key, value) {
                    if (!$(value).is(e.target) && $(value).has(e.target).length === 0) {
                        $(value).fadeOut("fast");
                    }
                });
            });

            $('#minfo<?= $stationId?>').click(function(e) {
                var statinfo_div = $('#si_statinfo');
                if (statinfo_div.is(":hidden")) {
                    statinfo_div.fadeToggle("fast");
                    statinfo_div.css({
                        left: e.pageX - 330,
                        top: e.pageY - 40
                    });
                    statinfo_div.html("<?= addslashes($info)?>");
                }
            });
        </script>
        <?php
    }
}
