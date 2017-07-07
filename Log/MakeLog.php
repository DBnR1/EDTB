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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

namespace EDTB\Log;

use EDTB\source\System;

/**
 * Display log entries
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class MakeLog
{
    /** @var int $timeDifference local time difference from UTC */
    public $timeDifference = 0;

    /**
     * MakeLog constructor.
     */
    public function __construct()
    {
        global $server, $user, $pwd, $db;

        /**
         * Connect to MySQL database
         */
        $this->mysqli = new \mysqli($server, $user, $pwd, $db);

        /**
         * check connection
         */
        if ($this->mysqli->connect_errno) {
            echo 'Failed to connect to MySQL: ' . $this->mysqli->connect_error;
        }
    }

    /**
     * Make log entries
     *
     * @param \mysqli_result $logRes
     * @param string $type
     * @return string $logdata
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public function makeLogEntries($logRes, $type): string
    {
        $thisSystem = '';
        $thisId = '';
        $i = 0;
        while ($obj = $logRes->fetch_object()) {
            if ($thisId != $obj->id) {
                $systemName = $obj->system_name === '' ? $obj->log_system_name : $obj->system_name;
                $logStationName = $obj->station_name;
                $logText = $obj->log_entry;
                $date = date_create($obj->stardate);
                $logAdded = date_modify($date, '+1286 years');
                $distance = $obj->distance !== '' ? number_format($obj->distance, 1) : '';
                $logData = '';

                if ($thisSystem !== $systemName && $type !== 'general') {
                    $add = $distance != 0 ? ' (distance ' . $distance . ' ly)' : '';

                    $sortable = '';
                    if ($i === 0 && $type !== 'log') {
                        $sssort = $this->getSort('slog');

                        $sortable = '<span class="right">';
                        $sortable .= '<a href="/?slog_sort=' . $sssort . '" title="Sort by date asc/desc">';
                        $sortable .= '<img class="icon" src="/style/img/sort.png" alt="Sort" style="margin-right: 0">';
                        $sortable .= '</a></span>';
                    }

                    /**
                     * provide crosslinks to screenshot gallery, log page, etc
                     */
                    $lCrosslinks = System::crosslinks($systemName, true, false, false);

                    $logData .= '<header><h2><img class="icon" src="/style/img/system_log.png" alt="log">';
                    $logData .= 'System log for <a href="/System?system_name=' . urlencode($systemName) . '">';
                    $logData .= $systemName;
                    $logData .= '</a>' . $lCrosslinks . $add . $sortable . '</h2></header>';
                    $logData .= '<hr>';
                } elseif ($type === 'general' && $i == 0) {
                    $gssort = $this->getSort('glog');

                    $sortable = '<span class="right">';
                    $sortable .= '<a href="/?glog_sort=' . $gssort . '" title="Sort by date asc/desc">';
                    $sortable .= '<img class="icon" src="/style/img/sort.png" alt="Sort" style="margin-right: 0">';
                    $sortable .= '</a></span>';

                    $logData .= '<header><h2><img class="icon" src="/style/img/log.png" alt="log">Commander\'s Log' . $sortable . '</h2></header>';
                    $logData .= '<hr>';
                }

                /**
                 * get title icons
                 */
                $titleIcons = $this->titleIcons($obj);

                $logTitle = !empty($obj->title) ? '&nbsp;&ndash;&nbsp;' . $obj->title : '';

                $logData .= '<h3>' . $titleIcons;
                $logData .= '<a href="javascript:void(0)" onclick="toggle_log_edit(\'' . $obj->id . '\')" style="color: inherit" title="Edit entry">';
                $logData .= date_format($logAdded, 'j M Y, H:i');

                if (!empty($logStationName)) {
                    $logData .= '&nbsp;[Station: ' . htmlspecialchars($logStationName) . ']';
                }

                $logData .= $logTitle;
                $logData .= '</a></h3>';
                $logData .= '<pre class="entriespre" style="margin-bottom: 20px">';

                if (!empty($obj->audio)) {
                    $logData .= $this->getAudio($obj);
                }

                $logData .= $logText;
                $logData .= '</pre>';
            }

            $thisSystem = $systemName;
            $thisId = $obj->id;
            $i++;
        }

        return $logData;
    }

    /**
     * Get sort ascending/descending
     *
     * @param string $toSort
     * @return string
     * @internal param string $sort
     */
    private function getSort($toSort): string
    {
        $gsort = $_GET[$toSort . '_sort'];

        if (isset($gsort) && $gsort !== 'undefined') {
            if ($gsort === 'asc') {
                $sort = 'desc';
            }
            if ($gsort === 'desc') {
                $sort = 'asc';
            }
        } else {
            $sort = 'asc';
        }

        return $sort;
    }

    /**
     * Get icons for the title
     *
     * @param object $obj
     * @return string
     */
    private function titleIcons($obj): string
    {
        // check if log is pinned
        $pinned = $obj->pinned == '1' ? '<img class="icon" src="/style/img/pinned.png" alt="Pinned">' : '';

        // check if log is personal
        $personal = $obj->type === 'personal' ? '<img class="icon" src="/style/img/user.png" alt="Personal">' : '';

        // check if log has audio
        $audio = $obj->audio !== '' ? '<a href="javascript:void(0)" onclick="$(\'#' . $obj->id . '\').fadeToggle(\'fast\')" title="Listen to audio logs"><img class="icon" src="/style/img/audio.png" alt="Audio"></a>' : '';

        return $pinned . $personal . $audio;
    }

    /**
     * Get audio log files
     *
     * @param object $obj
     * @return string
     */
    private function getAudio($obj): string
    {
        $logdata = '<div class="audio" id="' . $obj->id . '" style="display: none">';

        $audioFiles = explode(', ', $obj->audio);

        foreach ($audioFiles as $audioFile) {
            $file = $_SERVER['DOCUMENT_ROOT'] . '/audio_logs/' . $audioFile;
            $fileSrc = '/audio_logs/' . $audioFile;

            if (file_exists($file)) {
                $timestamp = filemtime($file) + ($this->timeDifference * 60 * 60);
                $recordDate = date('Y-m-d H:i:s', $timestamp);
                $date = date_create($recordDate);
                $record = date_modify($date, '+1286 years');
                $recordAdded = date_format($record, 'j M Y, H:i');
                $addedAgo = get_timeago($timestamp);

                $logdata .= '<div style="margin-bottom: 4px;  margin-top: 6px; margin-left: 3px">';
                $logdata .= 'Added: ' . $recordAdded . ' (' . $addedAgo . ')';
                $logdata .= '</div>';
                $logdata .= '<div>';
                $logdata .= '<audio controls>';
                $logdata .= '<source src="' . $fileSrc . '" type="audio/mp3">';
                $logdata .= 'Your browser does not support the audio element.';
                $logdata .= '</audio>';
                $logdata .= '</div>';
            }
        }

        $logdata .= '</div>';

        return $logdata;
    }

    /**
     * Add or update log entry
     *
     * @param object $data
     */
    public function addLog($data)
    {
        $lSystemName = $data->{'system_name'};
        $lStationName = $data->{'station_name'};
        $lEntry = $data->{'log_entry'};
        $lId = $data->{'edit_id'};
        $lType = $data->{'log_type'};
        $lPinned = $data->{'pinned'} == '1' ? '1' : '0';
        $lWeight = $data->{'weight'};
        $lTitle = $data->{'title'};
        $lAudiofiles = $data->{'audiofiles'};

        $escSystemName = $this->mysqli->real_escape_string($lSystemName);
        $escStationName = $this->mysqli->real_escape_string($lStationName);
        $escEntry = $this->mysqli->real_escape_string($lEntry);
        $escTitle = $this->mysqli->real_escape_string($lTitle);
        $escAudiofiles = $this->mysqli->real_escape_string($lAudiofiles);

        /**
         * get system id
         */
        $lSystem = $this->getId('system', $escSystemName);

        /**
         * get station id
         */
        $lStation = $this->getId('station', '', $escStationName, $lSystem);

        if ($lSystemName === '') {
            $lSystem = '0';
        }

        if ($lId !== '') {
            $query = "  UPDATE user_log SET
                        system_id = '$lSystem',
                        system_name = '$escSystemName',
                        station_id = '$lStation',
                        log_entry = '$escEntry',
                        title = '$escTitle',
                        type = '$lType',
                        weight = '$lWeight',
                        pinned = '$lPinned',
                        audio = '$escAudiofiles'
                        WHERE id = '$lId'
                        LIMIT 1";

            $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);

        } elseif (isset($_GET['deleteid'])) {
            $this->deleteLog();
        } else {
            $query = "  INSERT INTO user_log (system_id, system_name, station_id, log_entry, title, weight, pinned, type, audio)
                        VALUES
                        ('$lSystem',
                        '$escSystemName',
                        '$lStation',
                        '$escEntry',
                        '$escTitle',
                        '$lWeight',
                        '$lPinned',
                        '$lType',
                        '$escAudiofiles')";

            $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
        }
    }

    /**
     * Get system or station id
     *
     * @param string $what
     * @param string $escSystemName
     * @param string $escStationName
     * @param string $lSystem
     * @return mixed
     */
    public function getId($what, $escSystemName = '', $escStationName = '', $lSystem = '')
    {
        if ($what === 'system') {
            $query = "  SELECT id AS system_id
                        FROM edtb_systems
                        WHERE name = '$escSystemName'
                        LIMIT 1";

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
            $arr = $result->fetch_object();

            $retval = $arr->system_id;

            $result->close();
        } elseif ($what === 'station') {
            $query = "  SELECT id AS station_id
                        FROM edtb_stations
                        WHERE name = '$escStationName'
                        AND system_id = '$lSystem'
                        LIMIT 1";

            $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
            $arr = $result->fetch_object();

            $retval = $arr->station_id;

            $result->close();
        }

        return $retval;
    }

    /**
     * Delete log entry
     */
    private function deleteLog()
    {
        $query = "  SELECT audio
                    FROM user_log
                    WHERE id = '" . $_GET['deleteid'] . "'
                    LIMIT 1";

        $result = $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
        $arr = $result->fetch_object();

        $audio = $arr->audio;

        $result->close();

        $audioFiles = explode(', ', $audio);

        foreach ($audioFiles as $audioFile) {
            $file = $_SERVER['DOCUMENT_ROOT'] . '/audio_logs/' . $audioFile;

            if (file_exists($file) && is_file($file)) {
                if (!unlink($file)) {
                    $error = error_get_last();
                    write_log('Error: ' . $error['message'], __FILE__, __LINE__);
                }
            }
        }

        $query = "  DELETE FROM user_log
                    WHERE id = '" . $_GET['deleteid'] . "'
                    LIMIT 1";

        $this->mysqli->query($query) or write_log($this->mysqli->error, __FILE__, __LINE__);
    }
}
