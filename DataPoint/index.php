<?php
/**
 * Data Point
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

/**
 * Start session
 */
session_start();

/** @require Theme class */
require_once $_SERVER['DOCUMENT_ROOT'] . '/style/Theme.php';

/**
 * initiate page header
 */
$header = new Header();

/** @var string page_title */
$header->pageTitle = 'Data Point';

/**
 * display the header
 */
$header->displayHeader();

/** @require functions file */
require_once __DIR__ . '/functions.php';
/** @require MySQL table edit class */
require_once __DIR__ . '/Vendor/MySQL_table_edit/mte.php';

/** @var string $dataTable */
$dataTable = $_GET['table'] ?? $settings['data_view_default_table'];

/**
 * initate MySQLtabledit class
 */
$tabledit = new MySQLtabledit();

/** @var string table */
$tabledit->table = $dataTable;

/**
 * get column comment from database to use as a name for the fields
 */
$query = "  SELECT COLUMN_NAME, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$dataTable'";

$result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

$output = [];
$showt = [];

while ($columnObj = $result->fetch_object()) {
    $output[] = $columnObj->COLUMN_NAME;
    $showt[$columnObj->COLUMN_NAME] = $columnObj->COLUMN_COMMENT;
}

$result->close();

/** @var array links_to_db */
$tabledit->links_to_db = $settings['data_view_table'];

/** @var array skip */
$tabledit->skip = $settings['data_view_ignore'][$dataTable];

/** @var string primary_key the primary key of the table (must be AUTO_INCREMENT) */
$tabledit->primary_key = 'id';

/** @var array fields_in_list_view the fields you want to see in "list view" */
$tabledit->fields_in_list_view = $output;

/** @var int num_rows_list_view numbers of rows/records in "list view" */
$tabledit->num_rows_list_view = 10;

/** @var array fields_required required fields in edit or add record */
//$tabledit->fields_required = array('name');

/** @var string url_base */
$tabledit->url_base = 'Vendor/MySQL_table_edit/';

/** @var string url_script */
$tabledit->url_script = '/DataPoint';

/** @var array show_text */
$tabledit->show_text = $showt;
?>
    <div class="entries">
        <div class="entries_inner">
            <?php $tabledit->do_it(); ?>
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
$footer->displayFooter();
