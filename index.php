<?php

/**
 * 自製 PHP web_api (簡易框架 + 操作資料庫的 library)
 *
 * @author Shane Yi
 * @dateTime 2016-08-26 10:22:13
 */

// Auto loading all php file
require_once __DIR__ . '/autoload.php';

// 加載公共函式庫
require_once __DIR__ . '/core/common/function.php';

// 定義常量
define('CONFIG_DIR', __DIR__ . '/config');
define('BASE_DIR', __DIR__);

// 啟動框架
$app = new \core\lib\App();
$app->run();
