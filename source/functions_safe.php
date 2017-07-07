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

/** @require ini config */
require_once __DIR__ . '/config_ini.inc.php';

/**
 * Display notice message
 *
 * @param string $msg message to display
 * @param string $title title for the message
 * @return string $notice
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function notice($msg, $title = 'Notice')
{
    $notice = '<div class="notice">';
    $notice .= '<div class="notice_title"><img src="/style/img/notice_b.png" alt="Notice" class="icon" style="margin-bottom: 3px">' . $title . '</div>';
    $notice .= '<div class="notice_text">' . $msg . '</div>';
    $notice .= '</div>';

    return $notice;
}

/** @var string $uAgent the users user_agent*/
$uAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

/**
 * Get user's browser and platform
 *
 * @return array
 * @author ruudrp http://php.net/manual/en/function.get-browser.php#101125
 */
function getBrowser()
{
    global $uAgent;

    $bname = 'Unknown';
    $platform = 'Unknown';

    // First get the platform?
    if (preg_match('/linux/i', $uAgent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $uAgent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $uAgent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $uAgent) && !preg_match('/Opera/i', $uAgent)) {
        $bname = 'Internet Explorer';
        $ub = 'MSIE';
    } elseif (preg_match('/Firefox/i', $uAgent)) {
        $bname = 'Mozilla Firefox';
        $ub = 'Firefox';
    } elseif (preg_match('/Chrome/i', $uAgent)) {
        $bname = 'Google Chrome';
        $ub = 'Chrome';
    } elseif (preg_match('/Safari/i', $uAgent)) {
        $bname = 'Apple Safari';
        $ub = 'Safari';
    } elseif (preg_match('/Opera/i', $uAgent)) {
        $bname = 'Opera';
        $ub = 'Opera';
    } elseif (preg_match('/Netscape/i', $uAgent)) {
        $bname = 'Netscape';
        $ub = 'Netscape';
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . implode('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $uAgent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($uAgent, 'Version') < strripos($uAgent, $ub)) {
            $version= $matches['version'][0];
        } else {
            $version= $matches['version'][1];
        }
    } else {
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version === '') {
        $version = '?';
    }

    return [
        'userAgent' => $uAgent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'   => $pattern
    ];
}

/**
 * Get user's OS
 *
 * @return string $osPlatform
 * @author Gaurang http://stackoverflow.com/questions/3441880/get-users-os-and-version-number/15497878#15497878
 */
function getOS()
{
    global $uAgent;

    $osPlatform    = 'Unknown OS Platform';

    $osArray       =   [
                            '/windows nt 10/i'      =>  'Windows 10',
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
    ];

    foreach ($osArray as $regex => $value) {
        if (preg_match($regex, $uAgent)) {
            $osPlatform = $value;
        }
    }

    return $osPlatform;
}

/**
 * Converts bytes into human readable file size.
 *
 * @param string|float $bytes
 * @return string human readable file size (2,87 ??)
 * @author Mogilev Arseny
 */
function FileSizeConvert($bytes)
{
    $bytes = (float)$bytes;
    $arBytes = [
        0 => [
            'UNIT' => 'TB',
            'VALUE' => 1024 ** 4
        ],
        1 => [
            'UNIT' => 'GB',
            'VALUE' => 1024 ** 3
        ],
        2 => [
            'UNIT' => 'MB',
            'VALUE' => 1024 ** 2
        ],
        3 => [
            'UNIT' => 'KB',
            'VALUE' => 1024
        ],
        4 => [
            'UNIT' => 'B',
            'VALUE' => 0
        ],
    ];

    $result = null;
    foreach ($arBytes as $arItem) {
        if ($bytes >= $arItem['VALUE']) {
            $result = $bytes / $arItem['VALUE'];
            $result = str_replace('.', ',', (string)round($result, 2)) . ' ' . $arItem['UNIT'];
            break;
        }
    }
    return $result;
}

/**
 * Get time elapsed in string
 * http://stackoverflow.com/questions/27330650/how-to-display-time-in-x-days-ago-in-php
 *
 * @param int $ptime unix timestamp
 * @param bool $diff
 * @param bool $format
 * @return string $ret
 * @author Arun Kumar
 */
function get_timeago($ptime, $diff = true, $format = false)
{
    global $systemTime;

    $ptimeOg = $ptime;

    if ($diff === true) {
        $ptime -= ($systemTime * 60 * 60);
    }
    $etime = time() - $ptime;

    if ($etime < 1) {
        return 'less than ' . $etime . ' second ago';
    }

    $a = [
        12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    ];

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;

        if ($d >= 1) {
            $r = round($d);
            if ($format !== true) {
                return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }

            if (data_is_old($ptimeOg)) {
                return '<span class="old_data">' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago</span>';
            }

            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}

/**
 * Check if directory is empty
 * http://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty
 *
 * @param string $dir
 * @return string
 * @author Your Common Sense
 */
function is_dir_empty($dir)
{
    if (!is_readable($dir)) {
        return null;
    }

    return (count(scandir($dir)) === 2);
}

/**
 * Write an error log
 *
 * @param string $msg text to write
 * @param string $file
 * @param string $line
 * @param bool $debugOverride
 * @author Mauri Kujala <contact@edtb.xyz>
 */
function write_log($msg, $file = '', $line = '', $debugOverride = false)
{
    global $settings, $systemTime;

    if ($debugOverride !== false || (isset($settings['debug']) && $settings['debug'] === 'true')) {
        // write user info file if not exists
        $lfile = $_SERVER['DOCUMENT_ROOT'] . '/edtb_log_info.txt';
        if (!file_exists($lfile)) {
            $ua = getBrowser();
            $debugInfo = 'Browser: ' . $ua['name'] . ' ' . $ua['version'] . ' (' .$ua['platform'] . ')' . PHP_EOL;
            $debugInfo .= 'Platform: ' . getOS() . PHP_EOL;
            $debugInfo .= 'Reported as: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
            $debugInfo .= 'HTTP_HOST: ' . $_SERVER['HTTP_HOST'] . PHP_EOL;
            $debugInfo .= 'SERVER_SOFTWARE: ' . $_SERVER['SERVER_SOFTWARE'] . PHP_EOL;
            $debugInfo .= 'SERVER_NAME: ' . $_SERVER['SERVER_NAME'] . PHP_EOL;
            $debugInfo .= 'SERVER_ADDR: ' . $_SERVER['SERVER_ADDR'] . PHP_EOL;
            $debugInfo .= 'SERVER_PORT: ' . $_SERVER['SERVER_PORT'] . PHP_EOL;
            $debugInfo .= 'DOCUMENT_ROOT: ' . $_SERVER['DOCUMENT_ROOT'] . PHP_EOL;

            file_put_contents($lfile, $debugInfo);
        }

        $logfile = $_SERVER['DOCUMENT_ROOT'] . '/edtb_log.txt';
        $fd = fopen($logfile, 'ab');

        $where = '';
        if (isset($file)) {
            $onLine = $line === '' ? '' : ' on line ' . $line;
            $where = '[' . $file . '' . $onLine . '] ';
        }

        $str = '[' . date('d.m.Y H:i:s', time() + $systemTime * 60 * 60) . ']' . $where . $msg;

        fwrite($fd, $str . PHP_EOL);
        fclose($fd);
    }
}
