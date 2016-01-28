<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/functions.php");

// no direct access
if (strtolower(basename($_SERVER['PHP_SELF'])) == strtolower(basename(__FILE__)))
{
	die('No access...');
}

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

	var $version = '0.3'; // 03 jan 2011

	# text
	var $text;

	# language
	var $language = 'en';

	# database settings
	var $database;
	var $host;
	var $user;
	var $pass;

	# table of the database
	var $table;

	# the primary key of the table
	var $primary_key;

	# the fields you want to see in "list view"
	var $fields_in_list_view;

	# numbers of rows/records in "list view"
	var $num_rows_list_view = 15;

	# required fields in edit or add record
	var $fields_required;

	# help text
	var $help_text;

	# visible name of the fields
	var $show_text;

	var $width_editor = '100%';
	var $width_input_fields = '700px';
	var $width_text_fields = '698px';
	var $height_text_fields = '200px';

	# warning no .htacces ('on' or 'off')
	var $no_htaccess_warning = 'on';

	# Forget this - working on it...
	# needed in Joomla for images/css, example: 'http://www.website.com/administrator/components/com_componentname'
	var $url_base;
	# needed in Joomla, example: 'option=com_componentname'
	var $query_joomla_component;

	###########################
	function database_connect()
	{
		if (!($GLOBALS["___mysqli_ston"] = mysqli_connect($this->host,  $this->user,  $this->pass)))
		{
			die('Could not connect: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		}
		((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $this->database));
	}

	##############################
	function database_disconnect()
	{
		((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
	}


	################
	function do_it()
	{
		// Sorry: in Joomla, remove the next two lines and place the language vars instead
		require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/Vendor/MySQL_table_edit/lang/en.php");
		require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/source/Vendor/MySQL_table_edit/lang/" . $this->language . ".php");

		# No cache
		if (!headers_sent())
		{
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
			header("Cache-control: private");
		}

		if (!$this->url_base) $this->url_base = '.';

		# name of the script
		$break = explode('/', $_SERVER["SCRIPT_NAME"]);
		$this->url_script = $break[count($break) - 1];

		if ($_GET['mte_a'] == 'edit')
		{
			$this->edit_rec();
		}
		elseif ($_GET['mte_a'] == 'new')
		{
			$this->edit_rec();
		}
		elseif ($_GET['mte_a'] == 'del')
		{
			 $this->del_rec();
		}
		elseif ($_POST['mte_a'] == 'save')
		{
			$this->save_rec();
		}
		else
		{
			$this->show_list();
		}

		$this->close_and_print();
	}

	####################
	function show_list()
	{
		# message after add or edit
		$this->content_saved = $_SESSION['content_saved'];
		$_SESSION['content_saved'] = '';

		# default sort (a = ascending)
		$ad = 'a';

		if ($_GET['sort'] && in_array($_GET['sort'], $this->fields_in_list_view))
		{
			if ($_GET['ad'] == 'a') $asc_des = 'ASC';
			if ($_GET['ad'] == 'd') $asc_des = 'DESC';
			$order_by = "ORDER by " . $_GET['sort'] . ' ' . $asc_des ;
		}
		else
		{
			$order_by = "ORDER by $this->primary_key DESC";
		}

		# navigation 1/3
		$start = $_GET["start"];
		if (!$start) {$start = 0;} else {$start *=1;}

		// build query_string
		// query_joomla_component (joomla)
		if ($this->query_joomla_component) $query_string = '&option=' . $this->query_joomla_component ;
		// navigation
		$query_string .= '&start=' . $start;
		// sorting
		$query_string .= '&ad=' . $_GET['ad']  . '&sort=' . $_GET['sort'] ;
		// searching
		$query_string .= '&s=' . $_GET['s']  . '&f=' . $_GET['f'] ;
		//table
		$query_string .= '&table=' . $_GET['table']  . '';

		# search
		if ($_GET['s'] && $_GET['f'])
		{

			$in_search = addslashes(stripslashes($_GET['s']));
			$in_search_field = $_GET['f'];

			if ($in_search_field == $this->primary_key)
			{
				$where_search = "WHERE $in_search_field = '$in_search' ";
			}
			else
			{
				$where_search = "WHERE $in_search_field LIKE '%$in_search%' ";
			}
		}

		# select
		$sql = "SELECT * FROM `$this->table` $where_search $order_by";

		/*
		*	if sorting by distance
		*/

		if ($_GET['sort'] && $_GET['sort'] == "distance")
		{
			if ($_GET['ad'] == 'a') $asc_des = 'DESC';
			if ($_GET['ad'] == 'd') $asc_des = 'ASC';

			// figure out what coords to calculate from
			$usable_coords = usable_coords();
			$rusex = $usable_coords["x"];
			$rusey = $usable_coords["y"];
			$rusez = $usable_coords["z"];

			$res2 = mysqli_query($GLOBALS["___mysqli_ston"], "	SHOW COLUMNS
																FROM `$this->table`")
																or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			while ($rij3 = mysqli_fetch_assoc($res2))
			{
				$fields[] = $this->table . '.' . $rij3["Field"];
			}

			$fieldss = join(",", $fields);

			if ($asc_des == "DESC")
			{
				$order_by = "ORDER BY -(sqrt(pow((ritem_coordx-(" . $rusex . ")),2)+pow((ritem_coordy-(" . $rusey . ")),2)+pow((ritem_coordz-(" . $rusez . ")),2)))" . $asc_des;
			}
			else
			{
				$order_by = "ORDER BY sqrt(pow((ritem_coordx-(" . $rusex . ")),2)+pow((ritem_coordy-(" . $rusey . ")),2)+pow((ritem_coordz-(" . $rusez . ")),2)) DESC";
			}

			if ($this->table == "edtb_systems")
			{
				$sql = "SELECT " . $fieldss . ",edtb_systems.x AS ritem_coordx,
												edtb_systems.y AS ritem_coordy,
												edtb_systems.z AS ritem_coordz
												FROM $this->table
												$order_by";

			}
			elseif ($this->table == "edtb_stations")
			{
				$sql = "SELECT " . $fieldss . ",edtb_systems.x AS ritem_coordx,
												edtb_systems.y AS ritem_coordy,
												edtb_systems.z AS ritem_coordz
												FROM $this->table
												LEFT JOIN edtb_systems ON $this->table.system_id = edtb_systems.id
												$order_by";

			}
			else
			{
				$sql = "SELECT " . $fieldss . ",IFNULL(edtb_systems.x, user_systems_own.x) AS ritem_coordx,
												IFNULL(edtb_systems.y, user_systems_own.y) AS ritem_coordy,
												IFNULL(edtb_systems.z, user_systems_own.z) AS ritem_coordz
												FROM $this->table
												LEFT JOIN edtb_systems ON $this->table.system_name = edtb_systems.name
												LEFT JOIN user_systems_own ON $this->table.system_name = user_systems_own.name
												$order_by";
			}
			//write_log($sql);
		}

		$result = mysqli_query($GLOBALS["___mysqli_ston"], $sql);

		# navigation 2/3
		$hits_total = mysqli_num_rows($result);

		$sql .= " LIMIT $start, $this->num_rows_list_view";
		$result = mysqli_query($GLOBALS["___mysqli_ston"], $sql) or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

		if (mysqli_num_rows($result) > 0)
		{
			$result2 = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW COLUMNS FROM `$this->table`") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

			while ($rij2 = mysqli_fetch_assoc($result2))
			{
				extract($rij2);
				$field_type[$Field] = $Type;
			}

			$count = 0;
			while ($rij = mysqli_fetch_assoc($result))
			{
				$count++;
				$this_row = '';

				if ($background == '#38484F')
				{
					$background='#273238';
				}
				else
				{
					$background='#38484F';
				}

				$dist = false;
				$dist1 = false;
				$exact = "";
				$d_x = "";
				$d_y = "";
				$d_z = "";

				if (array_key_exists("x", $rij) && array_key_exists("y", $rij) && array_key_exists("z", $rij) || array_key_exists("system_name", $rij) || array_key_exists("system_id", $rij))
				{
					$dist = true;
					$dist1 = true;

					if (isset($rij["x"]) && isset($rij["y"]) && isset($rij["z"]))
					{
						$d_x = $rij["x"];
						$d_y = $rij["y"];
						$d_z = $rij["z"];
					}
					elseif (isset($rij["system_id"]))
					{
						$sys_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																				FROM edtb_systems
																				WHERE id = '" . $rij["system_id"] . "'
																				LIMIT 1");
						$found = mysqli_num_rows($sys_res);

						if ($found > 0)
						{
							$sys_arr = mysqli_fetch_assoc($sys_res);

							$d_x = $sys_arr["x"];
							$d_y = $sys_arr["y"];
							$d_z = $sys_arr["z"];
						}
					}
					elseif (isset($rij["system_name"]) || $found == 0)
					{
						if (valid_coordinates($rij["ritem_coordx"], $rij["ritem_coordy"], $rij["ritem_coordz"]))
						{
							$d_x = $rij["ritem_coordx"];
							$d_y = $rij["ritem_coordy"];
							$d_z = $rij["ritem_coordz"];
						}
						else
						{
							$sys_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																					FROM edtb_systems
																					WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $rij["system_name"]) . "'
																					LIMIT 1");

							$found = mysqli_num_rows($sys_res);

							if ($found > 0)
							{
								$sys_arr = mysqli_fetch_assoc($sys_res);

								$d_x = $sys_arr["x"];
								$d_y = $sys_arr["y"];
								$d_z = $sys_arr["z"];
							}
							else
							{
								$sys_own_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																							FROM user_systems_own
																							WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $rij["system_name"]) . "'
																							LIMIT 1");
								$own_found = mysqli_num_rows($sys_own_res);
								if ($own_found > 0)
								{
									$sys_own_arr = mysqli_fetch_assoc($sys_own_res);

									$d_x = $sys_own_arr["x"];
									$d_y = $sys_own_arr["y"];
									$d_z = $sys_own_arr["z"];
								}
								else
								{
									$d_x = "";
									$d_y = "";
									$d_z = "";
								}
							}
						}
					}
					else
					{
						$d_x = "";
						$d_y = "";
						$d_z = "";
					}
				}

				$ii = 0;
				foreach ($rij AS $key => $value)
				{
					$field_kind = $field_type[$key];

					$enum = false;
					$align = "";
					if ($field_kind == "enum('','0','1')" || $field_kind == "enum('0','1')")
					{
						$align = "text-align:center;";
						$enum = true;
					}
					//echo $field_kind;

					$sort_image = '';
					if (in_array($key, $this->fields_in_list_view))
					{
						if ($count == 1)
						{
							// show nice text of a value
							if ($this->show_text[$key])
							{
								$show_key = $this->show_text[$key];
							}
							else
							{
								$show_key = $key;
							}

							// sorting
							if ($_GET['sort'] == $key && $_GET['ad'] == 'a')
							{
								$sort_image = "<img src='/style/img/sort_a.png' style='width:9px;height:8px;border:none' alt=''>";
								$ad = 'd';
							}
							if ($_GET['sort'] == $key && $_GET['ad'] == 'd')
							{
								$sort_image = "<img src='/style/img/sort_d.png' style='width:9px;height:8px;border:none' alt=''>";
								$ad = 'a';
							}

							// remove sort  and ad and add new ones
							$query_sort = preg_replace('/&(sort|ad)=[^&]*/','', $query_string) . "&sort=$key&ad=$ad";
							//

							if (isset($this->skip))
							{
								if (!in_array($key, $this->skip))
								{
									$head .= "<td style='white-space:nowrap;padding:10px;" . $align . "'><a data-replace='true' data-target='.rightpanel' href='$this->url_script?$query_sort' class='mte_head'>$show_key</a> $sort_image</td>";
								}
							}
							else
							{
								$head .= "<td style='white-space:nowrap;padding:10px;" . $align . "'><a data-replace='true' data-target='.rightpanel' href='$this->url_script?$query_sort' class='mte_head'>$show_key</a> $sort_image</td>";
							}

							// add distance if x,y,z are defined
							if ($dist1 !== false)
							{
								if ($_GET['sort'] == "distance" && $_GET['ad'] == 'a')
								{
									$sort_image = "<img src='/style/img/sort_a.png' style='width:9px;height:8px;border:none' alt=''>";
									$ad = 'd';
								}
								if ($_GET['sort'] == "distance" && $_GET['ad'] == 'd')
								{
									$sort_image = "<img src='/style/img/sort_d.png' style='width:9px;height:8px;border:none' alt=''>";
									$ad = 'a';
								}

								$query_sort_d = preg_replace('/&(sort|ad)=[^&]*/','', $query_string) . "&sort=distance&ad=$ad";

								$head .= "<td style='white-space:nowrap;padding:10px'><a data-replace='true' data-target='.rightpanel' href='$this->url_script?$query_sort_d' class='mte_head'>Distance</a> $sort_image</td>";
								$dist1 = false;
							}

						}
						if ($key == $this->primary_key)
						{
							if (substr($this->table, 0, 4) == "edtb")
							{
								$buttons = "<td style='width:1%;white-space:nowrap;padding:10px;vertical-align:middle'></td>";
							}
							else
							{
								$buttons = "<td style='width:1%;white-space:nowrap;padding:10px;vertical-align:middle'><a href='javascript:void(0)' onclick='del_confirm($value)' title='Delete {$this->show_text[$key]} $value'><img src='/style/img/del.png' style='width:16px;height:16px;border:none' alt=''></a>&nbsp;<a href='?$query_string&mte_a=edit&id=$value' title='Edit {$this->show_text[$key]} $value'><img src='/style/img/edit.png' style='width:16px;height:16px;border:none' alt='Edit'></a></td>";
							}

							if ($key == "id" && $this->table == "edtb_systems")
							{
								$this_row .= "<td style='width:1%;padding:10px;vertical-align:middle'><a href='/system.php?system_id=" . $value . "'>" . $value . "</a></td>";
							}
							else
							{
								$this_row .= "<td style='width:1%;padding:10px;vertical-align:middle'>$value</td>";
							}
						}
						else
						{
							if (isset($this->skip))
							{
								if (!in_array($key, $this->skip))
								{
									$this_row .= set_data($key, $value, $d_x, $d_y, $d_z, $dist, $this->table, $enum);
								}
							}
							else
							{
								$this_row .= set_data($key, $value, $d_x, $d_y, $d_z, $dist, $this->table, $enum);
							}
						}
					$ii++;
					}
				}
				$rows .= "<tr style='border-bottom:1px solid #000;background:$background'>$buttons $this_row</tr>";
			}
		}
		else
		{
			$head = "<td style='padding:40px'>{$this->text['Nothing_found']}...</td>";
		}

		# navigation 3/3

		# remove start= from url
		$query_nav = preg_replace('/&(start|mte_a|id)=[^&]*/','', $query_string );

		# this page
		$this_page = ($this->num_rows_list_view + $start)/$this->num_rows_list_view;

		# last page
		$last_page = ceil($hits_total/$this->num_rows_list_view);

		# navigatie numbers
		if ($this_page>10)
		{
			$vanaf = $this_page - 10;
		}
		else
		{
			$vanaf = 1;
		}

		if ($last_page>$this_page + 10)
		{
			$tot = $this_page + 10;
		}
		else {$tot = $last_page; }


		for ($f=$vanaf;$f<=$tot;$f++)
		{
			$nav_toon = $this->num_rows_list_view * ($f-1);

			if ($f == $this_page)
			{
				$navigation .= "<td class='mte_nav' style='color:#fffffa;background-color:#808080;font-weight:bold'>$f</td> ";
			}
			else
			{
				$navigation .= "<td class='mte_nav' style='background-color:#0e0e11'><a data-replace='true' data-target='.rightpanel' class='mtelink' href='$this->url_script?$query_nav&start=$nav_toon'>$f</a></td>";
			}
		}
		if ($hits_total<$this->num_rows_list_view) { $navigation = '';}

		# Previous if
		if ($this_page > 1)
		{
			$last =  (($this_page - 1) * $this->num_rows_list_view ) - $this->num_rows_list_view;
			$last_page_html = "<a data-replace='true' data-target='.rightpanel' href='$this->url_script?$query_nav&start=$last' class='mte_nav_prev_next'>{$this->text['Previous']}</a>";
		}

		# Next if:
		if ($this_page != $last_page && $hits_total>1)
		{
			$next =  $start + $this->num_rows_list_view;
			$next_page_html =  "<a data-replace='true' data-target='.rightpanel' href='$this->url_script?$query_nav&start=$next' class='mte_nav_prev_next'>{$this->text['Next']}</a>";
		}


		$this->nav_bottom = '<span class="right" style="padding-top:6px">Number of entries: ';
		$this->nav_bottom .= number_format($hits_total);
		$this->nav_bottom .= '</span>';

		if ($navigation)
		{
			$nav_table = "
				<table style='border-collapse:separate;border-spacing:5px;margin-left:35%;margin-right:auto'>
					<tr>
						<td style='padding-right:6px;vertical-align:middle'>$last_page_html</td>
						$navigation
						<td style='padding-left:6px;vertical-align:middle'>$next_page_html</td>
					</tr>
				</table>
			";

			$this->nav_top = "
				<div style='margin-bottom:5px;margin-top:-20px;width:$this->width_editor'>
						$nav_table
				</div>
			";

			$this->nav_bottom .= "
				<div style='margin-top:20px;width:100%;text-align:center'>
						$nav_table
				</div>
			";
		}

		# Search form + Add Record button
		foreach ($this->fields_in_list_view as $option)
		{
			if
			(
				$this->show_text[$option]) {$show_option = $this->show_text[$option];
			}
			else
			{
				$show_option = $option;
			}

			if ($option == $in_search_field)
			{
				$options .= "<option selected value='$option'>$show_option</option>";
			}
			else
			{
				$options .= "<option value='$option'>$show_option</option>";
			}
		}

		$in_search_value = htmlentities(trim(stripslashes($_GET['s'])), ENT_QUOTES);

		$seach_form = "
			<table style='margin-left:0;padding-left:0;border-collapse:collapse;border-spacing:0'>
				<tr>
					<td style='white-space:nowrap;padding-bottom:20px'>
						<form method=get action='$this->url_script'>
							<input type='hidden' name='table' value='" . $_GET["table"] . "'>
							<select class='selectbox' name='f'>$options</select>
							<input class='textbox' type='text' name='s' value='$in_search_value' style='width:220px'>
							<input class='button' type='submit' value='{$this->text['Search']}' style='width:80px'>
				";
			if ($this->query_joomla_component) $seach_form .= "<input type='hidden' value='$this->query_joomla_component' name='option'>";
			$seach_form .= "</form>";

			if ($_GET['s'] && $_GET['f'])
			{
				if ($this->query_joomla_component) $add_joomla = '?option=' . $this->query_joomla_component;
				$seach_form .= "<button class='button' style='margin-left:0;margin-top:6px' onclick='window.location=\"$this->url_script$add_joomla\"' style='margin: 0 0 10px 10px'>{$this->text['Clear_search']}</button>";
			}

			$seach_form .= "
					</td>

					<td style='text-align:right;width:$this->width_editor'>";
					if (substr($this->table, 0, 4) != "edtb")
					{
						$seach_form .= "<button class='button' onclick='window.location=\"$this->url_script?$query_string&mte_a=new\"' style='margin: 0 0 10px 10px'>{$this->text['Add_Record']}</button>";
					}
					else
					{
						$seach_form .= "&nbsp;";
					}
					$seach_form .= "</td>

				</tr>
			</table>
		";

		// $this->javascript = "
			// function del_confirm(id) {
				// if (confirm('{$this->text['Delete']} record {$this->show_text[$this->primary_key]} ' + id + '...?')) {
					// window.location='$this->url_script?$query_string&mte_a=del&id=' + id
				// }
			// }
		// ";
		$this->javascript = "
			function del_confirm(id) {
				if (confirm('{$this->text['Delete']} record {$this->show_text[$this->primary_key]} ' + id + '...?')) {
					window.location=window.location.href + '&mte_a=del&id=' + id
				}
			}
		";
		# page content
		$this->content = "
			<div style='width: $this->width_editor;background:transparent;margin:0;border:none'>$seach_form</div>
			<table style='text-align:left;margin:0;border-collapse:collapse;border-spacing:0;width:$this->width_editor'>
				<tr style='background:#0e0e11; color: #fff'><td></td>$head</tr>
				$rows
			</table>

			$this->nav_bottom
		";
	}

	##################
	function del_rec()
	{
	##################

		$in_id = $_GET['id'];

		if (mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM " . $this->table . " WHERE `" . $this->primary_key . "` = '" . $in_id . "'"))
		{
			$this->content_deleted = "
				<div class='notify_deleted'>
					Record {$this->show_text[$this->primary_key]} $in_id {$this->text['deleted']}
				</div>
			";
			$this->show_list();
		}
		else
		{
			$this->content = "
			</div>
				<div style='padding:2px 20px 20px 20px;margin: 0 0 20px 0; background: #DF0000; color: #fff'><h3>Error</h3>" .
				((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)).
				"</div><a href='$this->url_script'>List records...</a>
			</div>";
		}
	}

	###################
	function edit_rec()
	{
	###################

		$in_id = $_GET['id'];

		# edit or new?
		if ($_GET['mte_a'] == 'edit') $edit=1;

		$count_required = 0;

		$result = mysqli_query($GLOBALS["___mysqli_ston"], "SHOW COLUMNS FROM `$this->table`");

		# get field types
		while ($rij = mysqli_fetch_array($result))
		{
			extract($rij);
			$field_type[$Field] = $Type;
		}

		if (!$edit)
		{
			$rij = $field_type;
		}
		else
		{
			if ($edit) $where_edit = "WHERE `$this->primary_key` = $in_id";
			$result = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `$this->table` $where_edit LIMIT 1 ;");
			$rij = mysqli_fetch_assoc($result);
		}

		foreach ($rij AS $key => $value)
		{
			if (!$edit) $value = '';
			$field = '';
			$options = '';
			$style = '';
			$field_id = '';
			$readonly = '';
			$value_htmlentities = '';

			if (in_array($key, $this->fields_required))
			{
				$count_required++;
				$style = "class='mte_req'";
				$field_id = "id='id_" . $count_required . "'";
			}

			$field_kind = $field_type[$key];

			# different fields
			# textarea
			if (preg_match("/text/", $field_kind))
			{
				$field = "<textarea class='textarea' name='$key' $style $field_id>$value</textarea>";
			}
			# select/options
			elseif (preg_match("/enum\((.*)\)/", $field_kind, $matches))
			{
				$all_options = substr($matches[1],1,-1);
				$options_array = explode("','",$all_options);
				foreach ($options_array AS $option)
				{
					if ($option == $value)
					{
						$options .= "<option selected>$option</option>";
					}
					else
					{
						$options .= "<option>$option</option>";
					}
				}
				$field = "<select class='selectbox' name='$key' $style $field_id>$options</select>";
			}
			# input
			elseif (!preg_match("/blob/", $field_kind))
			{
				if (preg_match("/\(*(.*)\)*/", $field_kind, $matches))
				{
					if ($key == $this->primary_key)
					{
						$style = "style='background:#ccc'";
						$readonly = 'readonly';
					}
					$value_htmlentities = htmlentities($value, ENT_QUOTES);
					if (!$edit && $key == $this->primary_key)
					{
						$field = "<input type='hidden' name='$key' value=''>[auto increment]";
					}
					else
					{
						// add ajax system name for some fields
						if ($key == "system_name")
						{
							$field = '	<input class="textbox" type="text" id="' . $key . '" name="' . $key . '" value="' . $value_htmlentities . '" maxlength="' . $matches[1] . '" ' . $style . ' ' . $readonly . ' ' . $field_id . ' onkeyup="showResult(this.value, \'37\', \'no\', \'no\', \'no\', \'no\', \'yes\')">
										<div class="suggestions" id="suggestions_37" style="margin-left:1px;"></div>';
						}
						else
						{
							$field = "<input class='textbox' type='text' id='$key' name='$key' value='$value_htmlentities' maxlength='{$matches[1]}' $style $readonly $field_id>";
						}
					}
				}
			}
			# blob: don't show
			elseif (preg_match("/blob/", $field_kind))
			{
				$field = '[<i>binary</i>]';
			}

			# make table row
			if ($background == '#273238') {$background='#38484F';}
				else {$background='#273238';}
			if ($this->show_text[$key]) {$show_key = $this->show_text[$key];}
				else {$show_key = $key;}
			$rows .= "\n\n<tr style='border-bottom:1px solid #000;background:$background'>\n<td style='vertical-align:middle;padding:8px'><strong>$show_key</strong></td>\n<td style='padding:8px'>$field</td></tr>";
		}

		$this->javascript = "
			function submitform() {
				var ok = 0;
				for (f = 1; f <= $count_required; f++)
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
				//	alert($count_required + ' ' + ok);

				if (ok == $count_required)
				{
					return true;
				}
				else
				{
					alert('{$this->text['Check_the_required_fields']}...')
					return false;
				}
			}
		";

		$this->content = "
				<div style='width: $this->width_editor;background:transparent'>

					<table style='border-collapse:collapse;border-spacing:0'>
						<tr>
						<td>
							<button onclick='window.location=\"{$_SESSION['hist_page']}\"' style='margin: 20px 15px 25px 15px'>{$this->text['Go_back']}</button></td>
						<td>
							<form method=post action='/datapoint.php?table=" . $_GET["table"] . "' onsubmit='return submitform()'>
							<input class='button' type='submit' value='{$this->text['Save']}' style='width: 80px;margin: 20px 0 25px 0'></td>
						</tr>
					</table>

				</div>

				<div style='width: $this->width_editor'>
					<table style='margin-bottom:20px;border-collapse:collapse;border-spacing:0'>
						$rows
					</table>
				</div>
		";

		if (!$edit) $this->content .= "<input type='hidden' name='mte_new_rec' value='1'>";
		if ($this->query_joomla_component) $this->content .= "<input type='hidden' name='option' value='$this->query_joomla_component'>";

		$this->content .= "
				<input type='hidden' name='mte_a' value='save'>
			</form>
		";
	}

	###################
	function save_rec()
	{
	###################

		$in_mte_new_rec = $_POST['mte_new_rec'];

		$updates = '';

		foreach($_POST as $key => $value)
		{
			if ($key == $this->primary_key)
			{
				$in_id = $value;
				$where = "$key = $value";
			}
			if ($key != 'mte_a' && $key != 'mte_new_rec' && $key != 'option')
			{
				if ($in_mte_new_rec)
				{
					$insert_fields .= " `$key`,";
					$insert_values .= " '" . addslashes(stripslashes($value)) . "',";
				}
				else
				{
					$updates .= " `$key` = '" . addslashes(stripslashes($value)) . "' ,";
				}
			}
		}
		$insert_fields = substr($insert_fields,0,-1);
		$insert_values = substr($insert_values,0,-1);
		$updates = substr($updates,0,-1);

		# new record:
		if ($in_mte_new_rec)
		{
			$sql = "INSERT INTO `$this->table` ($insert_fields) VALUES ($insert_values); ";
		}
		# edit record:
		else
		{
			$sql = "UPDATE `$this->table` SET $updates WHERE $where LIMIT 1; ";
		}

		//echo $sql; exit;
		if (mysqli_query($GLOBALS["___mysqli_ston"], $sql))
		{
			if ($in_mte_new_rec)
			{
				$saved_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
				$_GET['s'] = $saved_id;
				$_GET['f'] = $this->primary_key;
			}
			else
			{
				$saved_id = $in_id;
			}

			if ($this->show_text[$this->primary_key])
			{
				$show_primary_key = $this->show_text[$this->primary_key];
			}
			else
			{
				$show_primary_key = $this->primary_key;
			}

			$_SESSION['content_saved'] = "
				<div class='notify_success'>
					Record $show_primary_key $saved_id {$this->text['saved']}
				</div>
				";
			if ($in_mte_new_rec)
			{
				echo "<script>window.location='?start=0&f=&sort=" . $this->primary_key . "&ad=d";
				if ($this->query_joomla_component)
				{
					echo '&option=' . $this->query_joomla_component ;
				}
				echo "'</script>";
			}
			else
			{
				echo "<script>window.location='" . $_SESSION['hist_page'] . "'</script>";
			}
		}
		else
		{
			$this->content = "
				<div style='width: $this->width_editor'>
					<div style='padding:2px 20px 20px 20px;margin:0 0 20px 0;background:#DF0000;color:#fff'><h3>Error</h3>" .
					((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) .
					"</div><a href='{$_SESSION['hist_page']}'>{$this->text['Go_back']}...</a>
				</div>";
		}
	}

	##########################
	function close_and_print()
	{
	##########################

		# debug and warning no htaccess
		if ($this->debug) $this->debug .= '<br />';
		if (!file_exists('./.htaccess') && $this->no_htaccess_warning == 'on') $this->debug .= "{$this->text['Protect_this_directory_with']} .htaccess";

		if ($this->debug)
		$this->debug_html = "
			<div style='width: $this->width_editor'>
				<div class='mte_mess' style='background:#DD0000'>$this->debug</div>
			</div>";

		# save page location
		$session_hist_page = $this->url_script . '?' . $_SERVER['QUERY_STRING'];
		if ($this->query_joomla_component && !preg_match("/option=$this->query_joomla_component/",$session_hist_page))
		{
			$session_hist_page .= '&option=' . $this->query_joomla_component;
		}

		// no page history on the edit page because after refresh the Go Back is useless
		if (!$_GET['mte_a'])
		{
			$_SESSION['hist_page'] = $session_hist_page;
		}

		if ($this->query_joomla_component) $add_joomla = '?option=' . $this->query_joomla_component;

		echo "
		<div class='mte_content'>
		<script>
			$this->javascript
		</script>
			<div class='mte_head_1' style='text-align:center'><ul class='pagination'>";

		$count = count($this->links_to_db);
		$i = 0;
		foreach ($this->links_to_db as $link_h => $link_t)
		{
			if ($this->table == $link_h)
			{
				$active = " class='actives'";
			}
			else
			{
				$active = "";
			}

			if (($i % 7) == 0)
			{
				echo "</ul><br /><ul class='pagination' style='margin-top:-26px'>";
			}

			echo '<li' . $active . '><a data-replace="true" data-target=".rightpanel" class="mtelink" href="/datapoint.php?table=' . $link_h . '">' . $link_t . '</a></li>';
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
