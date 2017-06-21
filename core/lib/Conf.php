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
     * 找不到配置文件或配置項時，丟出 Exception
     *
     * @param  string      $name 配置文件名
     * @param  string|null $key  配置項名稱
     * @return mixed             返回配置內容
     */
    public static function get($filename, $key = null)
    {
        $pathFile = CONFIG_DIR . '/' . $filename . '.php';

        if (!is_file($pathFile)) {
            throw new \Exception("Error: 沒有配置的檔案 ", $filename);
        }

        if (!isset(self::$configs[$filename])) {
            self::$configs[$filename] = require $pathFile;
        }

        $config = self::$configs[$filename];

        if (!is_null($key)) {
            if (isset($config[$key])) {
                return $config[$key];
            } else {
                throw new \Exception("Error: 沒有這個配置項 ", $filename);
            }
        } else {
            return $config;
        }
    }
}
