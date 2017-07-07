<?php
/*
 * khoaofgod@gmail.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://faster.phpfastcache.com
 */

class phpfastcache_files extends  BasePhpFastCache implements phpfastcache_driver
{

    public function checkdriver()
    {
        if (is_writable($this->getPath())) {
            return true;
        } else {
        }
        return false;
    }

    /*
     * Init Cache Path
     */
    public function __construct($config = array())
    {
        $this->setup($config);
        $this->getPath(); // force create path

        if (!$this->checkdriver() && !isset($config['skipError'])) {
            throw new Exception("Can't use this driver for your website!");
        }
    }

    private function encodeFilename($keyword)
    {
        return trim(trim(preg_replace("/[^a-zA-Z0-9]+/", "_", $keyword), "_"));
        // return rtrim(base64_encode($keyword), '=');
    }

    private function decodeFilename($filename)
    {
        return $filename;
        // return base64_decode($filename);
    }

    /*
     * Return $FILE FULL PATH
     */
    private function getFilePath($keyword, $skip = false)
    {
        $path = $this->getPath();

        $filename = $this->encodeFilename($keyword);
        $folder = substr($filename, 0, 2);
        $path = rtrim($path, "/")."/".$folder;
        /*
         * Skip Create Sub Folders;
         */
        if ($skip == false) {
            if (!@file_exists($path)) {
                if (!@mkdir($path, $this->__setChmodAuto())) {
                    throw new Exception("PLEASE CHMOD ".$this->getPath()." - 0777 OR ANY WRITABLE PERMISSION!", 92);
                }
            } elseif (!is_writeable($path)) {
                if (!chmod($path, $this->__setChmodAuto())) {
                    throw new Exception("PLEASE CHMOD ".$this->getPath()." - 0777 OR ANY WRITABLE PERMISSION!", 92);
                }
            }
        }

        $filePath = $path."/".$filename.".txt";
        return $filePath;
    }

    public function driver_set($keyword, $value = "", $time = 300, $option = array())
    {
        $filePath = $this->getFilePath($keyword);
      //  echo "<br>DEBUG SET: ".$keyword." - ".$value." - ".$time."<br>";
        $data = $this->encode($value);

        $toWrite = true;
        /*
         * Skip if Existing Caching in Options
         */
        if (isset($option['skipExisting']) && $option['skipExisting'] == true && @file_exists($filePath)) {
            $content = $this->readfile($filePath);
            $old = $this->decode($content);
            $toWrite = false;
            if ($this->isExpired($old)) {
                $toWrite = true;
            }
        }

        if ($toWrite == true) {
            try {
                $f = @fopen($filePath, "w+");
                fwrite($f, $data);
                fclose($f);
            } catch (Exception $e) {
                // miss cache
                    return false;
            }
        }
    }

    public function driver_get($keyword, $option = array())
    {
        $filePath = $this->getFilePath($keyword);
        if (!@file_exists($filePath)) {
            return null;
        }

        $content = $this->readfile($filePath);
        $object = $this->decode($content);
        if ($this->isExpired($object)) {
            @unlink($filePath);
            $this->auto_clean_expired();
            return null;
        }

        return $object;
    }

    public function driver_delete($keyword, $option = array())
    {
        $filePath = $this->getFilePath($keyword, true);
        if (@unlink($filePath)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Return total cache size + auto removed expired files
     */
    public function driver_stats($option = array())
    {
        $res = array(
            "info"  =>  "",
            "size"  =>  "",
            "data"  =>  "",
        );

        $path = $this->getPath();
        $dir = @opendir($path);
        if (!$dir) {
            throw new Exception("Can't read PATH:".$path, 94);
        }

        $total = 0;
        $removed = 0;
        while ($file=@readdir($dir)) {
            if ($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if (!$subdir) {
                    throw new Exception("Can't read path:".$path."/".$file, 93);
                }

                while ($f = @readdir($subdir)) {
                    if ($f!="." && $f!="..") {
                        $filePath = $path."/".$file."/".$f;
                        $size = @filesize($filePath);
                        $object = $this->decode($this->readfile($filePath));
                        if ($this->isExpired($object)) {
                            @unlink($filePath);
                            $removed += $size;
                        }
                        $total += $size;
                    }
                } // end read subdir
            } // end if
        } // end while

       $res['size'] = $total - $removed;
        $res['info'] = array(
                "Total [bytes]" => $total,
                "Expired and removed [bytes]" => $removed,
                "Current [bytes]" => $res['size'],
       );
        return $res;
    }

    public function auto_clean_expired()
    {
        $autoclean = $this->get("keyword_clean_up_driver_files");
        if ($autoclean == null) {
            $this->set("keyword_clean_up_driver_files", 3600*24);
            $res = $this->stats();
        }
    }

    public function driver_clean($option = array())
    {
        $path = $this->getPath();
        $dir = @opendir($path);
        if (!$dir) {
            throw new Exception("Can't read PATH:".$path, 94);
        }

        while ($file=@readdir($dir)) {
            if ($file!="." && $file!=".." && is_dir($path."/".$file)) {
                // read sub dir
                $subdir = @opendir($path."/".$file);
                if (!$subdir) {
                    throw new Exception("Can't read path:".$path."/".$file, 93);
                }

                while ($f = @readdir($subdir)) {
                    if ($f!="." && $f!="..") {
                        $filePath = $path."/".$file."/".$f;
                        @unlink($filePath);
                    }
                } // end read subdir
            } // end if
        } // end while
    }

    public function driver_isExisting($keyword)
    {
        $filePath = $this->getFilePath($keyword, true);
        if (!@file_exists($filePath)) {
            return false;
        } else {
            // check expired or not
            $value = $this->get($keyword);
            if ($value == null) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function isExpired($object)
    {
        if (isset($object['expired_time']) && time() >= $object['expired_time']) {
            return true;
        } else {
            return false;
        }
    }
}
