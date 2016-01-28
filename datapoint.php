<?php
/*
*  ED ToolBox, a companion web app for the video game Elite Dangerous
*  (C) 1984 - 2016 Frontier Developments Plc.
*  ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

/**
 * Data point
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
*/

session_start();

$pagetitle = "Data Point";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/Vendor/MySQL_table_edit/mte.php");

$data_table = $_GET["table"] != "" ? $_GET["table"] : $settings['data_view_default_table'];

$tabledit = new MySQLtabledit();
// database settings:
$tabledit->database = $db;
$tabledit->host = $server;
$tabledit->user = $user;
$tabledit->pass = $pwd;

$tabledit->database_connect();
$tabledit->table = $data_table;

$colres = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT COLUMN_NAME, COLUMN_COMMENT
														FROM INFORMATION_SCHEMA.COLUMNS
														WHERE table_name = '" . $data_table . "'");

$output = array();
$showt = array();
while ($colarr = mysqli_fetch_assoc($colres))
{
    $output[] = $colarr['COLUMN_NAME'];
    $showt[$colarr['COLUMN_NAME']] = $colarr['COLUMN_COMMENT'];
}

$tabledit->links_to_db = $settings['data_view_table'];

$tabledit->skip = $settings['data_view_ignore'][$data_table];
// the primary key of the table (must be AUTO_INCREMENT)
$tabledit->primary_key = 'id';

// the fields you want to see in "list view"
$tabledit->fields_in_list_view = $output;

$tabledit->language = 'en';

// numbers of rows/records in "list view"
$tabledit->num_rows_list_view = 10;

// required fields in edit or add record
//$tabledit->fields_required = array('name');

$tabledit->url_base = '/source/Vendor/MySQL_table_edit/';

$tabledit->show_text = $showt;

$tabledit->width_editor = '100%';

// warning no .htacces ('on' or 'off')
$tabledit->no_htaccess_warning = 'off';

echo '<div class="entries"><div class="entries_inner">';
$tabledit->do_it();
echo '</div></div>';

require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");

$tabledit->database_disconnect();
