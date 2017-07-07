<?php
/** @require functions */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/functions.php';

// no direct access
if (strtolower(basename($_SERVER['PHP_SELF'])) == strtolower(basename(__FILE__))) {
    die('No access...');
}

/**
 * Class MySQLtabledit
 *
 * @property string debug_html
 */
class MySQLtabledit
{
    /**
     *
     * MySQL Edit Table
     *
     * Copyright (c) 2010 Martin Meijer - Browserlinux.com
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     */

    public $table, $primaryKey, $text, $linksToDb, $urlScript, $skip;

    /** the fields you want to see in "list view" */
    public $fieldsInListView;

    /** numbers of rows/records in "list view" */
    public $numRowsListView = 15;

    /** required fields in edit or add record */
    public $fieldsRequired;

    /** help text */
    public $helpText;

    /** visible name of the fields */
    public $showText;

    public $widthEditor = '100%';
    public $widthInputFields = '700px';
    public $widthTextFields = '698px';
    public $heightTextFields = '200px';

    public $urlBase;

    protected $mysqli;

    private $orderBy, $whereSearch, $content, $contentSaved, $contentDeleted, $navTop, $navBottom, $debug, $javascript, $countRequired;

    /**
     * MySQLtabledit constructor.
     */
    public function __construct()
    {
        global $server, $user, $pwd, $db;

        $this->mysqli = new mysqli($server, $user, $pwd, $db);

        if ($this->mysqli->connect_errno) {
            echo 'Failed to connect to MySQL: ' . $this->mysqli->connect_error;
        }
    }

    /**
     * Put it all together
     */
    public function do_it()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/DataPoint/Vendor/MySQL_table_edit/lang/en.php';

        // No cache
        /*if (!headers_sent()) {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header("Cache-control: private");
        }*/

        if (!$this->url_base) {
            $this->url_base = '.';
        }

        // name of the script
        //$break = explode("/", $_SERVER["SCRIPT_NAME"]);
        //$this->url_script = $break[count($break) - 1];

        if ($_GET['mte_a'] == 'edit') {
            $this->edit_rec();
        } elseif ($_GET['mte_a'] == 'new') {
            $this->edit_rec();
        } elseif ($_GET['mte_a'] == 'del') {
            $this->del_rec();
        } elseif ($_POST['mte_a'] == 'save') {
            $this->save_rec();
        } else {
            $this->show_list();
        }

        $this->close_and_print();
    }

    /**
     * Edit or add record
     */
    private function edit_rec()
    {
        $inId = $_GET['id'];

        // edit or new?
        $edit = $_GET['mte_a'] == 'edit' ? 1 : 0;

        $this->count_required = 0;

        $fieldType = $this->get_field_types();

        if (!$edit) {
            $rij = $fieldType;
        } else {
            if ($edit) {
                $whereEdit = "WHERE `$this->primary_key` = $inId";
            }

            $query = "SELECT * FROM `$this->table` $whereEdit LIMIT 1";
            $results = $this->mysqli->query($query);
            $rij = $results->fetch_assoc();
            $results->close();
        }

        $rows = $this->get_fields($rij);

        $this->javascript = "
            function submitform() {
                var ok = 0;
                for (f = 1; f <= $this->count_required; f++)
                {
                    var elem = document.getElementById('id_' + f);

                    if (elem.options) {
                        if (elem.options[elem.selectedIndex].text!=null && elem.options[elem.selectedIndex].text!='') {
                            ok++;
                        }
                    }
                    else {
                        if (elem.value!=null && elem.value!='') {
                            ok++;
                        }
                    }
                }

                if (ok == $this->count_required) {
                    return true;
                } else {
                    alert('{$this->text['Check_the_required_fields']}...')
                    return false;
                }
            }
        ";

        if (!$edit) {
            $closeForm .= '<input type="hidden" name="mte_new_rec" value="1">';
        }
        $closeForm .= '
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="mte_a" value="save">
                            <input class="button button_save" type="submit" value="Save" style="width:80px;margin:20px 0 25px 0">
                        </td>
                    </tr>';

        $this->content .= '
                <div style="width:' . $this->width_editor . '">
                    <form method="post" action="/DataPoint/?table=' . $_GET['table'] . '">
                        <table style="margin-bottom: 20px; border-collapse:collapse;border-spacing: 0">
                            <tr>
                                <td>
                                    <button onclick="window.location=\'' . $_SESSION['hist_page'] . '\'" style="margin: 20px 15px 25px 0">' . $this->text['Go_back'] . '</button>
                                </td>
                            </tr>
                            ' . $rows . '
                            ' . $closeForm . '
                        </table>
                    </form>
                </div>';
    }

    /**
     * Get field types for the table
     *
     * @return mixed
     */
    private function get_field_types()
    {
        $query = "SHOW COLUMNS FROM `$this->table`";
        $types = $this->mysqli->query($query);

        // get field types
        while ($obj = $types->fetch_object()) {
            $fieldType[$obj->Field] = $obj->Type;
        }

        $types->close();

        return $fieldType;
    }

    /**
     * Get fields for editing/adding entries
     *
     * @param array $rij
     * @return string
     */
    private function get_fields($rij)
    {
        // edit or new?
        $edit = $_GET['mte_a'] == 'edit' ? 1 : 0;

        $fieldType = $this->get_field_types();

        foreach ($rij as $key => $value) {
            if (!$edit) {
                $value = '';
            }

            $field = '';
            $options = '';
            $style = '';
            $fieldId = '';
            $readonly = '';

            if (isset($this->fields_required)) {
                if (in_array($key, $this->fields_required)) {
                    $this->count_required++;
                    $style = "class='mte_req'";
                    $fieldId = "id='id_" . $this->count_required . "'";
                }
            }

            $fieldKind = $fieldType[$key];

            /**
             * different fields
             */
            // textarea
            if (preg_match('/text/', $fieldKind)) {
                $field = "<textarea class='textarea' name='$key' $style $fieldId>$value</textarea>";
            }
            // select/options
            elseif (preg_match("/enum\((.*)\)/", $fieldKind, $matches)) {
                $allOptions = substr($matches[1], 1, -1);
                $optionsArray = explode("','", $allOptions);
                foreach ($optionsArray as $option) {
                    if ($option == $value) {
                        $options .= "<option selected>$option</option>";
                    } else {
                        $options .= "<option>$option</option>";
                    }
                }
                unset($option);
                $field = "<select class='selectbox' name='$key' $style $fieldId>$options</select>";
            }
            // input
            elseif (!preg_match('/blob/', $fieldKind)) {
                if (preg_match("/\(*(.*)\)*/", $fieldKind, $matches)) {
                    if ($key == $this->primary_key) {
                        $style = "style='background:#ccc'";
                        $readonly = 'readonly';
                    }
                    $valueHtmlentities = htmlentities($value, ENT_QUOTES);
                    if (!$edit && $key == $this->primary_key) {
                        $field = "<input type='hidden' name='$key' value=''>[auto increment]";
                    } else {
                        // add ajax system name for some fields
                        if ($key == 'system_name') {
                            $field = '<input class="textbox" type="text" id="' . $key . '" name="' . $key . '" value="' . $valueHtmlentities . '" 
                                                maxlength="' . $matches[1] . '" ' . $style . ' ' . $readonly . ' ' . $fieldId . ' 
                                                onkeyup="showResult(this.value, \'37\', \'no\', \'no\', \'no\', \'no\', \'yes\')">
                                                    <div class="suggestions" id="suggestions_37" style="margin-left: 1px"></div>';
                        } else {
                            $field = "<input class='textbox' type='text' id='$key' name='$key' value='$valueHtmlentities' maxlength='{$matches[1]}' $style $readonly $fieldId>";
                        }
                    }
                }
            }
            // blob: don't show
            elseif (preg_match('/blob/', $fieldKind)) {
                $field = '[<i>binary</i>]';
            }

            // make table row
            $background = $background == '#38484f' ? '#273238' : '#38484f';

            if ($this->show_text[$key]) {
                $showKey = $this->show_text[$key];
            } else {
                $showKey = $key;
            }

            $rows .= '<tr style="border-bottom:1px solid #000;background:' . $background . '">
                            <td style="vertical-align: middle; padding: 8px">
                                <strong>' . $showKey . '</strong>
                            </td>
                            <td style="padding: 8px">' . $field . '</td>
                      </tr>';
        }
        unset($value);

        return $rows;
    }

    /**
     * delete record
     */
    private function del_rec()
    {
        $inId = $_GET['id'];

        $stmt = 'DELETE FROM ' . $this->table . ' WHERE `' . $this->primary_key . "` = '$inId'";

        if ($this->mysqli->query($stmt)) {
            $this->content_deleted = "
                <div class='notify_deleted'>
                    Record {$this->show_text[$this->primary_key]} $inId {$this->text['deleted']}
                </div>
            ";
            $this->show_list();
        } else {
            $this->content = "
            </div>
                <div style='padding:2px 20px 20px 20px;margin: 0 0 20px 0;background:#DF0000;color:#fff'><h3>Error</h3>" . $this->mysqli->error . "</div><a href='$this->url_script'>List records...</a>
            </div>";
        }
    }

    /**
     * Show records
     */
    private function show_list()
    {
        // message after add or edit
        $this->content_saved = $_SESSION['content_saved'];
        $_SESSION['content_saved'] = '';

        // default sort (a = ascending)
        $ad = 'a';

        if ($_GET['sort'] && in_array($_GET['sort'], $this->fields_in_list_view)) {
            if ($_GET['ad'] == 'a') {
                $ascDes = 'ASC';
            }
            if ($_GET['ad'] == 'd') {
                $ascDes = 'DESC';
            }
            $this->order_by = 'ORDER by ' . $_GET['sort'] . ' ' . $ascDes ;
        } else {
            $this->order_by = "ORDER by $this->primary_key DESC";
        }

        // navigation 1/3
        $start = $_GET['start'];
        if (!$start) {
            $start = 0;
        } else {
            $start *= 1;
        }

        /**
         * build query_string
         */
        // navigation
        $queryString .= '&start=' . $start;
        // sorting
        $queryString .= '&ad=' . $_GET['ad']  . '&sort=' . $_GET['sort'] ;
        // searching
        $queryString .= '&s=' . $_GET['s']  . '&f=' . $_GET['f'] ;
        //table
        $queryString .= '&table=' . $_GET['table'];

        /**
         * search
         */
        if ($_GET['s'] && $_GET['f']) {
            $inSearch = addslashes(stripslashes($_GET['s']));
            $inSearchField = $_GET['f'];

            if ($inSearchField == $this->primary_key) {
                $this->where_search = "WHERE $inSearchField = '$inSearch' ";
            } else {
                $this->where_search = "WHERE $inSearchField LIKE '%$inSearch%' ";
            }
        }

        /**
         * get sql query
         */
        $sql = $this->get_sql();

        $hits = $this->mysqli->query($sql) or write_log($this->mysqli->error, __FILE__, __LINE__);

        // navigation 2/3
        $hitsTotal = $hits->num_rows;

        $hits->close();

        $sql .= " LIMIT $start, $this->num_rows_list_view";

        $result = $this->mysqli->query($sql) or write_log($this->mysqli->error, __FILE__, __LINE__);

        if ($result->num_rows > 0) {
            $query = "SHOW COLUMNS FROM `$this->table`";
            $cols = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

            while ($obj = $cols->fetch_object()) {
                $Field = $obj->Field;
                $Type = $obj->Type;
                $fieldType[$Field] = $Type;
            }

            $cols->close();

            $count = 0;
            while ($data = $result->fetch_object()) {
                $count++;
                $thisRow = '';

                $background = $background == '#38484f' ? '#273238' : '#38484f';

                $dist = false;
                $dist1 = false;
                $dX = '';
                $dY = '';
                $dZ = '';

                $escSysName = $this->mysqli->real_escape_string($data->system_name);

                if (property_exists($data, 'x') && property_exists($data, 'y') && property_exists($data, 'z') || property_exists($data, 'system_name') || property_exists($data, 'system_id')) {
                    $dist = true;
                    $dist1 = true;

                    if (isset($data->x) && isset($data->y) && isset($data->z)) {
                        $dX = $data->x;
                        $dY = $data->y;
                        $dZ = $data->z;
                    } elseif (isset($data->system_id)) {
                        $query = "  SELECT x, y, z
                                    FROM edtb_systems
                                    WHERE id = '$data->system_id'
                                    LIMIT 1";

                        $coordResult = $this->mysqli->query($query);
                        $found = $coordResult->num_rows;

                        if ($found > 0) {
                            $obj = $coordResult->fetch_object();

                            $dX = $obj->x;
                            $dY = $obj->y;
                            $dZ = $obj->z;
                        }
                        $coordResult->close();
                    } elseif (isset($data->system_name) || $found == 0) {
                        if (valid_coordinates($data->ritem_coordx, $data->ritem_coordy, $data->ritem_coordz)) {
                            $dX = $data->ritem_coordx;
                            $dY = $data->ritem_coordy;
                            $dZ = $data->ritem_coordz;
                        } else {
                            $query = "  SELECT x, y, z
                                        FROM edtb_systems
                                        WHERE name = '$escSysName'
                                        LIMIT 1";

                            $coordResult = $this->mysqli->query($query);
                            $found = $coordResult->num_rows;

                            if ($found > 0) {
                                $obj = $coordResult->fetch_object();

                                $dX = $obj->x;
                                $dY = $obj->y;
                                $dZ = $obj->z;
                            } else {
                                $query = "  SELECT x, y, z
                                            FROM user_systems_own
                                            WHERE name = '$escSysName'
                                            LIMIT 1";

                                $coordResult = $this->mysqli->query($query);
                                $ownFound = $coordResult->num_rows;

                                if ($ownFound > 0) {
                                    $obj = $coordResult->fetch_object();

                                    $dX = $obj->x;
                                    $dY = $obj->y;
                                    $dZ = $obj->z;
                                } else {
                                    $dX = '';
                                    $dY = '';
                                    $dZ = '';
                                }
                            }
                            $coordResult->close();
                        }
                    } else {
                        $dX = '';
                        $dY = '';
                        $dZ = '';
                    }
                }

                $ii = 0;
                foreach ($data as $key => $value) {
                    $fieldKind = $fieldType[$key];

                    $enum = false;
                    $align = '';
                    if ($fieldKind == "enum('','0','1')" || $fieldKind == "enum('0','1')") {
                        $align = 'text-align:center;';
                        $enum = true;
                    }
                    //echo $fieldKind;

                    $sortImage = '';
                    if (in_array($key, $this->fields_in_list_view)) {
                        if ($count == 1) {
                            // show nice text of a value
                            if ($this->show_text[$key]) {
                                $showKey = $this->show_text[$key];
                            } else {
                                $showKey = $key;
                            }

                            // sorting
                            if ($_GET['sort'] == $key && $_GET['ad'] == 'a') {
                                $sortImage = "<img src='/style/img/sort_a.png' style='width:9px;height:8px;border:none' alt='Asc' id='sort_a'>";
                                $ad = 'd';
                            }
                            if ($_GET['sort'] == $key && $_GET['ad'] == 'd') {
                                $sortImage = "<img src='/style/img/sort_d.png' style='width:9px;height:8px;border:none' alt='Desc' id='sort_d'>";
                                $ad = 'a';
                            }

                            // remove sort  and ad and add new ones
                            $querySort = preg_replace('/&(sort|ad)=[^&]*/', '', $queryString) . "&sort=$key&ad=$ad";
                            //

                            if (isset($this->skip)) {
                                if (!in_array($key, $this->skip)) {
                                    $head .= "<td style='white-space:nowrap;padding:10px;" . $align . "'><a data-replace='true' data-target='.rightpanel' href='$this->url_script?$querySort' class='mte_head'>$showKey</a> $sortImage</td>";
                                }
                            } else {
                                $head .= "<td style='white-space:nowrap;padding:10px;" . $align . "'><a data-replace='true' data-target='.rightpanel' href='$this->url_script?$querySort' class='mte_head'>$showKey</a> $sortImage</td>";
                            }

                            // add distance if x,y,z are defined
                            if ($dist1 !== false) {
                                if ($_GET['sort'] == 'distance' && $_GET['ad'] == 'a') {
                                    $sortImage = "<img src='/style/img/sort_a.png' style='width:9px;height:8px;border:none' alt=''>";
                                    $ad = 'd';
                                }
                                if ($_GET['sort'] == 'distance' && $_GET['ad'] == 'd') {
                                    $sortImage = "<img src='/style/img/sort_d.png' style='width:9px;height:8px;border:none' alt=''>";
                                    $ad = 'a';
                                }

                                $querySortD = preg_replace('/&(sort|ad)=[^&]*/', '', $queryString) . "&sort=distance&ad=$ad";

                                $head .= "<td style='white-space:nowrap;padding:10px'><a data-replace='true' data-target='.rightpanel' href='$this->url_script?$querySortD' class='mte_head'>Distance</a> $sortImage</td>";
                                $dist1 = false;
                            }
                        }
                        if ($key == $this->primary_key) {
                            if (substr($this->table, 0, 4) == 'edtb') {
                                $buttons = "<td style='width:1%;white-space:nowrap;padding:10px;vertical-align:middle'></td>";
                            } else {
                                $buttons = "<td style='width:1%;white-space:nowrap;padding:10px;vertical-align:middle'><a href='javascript:void(0)' onclick='del_confirm($value)' class='delete_record' title='Delete {$this->show_text[$key]} $value' id='delete_" . $value . "'><img src='/style/img/del.png' style='width:16px;height:16px;border:none' alt='Delete' class='data_point_delete'></a>&nbsp;<a href='?$queryString&mte_a=edit&id=$value' class='edit_record' title='Edit {$this->show_text[$key]} $value' id='edit_" . $value . "'><img src='/style/img/edit.png' style='width:16px;height:16px;border:none' alt='Edit' class='data_point_edit'></a></td>";
                            }

                            if ($key == 'id' && $this->table == 'edtb_systems') {
                                $thisRow .= "<td style='width:1%;padding:10px;vertical-align:middle'><a href='/System?system_id=" . $value . "'>" . $value . '</a></td>';
                            } else {
                                $thisRow .= "<td style='width:1%;padding:10px;vertical-align:middle'>$value</td>";
                            }
                        } else {
                            if (isset($this->skip)) {
                                if (!in_array($key, $this->skip)) {
                                    $thisRow .= setData($key, $value, $dX, $dY, $dZ, $dist, $this->table, $enum);
                                }
                            } else {
                                $thisRow .= setData($key, $value, $dX, $dY, $dZ, $dist, $this->table, $enum);
                            }
                        }
                        $ii++;
                    }
                }
                unset($value);
                $rows .= "<tr style='border-bottom:1px solid #000;background:$background'>$buttons $thisRow</tr>";
            }
        } else {
            $head = "<td style='padding:40px'>{$this->text['Nothing_found']}...</td>";
        }

        // navigation 3/3

        // remove start= from url
        $queryNav = preg_replace('/&(start|mte_a|id)=[^&]*/', '', $queryString);

        // this page
        $thisPage = ($this->num_rows_list_view + $start) / $this->num_rows_list_view;

        // last page
        $lastPage = ceil($hitsTotal / $this->num_rows_list_view);

        // navigatie numbers
        if ($thisPage>10) {
            $vanaf = $thisPage - 10;
        } else {
            $vanaf = 1;
        }

        if ($lastPage > $thisPage + 10) {
            $tot = $thisPage + 10;
        } else {
            $tot = $lastPage;
        }

        for ($f = $vanaf; $f <= $tot; $f++) {
            $navToon = $this->num_rows_list_view * ($f - 1);

            if ($f == $thisPage) {
                $navigation .= "<td class='mte_nav' style='color:#fffffa;background-color:#808080;font-weight:700'>$f</td> ";
            } else {
                $navigation .= "<td class='mte_nav' style='background-color:#0e0e11'><a data-replace='true' data-target='.rightpanel' class='mtelink' href='$this->url_script?$queryNav&start=$navToon'>$f</a></td>";
            }
        }
        if ($hitsTotal < $this->num_rows_list_view) {
            $navigation = '';
        }

        // Previous if
        if ($thisPage > 1) {
            $last =  (($thisPage - 1) * $this->num_rows_list_view) - $this->num_rows_list_view;
            $lastPageHtml = "<a data-replace='true' data-target='.rightpanel' href='$this->url_script?$queryNav&start=$last' class='mte_nav_prev_next'>{$this->text['Previous']}</a>";
        }

        // Next if:
        if ($thisPage != $lastPage && $hitsTotal>1) {
            $next =  $start + $this->num_rows_list_view;
            $nextPageHtml =  "<a data-replace='true' data-target='.rightpanel' href='$this->url_script?$queryNav&start=$next' class='mte_nav_prev_next'>{$this->text['Next']}</a>";
        }

        $this->nav_bottom = '<span class="right" style="padding-top: 6px">Number of entries: ';
        $this->nav_bottom .= number_format($hitsTotal);
        $this->nav_bottom .= '</span>';

        if ($navigation) {
            $navTable = "
                <table style='border-collapse:separate;border-spacing:5px;margin-left:35%;margin-right:auto'>
                    <tr>
                        <td style='padding-right:6px;vertical-align:middle'>$lastPageHtml</td>
                        $navigation
                        <td style='padding-left:6px;vertical-align:middle'>$nextPageHtml</td>
                    </tr>
                </table>
            ";

            $this->nav_top = "
                <div style='margin-bottom:5px;margin-top:-20px;width:$this->width_editor'>
                        $navTable
                </div>
            ";

            $this->nav_bottom .= "
                <div style='margin-top:20px;width:100%;text-align:center'>
                        $navTable
                </div>
            ";
        }

        /**
         * Search form + Add Record button
         */
        foreach ($this->fields_in_list_view as $option) {
            $showOption = $this->show_text[$option] ? $this->show_text[$option] : $option;

            $options .= $option == $inSearchField ? '<option selected value="' . $option . '">' . $showOption . '</option>' : '<option value="' . $option . '">' . $showOption . '</option>';
        }
        unset($option);

        $inSearchValue = htmlentities(trim(stripslashes($_GET['s'])), ENT_QUOTES);

        $seachForm = "
            <table style='margin-left:0;padding-left:0;border-collapse:collapse;border-spacing:0;width:100%'>
                <tr>
                    <td style='white-space:nowrap;padding-bottom:20px'>
                        <form method=get action='$this->url_script' id='search_form'>
                            <input type='hidden' name='table' value='" . $_GET['table'] . "'>
                            <select class='selectbox' name='f'>$options</select>
                            <input class='textbox' type='text' name='s' value='$inSearchValue' style='width:220px'>
                            <input class='button' type='submit' value='{$this->text['Search']}' style='width:80px'>
                ";

        $seachForm .= '</form>';

        if ($_GET['s'] && $_GET['f']) {
            $seachForm .= "<button class='button button_clear' onclick='window.location=\"$this->url_script\"' style='margin: 0 0 10px 10px'>{$this->text['Clear_search']}</button>";
        }

        $seachForm .= '
                    </td>

                    <td style="text-align: right">';
        if (substr($this->table, 0, 4) != 'edtb') {
            $seachForm .= "<button class='button button_add' onclick='window.location=\"$this->url_script?$queryString&mte_a=new\"' style='margin: 0 0 10px 10px'>{$this->text['Add_Record']}</button>";
        } else {
            $seachForm .= '&nbsp;';
        }
        $seachForm .= '</td>

                </tr>
            </table>
        ';

        $this->javascript = "
            function del_confirm(id) {
                if (confirm('{$this->text['Delete']} record {$this->show_text[$this->primary_key]} ' + id + '...?')) {
                    window.location=window.location.href + '&mte_a=del&id=' + id
                }
            }
        ";
        // page content
        $this->content = "
            <div style='width: $this->width_editor;background:transparent;margin:0;border:none'>$seachForm</div>
            <table style='text-align:left;margin:0;border-collapse:collapse;border-spacing:0;width:$this->width_editor'>
                <tr style='background:#0e0e11; color: #fff'><td></td>$head</tr>
                $rows
            </table>

            $this->nav_bottom
        ";
    }

    /**
     * Determine SQL query to use
     *
     * @return string
     */
    private function get_sql()
    {
        /**
         * select
         */
        $sql = "SELECT SQL_CACHE * FROM `$this->table` $this->where_search $this->order_by";

        /**
         * if sorting by distance
         */
        if ($_GET['sort'] && $_GET['sort'] == 'distance') {
            if ($_GET['ad'] == 'a') {
                $ascDes = 'DESC';
            }
            if ($_GET['ad'] == 'd') {
                $ascDes = 'ASC';
            }

            // figure out what coords to calculate from
            $usableCoords = usable_coords();
            $rusex = $usableCoords['x'];
            $rusey = $usableCoords['y'];
            $rusez = $usableCoords['z'];

            $query = "SHOW COLUMNS FROM `$this->table`";

            $columns = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

            while ($obj = $columns->fetch_object()) {
                $fields[] = $this->table . ' . ' . $obj->Field;
            }

            $columns->close();

            $fieldss = join(',', $fields);

            if ($ascDes == 'DESC') {
                $this->order_by = "ORDER BY -(sqrt(pow((ritem_coordx-($rusex)),2)+pow((ritem_coordy-($rusey)),2)+pow((ritem_coordz-($rusez)),2)))" . $ascDes;
            } else {
                $this->order_by = "ORDER BY sqrt(pow((ritem_coordx-($rusex)),2)+pow((ritem_coordy-($rusey)),2)+pow((ritem_coordz-($rusez)),2)) DESC";
            }

            if ($this->table == 'edtb_systems') {
                $sql = 'SELECT ' . $fieldss . ",edtb_systems.x AS ritem_coordx,
                                                edtb_systems.y AS ritem_coordy,
                                                edtb_systems.z AS ritem_coordz
                                                FROM $this->table
                                                $this->order_by";
            } elseif ($this->table == 'edtb_stations') {
                $sql = 'SELECT ' . $fieldss . ",edtb_systems.x AS ritem_coordx,
                                                edtb_systems.y AS ritem_coordy,
                                                edtb_systems.z AS ritem_coordz
                                                FROM $this->table
                                                LEFT JOIN edtb_systems ON $this->table.system_id = edtb_systems.id
                                                $this->order_by";
            } else {
                $sql = 'SELECT ' . $fieldss . ",IFNULL(edtb_systems.x, user_systems_own.x) AS ritem_coordx,
                                                IFNULL(edtb_systems.y, user_systems_own.y) AS ritem_coordy,
                                                IFNULL(edtb_systems.z, user_systems_own.z) AS ritem_coordz
                                                FROM $this->table
                                                LEFT JOIN edtb_systems ON $this->table.system_name = edtb_systems.name
                                                LEFT JOIN user_systems_own ON $this->table.system_name = user_systems_own.name
                                                $this->order_by";
            }
        }

        return $sql;
    }

    /**
     * Save record
     */
    private function save_rec()
    {
        $inMteNewRec = $_POST['mte_new_rec'];

        $updates = '';

        foreach ($_POST as $key => $value) {
            if ($key == $this->primary_key) {
                $inId = $value;
                $where = "$key = $value";
            }
            if ($key != 'mte_a' && $key != 'mte_new_rec' && $key != 'option') {
                if ($inMteNewRec) {
                    $insertFields .= " `$key`,";
                    $insertValues .= " '" . addslashes(stripslashes($value)) . "',";
                } else {
                    $updates .= " `$key` = '" . addslashes(stripslashes($value)) . "' ,";
                }
            }
        }
        unset($value);

        $insertFields = substr($insertFields, 0, -1);
        $insertValues = substr($insertValues, 0, -1);
        $updates = substr($updates, 0, -1);

        // new record
        if ($inMteNewRec) {
            $sql = "INSERT INTO `$this->table` ($insertFields) VALUES ($insertValues); ";
        }
        // edit record
        else {
            $sql = "UPDATE `$this->table` SET $updates WHERE $where LIMIT 1; ";
        }

        if ($this->mysqli->query($sql)) {
            if ($inMteNewRec) {
                $savedId = $this->mysqli->insert_id;
                $_GET['s'] = $savedId;
                $_GET['f'] = $this->primary_key;
            } else {
                $savedId = $inId;
            }

            if ($this->show_text[$this->primary_key]) {
                $showPrimaryKey = $this->show_text[$this->primary_key];
            } else {
                $showPrimaryKey = $this->primary_key;
            }

            $_SESSION['content_saved'] = '
                <div class="notify_success">
                    Record ' . $showPrimaryKey . ' <span id="saved_id">' . $savedId . '</span> ' . $this->text['saved'] . '
                </div>
                ';

            if ($inMteNewRec) {
                echo "<script>window.location='?start=0&f=&sort=" . $this->primary_key . '&table=' . $this->table . "&ad=d'";
                echo '</script>';
            } else {
                echo "<script>window.location='" . $_SESSION['hist_page'] . "'</script>";
            }
        } else {
            $this->content = "
                <div style='width: $this->width_editor'>
                    <div style='padding:2px 20px 20px 20px;margin:0 0 20px 0;background:#DF0000;color:#fff'><h3>Error</h3>" . $this->mysqli->error . "</div><a href='{$_SESSION['hist_page']}'>{$this->text['Go_back']}...</a>
                </div>";
        }
    }

    /**
     *
     */
    private function close_and_print()
    {
        // debug and warning no htaccess
        if ($this->debug) {
            $this->debug .= '<br>';
        }

        if ($this->debug) {
            $this->debug_html = "
            <div style='width: $this->width_editor'>
                <div class='mte_mess' style='background:#DD0000'>$this->debug</div>
            </div>";
        }

        // save page location
        $sessionHistPage = $this->url_script . '?' . $_SERVER['QUERY_STRING'];

        // no page history on the edit page because after refresh the Go Back is useless
        if (!$_GET['mte_a']) {
            $_SESSION['hist_page'] = $sessionHistPage;
        }

        echo "
        <div class='mte_content'>
        <script>
            $this->javascript
        </script>
            <div class='mte_head_1' style='text-align:center'><ul class='pagination'>";

        //$count = count($this->links_to_db);
        $i = 0;
        foreach ($this->links_to_db as $linkH => $linkT) {
            if ($this->table == $linkH) {
                $active = ' class="actives"';
            } else {
                $active = '';
            }

            if (($i % 7) == 0) {
                echo '</ul><br><ul class="pagination" style="margin-top:-26px">';
            }

            echo '<li' . $active . '><a data-replace="true" data-target=".rightpanel" class="mtelink" href="/DataPoint?table=' . $linkH . '">' . $linkT . '</a></li>';
            $i++;
        }

        echo "</ul></div>
            $this->nav_top
            $this->debug_html
            $this->content_saved
            $this->content_deleted
            $this->content
        </div>
        ";
    }
}
