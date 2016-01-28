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
 * Backend file to convert screenshots
 *
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

/*
*	 screenshots
*/

if (isset($settings["old_screendir"]) && $settings["old_screendir"] != "C:\Users" && $settings["old_screendir"] != "C:\Users\\")
{
	if (is_dir($settings["old_screendir"]) && is_writable($settings["old_screendir"]))
	{
		// move screenshots
		if (!$screenshots = scandir($settings["old_screendir"]))
		{
			$error = error_get_last();
			write_log("Error: " . $error["message"] . "", __FILE__, __LINE__);
		}
		else
		{
			$newscreendir = "" . $settings["new_screendir"] . "/" . $prev_system . "";

			$added = 0;
			foreach ($screenshots as $file)
			{
				if (substr($file, -3) == "bmp")
				{
					if (!is_dir($newscreendir))
					{
						if (!mkdir($newscreendir, 0775, true))
						{
							$error = error_get_last();
							write_log("Error: " . $error['message'] . " - Could not create new screendir", __FILE__, __LINE__);
							break;
						}
					}
					$old_file_bmp = "" . $settings["old_screendir"] . "/" . $file . "";
					$old_file_og = "" . $settings["old_screendir"] . "/originals/" . $file . "";
					$edited = "" . date ("Y-m-d_H-i-s", filemtime($old_file_bmp)) . "";
					$new_filename = "" . $edited . "-" . $prev_system . ".jpg";
					$new_file_jpg = "" . $settings["old_screendir"] . "/" . $new_filename . "";
					$new_screenshot = "" . $newscreendir . "/" . $new_filename . "";

					// convert from bmp to jpg
					exec("\"" . $settings["install_path"] . "/bin/ImageMagick/convert\" \"" . $old_file_bmp . "\" \"" . $new_file_jpg . "\"", $out);

					if (!empty($out))
					{
						$error = json_encode($out);
						write_log("Error #8: " . $error . "", __FILE__, __LINE__);
					}

					if ($settings["keep_og"] == "false")
					{
						if (!unlink($old_file_bmp))
						{
							$error = error_get_last();
							write_log("Error: " . $error['message'] . " - Could not remove " . $old_file_bmp . "", __FILE__, __LINE__);
						}
					}
					else
					{
						if (!is_dir("" . $settings["old_screendir"] . "/originals"))
						{
							if (!mkdir("" . $settings["old_screendir"] . "/originals", 0775, true))
							{
								$error = error_get_last();
								write_log("Error: " . $error['message'] . " - Could not create directory " . $settings["old_screendir"] . "/originals", __FILE__, __LINE__);
								break;
							}
						}
						if (!rename("" . $old_file_bmp . "", "" . $old_file_og . ""))
						{
							$error = error_get_last();
							write_log("Error: " . $error['message'] . " - Could not rename " . $old_file_bmp . " to " . $old_file_og . "", __FILE__, __LINE__);
						}
					}
					// move to new screenshot folder
					if (!rename("" . $new_file_jpg . "", "" . $new_screenshot . ""))
					{
						$error = error_get_last();
						write_log("Error: " . $error['message'] . " - Could not rename " . $new_file_jpg . " to " . $new_screenshot . "", __FILE__, __LINE__);
					}
					$added++;

					/*
					*	add no more than 10 at a time
					*/

					if ($added > 10)
					{
						break;
					}
				}
			}
		}
		// make thumbnails for the gallery
		if ($added > 0)
		{
			$thumbdir = "" . $newscreendir . "/thumbs";

			if (!is_dir($thumbdir))
			{
				if (!mkdir("" . $thumbdir . "", 0775, true))
				{
					$error = error_get_last();
					write_log("Error: " . $error['message'] . " - Could not create directory " . $thumbdir . "", __FILE__, __LINE__);
					//break;
				}
			}
			exec("\"" . $settings["install_path"] . "/bin/ImageMagick/mogrify\" -resize " . $settings["thumbnail_size"] . " -background #333333 -gravity center -extent " . $settings["thumbnail_size"] . " -format jpg -quality 95 -path \"" . $thumbdir . "\" \"" . $newscreendir . "/\"*.jpg", $out3);

			if (!empty($out3))
			{
				$error = json_encode($out3);
				write_log("Error #5: ". $error . "", __FILE__, __LINE__);
			}
		}
	}
	else
	{
		write_log("Error: " . $settings["old_screendir"] . " is not writable", __FILE__, __LINE__);
	}
}
