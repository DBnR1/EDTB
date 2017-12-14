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
        if (!self::validDir($dir)) {
            return false;
        }

        /**
         * check if new screenshot dir exists and is writable
         */
        if (!self::validDir($newdir)) {
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
     *
     * @return bool
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public static function validDir($dir): bool
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
     * @param string $galleryName name of the gallery to create (ie. the system name)
     *
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    public function makeGallery($galleryName)
    {
        global $settings, $systemTime, $mysqli;

        /**
         * get visit time for the system so we can tell if the screenshots were taken in that system
         */
        $escGalleryName = $mysqli->real_escape_string($galleryName);

        $query = "  SELECT visit
                    FROM user_visited_systems
                    WHERE system_name = '$escGalleryName'
                    ORDER BY visit DESC
                    LIMIT 1";

        $result = $mysqli->query($query) or write_log($mysqli->error, __FILE__, __LINE__);

        $obj = $result->fetch_object();

        $visitTime = isset($obj->visit) ? strtotime($obj->visit) : time();

        $result->close();

        /**
         * scan screenshot directory for bmp files
         */
        $screenshots = glob($settings['old_screendir'] . '/*.bmp');

        /**
         * strip invalid characters from the gallery name
         */
        $galleryName = stripInvalidDosChars($galleryName);

        /** @var string $newscreendir */
        $newscreendir = $settings['new_screendir'] . '/' . $galleryName;

        $added = 0;
        foreach ($screenshots as $file) {
            $fileName = str_replace($settings['old_screendir'] . '/', '', $file);
            $oldFileBmp = $file;
            $oldFileOg = $settings['old_screendir'] . '/originals/' . $fileName;
            $filetimeO = filemtime($oldFileBmp);
            $filetime = $filetimeO + ($systemTime * 60 * 60);

            /**
             * if screenshot was taken after entering the system
             */
            if ($filetime > $visitTime) {
                /**
                 * create gallery directory
                 */
                $this->createDir($newscreendir);

                $edited = date('Y-m-d_H-i-s', $filetimeO);
                $newFilename = $edited . '-' . $galleryName . '.jpg';
                $newFileJpg = $settings['old_screendir'] . '/' . $newFilename;
                $newScreenshot = $newscreendir . '/' . $newFilename;

                /**
                 * convert from bmp to jpg
                 */
                if (file_exists($oldFileBmp)) {
                    /**
                     * execute ImageMagick convert
                     */
                    $command =
                        '"' . $settings['install_path'] . '/bin/ImageMagick/convert" "' . $oldFileBmp . '" "' . $newFileJpg . '"';
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
                    $this->removeFile($oldFileBmp);
                    /**
                     * ... or move to "originals" directory
                     */
                } else {
                    $this->createDir($settings['old_screendir'] . '/originals');

                    if (file_exists($oldFileOg)) {
                        $oldFileOg = $settings['old_screendir'] . '/originals/' . $filetime . '_' . $fileName;
                    }

                    $this->moveFile($oldFileBmp, $oldFileOg);
                }

                /**
                 * move the converted file to screenshot folder
                 */
                $this->moveFile($newFileJpg, $newScreenshot);

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
                $this->createDir($settings['old_screendir'] . '/originals');

                if (file_exists($oldFileOg)) {
                    $oldFileOg = $settings['old_screendir'] . '/originals/' . $filetime . '_' . $fileName;
                }

                $this->moveFile($oldFileBmp, $oldFileOg);
            }
        }

        /**
         * make thumbnails for the gallery
         */
        if ($added > 0) {
            $this->makeThumbs($newscreendir);
        }
    }

    /**
     * Create a directory
     *
     * @param string $dirName
     *
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function createDir($dirName)
    {
        try {
            if (!mkdir($dirName, 0775, true) && !is_dir($dirName)) {
                $error = error_get_last();
                throw new \Exception($error);
            }
        } catch (\Exception $e) {
            write_log('Error: ' . $e->getMessage(), __FILE__, __LINE__);
        }
    }

    /**
     * Remove screenshot
     *
     * @param string $file path to file
     *
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function removeFile($file)
    {
        if (file_exists($file) && !unlink($file)) {
            $error = error_get_last();
            write_log('Error: ' . $error['message'], __FILE__, __LINE__);
        }
    }

    /**
     * Move screenshot files around
     *
     * @param string $from source file path
     * @param string $to output file path
     *
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function moveFile($from, $to)
    {
        if (file_exists($from) && !rename($from, $to)) {
            $error = error_get_last();
            write_log('Error: ' . $error['message'], __FILE__, __LINE__);
        }
    }

    /**
     * Make thumbnail directory and thumbnails
     *
     * @param string $newScreendir
     *
     * @author Mauri Kujala <contact@edtb.xyz>
     */
    private function makeThumbs($newScreendir)
    {
        global $settings;
        /**
         * create thumbnail directory
         */
        $thumbDir = $newScreendir . '/thumbs';
        $this->createDir($thumbDir);

        /**
         * run ImageMagick mogrify
         */
        $command = '"' . $settings['install_path'] . '/bin/ImageMagick/mogrify" -resize ' . $settings['thumbnail_size'] .
            ' -background #333333 -gravity center -extent ' . $settings['thumbnail_size'] . ' -format jpg -quality 95 -path "' .
            $thumbDir . '" "' . $newScreendir . '/"*.jpg';
        exec($command, $out3);

        if (!empty($out3)) {
            $error = json_encode($out3);
            write_log('Error: ' . $error, __FILE__, __LINE__);
        }
    }
}
