<?php
/**
 * System map
 *
 * No description
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

/** @require Theme class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Theme.php';

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->page_title = 'System Map';

/**
 * display the header
 */
$header->display_header();

/**
 * determine what system to display
 */
$system = $curSys['name'];
if (isset($_GET['system'])) {
    $system = $_GET['system'];
}

/**
 * get string if system already mapped
 */
$esc_system_name = $mysqli->real_escape_string($system);

$query = "  SELECT string
            FROM user_system_map
            WHERE system_name = '$esc_system_name'
            LIMIT 1";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
$obj = $result->fetch_object();

$string = $obj->string;

$result->close();

$link_map = !empty($string) ? '<span id="mlink">&nbsp;&ndash;&nbsp;<a href="http://map.edtb.xyz?v1=' . $string . '" target="_blank" id="maplink" title="View on map.edtb.xyz">View on map.edtb.xyz</a></span>' : '<span id="mlink"></span>';
?>
<link type="text/css" href="/source/Vendor/jquery-ui-1.11.4/jquery-ui.min.css" rel="stylesheet" />
<script src="/source/Vendor/timmywil-jquery-panzoom/panzoom.js"></script>
<script src="/source/Vendor/jquery-ui-1.11.4/jquery-ui.min.js"></script>
<script src="/source/Vendor/color-thief.js"></script>
<script src="SystemMap.js"></script>

<section id="focal">
    <div class="entries explor_entries">
        <div class="top">
            <span class="right" id="value" style="display:none">
                Approximate value:
                <span class="text" id="minval"></span><span id="minvaln" style="display:none"></span>
                <span class="text" id="dash"></span>
                <span class="text" id="maxval"></span><span id="maxvaln" style="display:none"></span>&nbsp;
                ( Very experimental )
            </span>
            <div class="sm_system">System map for: <?php echo $system . $link_map?></div>
            <div id="smsys" style="display:none"><?php echo $system?></div>
            <span class="text" style="margin-right:20px">Add bodies :</span>
            <?php
            $types = array('star', 'planet', 'other');

            foreach ($types as $type) {
                $border = $type === 'other' ? ' style="border-right:1px solid #333"' : '';
                ?>
                <div class="categories" id="<?php echo $type?>_click"<?php echo $border?>>
                    <?php echo $type?>
                </div>
                <div class="stars_planets" id="<?php echo $type?>" style="display:none">
                <?php
                $json_file = 'bodies.json';
                $json_string = file_get_contents($json_file);

                $json_arr = json_decode($json_string, true);

                $i = 0;
                $last_img = '';

                Utility::orderBy($json_arr, 'order_num ASC, name ASC');

                $last_name = '';
                foreach ($json_arr as $arr) {
                    $type2 = $arr['type'];
                    if ($type2 == $type) {
                        $img = str_replace(' ', '_', $arr['name']);
                        $img = str_replace(',', '', $img);
                        $img = strtolower($img);

                        $imgfiles = glob($_SERVER['DOCUMENT_ROOT'] . '/SystemMap/bodies/' . $img . '_*');

                        $name = $arr['name'];
                        $id = $arr['id'];
                        $width = $arr['width'];
                        $min_value = $arr['min_value'];
                        $max_value = $arr['max_value'];

                        if ($name != $last_name) {
                            echo '<div class="cat_name">' . $name;
                        }

                        $ii = 0;
                        foreach ($imgfiles as $imgfile) {
                            $src = str_replace($_SERVER['DOCUMENT_ROOT'], '', $imgfile);
                            $imgid = $imgfile[strlen($imgfile) - 5];

                            $bid = $type . '_' . $id . '_' . $i . '_' . $ii;

                            ?>
                            <script>
                                var options<?php echo $id . $imgid?> = [];
                                options<?php echo $id . $imgid?>["id"] = "<?php echo $id?>";
                                options<?php echo $id . $imgid?>["type"] = "<?php echo $type?>";
                                options<?php echo $id . $imgid?>["name"] = "<?php echo $name?>";
                                options<?php echo $id . $imgid?>["src"] = "<?php echo $src?>";
                                options<?php echo $id . $imgid?>["imgid"] = "<?php echo $imgid?>";
                                options<?php echo $id . $imgid?>["width"] = "<?php echo $width?>";
                                options<?php echo $id . $imgid?>["min_value"] = "<?php echo $min_value?>";
                                options<?php echo $id . $imgid?>["max_value"] = "<?php echo $max_value?>";
                                options<?php echo $id . $imgid?>["bid"] = "<?php echo $bid?>";
                                options<?php echo $id . $imgid?>["bodyid"] = "<?php echo $id?>";
                                options<?php echo $id . $imgid?>["landable"] = 0;
                                options<?php echo $id . $imgid?>["ringed"] = 0;
                                options<?php echo $id . $imgid?>["scanned"] = 1;
                                options<?php echo $id . $imgid?>["firstdisc"] = 0;
                                options<?php echo $id . $imgid?>["do_update"] = true;
                                options<?php echo $id . $imgid?>["pos_top"] = false;
                                options<?php echo $id . $imgid?>["pos_left"] = false;
                                options<?php echo $id . $imgid?>["source"] = "php";
                            </script>
                            <div class="add" onclick="add_body(options<?php echo $id . $imgid?>)">
                                <img class="add_img_<?php echo $type?>" src="<?php echo $src?>" alt="<?php echo $name?>" />
                            </div>
                            <?php
                            $ii++;
                        }

                        if ($img != $last_img) {
                            echo '</div>';
                        }

                        $last_name = $name;
                        $i++;
                    }
                }
                ?>
                </div>
                <?php
            }
            ?>
            <span class="text" style="margin-left:40px;margin-right:20px">Controls :</span>
            <div class="categories" id="toggle_grid">Toggle grid</div>
            <div class="categories" id="toggle_names" style="width:77px">Hide names</div>
            <div class="categories" id="toggle_background">Toggle background</div>
        </div>

        <div class="panzoom"></div>

        <script>
            (function() {
                var $section = $("#focal");
                var $panzoom = $section.find(".panzoom").panzoom({
                    startTransform: 'scale(1.0)',
                    animate: false,
                    increment: 0.1,
                    disablePan: false,
                    disableZoom: true,
                    cursor: "/style/img/cursor.png"
                });

                /*$panzoom.parent().on("mousewheel.focal", function(e)
                 {
                 e.preventDefault();
                 var delta = e.delta || e.originalEvent.wheelDelta;
                 var zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
                 $panzoom.panzoom("zoom", zoomOut,
                 {
                 increment: 0.1,
                 animate: false,
                 focal: e
                 });
                 });*/
            })();
            /**
             * size of the grid
             */
            var gridsize = 6;

            /**
             * location of body images
             * @type {string}
             */
            var bodies = "/SystemMap/bodies";
        </script>
    </div>
</section>

<script>
    Array.prototype.clean = function(deleteValue) {
        for (var i = 0; i < this.length; i++) {
            if (this[i] == deleteValue) {
                this.splice(i, 1);
                i--;
            }
        }
        return this;
    };

    window.onload = function() {
        /**
         * add bodies according to url parameters
         */
        var url_vars;

        <?php
        if (!empty($string)) {
            ?>
            url_vars = "<?php echo $string?>";
            <?php
        } else {
            ?>
            url_vars = getUrlVars().v1;
            <?php
        }
        ?>

        if (url_vars) {
            var url_parts = url_vars.split("c").clean("");

            var controls = url_parts[1];

            var show_grid = controls.substr(0, 1);
            var show_bg = controls.substr(1, 1);
            var show_names = controls.substr(2, 1);

            var bodies = url_parts[0].split("l").clean(""),
                i;

            $.getJSON("bodies.json", function(data) {
                for (i = 0; i < bodies.length; i++) {
                    var options = [],
                        parts = bodies[i].split("i"),
                        ia;

                    options["imgid"] = parts[0];
                    options["pos_left"] = parts[1] * gridsize;
                    options["pos_top"] = parts[2] * gridsize;
                    options["width"] = parts[3];

                    var opt_parts = parts[4];
                    options["ringed"] = opt_parts.substr(0, 1);
                    options["firstdisc"] = opt_parts.substr(1, 1);
                    options["scanned"] = opt_parts.substr(2, 1);
                    options["landable"] = opt_parts.substr(3, 1);

                    options["bodyid"] = parts[5].substr(0, 2);

                    options["show_name"] = 1;
                    if (show_names == "0") {
                        options["show_name"] = 0;
                    }

                    for (ia = 0; ia < data.length; ia++) {
                        if (data[ia].id == options["bodyid"]) {
                            options["id"] = data[ia].id;
                            //var width2 = data[ia].width;
                            options["type"] = data[ia].type;
                            options["min_value"] = data[ia].min_value * 1;
                            options["max_value"] = data[ia].max_value * 1;
                            options["name"] = data[ia].name;
                            options["bid"] = options["type"] + '_' + options["id"] + '_' + i + '_' + ia;
                            var bodyname = replaceAll(options["name"], " ", "_");
                            bodyname = replaceAll(bodyname, ",", "");
                            bodyname = bodyname.toLowerCase();

                            options["src"] = '/SystemMap/bodies/' + bodyname + '_' + options["imgid"] + '.png';

                            break;
                        }
                    }

                    options["do_update"] = false;
                    options["source"] = "url";

                    add_body(options);
                }
            });

            if (show_grid == "0") {
                $(".panzoom").css("background-image", "none");
            }

            if (show_bg == "0") {
                $(".rightpanel").css("background-image", "none");
            }

            if (show_names == "0") {
                $("#toggle_names").html("Show names");
            }
        }
    };

    $(document).ready(function() {
        var star = $("#star");
        var planet = $("#planet");
        var other = $("#other");
        update_price();

        $("#star_click").mouseover(function() {
            if (star.is(":hidden")) {
                star.fadeToggle("fast");
            }
            if (planet.is(":visible")) {
                planet.hide();
            }
            if (other.is(":visible")) {
                other.hide();
            }
        });

        $("#planet_click").mouseover(function() {
            if (planet.is(":hidden")) {
                planet.fadeToggle("fast");
            }
            if (star.is(":visible")) {
                star.hide();
            }
            if (other.is(":visible")) {
                other.hide();
            }
        });

        $("#other_click").mouseover(function() {
            if (other.is(":hidden")) {
                other.fadeToggle("fast");
            }
            if (planet.is(":visible")) {
                planet.hide();
            }
            if (star.is(":visible")) {
                star.hide();
            }
        });

        /**
         * hide add body when hovering over the grid area
         */
        $(".panzoom, .rightpanel-top").mouseover(function() {
            if (other.is(":visible")) {
                other.fadeOut("fast");
            }
            if (planet.is(":visible")) {
                planet.fadeOut("fast");
            }
            if (star.is(":visible")) {
                star.fadeOut("fast");
            }
        });

        /**
         * toggle grid
         */
        $("#toggle_grid").click(function() {
            var panzoom = $(".panzoom");
            if (panzoom.css("background-image") == "none") {
                panzoom.css("background-image", "repeating-linear-gradient(0deg, transparent, transparent 20px, #333333 20px, #333333 21px), repeating-linear-gradient(-90deg, transparent, transparent 20px, #333333 20px, #333333 21px)");
            } else {
                panzoom.css("background-image", "none");
            }
            update_url();
        });

        /**
         * toggle body names
         */
        var toggle_names = $("#toggle_names");
        toggle_names.click(function() {
            if (toggle_names.html() == "Show names") {
                toggle_names.html("Hide names");
                $(".name").fadeIn("fast");
            } else {
                $(".name").fadeOut("fast");
                toggle_names.html("Show names");
            }
            update_url();
        });

        /**
         * toggle body background
         */
        $("#toggle_background").click(function() {
            var rightpanel = $(".rightpanel");
            if (rightpanel.css("background-image") == "none") {
                rightpanel.css("background-image", "url(/style/img/backg.jpg)");
            } else {
                rightpanel.css("background-image", "none");
            }
            update_url();
        });
    });
</script>
<!-- Hide divs by clicking outside of them -->
<script>
    $(document).mouseup(function (e)
    {
        var container = [];
        container.push($(".panzoom").find(".addinfo"));

        $.each(container, function(key, value)
        {
            if (!$(value).is(e.target) // if the target of the click isn't the container...
                && $(value).has(e.target).length === 0) // ... nor a descendant of the container
            {
                $(value).fadeOut("fast");
            }
        });
    });
</script>
<?php
/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->display_footer();
