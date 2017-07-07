<?php
/**
 * System class
 *
 * No description
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

namespace EDTB\source;

/**
 * Functions relating to systems
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class System
{
    /**
     * Check if system is mapped in System map
     *
     * @param string $systemName
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function isMapped($systemName): bool
    {
        global $mysqli;

        if (empty($systemName)) {
            return false;
        }

        $escSystemName = $mysqli->real_escape_string($systemName);

        $query = "  SELECT id
                    FROM user_system_map
                    WHERE system_name = '$escSystemName'
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $num = $result->num_rows;

        $result->close();

        return $num > 0;
    }

    /**
     * Check if system has screenshots
     *
     * @param string $systemName
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function hasScreenshots($systemName): bool
    {
        global $settings;

        $systemName = strip_invalid_dos_chars($systemName);

        if (empty($systemName)) {
            return false;
        }

        if (is_dir($settings['new_screendir'] . '/' . $systemName)) {
            return true;
        }

        return false;
    }

    /**
     * Check if system is logged
     *
     * @param string $system
     * @param bool $isId
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function isLogged($system, $isId = false): bool
    {
        global $mysqli;

        if (empty($system)) {
            return false;
        }

        $escSystemName = $mysqli->real_escape_string($system);

        if ($isId !== false) {
            $query = "  SELECT id
                        FROM user_log
                        WHERE system_id = '$system'
                        AND system_id != ''
                        LIMIT 1";
        } else {
            $query = "  SELECT id
                        FROM user_log
                        WHERE system_name = '$escSystemName'
                        AND system_name != ''
                        LIMIT 1";
        }

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $logged = $result->num_rows;

        $result->close();

        if ($logged > 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if a system exists in our database
     *
     * @param string $systemName
     * @param bool $onlyOwn
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function exists($systemName, $onlyOwn = false): bool
    {
        global $mysqli;

        if (empty($systemName)) {
            return false;
        }

        $escSystemName = $mysqli->real_escape_string($systemName);

        $query = "  SELECT
                    id
                    FROM edtb_systems
                    WHERE name = '$escSystemName'
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
        $count = $result->num_rows;

        $result->close();

        if ($count == 0 || $onlyOwn === true) {
            $query = "  SELECT
                        id
                        FROM user_systems_own
                        WHERE name = '$escSystemName'
                        LIMIT 1";

            $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);
            $count = $result->num_rows;

            $result->close();
        }

        if ($count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Return links to screenshots, system log or system map
     *
     * @param string $system
     * @param bool $showScreens
     * @param bool $showSystem
     * @param bool $showLogs
     * @param bool $showMap
     * @return string $return
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function crosslinks($system, $showScreens = true, $showSystem = false, $showLogs = true, $showMap = true): string
    {
        $return = '';
        // check if system has screenshots
        if ($showScreens === true && System::hasScreenshots($system)) {
            $return .= '<a href="/Gallery?spgmGal=' . urlencode(strip_invalid_dos_chars($system)) . '" title="View image gallery" class="gallery_link">';
            $return .= '<img src="/style/img/image.png" class="icon" alt="Gallery" style="margin-left: 5px;  margin-right: 0; vertical-align: top">';
            $return .= '</a>';
        }

        // check if system is logged
        if ($showLogs === true && System::isLogged($system)) {
            $return .= '<a href="/Log?system=' . urlencode($system) . '" style="color: inherit" title="System has log entries" class="log_link">';
            $return .= '<img src="/style/img/log.png" class="icon" style="margin-left: 5px; margin-right: 0">';
            $return .= '</a>';
        }

        // check if system is mapped
        if ($showMap === true && System::isMapped($system)) {
            $return .= '<a href="/SystemMap/?system=' . urlencode($system) . '" style="color: inherit" title="System map" class="system_map_link">';
            $return .= '<img src="/style/img/grid.png" class="icon" style="margin-left: 5px; margin-right: 0">';
            $return .= '</a>';
        }

        // show link if system exists
        if ($showSystem === true && System::exists($system)) {
            $return .= '<a href="/System?system_name=' . urlencode($system) . '" style="color: inherit" title="System info" class="system_info_link">';
            $return .= '<img src="/style/img/info.png" class="icon" alt="Info" style="margin-left: 5px; margin-right: 0">';
            $return .= '</a>';
        }

        return $return;
    }

    /**
     * Return the number of visits user has made to system $system
     *
     * @param $system
     * @return int
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function numVisits($system): int
    {
        global $mysqli;

        $escSystemName = $mysqli->real_escape_string($system);

        $query = "  SELECT id
                    FROM user_visited_systems
                    WHERE system_name = '$escSystemName'";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $numVisits = $result->num_rows;

        $result->close();

        return $numVisits;
    }
}
