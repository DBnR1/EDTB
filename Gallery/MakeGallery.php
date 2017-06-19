<?php
/**
 * Make screenshot galleries
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

namespace EDTB\Gallery;

/**
 * Make screenshot galleries
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 */
class MakeGallery
{
    /**
     * Check if generating a gallery is a go
     *
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function go(): bool
    {
        global $settings;

        $dir = $settings['old_screendir'];
        $newdir = $settings['new_screendir'];

        /**
         * check if current screenshot dir exists and is writable
         */
        if (!MakeGallery::valid_dir($dir)) {
            return false;
        }

        /**
         * check if new screenshot dir exists and is writable
         */
        if (!MakeGallery::valid_dir($newdir)) {
            return false;
        }

        /**
         * check if current screenshot dir can be scanned
         */
        if (!scandir($dir)) {
            $error = error_get_last();
            write_log('Error: ' . $error['message'], __FILE__, __LINE__);

            return false;
        }

        /**
         * if we've made it this far, we're good to go
         */
        return true;
    }

    /**
     * Check if dir is valid
     *
     * @param $dir
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function valid_dir($dir): bool
    {
        /**
         * check if value is set
         */
        if (!isset($dir) || empty($dir)) {
            return false;
        }

        /**
         * check if dir is the default value
         */
        if ($dir === "C:\\Users" || $dir === "C:\\Users\\") {
            return false;
        }

        /**
         * check if dir exists and is writable
         */
        if (!is_dir($dir) || !is_writable($dir)) {
            write_log('Error: ' . $dir . " is not writable or doesn't exist", __FILE__, __LINE__);

            return false;
        }

        return true;
    }

    /**
     * Convert screenshots to jpg and move to screenhot folder
     *
     * @param string $gallery_name name of the gallery to create (ie. the system name)
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public function make_gallery($gallery_name)
    {
        global $settings, $system_time, $mysqli;

        /**
         * get visit time for the system so we can tell if the screenshots were taken in that system
         */
        $esc_gallery_name = $mysqli->real_escape_string($gallery_name);

        $query = "  SELECT visit
                    FROM user_visited_systems
                    WHERE system_name = '$esc_gallery_name'
                    ORDER BY visit DESC
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $obj = $result->fetch_object();

        $visit_time = isset($obj->visit) ? strtotime($obj->visit) : time();

        $result->close();

        /**
         * scan screenshot directory for bmp files
         */
        $screenshots = glob($settings['old_screendir'] . '/*.bmp');

        /**
         * strip invalid characters from the gallery name
         */
        $gallery_name = strip_invalid_dos_chars($gallery_name);

        /** @var string $newscreendir */
        $newscreendir = $settings['new_screendir'] . '/' . $gallery_name;

        $added = 0;
        foreach ($screenshots as $file) {
            $file_name = str_replace($settings['old_screendir'] . '/', '', $file);
            $old_file_bmp = $file;
            $old_file_og = $settings['old_screendir'] . '/originals/' . $file_name;
            $filetime_o = filemtime($old_file_bmp);
            $filetime = $filetime_o + ($system_time * 60 * 60);

            /**
             * if screenshot was taken after entering the system
             */
            if ($filetime > $visit_time) {
                /**
                 * create gallery directory
                 */
                $this->create_dir($newscreendir);

                $edited = date('Y-m-d_H-i-s', $filetime_o);
                $new_filename = $edited . '-' . $gallery_name . '.jpg';
                $new_file_jpg = $settings['old_screendir'] . '/' . $new_filename;
                $new_screenshot = $newscreendir . '/' . $new_filename;

                /**
                 * convert from bmp to jpg
                 */
                if (file_exists($old_file_bmp)) {
                    /**
                     * execute ImageMagick convert
                     */
                    $command = '"' . $settings['install_path'] . '/bin/ImageMagick/convert" "' . $old_file_bmp . '" "' . $new_file_jpg . '"';
                    exec($command, $out);

                    if (!empty($out)) {
                        $error = json_encode($out);
                        write_log('Error: ' . $error, __FILE__, __LINE__);
                    }
                }

                /**
                 * delete original...
                 */
                if ($settings['keep_og'] === 'false') {
                    $this->remove_file($old_file_bmp);
                    /**
                     * ... or move to "originals" directory
                     */
                } else {
                    $this->create_dir($settings['old_screendir'] . '/originals');

                    if (file_exists($old_file_og)) {
                        $old_file_og = $settings['old_screendir'] . '/originals/' . $filetime . '_' . $file_name;
                    }

                    $this->move_file($old_file_bmp, $old_file_og);
                }

                /**
                 * move the converted file to screenshot folder
                 */
                $this->move_file($new_file_jpg, $new_screenshot);

                /**
                 * add no more than 15 at a time
                 */
                $added++;

                if ($added > 15) {
                    break;
                }
            } else {
                /**
                 * if screenshot was taken before entering the system, move it to "originals" directory
                 * so it doesn't interfere with the script in the future
                 */
                $this->create_dir($settings['old_screendir'] . '/originals');

                if (file_exists($old_file_og)) {
                    $old_file_og = $settings['old_screendir'] . '/originals/' . $filetime . '_' . $file_name;
                }

                $this->move_file($old_file_bmp, $old_file_og);
            }
        }
        unset($file);

        /**
         * make thumbnails for the gallery
         */
        if ($added > 0) {
            $this->make_thumbs($newscreendir);
        }
    }

    /**
     * Create a directory
     *
     * @param string $dir_name
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function create_dir($dir_name)
    {
        try {
            if (!is_dir($dir_name)) {
                if (!mkdir($dir_name, 0775, true)) {
                    $error = error_get_last();
                    throw new \Exception($error);
                }
            }
        } catch (\Exception $e) {
            write_log('Error: ' . $e->getMessage(), __FILE__, __LINE__);
        }
    }

    /**
     * Remove screenshot
     *
     * @param string $file path to file
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function remove_file($file)
    {
        if (file_exists($file)) {
            if (!unlink($file)) {
                $error = error_get_last();
                write_log('Error: ' . $error['message'], __FILE__, __LINE__);
            }
        }
    }

    /**
     * Move screenshot files around
     *
     * @param string $from source file path
     * @param string $to output file path
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function move_file($from, $to)
    {
        if (file_exists($from)) {
            if (!rename($from, $to)) {
                $error = error_get_last();
                write_log('Error: ' . $error['message'], __FILE__, __LINE__);
            }
        }
    }

    /**
     * Make thumbnail directory and thumbnails
     *
     * @param string $new_screendir
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function make_thumbs($new_screendir)
    {
        global $settings;
        /**
         * create thumbnail directory
         */
        $thumb_dir = $new_screendir . '/thumbs';
        $this->create_dir($thumb_dir);

        /**
         * run ImageMagick mogrify
         */
        $command = '"' . $settings['install_path'] . '/bin/ImageMagick/mogrify" -resize ' . $settings['thumbnail_size'] . ' -background #333333 -gravity center -extent ' . $settings['thumbnail_size'] . ' -format jpg -quality 95 -path "' . $thumb_dir . '" "' . $new_screendir . '/"*.jpg';
        exec($command, $out3);

        if (!empty($out3)) {
            $error = json_encode($out3);
            write_log('Error: ' . $error, __FILE__, __LINE__);
        }
    }
}
