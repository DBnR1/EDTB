<?php
/*
*    ED ToolBox, a companion web app for the video game Elite Dangerous
*    (C) 1984 - 2015 Frontier Developments Plc.
*    ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*    Copyright (C) 2016 Mauri Kujala (contact@edtb.xyz)
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

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/config.inc.php");
require_once("" . $settings["install_path"] . "/data/server_config.inc.php");

date_default_timezone_set('UTC');

/*
*    connect to database
*/

function db_connect($server, $user, $pwd, $db)
{
	$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($server, $user, $pwd));
	if (!$link)
	{
		exit('Could not connect: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}
	if (!((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $db)))
	{
		exit('Could not select database: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}
}

db_connect($server, $user, $pwd, $db);

/*
*	expand the settings variable with stuff from the db
*/

$settings_res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CACHE user_settings.variable, user_settings.value, edtb_settings_info.type
															FROM user_settings
															LEFT JOIN edtb_settings_info ON edtb_settings_info.variable = user_settings.variable")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

while ($settings_arr = mysqli_fetch_assoc($settings_res))
{
	$variable = $settings_arr["variable"];
	$value = $settings_arr["value"];

	if ($settings_arr["type"] == "array")
	{
		// split by new line
		$values = preg_split("/\r\n|\r|\n|" . PHP_EOL . "/", $value);

		foreach ($values as $arvalue)
		{
			if (!empty($arvalue))
			{
				$count = 0;
				$parts = explode(">>", $arvalue);

				$var = $parts[0];
				$val = $parts[1];

				$values_s = explode(",", $val);
				$count = count($values_s);

				if ($count > 1)
				{
					$i = 0;
					foreach	($values_s as $val_f)
					{
						$settings[$variable][$var][$i] = $val_f;
						$i++;
					}
				}
				else
				{
					$settings[$variable][$var] = $val;
				}
			}
		}
	}
	elseif ($settings_arr["type"] == "csl")
	{
		$values = explode(",", $value);

		$i = 0;
		foreach ($values as $arvalue)
		{
			$settings[$variable][$i] = trim($arvalue);
			$i++;
		}
	}
	else
	{
		$settings[$variable] = $value;
	}
}

$maplink = $settings["default_map"] == "galaxy_map" ? "/galmap.php" : "/map.php";
$dropdown = $settings["dropdown"];
array_push($dropdown, $settings["maxdistance"]);

global $settings;

/*
*	 links for the navigation panel
*/

$links = array( "ED ToolBox--log.png--true" => "/",
				"System Information--info.png--true" => "/system.php",
				"Galaxy Map&nbsp;&nbsp;&&nbsp;&nbsp;Neighborhood Map--grid.png--true" => $maplink,
				"Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks--poi.png--false" => "/poi.php",
				"Nearest Systems&nbsp;&nbsp;&&nbsp;&nbsp;Stations--find.png--false" => "/nearest_systems.php",
				"Data Point--dataview.png--false" => "/datapoint.php",
				"Galnet News--news.png--false" => "/galnet.php",
				"Screenshot Gallery--gallery.png--false" => "/gallery.php",
				"System Log--log.png--true" => "/");

/*
*    get current system
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/curSys.php");

/*
*	update companion api data
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/get/getAPIdata.php");

/*
*	screenshots
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/makeScreenshots.php");

/*
*	FD mappings
*/

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/FDMaps.php");

/*
*	 return last known system and coords
*/

function last_known_system()
{
	$coord_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_visited_systems.system_name,
															edtb_systems.x, edtb_systems.y, edtb_systems.z,
															user_systems_own.x AS own_x,
															user_systems_own.y AS own_y,
															user_systems_own.z AS own_z
															FROM user_visited_systems
															LEFT JOIN edtb_systems ON user_visited_systems.system_name = edtb_systems.name
															LEFT JOIN user_systems_own ON user_visited_systems.system_name = user_systems_own.name
															WHERE edtb_systems.x != '' OR user_systems_own.x != ''
															ORDER BY user_visited_systems.visit DESC
															LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$results = mysqli_num_rows($coord_res);
	$last_system = array();

	if ($results > 0)
	{
		$coord_arr = mysqli_fetch_assoc($coord_res);
		$last_system["name"] = $coord_arr["system_name"];
		$last_system["x"] = $coord_arr["x"];
		$last_system["y"] = $coord_arr["y"];
		$last_system["z"] = $coord_arr["z"];

		if ($last_system["x"] == "")
		{
			$last_system["x"] = $coord_arr["own_x"];
			$last_system["y"] = $coord_arr["own_y"];
			$last_system["z"] = $coord_arr["own_z"];
		}
	}
	else
	{
		$last_system["name"] = "";
		$last_system["x"] = "";
		$last_system["y"] = "";
		$last_system["z"] = "";
	}

	return $last_system;
}

/*
*	 diplay notice message
*/

function notice($msg, $title = "Notice")
{
	$notice = '<div class="notice">';
	$notice .= '<div class="notice_title"><img src="/style/img/notice_b.png" alt="Notice" style="vertical-align:middle" />&nbsp;' . $title . '</div>';
	$notice .= '<div class="notice_text">' . $msg . '</div>';
	$notice .= '</div>';
	return $notice;
}

/*
*	http://php.net/manual/en/function.get-browser.php
*/

$u_agent = $_SERVER['HTTP_USER_AGENT'];

function getBrowser()
{
    global $u_agent;

    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif(preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif(preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif(preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif(preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif(preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

/*
*	http://stackoverflow.com/questions/3441880/get-users-os-and-version-number/15497878#15497878
*/

function getOS() {

    global $u_agent;

    $os_platform    =   "Unknown OS Platform";

    $os_array       =   array(
                            '/windows nt 10/i'     =>  'Windows 10',
                            '/windows nt 6.3/i'     =>  'Windows 8.1',
                            '/windows nt 6.2/i'     =>  'Windows 8',
                            '/windows nt 6.1/i'     =>  'Windows 7',
                            '/windows nt 6.0/i'     =>  'Windows Vista',
                            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                            '/windows nt 5.1/i'     =>  'Windows XP',
                            '/windows xp/i'         =>  'Windows XP',
                            '/windows nt 5.0/i'     =>  'Windows 2000',
                            '/windows me/i'         =>  'Windows ME',
                            '/win98/i'              =>  'Windows 98',
                            '/win95/i'              =>  'Windows 95',
                            '/win16/i'              =>  'Windows 3.11',
                            '/macintosh|mac os x/i' =>  'Mac OS X',
                            '/mac_powerpc/i'        =>  'Mac OS 9',
                            '/linux/i'              =>  'Linux',
                            '/ubuntu/i'             =>  'Ubuntu',
                            '/iphone/i'             =>  'iPhone',
                            '/ipod/i'               =>  'iPod',
                            '/ipad/i'               =>  'iPad',
                            '/android/i'            =>  'Android',
                            '/blackberry/i'         =>  'BlackBerry',
                            '/webos/i'              =>  'Mobile'
                        );

    foreach ($os_array as $regex => $value) {

        if (preg_match($regex, $u_agent)) {
            $os_platform    =   $value;
        }

    }

    return $os_platform;
}

/*
*	 calculating coordinates
*/

function trilateration3d($p1,$p2,$p3,$p4)
{
        $ex = vector_unit(vector_diff($p2, $p1));
        $i = vector_dot_product($ex, vector_diff($p3, $p1));
        $ey = vector_unit(vector_diff(vector_diff($p3, $p1), vector_multiply($ex, $i)));
        $ez = vector_cross($ex,$ey);
        $d = vector_length($p2, $p1);
        $r1 = $p1[3]; $r2 = $p2[3]; $r3 = $p3[3]; $r4 = $p4[3];
        if($d - $r1 >= $r2 || $r2 >= $d + $r1){
                return array();
        }
        $j = vector_dot_product($ey, vector_diff($p3, $p1));
        $x = (($r1*$r1) - ($r2*$r2) + ($d*$d)) / (2*$d);
        $y = ((($r1*$r1) - ($r3*$r3) + ($i*$i) + ($j*$j)) / (2*$j)) - (($i*$x) / $j);
        $z = $r1*$r1 - $x*$x - $y*$y;

        if($z < 0){
                return array();
        }
        $z1 = sqrt($z);
        $z2 = $z1 * -1;

        $result1 = $p1;
        $result1 = vector_sum($result1, vector_multiply($ex, $x));
        $result1 = vector_sum($result1, vector_multiply($ey, $y));
        $result1 = vector_sum($result1, vector_multiply($ez, $z1));
        $result2 = $p1;
        $result2 = vector_sum($result2, vector_multiply($ex, $x));
        $result2 = vector_sum($result2, vector_multiply($ey, $y));
        $result2 = vector_sum($result2, vector_multiply($ez, $z2));

        $r1 = vector_length($p4, $result1);
        $r2 = vector_length($p4, $result2);
        $t1 = $r1 - $r4;
        $t2 = $r2 - $r4;
        $coords = array();

        if(abs($t1) < abs($t2)){

                $result1[0]+=(1/64);
                $result1[0]*=32;
                $result1[0]=floor($result1[0]);
                $result1[0]/=32;

                $result1[1]+=(1/64);
                $result1[1]*=32;
                $result1[1]=floor($result1[1]);
                $result1[1]/=32;

                $result1[2]+=(1/64);
                $result1[2]*=32;
                $result1[2]=floor($result1[2]);
                $result1[2]/=32;

                $coords = array($result1[0], $result1[1], $result1[2]);
        }
        else{

                $result2[0]+=(1/64);
                $result2[0]*=32;
                $result2[0]=floor($result2[0]);
                $result2[0]/=32;

                $result2[1]+=(1/64);
                $result2[1]*=32;
                $result2[1]=floor($result2[1]);
                $result2[1]/=32;

                $result2[2]+=(1/64);
                $result2[2]*=32;
                $result2[2]=floor($result2[2]);
                $result2[2]/=32;

                $coords = array($result2[0], $result2[1], $result2[2]);
        }

        return $coords;
}

function vector_length($p1, $p2){
        $a1 = $p1[0];
        $a2 = $p2[0];
        $b1 = $p1[1];
        $b2 = $p2[1];
        $c1 = $p1[2];
        $c2 = $p2[2];
        $dist = sqrt((($a2-$a1)*($a2-$a1))+(($b2-$b1)*($b2-$b1))+(($c2-$c1)*($c2-$c1)));

        return round($dist, 3, PHP_ROUND_HALF_EVEN);
}

function vector_sum($v1, $v2){
        $v = array();
        $v[0] = $v1[0] + $v2[0];
        $v[1] = $v1[1] + $v2[1];
        $v[2] = $v1[2] + $v2[2];
        return $v;
}

function vector_cross($v1, $v2){
        $v = array();
        $v[0] = ($v1[1] * $v2[2]) - ($v1[2] * $v2[1]);
        $v[1] = ($v1[2] * $v2[0]) - ($v1[0] * $v2[2]);
        $v[2] = ($v1[0] * $v2[1]) - ($v1[1] * $v2[0]);
        return $v;
}

function vector_multiply($v, $i){
        return array($v[0] * $i, $v[1] * $i, $v[2] * $i);
}

function vector_dot_product($v1, $v2){
        $ret = ($v1[0] * $v2[0]) + ($v1[1] * $v2[1]) + ($v1[2] * $v2[2]);
        return $ret;
}

function vector_diff($p1,$p2){
        $ret = array($p1[0] - $p2[0], $p1[1] - $p2[1], $p1[2] - $p2[2]);
        return $ret;
}

function vector_norm($v){
        $l = sqrt(($v[0]*$v[0])+($v[1]*$v[1])+($v[2]*$v[2]));
        return $l;
}

function vector_div($v, $l){
        return array($v[0]/$l, $v[1] / $l, $v[2] / $l);
}

function vector_unit($v){
        $l = vector_norm($v);
        if($l == 0){
                return -1;
        }
        return vector_div($v, $l);
}

/*
*	 https://gist.github.com/laiello/8189351
*/

function xml2array($url, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create'))
    {
        return array ();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($url, 'rb')))
    {
        return array ();
    }
    while (!feof($fp))
    {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array ();
    foreach ($xml_values as $data)
    {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value))
        {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes)
        {
            foreach ($attributes as $attr => $val)
            {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open")
        {
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current))))
            {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else
            {
                if (isset ($current[$tag][0]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr']))
                    {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        elseif ($type == "complete")
        {
            if (!isset ($current[$tag]))
            {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            }
            else
            {
                if (isset ($current[$tag][0]) and is_array($current[$tag]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data)
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes)
                    {
                        if (isset ($current[$tag . '_attr']))
                        {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data)
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        }
        elseif ($type == 'close')
        {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
}

/*
*	 http://stackoverflow.com/questions/27330650/how-to-display-time-in-x-days-ago-in-php
*/

function get_timeago($ptime, $diff = true, $format = false)
{
	global $system_time;

	$ptime_og = $ptime;

	if ($diff === true)
	{
		$ptime = $ptime-($system_time*60*60);
	}
	$etime = time()-$ptime;

	if ($etime < 1)
	{
		return 'less than '.$etime.' second ago';
	}

	$a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
				30 * 24 * 60 * 60       =>  'month',
				24 * 60 * 60            =>  'day',
				60 * 60             	=>  'hour',
				60                  	=>  'minute',
				1                   	=>  'second'
	);

	foreach ($a as $secs => $str)
	{
		$d = $etime / $secs;

		if ($d >= 1)
		{
			$r = round($d);
			if ($format !== true)
			{
				return '' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
			}
			else
			{


				if (data_is_old($ptime_og))
				{
					return '<span class="old_data">' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago</span>';
				}
				else
				{
					return '' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
				}
			}
		}
	}
}

/*
*	check if data is old
*/

function data_is_old($time)
{
	global $settings;

	$old = $settings["data_notify_age"]*24*60*60;
	$since = time()-$old;

	if (empty($time))
	{
		return false;
	}

	if ($time < $since)
	{
		return true;
	}
	else
	{
		return false;
	}
}

/*
*	random insult generator... yes
*/

function random_insult($who_to_insult)
{
	$who = explode(" ", $who_to_insult);
	$first_name = $who[0];
	$last_name = $who[1];

	$whoa = array(	"" . $first_name . " so called " . $last_name . "",
					//"" . $first_name . " 'I drink cum' " . $last_name . "",
					"" . $first_name . " " . $last_name . "");

	// Insults from museangel.net, katoninetales.com, mandatory.com with some of my own thrown in for good measure
	$pool1 = array("moronic", "putrid", "disgusting", "cockered", "droning", "fobbing", "frothy", "smelly", "infectious", "puny", "roguish", "assinine", "tottering", "shitty", "villainous", "pompous", "elitist", "dirty");
	$pool2 = array("shit-kicking", "Federal", "butt-munching", "clap-ridden", "fart-eating", "clay-brained", "sheep-fucking");
	$pool3 = array("hemorrhoid", "assface", "whore", "kretin", "cumbucket", "fuckface", "asshole", "turd", "taint", "knob", "tit", "shart", "douche");

	shuffle($pool1);
	shuffle($pool2);
	shuffle($pool3);
	shuffle($whoa);

	$insult = "the " . $pool1[0] . " " . $pool2[0] . " " . $pool3[0] . " " . $whoa[0] . "";

	return $insult;
}

/*
*	http://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty
*/

function is_dir_empty($dir)
{
	if (!is_readable($dir)) return null;
	return (count(scandir($dir)) == 2);
}

/*
*	parse data for data point
*/

function set_data($key, $value, $d_x, $d_y, $d_z, &$dist, $table, $enum)
{
	global $curSys;

	$distance = "";
	$this_row = "";

	// Regular Expression filter for links
	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

	if ($value == "")
	{
		$value = "n/a";
	}

	if ($dist !== false)
	{
		// figure out what coords to calculate from
		$usable_coords = usable_coords();
		$usex = $usable_coords["x"];
		$usey = $usable_coords["y"];
		$usez = $usable_coords["z"];
		$exact = $usable_coords["current"] === true ? "" : " *";

		if (valid_coordinates($d_x, $d_y, $d_z))
		{
			$distance = number_format(sqrt(pow(($d_x-($usex)), 2)+pow(($d_y-($usey)), 2)+pow(($d_z-($usez)), 2)), 2);
			$this_row .= '<td style="padding:10px;white-space:nowrap;vertical-align:middle">' . $distance . ' ' . $exact . '</td>';
		}
		else
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle">n/a</td>';
		}

		$dist = false;
	}
	// make a link for systems with an id
	if ($key == "system_id" && $value != "0")
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle"><a href="/system.php?system_id=' . $value . '">' . $value . '</a></td>';
	}
	// make a link for systems with system name
	elseif (strpos($key, "system_name") !== false && $value != "0" || $key == "name" && $table == "edtb_systems")
	{
		// check if system has screenshots
		$screenshots = has_screenshots($value) ? '<a href="/gallery.php?spgmGal=' . urlencode($value) . '" title="View image gallery"><img src="/style/img/image.png" alt="Gallery" style="margin-left:5px;vertical-align:top" /></a>' : "";

		// check if system is logged
		$loglink = is_logged($value) ? '<a href="log.php?system=' . urlencode($value) . '" style="color:inherit" title="System has log entries"><img src="/style/img/log.png" style="margin-left:5px;vertical-align:top" /></a>' : "";

		$this_row .= '<td style="padding:10px;vertical-align:middle"><a href="/system.php?system_name=' . urlencode($value) . '">' . $value . '' . $loglink.$screenshots . '</a></td>';
	}
	// number format some values
	elseif (strpos($key, "price") !== false || strpos($key, "ls") !== false || strpos($key, "population") !== false || strpos($key, "distance") !== false)
	{
		if (is_numeric($value) && $value != null)
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle">' . number_format($value) . '</td>';
		}
		else
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle">n/a</td>';
		}
	}
	// make links
	elseif (preg_match($reg_exUrl, $value, $url))
	{
		if (mb_strlen($value) >= 80)
		{
			$urli = "" . substr($value, 0, 80) . "...";
		}
		else
		{
			$urli = $value;
		}
		$this_row .= '<td style="padding:10px;vertical-align:middle">' . preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $value) . '</td>';
	}
	// make 0,1 human readable
	elseif ($enum !== false)
	{
		$real_value = "n/a";
		if ($value == "0")
		{
			$real_value = "<span class='enum_no'>&#10799;</span>";
		}

		if ($value == "1")
		{
			$real_value = "<span class='enum_yes'>&#10003;</span>";
		}

		$this_row .= '<td style="padding:10px;text-align:center;vertical-align:middle">' .  $real_value . '</td>';
	}
	else
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle">' . substr(strip_tags($value), 0, 100) . '</td>';
	}

	// parse log entries
	if ($key == "log_entry")
	{
		if (mb_strlen($value) >= 100)
		{
			$this_row = '<td style="padding:10px;vertical-align:middle">' . substr(strip_tags($value), 0, 100) . '...</td>';
		}
		else
		{
			$this_row = '<td style="padding:10px;vertical-align:middle">' . $value . '</td>';
		}
	}

	return $this_row;
}

/**
* Converts bytes into human readable file size.
*
* @param string $bytes
* @return string human readable file size (2,87 Мб)
* @author Mogilev Arseny
*/

function FileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
	$arBytes = array(
		0 => array(
			"UNIT" => "TB",
			"VALUE" => pow(1024, 4)
		),
		1 => array(
			"UNIT" => "GB",
			"VALUE" => pow(1024, 3)
		),
		2 => array(
			"UNIT" => "MB",
			"VALUE" => pow(1024, 2)
		),
		3 => array(
			"UNIT" => "KB",
			"VALUE" => 1024
		),
		4 => array(
			"UNIT" => "B",
			"VALUE" => 0
		),
	);

    foreach ($arBytes as $arItem)
    {
        if ($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

/*
*	return the correct starport icon
*/

function get_station_icon($type, $planetary = "0", $style = "margin-right:6px")
{
	$icon = $planetary == "1" ? '<img src="/style/img/spaceports/planetary.png" alt="Planetary" style="' . $style . '" />' : '<img src="/style/img/spaceports/spaceport.png" alt="Starport" style="' . $style . '" />';

	if ($type == "Coriolis Starport")
	{
		$icon = '<img src="/style/img/spaceports/coriolis.png" alt="Coriolis Starport" style="' . $style . '" />';
	}
	elseif ($type == "Orbis Starport")
	{
		$icon = '<img src="/style/img/spaceports/orbis.png" alt="Orbis Starport" style="' . $style . '" />';
	}
	elseif ($type == "Ocellus Starport")
	{
		$icon = '<img src="/style/img/spaceports/ocellus.png" alt="Ocellus Starport" style="' . $style . '" />';
	}
	elseif (stripos($type, "unknown") !== false && $planetary == "0")
	{
		$icon = '<img src="/style/img/spaceports/unknown.png" alt="Unknown" style="' . $style . '" />';
	}

	return $icon;
}

/*
*	return the correct allegiance icon
*/

function get_allegiance_icon($allegiance)
{
	$pic = "system.png";

	if (!empty($allegiance))
	{
		$pic = $allegiance == "Empire" ? "empire.png" : $pic;
		$pic = $allegiance == "Alliance" ? "alliance.png" : $pic;
		$pic = $allegiance == "Federation" ? "federation.png" : $pic;
	}

	return $pic;
}

/*
*	simple log
*/

function write_log($msg, $file = "", $line = "", $debug_override = false)
{
	global $settings;

	if (isset($settings["debug"]) && $settings["debug"] == "true" || $debug_override !== false)
	{
		// write user info file if not exists
		$lfile = "" . $_SERVER["DOCUMENT_ROOT"] . "/edtb_log_info.txt";
		if (!file_exists($lfile))
		{
			$ua = getBrowser();
			$debug_info = "Browser: " . $ua['name'] . " " . $ua['version'] . " (" .$ua['platform'] . ")\n";
			$debug_info .= "Platform: " . getOS() . "\n";
			$debug_info .= "Reported as: " . $_SERVER["HTTP_USER_AGENT"] . "\n";
			$debug_info .= "HTTP_HOST: " . $_SERVER["HTTP_HOST"] . "\n";
			$debug_info .= "SERVER_SOFTWARE: " . $_SERVER["SERVER_SOFTWARE"] . "\n";
			$debug_info .= "SERVER_NAME: " . $_SERVER["SERVER_NAME"] . "\n";
			$debug_info .= "SERVER_ADDR: " . $_SERVER["SERVER_ADDR"] . "\n";
			$debug_info .= "SERVER_PORT: " . $_SERVER["SERVER_PORT"] . "\n";
			$debug_info .= "DOCUMENT_ROOT: " . $_SERVER["DOCUMENT_ROOT"] . "\n";

			file_put_contents($lfile, $debug_info);
		}

		$logfile = "" . $_SERVER["DOCUMENT_ROOT"] . "/edtb_log.txt";
		$fd = fopen($logfile, "a");

		if (isset($file))
		{
			$on_line = $line == "" ? "" : " on line " . $line . "";
			$where = "[" . $file . "" . $on_line . "]";
		}

		$str = "[" . date("d.m.Y H:i:s", time()) . "]" . $where . " " . $msg;

		fwrite($fd, $str . "\n");
		fclose($fd);
	}
}

/*
*	return usable coordinates
*/

function usable_coords()
{
	global $curSys;

	$usable = array();

	if (valid_coordinates($curSys["x"], $curSys["y"], $curSys["z"]))
	{
		$usable["x"] = $curSys["x"];
		$usable["y"] = $curSys["y"];
		$usable["z"] = $curSys["z"];

		$usable["current"] = true;
	}
	else
	{
		$last_coords = last_known_system();

		$usable["x"] = $last_coords["x"];
		$usable["y"] = $last_coords["y"];
		$usable["z"] = $last_coords["z"];

		$usable["current"] = false;
	}
	return $usable;
}

/*
* 	validate coordinates
*/

function valid_coordinates($x, $y, $z)
{
	if (is_numeric($x) && is_numeric($y) && is_numeric($z))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/*
*	check if system has screenshots
*/

function has_screenshots($system_name)
{
	global $settings;

	if (empty($system_name))
	{
		return false;
	}

	if (is_dir("" . $_SERVER["DOCUMENT_ROOT"] . "/screenshots/" . $system_name . ""))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/*
*	check if system is logged
*/

function is_logged($system, $is_id = false)
{
	global $settings;

	if (empty($system))
	{
		return false;
	}

	if ($is_id !== false)
	{
		$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																				FROM user_log
																				WHERE system_id = '" . $system . "'
																				AND system_id != ''
																				LIMIT 1"));
	}
	else
	{
		$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT id
																				FROM user_log
																				WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "'
																				AND system_name != ''
																				LIMIT 1"));
	}

	if ($logged > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

/*
 *	Replace output text for text-to-speech overrides
 *  Mappings configured in ini:
 *  tts_override["find"] = "replace"
*/

function tts_override($text)
{
	global $settings;

	foreach ($settings["tts_override"] as $find => $replace)
	{
		$text = str_ireplace($find, $replace, $text);
	}

	return $text;
}

/*
*	checks if a system exists in our database
*/

function system_exists($system_name)
{
	$count = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																		id
																		FROM edtb_systems
																		WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
																		LIMIT 1"));
	if ($count == 0)
	{
		$count = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT
																			id
																			FROM user_systems_own
																			WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system_name) . "'
																			LIMIT 1"));
	}

	if ($count > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

/*
*	fetches data from edtb_common
*/

function edtb_common($name, $field, $update = false, $value = "")
{
	if ($update !== true)
	{
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT " . $field . "
															FROM edtb_common
															WHERE name = '" . $name . "'
															LIMIT 1")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		$arr = mysqli_fetch_assoc($res);

		$value = $arr[$field];

		return $value;
	}
	else
	{
		$res = mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
															SET " . $field . " = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $value) . "'
															WHERE name = '" . $name . "'
															LIMIT 1")
															or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
	}
}

/*
*	updates last access time
*/

function update_last_access()
{
	if (!mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE edtb_common SET unixtime = UNIX_TIMESTAMP() WHERE name = 'last_access' LIMIT 1"))
	{
		 write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
		 return false;
	}
	else
	{
		return true;
	}
}

/*
*	returns rank icon/name
*/

function get_rank($type, $rank, $icon = true)
{
	global $ranks;

	if ($icon !== false)
	{
		return "/style/img/ranks/" . $type . "/rank-" . $rank . ".png";
	}
	else
	{
		return $ranks[$type][$rank];
	}
}

/*
*	returns proper ship name
*/

function ship_name($name)
{
	global $ships;

	if (array_key_exists(strtolower($name), $ships))
	{
		$ship_name = $ships[strtolower($name)];
	}
	else
	{
		$ship_name = $name;
	}

	return $ship_name;
}

/*
*	returns distance from current to $system
*/

function get_distance($system, $is_id = false)
{
	// fetch target coordinates
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "	(SELECT
														edtb_systems.x AS target_x,
														edtb_systems.y AS target_y,
														edtb_systems.z AS target_z
														FROM edtb_systems
														WHERE edtb_systems.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "')
														UNION
														(SELECT
														user_systems_own.x AS target_x,
														user_systems_own.y AS target_y,
														user_systems_own.z AS target_z
														FROM user_systems_own
														WHERE user_systems_own.name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $system) . "')
														LIMIT 1")
														or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

	$arr = mysqli_fetch_assoc($res);

	$target_x = $arr["target_x"];
	$target_y = $arr["target_y"];
	$target_z = $arr["target_z"];

	// figure out what coords to calculate from
	$usable_coords = usable_coords();
	$usex = $usable_coords["x"];
	$usey = $usable_coords["y"];
	$usez = $usable_coords["z"];
	$exact = $usable_coords["current"] === true ? "" : " *";

	if (valid_coordinates($target_x, $target_y, $target_z))
	{
		$dist = number_format(sqrt(pow(($target_x-($usex)), 2)+pow(($target_y-($usey)), 2)+pow(($target_z-($usez)), 2)), 2);
		$distance = $dist . ' ly ' . $exact;
	}
	else
	{
		$distance = '';
	}

	return $distance;
}

