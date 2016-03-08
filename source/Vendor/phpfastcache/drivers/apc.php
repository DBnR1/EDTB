<?php

/*
 * khoaofgod@gmail.com
 * Website: http://www.phpfastcache.com
 * Example at our website, any bugs, problems, please visit http://faster.phpfastcache.com
 */


class phpfastcache_apc extends BasePhpFastCache implements phpfastcache_driver
{
    public function checkdriver()
    {
        // Check apc
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            return true;
        } else {
            $this->fallback = true;
            return false;
        }
    }

    public function __construct($config = array())
    {
        $this->setup($config);

        if (!$this->checkdriver() && !isset($config['skipError'])) {
            $this->fallback = true;
        }
    }

    public function driver_set($keyword, $value = "", $time = 300, $option = array())
    {
        if (isset($option['skipExisting']) && $option['skipExisting'] == true) {
            return apc_add($keyword, $value, $time);
        } else {
            return apc_store($keyword, $value, $time);
        }
    }

    public function driver_get($keyword, $option = array())
    {
        // return null if no caching
        // return value if in caching

        $data = apc_fetch($keyword, $bo);
        if ($bo === false) {
            return null;
        }
        return $data;
    }

    public function driver_delete($keyword, $option = array())
    {
        return apc_delete($keyword);
    }

    public function driver_stats($option = array())
    {
        $res = array(
            "info" => "",
            "size"  => "",
            "data"  =>  "",
        );

        try {
            $res['data'] = apc_cache_info("user");
        } catch (Exception $e) {
            $res['data'] =  array();
        }

        return $res;
    }

    public function driver_clean($option = array())
    {
        @apc_clear_cache();
        @apc_clear_cache("user");
    }

    public function driver_isExisting($keyword)
    {
        if (apc_exists($keyword)) {
            return true;
        } else {
            return false;
        }
    }
}
