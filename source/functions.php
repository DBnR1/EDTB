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
*    get current system
*/

if (is_dir($settings["log_dir"]))
{
    // select the newest  file
    if (!$files = scandir($settings["log_dir"], SCANDIR_SORT_DESCENDING))
	{
		$error = error_get_last();
		write_log($error["message"], __FILE__, __LINE__);
	}
    $newest_file = $files[0];

    // read file to an array
    if (!$line = file("" . $settings["log_dir"] . "/" . $newest_file . ""))
	{
		$error = error_get_last();
		write_log($error["message"], __FILE__, __LINE__);
	}
	else
	{
		//  reverse array
		$lines = array_reverse($line);

		foreach ($lines as $line_num => $line)
		{
			$pos = strrpos($line, "System:");
			if ($pos !== false)
			{
				preg_match_all("/\((.*?)\) B/", $line, $matches);
				$cssystemname = $matches[1][0];
				$current_system = $cssystemname;

				preg_match_all("/\{(.*?)\} System:/", $line, $matches2);
				$visited_time = $matches2[1][0];

				$current_system = isset($current_system) ? $current_system : "";

				$current_coordinates = "";
				$coordx = "";
				$coordy = "";
				$coordz = "";
				$current_id = -1;

				$res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT 	id, population, allegiance, economy, government, ruling_faction, state,
																			security, power, power_state, x, y, z
																	FROM edtb_systems
																	WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $current_system) . "'
																	LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
				$exists = mysqli_num_rows($res);

				if ($exists > 0)
				{
					$arr = mysqli_fetch_assoc($res);

					$current_coordinates = "" . $arr['x'] . "," . $arr['y'] . "," . $arr['z'] . "";
					$current_id = $arr["id"];
					$current_population = $arr["population"];
					$current_allegiance = $arr["allegiance"];
					$current_economy = $arr["economy"];
					$current_government = $arr["government"];
					$current_ruling_faction = $arr["ruling_faction"];
					$current_state = $arr["state"];
					$current_security = $arr["security"];
					$current_power = $arr["power"];
					$current_power_state = $arr["power_state"];

					$coordx = $arr["x"];
					$coordy = $arr["y"];
					$coordz = $arr["z"];
				}
				else
				{
					$cres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																		FROM user_systems_own
																		WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $current_system) . "'
																		LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

					$oexists = mysqli_num_rows($cres);

					if ($oexists > 0)
					{
						$carr = mysqli_fetch_assoc($cres);

						$coordx = $carr["x"] == "" ? "" : $carr["x"];
						$coordy = $carr["y"] == "" ? "" : $carr["y"];
						$coordz = $carr["z"] == "" ? "" : $carr["z"];
						$current_coordinates = "" . $coordx . "," . $coordy . "," . $coordz . "";
					}
					else
					{
						$current_coordinates = "";
						$coordx = "";
						$coordy = "";
						$coordz = "";
					}
				}

				// fetch previous system
				$p_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT value
																	FROM edtb_common
																	WHERE id = '1'
																	LIMIT 1")
																	or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
				$p_arr = mysqli_fetch_assoc($p_res);
				$prev_system = $p_arr["value"];

				if ($prev_system != $cssystemname && $cssystemname != "")
				{
					// add system to user_visited_systems
					$rows = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT system_name
																		FROM user_visited_systems
																		ORDER BY id
																		DESC LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);
					$vs_arr = mysqli_fetch_assoc($rows);

					$visited_on = "" . date("Y-m-d") . " " . $visited_time . "";

					if ($vs_arr["system_name"] != $current_system && $current_system != "")
					{
						mysqli_query($GLOBALS["___mysqli_ston"], "	INSERT INTO user_visited_systems (system_name, visit)
																	VALUES
																	('" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $current_system) . "',
																	'" . $visited_on . "')") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

						// export to edsm
						if ($settings["edsm_api_key"] != "" && $settings["edsm_export"] == "true" && $settings["edsm_cmdr_name"] != "")
						{
							$visited_on_utc = date("Y-m-d H:i:s");
							$export = file_get_contents("http://www.edsm.net/api-logs-v1/set-log?commanderName=" . urlencode($settings["edsm_cmdr_name"]) . "&apiKey=" . $settings["edsm_api_key"] . "&systemName=" . urlencode($current_system) . "&dateVisited=" . urlencode($visited_on_utc) . "");

							write_log($export, __FILE__, __LINE__);
						}

						$newSystem = TRUE;
					}

					// update latest system
					mysqli_query($GLOBALS["___mysqli_ston"], "	UPDATE edtb_common
																SET value = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $current_system) . "'
																WHERE id = '1'
																LIMIT 1") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

					$newSystem = TRUE;
				}
				else
				{
					$newSystem = FALSE;
				}

				GLOBAL $current_coordinates, $coordx, $coordy, $coordz, $current_system, $current_id, $current_population, $current_allegiance, $current_economy, $current_government, $current_ruling_faction, $newSystem;

				break;
			}
		}
	}
}
else
{
	write_log("Error: " . $settings["log_dir"] . " is not a directory", __FILE__, __LINE__);
}

/*
*	 screenshots
*/

if (isset($settings["old_screendir"]) && is_dir($settings["old_screendir"]) && $settings["old_screendir"] != "C:\Users" && $settings["old_screendir"] != "C:\Users\\")
{
	// move screenshots
	if (!$screenshots = scandir($settings["old_screendir"]))
	{
		$error = error_get_last();
		write_log($error["message"], __FILE__, __LINE__);
	}
	else
	{
		$newscreendir = "" . $settings["new_screendir"] . "/" . $prev_system . "";

		$added = 0;
		foreach ($screenshots as $file)
		{
			if (substr($file, -3) == "bmp")
			{
				if (!is_dir($newscreendir))
				{
					if (!mkdir($newscreendir, 0775, true))
					{
						$error = error_get_last();
						write_log("Error: " . $error['message'] . " - Could not create new screendir", __FILE__, __LINE__);
						break;
					}
				}
				$old_file_bmp = "" . $settings["old_screendir"] . "/" . $file . "";
				$old_file_og = "" . $settings["old_screendir"] . "/originals/" . $file . "";
				$edited = "" . date ("Y-m-d_H-i-s", filemtime($old_file_bmp)) . "";
				$new_filename = "" . $edited . "-" . $prev_system . ".jpg";
				$new_file_jpg = "" . $settings["old_screendir"] . "/" . $new_filename . "";
				$new_screenshot = "" . $newscreendir . "/" . $new_filename . "";

				// convert from bmp to jpg
				exec("\"" . $settings["install_path"] . "/bin/ImageMagick/convert\" \"" . $old_file_bmp . "\" \"" . $new_file_jpg . "\"", $out);

				if (!empty($out))
				{
					$error = json_encode($out);
					write_log("Error #8: " . $error . "", __FILE__, __LINE__);
				}

				if ($settings["keep_og"] == "false")
				{
					if (!unlink($old_file_bmp))
					{
						$error = error_get_last();
						write_log("Error: " . $error['message'] . " - Could not remove " . $old_file_bmp . "", __FILE__, __LINE__);
					}
				}
				else
				{
					if (!is_dir("" . $settings["old_screendir"] . "/originals"))
					{
						if (!mkdir("" . $settings["old_screendir"] . "/originals", 0775, true))
						{
							$error = error_get_last();
							write_log("Error: " . $error['message'] . " - Could not create directory " . $settings["old_screendir"] . "/originals", __FILE__, __LINE__);
							break;
						}
					}
					if (!rename("" . $old_file_bmp . "", "" . $old_file_og . ""))
					{
						$error = error_get_last();
						write_log("Error: " . $error['message'] . " - Could not rename " . $old_file_bmp . " to " . $old_file_og . "", __FILE__, __LINE__);
					}
				}
				// move to new screenshot folder
				if (!rename("" . $new_file_jpg . "", "" . $new_screenshot . ""))
				{
					$error = error_get_last();
					write_log("Error: " . $error['message'] . " - Could not rename " . $new_file_jpg . " to " . $new_screenshot . "", __FILE__, __LINE__);
				}
				$added++;

				/*
				*	add no more than 10 at a time
				*/

				if ($added > 10)
				{
					break;
				}
			}
		}
	}
	// make thumbnails for the gallery
	if ($added > 0)
	{
		$thumbdir = "" . $newscreendir . "/thumbs";

		if (!is_dir($thumbdir))
		{
			if (!mkdir("" . $thumbdir . "", 0775, true))
			{
				$error = error_get_last();
				write_log("Error: " . $error['message'] . " - Could not create directory " . $thumbdir . "", __FILE__, __LINE__);
				break;
			}
		}
		exec("\"" . $settings["install_path"] . "/bin/ImageMagick/mogrify\" -resize " . $settings["thumbnail_size"] . " -background #333333 -gravity center -extent " . $settings["thumbnail_size"] . " -format jpg -quality 95 -path \"" . $thumbdir . "\" \"" . $newscreendir . "/\"*.jpg", $out3);

		if (!empty($out3))
		{
			$error = json_encode($out3);
			write_log("Error #5: ". $error . "", __FILE__, __LINE__);
		}
	}
}
else
{
	write_log("Error #11", __FILE__, __LINE__);
}

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
	$notice .= '<div class="notice_title"><img src="/style/img/notice_b.png" alt="Notice" style="vertical-align:middle;" />&nbsp;' . $title . '</div>';
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

function get_timeago($ptime)
{
	$etime = time() - $ptime;

	if( $etime < 1 )
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

	foreach( $a as $secs => $str )
	{
		$d = $etime / $secs;

		if( $d >= 1 )
		{
			$r = round( $d );
			return '' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
		}
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
	if (!is_readable($dir)) return NULL;
	return (count(scandir($dir)) == 2);
}

/*
*	parse data for data point
*/

function set_data($key, $value, $d_x, $d_y, $d_z, &$dist, $table, $enum)
{
	global $coordx, $coordy, $coordz;
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
			$this_row .= '<td style="padding:10px;white-space:nowrap;vertical-align:middle;">' . $distance . ' ' . $exact . '</td>';
		}
		else
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle;">n/a' . $d_x . '</td>';
		}

		$dist = false;
	}
	// make a link for systems with an id
	if ($key == "system_id" && $value != "0")
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle;"><a href="/system.php?system_id=' . $value . '">' . $value . '</a></td>';
	}
	// make a link for systems with system name
	else if ($key == "system_name" && $value != "0" || $key == "name" && $table == "edtb_systems")
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle;"><a href="/system.php?system_name=' . urlencode($value) . '">' . $value . '</a></td>';
	}
	// number format some values
	else if (strrpos($key, "price") !== false || strrpos($key, "ls") !== false || strrpos($key, "population") !== false || strrpos($key, "distance") !== false)
	{
		if (is_numeric($value) && $value != null)
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle;">' . number_format($value) . '</td>';
		}
		else
		{
			$this_row .= '<td style="padding:10px;vertical-align:middle;">n/a</td>';
		}
	}
	// make links
	else if (preg_match($reg_exUrl, $value, $url))
	{
		if (mb_strlen($value) >= 80)
		{
			$urli = "" . substr($value, 0, 80) . "...";
		}
		else
		{
			$urli = $value;
		}
		$this_row .= '<td style="padding:10px;vertical-align:middle;">' . preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $value) . '</td>';
	}
	// make 0,1 human readable
	else if ($enum !== false)
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

		$this_row .= '<td style="padding:10px;text-align:center;vertical-align:middle;">' .  $real_value . '</td>';
	}
	else
	{
		$this_row .= '<td style="padding:10px;vertical-align:middle;">' . substr(strip_tags($value), 0, 100) . '</td>';
	}

	// parse log entries
	if ($key == "log_entry")
	{
		if (mb_strlen($value) >= 100)
		{
			$this_row = '<td style="padding:10px;vertical-align:middle;">' . substr(strip_tags($value), 0, 100) . '...</td>';
		}
		else
		{
			$this_row = '<td style="padding:10px;vertical-align:middle;">' . $value . '</td>';
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

function get_station_icon($type, $planetary = "0", $style = "margin-right:6px;")
{
	$icon = $planetary == "1" ? '<img src="/style/img/spaceports/planetary.png" alt="Planetary" style="' . $style . '" />' : '<img src="/style/img/spaceports/spaceport.png" alt="Starport" style="' . $style . '" />';

	if ($type == "Coriolis Starport")
	{
		$icon = '<img src="/style/img/spaceports/coriolis.png" alt="Coriolis Starport" style="' . $style . '" />';
	}
	else if ($type == "Orbis Starport")
	{
		$icon = '<img src="/style/img/spaceports/orbis.png" alt="Orbis Starport" style="' . $style . '" />';
	}
	else if ($type == "Ocellus Starport")
	{
		$icon = '<img src="/style/img/spaceports/ocellus.png" alt="Ocellus Starport" style="' . $style . '" />';
	}
	else if (stripos($type, "unknown") !== false && $planetary == "0")
	{
		$icon = '<img src="/style/img/spaceports/unknown.png" alt="Unknown" style="' . $style . '" />';
	}

	return $icon;
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
	global $coordx, $coordy, $coordz;

	$usable = array();

	if (is_numeric($coordx) && is_numeric($coordy) && is_numeric($coordz))
	{
		$usable["x"] = $coordx;
		$usable["y"] = $coordy;
		$usable["z"] = $coordz;

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