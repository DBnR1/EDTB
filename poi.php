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

$pagetitle = "Points of Interest&nbsp;&nbsp;&&nbsp;&nbsp;Bookmarks";
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/add/poi.php");
?>
<div class="entries">
	<div class="entries_inner">
		<table>
			<tr>
				<td class="systeminfo_station_name" style="min-width:400px;"><a href="javascript:void(0);" onclick="tofront('addpoi');" title="Add point of interest">Points of Interest</a></td>
				<td class="systeminfo_station_name" style="min-width:400px;">Bookmarks</td>
			</tr>
			<tr>
				<td style="vertical-align:top;">
					<table>
						<?php
						if (is_numeric($coordx))
						{
							$usex = $coordx;
							$usey = $coordy;
							$usez = $coordz;
						}
						else
						{
							$last_coords = last_known_system();

							$usex = $last_coords["x"];
							$usey = $last_coords["y"];
							$usez = $last_coords["z"];
						}
						// get poi in correct order
						$poi_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_poi.id, user_poi.poi_name, user_poi.system_name, user_poi.text,
																				user_poi.x AS poi_coordx, user_poi.y AS poi_coordy, user_poi.z AS poi_coordz,
																				edtb_systems.id AS system_id,
																				user_poi_categories.name AS catname
																				FROM user_poi
																				LEFT JOIN edtb_systems ON user_poi.system_name = edtb_systems.name
																				LEFT JOIN user_poi_categories ON user_poi_categories.id = user_poi.category_id
																				ORDER BY sqrt(pow((poi_coordx-(" . $usex . ")),2)+pow((poi_coordy-(" . $usey . ")),2)+pow((poi_coordz-(" . $usez . ")),2))")
																				or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

						$poinum = mysqli_num_rows($poi_res);

						if ($poinum > 0)
						{
							if (!is_numeric($coordx))
							{
								echo "<tr><td class='station_info_price_info' style='min-width:420px;max-width:500;'><p><strong>No coordinates for current location, last known location used.</strong></p></td></tr>";
							}

							$i = 0;
							$to_end = array();
							while ($poi_arr = mysqli_fetch_assoc($poi_res))
							{
								$poi_id = $poi_arr["id"];
								$poi_text = $poi_arr["text"];
								$poi_name = $poi_arr["poi_name"];
								$poi_system_name = $poi_arr["system_name"];
								$poi_system_id = $poi_arr["system_id"];
								$poi_cat_name = $poi_arr["catname"];

								$poi_coordx = $poi_arr["poi_coordx"];
								$poi_coordy = $poi_arr["poi_coordy"];
								$poi_coordz = $poi_arr["poi_coordz"];

								/*
								*	if coords are not set, see if user has calculated them
								*/

								if (!is_numeric($poi_coordx) && !is_numeric($poi_coordy) && !is_numeric($poi_coordz))
								{
									$c_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																						FROM user_systems_own
																						WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $poi_system_name) . "'
																						LIMIT 1")
																						or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

									$c_arr = mysqli_fetch_assoc($c_res);

									$poi_coordx = $c_arr["x"] == "" ? "" : $c_arr["x"];
									$poi_coordy = $c_arr["y"] == "" ? "" : $c_arr["y"];
									$poi_coordz = $c_arr["z"] == "" ? "" : $c_arr["z"];
								}

								/*
								*	if poi has coordinates, show them first
								*/

								if (is_numeric($poi_coordx) && is_numeric($poi_coordy) && is_numeric($poi_coordz))
								{
									$distance = number_format(sqrt(pow(($poi_coordx-($usex)), 2)+pow(($poi_coordy-($usey)), 2)+pow(($poi_coordz-($usez)), 2)), 1)." ly";

									$poi_cat = "";
									if ($poi_cat_name != "")
									{
										$poi_cat = '&nbsp;-&nbsp;' . $poi_cat_name . '&nbsp;';
									}

									// if visited, change border color
									$style_override = "";
									$visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																											FROM user_visited_systems
																											WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $poi_system_name) . "'
																											LIMIT 1"));
									if ($visited)
									{
										$style_override = ' style="border-left: 3px solid #3DA822;"';
									}

									if ($i % 2)
									{
										$tdclass = "station_info_price_info";
									}
									else
									{
										$tdclass = "station_info_price_info2";
									}

									echo '<tr>
											<td class="' . $tdclass . '" style="min-width:420px;max-width:500;">
												<div class="poi"' . $style_override . '>
													<a href="javascript:void(0);" onclick="update_values(\'/get/getPoiEditData.php?poi_id=' . $poi_id . '\',\'' . $poi_id . '\');tofront(\'addpoi\');" style="color:inherit;" title="Click to edit entry">';
									$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																											FROM user_log
																											WHERE system_id = '" . $poi_system_id . "'
																											AND system_id != '0'
																											LIMIT 1"));

									$loglink = "";
									if ($logged > 0)
									{
										$loglink = '&nbsp;[&nbsp;<a href="log.php?system=' . $poi_system_name . '&system_id=' . $poi_system_id . '" style="color:inherit;" title="Click to see log">Log entry</a>&nbsp;]&nbsp;';
									}

									if ($poi_system_id != "")
									{
										echo '(' . $distance . ')</a>&nbsp;<a title="System information" href="/system.php?system_id=' . $poi_system_id . '" style="color:inherit;">';
									}
									else
									{
										echo '(' . $distance . ')</a>&nbsp;<a href="#" style="color:inherit;">';
									}

									if (empty($poi_name))
									{
										echo $poi_system_name;
									}
									else
									{
										echo $poi_name;
									}

									echo '</a>' . $loglink  . '' . $poi_cat . '<br />';

									// make a link if text includes url
									$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
									if (preg_match($reg_exUrl, $poi_text, $url))
									{
										if (mb_strlen($poi_text) >= 60)
										{
											$urli = "" . substr($poi_text, 0, 60) . "...";
										}
										else
										{
											$urli = $poi_text;
										}
										$poi_text = preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $poi_text);
									}
									echo nl2br($poi_text);
									echo '</div></td></tr>';
									$i++;
								}
								else
								{
									$to_last[$i]["id"] = $poi_id;
									$to_last[$i]["poi_text"] = $poi_text;
									$to_last[$i]["poi_name"] = $poi_name;
									$to_last[$i]["poi_system_name"] = $poi_system_name;
									$to_last[$i]["poi_system_id"] = $poi_system_id;
									$to_last[$i]["poi_cat_name"] = $poi_cat_name;
								}
							}

							/*
							*	display poi's with no coordinates at the end
							*/

							foreach ($to_last as $poi)
							{
								$poi_id = $poi["id"];
								$poi_text = $poi["text"];
								$poi_name = $poi["poi_name"];
								$poi_system_name = $poi["system_name"];
								$poi_system_id = $poi["system_id"];
								$poi_cat_name = $poi["catname"];

								$poi_cat = "";
								if ($poi_cat_name != "")
								{
									$poi_cat = '&nbsp;-&nbsp;' . $poi_cat_name . '&nbsp;';
								}

								$distance = "n/a";

								// if visited, change border color
								$style_override = "";
								$visited = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																										FROM user_visited_systems
																										WHERE system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $poi_system_name) . "'
																										LIMIT 1"));
								if ($visited)
								{
									$style_override = ' style="border-left: 3px solid #3DA822;"';
								}

								if ($i % 2)
								{
									$tdclass = "station_info_price_info";
								}
								else
								{
									$tdclass = "station_info_price_info2";
								}

								echo '<tr>
										<td class="' . $tdclass . '" style="min-width:420px;max-width:500;">
											<div class="poi"' . $style_override . '>
												<a href="javascript:void(0);" onclick="update_values(\'/get/getPoiEditData.php?poi_id=' . $poi_id . '\',\'' . $poi_id . '\');tofront(\'addpoi\');" style="color:inherit;" title="Click to edit entry">';
								$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																										FROM user_log
																										WHERE system_id = '" . $poi_system_id . "'
																										AND system_id != '0'
																										LIMIT 1"));

								$loglink = "";
								if ($logged > 0)
								{
									$loglink = '&nbsp;[&nbsp;<a href="log.php?system=' . $poi_system_name . '&system_id=' . $poi_system_id . '" style="color:inherit;" title="Click to see log">Log entry</a>&nbsp;]&nbsp;';
								}

								echo '(' . $distance . ')</a>&nbsp;<a href="#" style="color:inherit;">';

								if (empty($poi_name))
								{
									echo $poi_system_name;
								}
								else
								{
									echo $poi_name;
								}

								echo '</a>' . $loglink  . '' . $poi_cat . '<br />';
								// make a link if text includes url
								$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
								if (preg_match($reg_exUrl, $poi_text, $url))
								{
									if (mb_strlen($poi_text) >= 60)
									{
										$urli = "" . substr($poi_text, 0, 60) . "...";
									}
									else
									{
										$urli = $poi_text;
									}
									$poi_text = preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $poi_text);
								}
								echo nl2br($poi_text);
								echo '</div></td></tr>';
							}
						}
						else
						{
							echo '<tr><td class="station_info_price_info" style="min-width:420px;max-width:500;"><strong>No points of interest.<br />Click the "Points of Interest" text to add one.</strong></td></tr>';
						}
						?>
					</table>
				</td>
				<td style="vertical-align:top;">
					<table>
					<?php
					// get bookmarks
					$bm_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_bookmarks.id, user_bookmarks.system_id, user_bookmarks.system_name AS bm_system_name,
																			user_bookmarks.comment, user_bookmarks.added_on,
																			edtb_systems.x AS bm_coordx,
																			edtb_systems.y AS bm_coordy,
																			edtb_systems.z AS bm_coordz,
																			edtb_systems.name AS system_name,
																			user_bm_categories.name AS category_name
																			FROM user_bookmarks
																			LEFT JOIN edtb_systems ON user_bookmarks.system_id = edtb_systems.id
																			LEFT JOIN user_bm_categories ON user_bookmarks.category_id = user_bm_categories.id
																			ORDER BY sqrt(pow((bm_coordx-(" . $usex . ")),2)+pow((bm_coordy-(" . $usey . ")),2)+pow((bm_coordz-(" . $usez . ")),2))")
																			or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

					$bmnum = mysqli_num_rows($bm_res);

					if ($bmnum > 0)
					{
						if (!is_numeric($coordx))
						{
							echo '<tr><td class="station_info_price_info" style="min-width:420px;max-width:500;"><p><strong>No coordinates for current location, last known location used.</strong></p></td></tr>';
						}

						$i = 0;
						while ($bm_arr = mysqli_fetch_assoc($bm_res))
						{
							$bm_id = $bm_arr["id"];
							$bm_text = $bm_arr["comment"];
							$bm_system_name = $bm_arr["system_name"] == "" ? $bm_arr["bm_system_name"] : $bm_arr["system_name"];
							$bm_cat_name = $bm_arr["category_name"];
							$bm_system_id = $bm_arr["system_id"];

							$bm_coordx = $bm_arr["bm_coordx"];
							$bm_coordy = $bm_arr["bm_coordy"];
							$bm_coordz = $bm_arr["bm_coordz"];

							/*
							*	if coords are not set, see if user has calculated them
							*/

							if (!is_numeric($bm_coordx) && !is_numeric($bm_coordy) && !is_numeric($bm_coordz))
							{
								$cb_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT x, y, z
																						FROM user_systems_own
																						WHERE name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $bm_system_name) . "'
																						LIMIT 1")
																						or write_log(mysqli_error($GLOBALS["___mysqli_ston"]), __FILE__, __LINE__);

								$cb_arr = mysqli_fetch_assoc($cb_res);

								$bm_coordx = $cb_arr["x"] == "" ? "" : $cb_arr["x"];
								$bm_coordy = $cb_arr["y"] == "" ? "" : $cb_arr["y"];
								$bm_coordz = $cb_arr["z"] == "" ? "" : $cb_arr["z"];
							}

							/*
							*	if bm has coordinates, show them first
							*/

							if (is_numeric($bm_coordx) && is_numeric($bm_coordy) && is_numeric($bm_coordz))
							{
								$bm_cat = "";
								if ($bm_cat_name != "")
								{
									$bm_cat = '&nbsp;-&nbsp;' . $bm_cat_name . '&nbsp;';
								}

								$distance = sqrt(pow(($bm_coordx-($usex)), 2)+pow(($bm_coordy-($usey)), 2)+pow(($bm_coordz-($usez)), 2));

								$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																										FROM user_log
																										WHERE system_id = '" . $bm_system_id . "' AND system_id != '0'
																										LIMIT 1"));
								$loglink = "";
								if ($logged > 0)
								{
									$loglink = '&nbsp;[&nbsp;<a href="log.php?system=' . $bm_system_name . '&system_id=' . $bm_system_id . '" style="color:inherit;" title="Click to see log">Log entry</a>&nbsp;]&nbsp;';
								}

								if ($i % 2)
								{
									$tdclass = "station_info_price_info";
								}
								else
								{
									$tdclass = "station_info_price_info2";
								}

								echo '<tr><td class="' . $tdclass . '" style="min-width:420px;max-width:500;">
								<div class="poi" style="border-left: 3px solid #3DA822;">
										<a href="javascript:void(0);" onclick="update_values(\'/get/getBmEditData.php?bm_id=' . $bm_id . '\',\'' . $bm_id . '\');tofront(\'addbm\');" style="color:inherit;" title="Click to edit entry">';

								if ($bm_system_id != "" && $bm_system_id != "-1")
								{
									echo '(' . number_format($distance, 1) . ' ly)</a>&nbsp;<a title="System information" href="/system.php?system_id=' . $bm_system_id . '" style="color:inherit;">';
								}
								else
								{
									echo '(' . number_format($distance, 1) . ' ly)</a>&nbsp;<a href="#" style="color:inherit;">';
								}

								echo $bm_system_name;

								echo '</a>' . $loglink . '' . $bm_cat . '<br />';
								// make a link if text includes url
								$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
								if (preg_match($reg_exUrl, $bm_text, $url))
								{
									if (mb_strlen($bm_text) >= 60)
									{
										$urli = "" . substr($bm_text, 0, 60) . "...";
									}
									else
									{
										$urli = $bm_text;
									}
									$bm_text = preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $bm_text);
								}
								echo nl2br($bm_text);
								echo '</div></td></tr>';

								$i++;
							}
							else
							{
								$to_lastb[$i]["bm_id"] = $bm_id;
								$to_lastb[$i]["bm_text"] = $bm_text;
								$to_lastb[$i]["bm_system_name"] = $bm_system_name;
								$to_lastb[$i]["bm_cat_name"] = $bm_cat_name;
								$to_lastb[$i]["bm_system_id"] = $bm_system_id;
							}
						}

						/*
						*	display bookmarks with no coordinates at the end
						*/

						foreach ($to_lastb as $bm)
						{
							$bm_id = $bm["bm_id"];
							$bm_text = $bm["bm_text"];
							$bm_system_name = $bm["bm_system_name"];
							$bm_cat_name = $bm["bm_cat_name"];
							$bm_system_id = $bm["bm_system_id"];

							$bm_cat = "";
							if ($bm_cat_name != "")
							{
								$bm_cat = '&nbsp;-&nbsp;' . $bm_cat_name . '&nbsp;';
							}

							$distance = sqrt(pow(($bm_coordx-($usex)), 2)+pow(($bm_coordy-($usey)), 2)+pow(($bm_coordz-($usez)), 2));

							$logged = mysqli_num_rows(mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT (1)
																									FROM user_log
																									WHERE system_id = '" . $bm_system_id . "'
																									AND system_id != '0'
																									LIMIT 1"));
							$loglink = "";
							if ($logged > 0)
							{
								$loglink = '&nbsp;[&nbsp;<a href="log.php?system=' . $bm_system_name . '&system_id=' . $bm_system_id . '" style="color:inherit;" title="Click to see log">Log entry</a>&nbsp;]&nbsp;';
							}

							if ($i % 2)
							{
								$tdclass = "station_info_price_info";
							}
							else
							{
								$tdclass = "station_info_price_info2";
							}

							echo '<tr><td class="' . $tdclass . '" style="min-width:420px;max-width:500;">
							<div class="poi" style="border-left: 3px solid #3DA822;">
									<a href="javascript:void(0);" onclick="update_values(\'/get/getBmEditData.php?bm_id=' . $bm_id . '\',\'' . $bm_id . '\');tofront(\'addbm\');" style="color:inherit;" title="Click to edit entry">';

							if ($bm_system_id != "" && $bm_system_id != "-1")
							{
								echo '(n/a)</a>&nbsp;<a title="System information" href="/system.php?system_id=' . $bm_system_id . '" style="color:inherit;">';
							}
							else
							{
								echo '(n/a)</a>&nbsp;<a href="#" style="color:inherit;">';
							}

							echo $bm_system_name;

							echo '</a>' . $loglink . '' . $bm_cat . '<br />';
							// make a link if text includes url
							$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
							if (preg_match($reg_exUrl, $bm_text, $url))
							{
								if (mb_strlen($bm_text) >= 60)
								{
									$urli = "" . substr($bm_text, 0, 60) . "...";
								}
								else
								{
									$urli = $bm_text;
								}
								$bm_text = preg_replace($reg_exUrl, "<a href='" . $url[0] . "' target='_BLANK'>" . $urli . "</a> ", $bm_text);
							}
							echo nl2br($bm_text);
							echo '</div></td></tr>';
						}
					}
					else
					{
						echo '<tr><td class="station_info_price_info" style="min-width:420px;max-width:500;"><strong>No bookmarks.<br />Click the allegiance icon on the top left corner to add one.</strong></td></tr>';
					}
					?>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php
require_once("" . $_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
