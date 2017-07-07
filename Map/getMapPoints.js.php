<?php
/**
 * Ajax backend file to fetch map points for Neighborhood Map
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/config.inc.php';
/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';
/** @require MySQL */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/MySQL.php';
/** @require curSys */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/curSys.php';

header('content-type: application/x-javascript');

if (isset($_GET['maxdistance']) && is_numeric($_GET['maxdistance'])) {
    $settings['maxdistance'] = $_GET['maxdistance'];
}

/**
 * if current coordinates aren't valid, use last known coordinates
 */
$disclaimer = '';
if (!valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
    // get last known coordinates
    $lastCoords = last_known_system();

    $curSys['x'] = $lastCoords['x'];
    $curSys['y'] = $lastCoords['y'];
    $curSys['z'] = $lastCoords['z'];

    $disclaimer = '<p><strong>No coordinates for current location, last known location used</strong></p>';
}

if (!valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
    $curSys['x'] = '0';
    $curSys['y'] = '0';
    $curSys['z'] = '0';

    $disclaimer = '<p><strong>Current location unknown, Sol used.</strong></p>';
}

$data = '';
$lastRow = '';

/**
 * fetch point of interest data for the map
 */
if ($settings['nmap_show_pois'] === 'true') {
    $query = "  SELECT poi_name, system_name, x, y, z
                FROM user_poi
                WHERE x != '' AND y != '' AND z != ''";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($obj = $result->fetch_object()) {
        $name = $obj->system_name;
        $dispName = $obj->poi_name !== '' ? $obj->poi_name : $obj->system_name;

        $poiCoordx = $obj->x;
        $poiCoordy = $obj->y;
        $poiCoordz = $obj->z;

        $coord = "$poiCoordx,$poiCoordy,$poiCoordz";

        $distanceFromCurrent = '';
        if (valid_coordinates($poiCoordx, $poiCoordy, $poiCoordz)) {
            $distanceFromCurrent = sqrt((($poiCoordx - $curSys['x']) ** 2) + (($poiCoordy - $curSys['y']) ** 2) + (($poiCoordz - $curSys['z']) ** 2));
        }

        // only show systems if distance is less than the limit set by the user
        if ($distanceFromCurrent !== '' && $distanceFromCurrent <= $settings['maxdistance']) {
            $escName = $mysqli->real_escape_string($name);
            $query = "  SELECT id, visit
                        FROM user_visited_systems
                        WHERE system_name = '$escName'
                        ORDER BY visit ASC
                        LIMIT 1";

            $visited = $mysqli->query($query)->num_rows;

            $marker = 'marker:{symbol:"url(/style/img/goto.png)"}';
            if ($name === 'SOL') {
                $marker = 'marker:{symbol:"circle",radius:3,fillColor:"#37bf1c"}';
            } elseif ($visited > 0) {
                $marker = 'marker:{symbol:"url(/style/img/goto-g.png)"}';
            }

            $data = '{name:"' . $dispName . '",data:[[' . $coord . ']],' . $marker . '}' . $lastRow;

            $lastRow = ',' . $data;
        }
    }
    $result->close();
}

/**
 *  fetch bookmark data for the map
 */
if ($settings['nmap_show_bookmarks'] === 'true') {
    $query = '  SELECT user_bookmarks.comment, user_bookmarks.added_on,
                edtb_systems.name AS system_name, edtb_systems.x, edtb_systems.y, edtb_systems.z,
                user_bm_categories.name AS category_name
                FROM user_bookmarks
                LEFT JOIN edtb_systems ON user_bookmarks.system_name = edtb_systems.name
                LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id';

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($bmObj = $result->fetch_object()) {
        $bmSystemName = $bmObj->system_name;
        $bmComment = $bmObj->comment;
        $bmAddedOn = $bmObj->added_on;
        $bmCategoryName = $bmObj->category_name;

        // coordinates for distance calculations
        $bmCoordx = $bmObj->x;
        $bmCoordy = $bmObj->y;
        $bmCoordz = $bmObj->z;
        $coord = $bmObj->x . ',' . $bmObj->y . ',' . $bmObj->z;

        /**
         * if coords are not set, see if user has calculated them
         */
        if (!valid_coordinates($bmCoordx, $bmCoordy, $bmCoordz)) {
            $escName = $mysqli->real_escape_string($bmSystemName);
            $query = "  SELECT x, y, z
                        FROM user_systems_own
                        WHERE name = '$escName'
                        LIMIT 1";

            $coordRes = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $coordRes->fetch_object();

            $bmCoordx = $obj->x;
            $bmCoordy = $obj->y;
            $bmCoordz = $obj->z;

            $coordRes->close();
        }

        if (valid_coordinates($bmCoordx, $bmCoordy, $bmCoordz)) {
            $distanceFromCurrent = '';
            if (valid_coordinates($bmCoordx, $bmCoordy, $bmCoordz)) {
                $distanceFromCurrent = sqrt((($bmCoordx - $curSys['x']) ** 2) + (($bmCoordy - $curSys['y']) ** 2) + (($bmCoordz - $curSys['z']) ** 2));
            }

            // only show systems if distance is less than the limit set by the user
            if ($distanceFromCurrent !== '' && $distanceFromCurrent <= $settings['maxdistance']) {
                $marker = 'marker:{symbol:"url(/style/img/bm.png)"}';

                $data = '{name:"' . $bmSystemName . '",data:[[' . $coord . ']],' . $marker . '}' . $lastRow;

                $lastRow = ',' . $data;
            }
        }
    }
    $result->close();
}

/**
 *  fetch rares data for the map
 */
if ($settings['nmap_show_rares'] === 'true') {
    $query = "  SELECT
                edtb_rares.item, edtb_rares.station, edtb_rares.system_name, edtb_rares.ls_to_star,
                edtb_systems.x, edtb_systems.y, edtb_systems.z
                FROM edtb_rares
                LEFT JOIN edtb_systems ON edtb_rares.system_name = edtb_systems.name
                WHERE edtb_rares.system_name != ''";

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($rareObj = $result->fetch_object()) {
        $rareItem = $rareObj->item;
        $rareStation = $rareObj->station;
        $rareSystem = $rareObj->system_name;
        $rareDistToStar = number_format($rareObj->ls_to_star);

        $rareDispName = $rareItem . ' - ' . $rareSystem . ' (' . $rareStation . ' - ' . $rareDistToStar . ' ls)';

        // coordinates for distance calculations
        $rareCoordx = $rareObj->x;
        $rareCoordy = $rareObj->y;
        $rareCoordz = $rareObj->z;

        $rareCoord = $rareCoordx . ',' . $rareCoordy . ',' . $rareCoordz;

        $rareDistanceFromCurrent = '';
        if (valid_coordinates($rareCoordx, $rareCoordy, $rareCoordz)) {
            $rareDistanceFromCurrent = sqrt((($rareCoordx - $curSys['x']) ** 2) + (($rareCoordy - $curSys['y']) ** 2) + (($rareCoordz - $curSys['z']) ** 2));
        }

        // only show systems if distance is less than the limit set by the user
        if ($rareDistanceFromCurrent !== '' && $rareDistanceFromCurrent <= $settings['maxdistance']) {
            $rareMarker = 'marker:{symbol:"url(/style/img/rare.png)"}';

            $data = '{name:"' . $rareDispName . '",data:[[' . $rareCoord . ']],' . $rareMarker . '}' . $lastRow;

            $lastRow = ',' . $data;
        }
    }
    $result->close();
}

/**
 * fetch visited systems data for the map
 */
if ($settings['nmap_show_visited_systems'] === 'true') {
    $query = '  SELECT
                user_visited_systems.system_name AS system_name, user_visited_systems.visit,
                edtb_systems.x, edtb_systems.y, edtb_systems.z, edtb_systems.id AS sysid, edtb_systems.allegiance
                FROM user_visited_systems
                LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
                GROUP BY user_visited_systems.system_name
                ORDER BY user_visited_systems.visit ASC';

    $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

    while ($obj = $result->fetch_object()) {
        $name = $obj->system_name;
        $escName = $mysqli->real_escape_string($name);
        $sysid = $obj->sysid;

        // coordinates for distance calculations
        $vsCoordx = $obj->x;
        $vsCoordy = $obj->y;
        $vsCoordz = $obj->z;

        /**
         * if coords are not set, see if user has calculated them
         */
        if (!valid_coordinates($vsCoordx, $vsCoordy, $vsCoordz)) {
            $query = "  SELECT x, y, z
                        FROM user_systems_own
                        WHERE name = '$escName'
                        LIMIT 1";

            $coordRes = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $obj = $coordRes->fetch_object();

            $vsCoordx = $obj->x;
            $vsCoordy = $obj->y;
            $vsCoordz = $obj->z;
        }

        $distanceFromCurrent = '';
        if (valid_coordinates($vsCoordx, $vsCoordy, $vsCoordz)) {
            $coord = $vsCoordx . ',' . $vsCoordy . ',' . $vsCoordz;

            $distanceFromCurrent = sqrt((($vsCoordx - $curSys['x']) ** 2) + (($vsCoordy - $curSys['y']) ** 2) + (($vsCoordz - $curSys['z']) ** 2));

            // only show systems if distance is less than the limit set by the user
            if ($distanceFromCurrent <= $settings['maxdistance']) {
                $query = "  SELECT id
                            FROM user_log
                            WHERE system_name = '$escName'
                            LIMIT 1";

                $loggedResult = $mysqli->query($query);

                $logged = $loggedResult->num_rows;

                $allegiance = $obj->allegiance;

                switch ($allegiance) {
                    case 'Empire':
                        $color = 'rgba(231, 216, 132, 0.7)';
                        break;
                    case 'Alliance':
                        $color = 'rgba(9, 180, 244, 0.7)';
                        break;
                    case 'Federation':
                        $color = 'rgba(140, 140, 140, 0.7)';
                        break;
                    default:
                    $color = 'rgba(255, 255, 255, 0.8)';
                }

                if ($logged > 0 && strtolower($name) != strtolower($curSys['name'])) {
                    $marker = 'marker:{symbol:"circle",radius:3,fillColor:"' . $color . '",lineWidth:"2",lineColor:"#2e92e7"}';
                } elseif (strtolower($name) == strtolower($curSys['name'])) {
                    $marker = 'marker:{symbol:"circle",radius:4,fillColor:"' . $color . '",lineWidth:"2",lineColor:"#f44b09"}';
                } else {
                    $marker = 'marker:{symbol:"circle",radius:3,fillColor:"' . $color . '"}';
                }

                $data = $lastRow;
                if (isset($name, $coord)) {
                    $data = '{name:"' . $name . '",data:[[' . $coord . ']],' .$marker. '}' . $lastRow;
                }

                $lastRow = ',' . $data;
            }
        }
    }
    $result->close();
}

/**
 * get the max/min values for map display
 */
if (valid_coordinates($curSys['x'], $curSys['y'], $curSys['z'])) {
    $maxx = $curSys['x'] + $settings['maxdistance'];
    $maxy = $curSys['y'] + $settings['maxdistance'];
    $maxz = $curSys['z'] + $settings['maxdistance'];
    $minx = $curSys['x'] - $settings['maxdistance'];
    $miny = $curSys['y'] - $settings['maxdistance'];
    $minz = $curSys['z'] - $settings['maxdistance'];
} else {
    $maxx = 100;
    $maxy = 100;
    $maxz = 100;
    $minx = -100;
    $miny = -100;
    $minz = -100;
}

/**
 * change between 3D and 2D maps
 */
if (isset($_GET['mode']) && $_GET['mode'] === '2d') {
    $threed = 'false';
    $zoomtype = "zoomType: 'xy',";
    $panning = 'true';
    $pankey = "panKey: 'shift',";
} else {
    $threed = 'true';
    $zoomtype = '';
    $panning = 'false';
    $pankey = '';
}
?>
/** custom tooltip format */
function tooltipFormatter() {
    var value;
    <?php
    if (isset($_GET['mode']) && $_GET['mode'] === '2d') {
        ?>
        value = this.series.name.toUpperCase();
        <?php
    } else {
        ?>
        value = this.series.name.toUpperCase() + " is " + Math.round(Math.sqrt(Math.pow((this.x-(<?= $curSys['x']?>)), 2)+Math.pow((this.y-(<?= $curSys['y']?>)), 2)+Math.pow((this.point.z-(<?= $curSys['z']?>)), 2))) + " ly away";
        <?php
    }
    ?>
    return value;
}

$(function ()
{
    // Give the points a 3D feel by adding a radial gradient
    /** Highcharts.getOptions().colors = $.map(Highcharts.getOptions().colors, function (color) {
        return {
            radialGradient: {
                cx: 0.4,
                cy: 0.3,
                r: 0.5
            },
            stops: [
                [0, color],
                [1, Highcharts.Color(color).brighten(-0.2).get('rgb')]
            ]
        };
    }); */
    Highcharts.theme =
    {
        /** colors: ['rgba(117,38,38,0.7)', 'rgba(192,251,251,0.7)', 'rgba(120,171,173,0.7)', 'rgba(195,44,222,0.7)', 'rgba(255,179,0,0.7)', 'rgba(24,219,216,0.7)', 'rgba(128,0,0,0.7)', 'rgba(145,232,23,0.7)'], */
        chart:
        {
            backgroundColor: 'transparent',
            style:
            {
                fontFamily: "Telegrama"
            },
            plotBorderColor: '#606063'
        },
        xAxis:
        {
            gridLineColor: '#707073',
            backgroundColor: "#CCC",
            labels:
            {
                style:
                {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            minorGridLineColor: '#505053',
            tickColor: '#707073',
            title:
            {
                style:
                {
                    color: '#A0A0A3'

                }
            }
        },
        yAxis:
        {
            gridLineColor: '#707073',
            labels:
            {
                style:
                {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            minorGridLineColor: '#505053',
            tickColor: '#707073',
            tickWidth: 1,
            title:
            {
                style:
                {
                    color: '#A0A0A3'
                }
            }
        },
        tooltip:
        {
            backgroundColor: 'rgba(0, 0, 0, 0.85)',
            style:
            {
                color: '#FFFFFA',
                fontSize: '11px',
                fontFamily: 'Sintony',
                letterSpacing: 'normal'
            }
        },
        plotOptions:
        {
            series:
            {
                dataLabels:
                {
                    color: '#B0B0B3'
                },
                marker:
                {
                    lineColor: '#333'
                },
                enableMouseTracking: true
            },
            boxplot:
            {
                fillColor: '#505053'
            },
            candlestick:
            {
                lineColor: 'white'
            }
       }
    };

    // Apply the theme
    Highcharts.setOptions(Highcharts.theme);

    // get the jQuery wrapper
    //var $report = $('#report');

    // Set up the chart
    var chart = new Highcharts.Chart(
    {
        loading:
        {
            labelStyle:
            {
                fontStyle: 'italic'
            }
        },
        chart:
        {
            renderTo: 'container',
            margin: 90,
            type: 'scatter',
            stickyTracking: false,
            <?= $zoomtype?>
            panning: <?= $panning?>,
            <?= $pankey?>
            options3d:
            {
                enabled: <?= $threed?>,
                alpha: 20,
                beta: 30,
                depth: 120,
                frame:
                {
                    back:
                    {
                        color: "#1E2021"
                    },
                    side:
                    {
                        color: "#1E2021"
                    },
                    bottom:
                    {
                        color: "#1E2021"
                    }
                }
            }
        },
        title:
        {
            text: ''
        },
        subtitle:
        {
            text: ''
        },
        plotOptions:
        {
            scatter:
            {
                width:10,
                height: 10,
                depth: 10
            },
            series:
            {
                animation: false,
                cursor: 'pointer',
                point:
                {
                    events:
                    {
                        click: function ()
                        {
                            get_mi(this.series.name);
                        }
                    }
                }
            }
        },
        tooltip:
        {
            formatter: tooltipFormatter,
            animation: false
        },
        xAxis:
        {
            min: <?= round($minx)?>,
            max: <?= round($maxx)?>,
            gridLineWidth: 1
        },
        yAxis:
        {
            min: <?= round($miny)?>,
            max: <?= round($maxy)?>,
            title: null
        },
        zAxis:
        {
            min: <?= round($minz)?>,
            max: <?= round($maxz)?>
        },
        credits:
        {
            enabled: true
        },
        legend:
        {
            enabled: false
        },
        exporting:
        {
            enabled: false
        },
        series: [<?= $data ?>]
    });

    // Add mouse events for rotation
    $(chart.container).bind('mousedown.hc touchstart.hc', function (e)
    {
        e = chart.pointer.normalize(e);

        var posX = e.pageX,
            posY = e.pageY,
            alpha = chart.options.chart.options3d.alpha,
            beta = chart.options.chart.options3d.beta,
            newAlpha,
            newBeta,
            sensitivity = 5; // lower is more sensitive

        $(document).bind(
        {
            'mousemove.hc touchdrag.hc': function (e)
            {
                // Run beta
                newBeta = beta + (posX - e.pageX) / sensitivity;
                newBeta = Math.min(100, Math.max(-100, newBeta));
                chart.options.chart.options3d.beta = newBeta;

                // Run alpha
                newAlpha = alpha + (e.pageY - posY) / sensitivity;
                newAlpha = Math.min(100, Math.max(-100, newAlpha));
                chart.options.chart.options3d.alpha = newAlpha;

                chart.redraw(false);
            },
                'mouseup touchend': function ()
                {
                    $(document).unbind('.hc');
                }
        });
    });
    $('#loader').hide();
});

var disclaimer = $('#disclaimer');
<?php
if ($disclaimer !== '') {
    ?>
    disclaimer.html('<?= $disclaimer?>');
    <?php
} else {
    ?>
    disclaimer.html("");
    <?php
}
