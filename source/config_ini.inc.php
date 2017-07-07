<?php
/**
 * Config file
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

/**
 * Register project-specific autoloader for classes, adapted from the PSR-4 example file
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {

    /** @var string $prefix project-specific namespace prefix */
    $prefix = 'EDTB\\';

    /** @var string $baseDir base directory for the namespace prefix */
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relativeClass = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

/** @var string $iniDir ini file directory */
$iniDir = str_replace("\\EDTB\\source", '', __DIR__);
$iniDir = str_replace("\\", '/', $iniDir);
/** @var string ini_file ini file */
$iniFile = $iniDir . '/data/edtoolbox_v1.ini';

/** @var array $settings global user settings variable */
$settings = parse_ini_file($iniFile);

/**
 * set the new screendir if it's empty
 */
$settings['new_screendir'] = empty($settings['new_screendir']) ? $iniDir . '/EDTB/screenshots' : $settings['new_screendir'];
