<?php
/**
 * Make log entries
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

class MakeLog
{
    /**
     * @param string $sort
     * @return string
     */
    private function get_sort($sort)
    {
        if (isset($sort) && $sort != "undefined") {
            if ($sort == "asc") {
                $sort = "desc";
            }
            if ($sort == "desc") {
                $sort = "asc";
            }
        } else {
            $sort = "asc";
        }

        return $sort;
    }

    /**
     * @param object $obj
     * @return string
     */
    private function title_icons($obj)
    {
        // check if log is pinned
        $pinned = $obj->pinned == "1" ? '<img class="icon" src="/style/img/pinned.png" alt="Pinned" />' : "";

        // check if log is personal
        $personal .= $obj->type == "personal" ? '<img class="icon" src="/style/img/user.png" alt="Personal" />' : "";

        // check if log has audio
        $audio .= $obj->audio != "" ? '<a href="javascript:void(0)" onclick="$(\'#' . $obj->id . '\').fadeToggle(\'fast\')" title="Listen to audio logs"><img class="icon" src="/style/img/audio.png" alt="Audio" /></a>' : "";

        return $pinned . $personal . $audio;
    }

    /**
     * @param object $obj
     * @return string
     */
    private function get_audio($obj)
    {
        global $system_time;

        $logdata .= '<div class="audio" id="' . $obj->id . '" style="display:none">';

        $audio_files = explode(", ", $obj->audio);

        foreach ($audio_files as $audio_file) {
            $file = $_SERVER["DOCUMENT_ROOT"] . "/audio_logs/" . $audio_file;
            $file_src = "/audio_logs/" . $audio_file;

            if (file_exists($file)) {
                $timestamp = filemtime($file) + ($system_time * 60 * 60);
                $record_date = date("Y-m-d H:i:s", $timestamp);
                $date = date_create($record_date);
                $record = date_modify($date, "+1286 years");
                $record_added = date_format($record, "j M Y, H:i");
                $added_ago = get_timeago($timestamp);

                $logdata .= '<div style="margin-bottom:4px;margin-top:6px;margin-left:3px">';
                $logdata .= 'Added: ' . $record_added . ' (' . $added_ago . ')';
                $logdata .= '</div>';
                $logdata .= '<div>';
                $logdata .= '<audio controls>';
                $logdata .= '<source src="' . $file_src . '" type="audio/mp3">';
                $logdata .= 'Your browser does not support the audio element.';
                $logdata .= '</audio>';
                $logdata .= '</div>';
            }
        }
        unset($audio_file);
        $logdata .= '</div>';

        return $logdata;
    }

    /**
     * Make log entries
     *
     * @param mysqli_result $log_res
     * @param string $type
     * @return string $logdata
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public function make_log_entries($log_res, $type)
    {
        $this_system = "";
        $this_id = "";
        $i = 0;
        while ($obj = $log_res->fetch_object()) {
            if ($this_id != $obj->id) {
                $system_name = $obj->system_name == "" ? $obj->log_system_name : $obj->system_name;
                $log_station_name = $obj->station_name;
                $log_text = $obj->log_entry;
                $date = date_create($obj->stardate);
                $log_added = date_modify($date, "+1286 years");
                $distance = $obj->distance != "" ? number_format($obj->distance, 1) : "";

                if ($this_system != $system_name && $type != "general") {
                    $add = $distance != 0 ? " (distance " . $distance . " ly)" : "";

                    $sortable = "";
                    if ($i == 0 && $type != "log") {
                        $sssort = $this->get_sort($_GET["slog_sort"]);

                        $sortable = '<span class="right">';
                        $sortable .= '<a href="/index.php?slog_sort=' . $sssort . '" title="Sort by date asc/desc">';
                        $sortable .= '<img class="icon" src="/style/img/sort.png" alt="Sort" style="margin-right:0" />';
                        $sortable .= '</a></span>';
                    }

                    /**
                     * provide crosslinks to screenshot gallery, log page, etc
                     */
                    $l_crosslinks = System::crosslinks($system_name, true, false, false);

                    $logdata = '<header><h2><img class="icon" src="/style/img/system_log.png" alt="log" />';
                    $logdata .= 'System log for <a href="/System?system_name=' . urlencode($system_name) . '">';
                    $logdata .= $system_name;
                    $logdata .= '</a>' . $l_crosslinks . $add . $sortable . '</h2></header>';
                    $logdata .= '<hr>';
                } elseif ($type == "general" && $i == 0) {
                    $gssort = $this->get_sort($_GET["glog_sort"]);

                    $sortable = '<span class="right">';
                    $sortable .= '<a href="/index.php?glog_sort=' . $gssort . '" title="Sort by date asc/desc">';
                    $sortable .= '<img class="icon" src="/style/img/sort.png" alt="Sort" style="margin-right:0" />';
                    $sortable .= '</a></span>';

                    $logdata = '<header><h2><img class="icon" src="/style/img/log.png" alt="log" />Commander\'s Log' . $sortable . '</h2></header>';
                    $logdata .= '<hr>';
                }

                /**
                 * get title icons
                 */
                $title_icons = $this->title_icons($obj);

                $log_title = !empty($obj->title) ? '&nbsp;&ndash;&nbsp;' . $obj->title : "";

                $logdata .= '<h3>' . $title_icons;
                $logdata .= '<a href="javascript:void(0)" onclick="toggle_log_edit(\'' . $obj->id . '\')" style="color:inherit" title="Edit entry">';
                $logdata .= date_format($log_added, "j M Y, H:i");

                if (!empty($log_station_name)) {
                    $logdata .= '&nbsp;[Station: ' . htmlspecialchars($log_station_name) . ']';
                }

                $logdata .= $log_title;
                $logdata .= '</a></h3>';
                $logdata .= '<pre class="entriespre" style="margin-bottom:20px">';

                if (!empty($obj->audio)) {
                    $logdata .= $this->get_audio($obj);
                }

                $logdata .= $log_text;
                $logdata .= '</pre>';
            }

            $this_system = $system_name;
            $this_id = $obj->id;
            $i++;
        }

        return $logdata;
    }
}
