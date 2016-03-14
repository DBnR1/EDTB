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
    private function get_sort($to_sort)
    {
        $gsort = $_GET[$to_sort . "_sort"];

        if (isset($gsort) && $gsort != "undefined") {
            if ($gsort == "asc") {
                $sort = "desc";
            }
            if ($gsort == "desc") {
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
                        $sssort = $this->get_sort("slog");

                        $sortable = '<span class="right">';
                        $sortable .= '<a href="/?slog_sort=' . $sssort . '" title="Sort by date asc/desc">';
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
                    $gssort = $this->get_sort("glog");

                    $sortable = '<span class="right">';
                    $sortable .= '<a href="/?glog_sort=' . $gssort . '" title="Sort by date asc/desc">';
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

    /**
     * @param string $what
     * @param string $esc_system_name
     * @param string $esc_station_name
     * @param string $l_system
     * @return mixed
     */
    public function get_id($what, $esc_system_name = "", $esc_station_name = "", $l_system = "")
    {
        global $mysqli;

        if ($what == "system") {
            $query = "  SELECT id AS system_id
                        FROM edtb_systems
                        WHERE name = '$esc_system_name'
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $arr = $result->fetch_object();

            $retval = $arr->system_id;

            $result->close();
        } elseif ($what == "station") {
            $query = "  SELECT id AS station_id
                        FROM edtb_stations
                        WHERE name = '$esc_station_name'
                        AND system_id = '$l_system'
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $arr = $result->fetch_object();

            $retval = $arr->station_id;

            $result->close();
        }

        return $retval;
    }

    /**
     *
     */
    private function delete_log()
    {
        global $mysqli;

        $query = "  SELECT audio
                    FROM user_log
                    WHERE id = '" . $_GET["deleteid"] . "'
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $arr = $result->fetch_object();

        $audio = $arr->audio;

        $result->close();

        $audio_files = explode(", ", $audio);

        foreach ($audio_files as $audio_file) {
            $file = $_SERVER["DOCUMENT_ROOT"] . "/audio_logs/" . $audio_file;

            if (file_exists($file) && is_file($file)) {
                if (!unlink($file)) {
                    $error = error_get_last();
                    write_log("Error: " . $error["message"], __FILE__, __LINE__);
                }
            }
        }
        unset($audio_file);

        $query = "  DELETE FROM user_log
                    WHERE id = '" . $_GET["deleteid"] . "'
                    LIMIT 1";

        $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
    }

    /**
     * @param object $data
     */
    public function add_log($data)
    {
        global $mysqli;

        $l_system_name = $data->{"system_name"};
        $l_station_name = $data->{"station_name"};
        $l_entry = $data->{"log_entry"};
        $l_id = $data->{"edit_id"};
        $l_type = $data->{"log_type"};
        $l_pinned = $data->{"pinned"} == "1" ? "1" : "0";
        $l_weight = $data->{"weight"};
        $l_title = $data->{"title"};
        $l_audiofiles = $data->{"audiofiles"};

        $esc_system_name = $mysqli->real_escape_string($l_system_name);
        $esc_station_name = $mysqli->real_escape_string($l_station_name);
        $esc_entry = $mysqli->real_escape_string($l_entry);
        $esc_title = $mysqli->real_escape_string($l_title);
        $esc_audiofiles = $mysqli->real_escape_string($l_audiofiles);

        /**
         * get system id
         */
        $l_system = $this->get_id("system", $esc_system_name);

        /**
         * get station id
         */
        $l_station = $this->get_id("station", "", $esc_station_name, $l_system);

        if ($l_system_name == "") {
            $l_system = "0";
        }

        if ($l_id != "") {
            $query = "  UPDATE user_log SET
                        system_id = '$l_system',
                        system_name = '$esc_system_name',
                        station_id = '$l_station',
                        log_entry = '$esc_entry',
                        title = '$esc_title',
                        type = '$l_type',
                        weight = '$l_weight',
                        pinned = '$l_pinned',
                        audio = '$esc_audiofiles'
                        WHERE id = '$l_id'
                        LIMIT 1";

            $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        } elseif (isset($_GET["deleteid"])) {
            $this->delete_log();
        } else {
            $query = "  INSERT INTO user_log (system_id, system_name, station_id, log_entry, title, weight, pinned, type, audio)
                        VALUES
                        ('$l_system',
                        '$esc_system_name',
                        '$l_station',
                        '$esc_entry',
                        '$esc_title',
                        '$l_weight',
                        '$l_pinned',
                        '$l_type',
                        '$esc_audiofiles')";

            $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        }
    }
}
