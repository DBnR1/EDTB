<?php
/**
 * Theme class
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

namespace EDTB\style;

/** @require header class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Header.php';
/** @require footer class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Footer.php';

/**
 * Class Theme
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class Theme
{
    /**
     * Get the sidebar style the user is using
     *
     * @return string
     */
    public static function sidebarStyle(): string
    {
        if (isset($_COOKIE['style']) && $_COOKIE['style'] === 'narrow') {
            return 'narrow';
        }

        return 'normal';
    }

}
