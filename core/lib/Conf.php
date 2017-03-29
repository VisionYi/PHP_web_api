<?php

namespace core\lib;

class Conf
{
    /**
     * 緩存配置文件
     * @var array $configs
     */
    public static $configs = [];

    /**
     * 取得一個配置文件的所有配置或其中一個配置項
     * 找不到配置文件或配置項時，丟出Exception例外
     *
     * @param  string      $name 配置文件名
     * @param  string|null $key  配置项名
     * @return mixed             返回配置內容
     */
    public static function get($fileName, $key = null)
    {
        $pathfile = CONFIG_DIR . '/' . $fileName . '.php';

        if (!is_file($pathfile)) {
            throw new \Exception("Error: 沒有配置的檔案 ", $fileName);
        }

        if (!isset(self::$configs[$fileName])) {
            self::$configs[$fileName] = require $pathfile;
        }

        $config = self::$configs[$fileName];

        if (!is_null($key)) {
            if (isset($config[$key])) {
                return $config[$key];
            } else {
                throw new \Exception("Error: 沒有這個配置項 ", $fileName);
            }
        } else {
            return $config;
        }
    }
}
