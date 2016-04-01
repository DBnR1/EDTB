<?php
/**
 * Points of Interest and Bookmarks
 *
 * Front-end file for Points of interest and Bookmarks.
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
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/Theme.class.php");

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->page_title = "Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks";

/**
 * display the header
 */
$header->display_header();

require_once("add_poi.php");
?>
    <div class="entries">
        <!-- points of interest and bookmarks are rendered here -->
        <div class="entries_inner" id="poi_bm">
        </div>
    </div>
<?php
/**
 * initiate page footer
 */
$footer = new Footer();

/**
 * display the footer
 */
$footer->display_footer();

