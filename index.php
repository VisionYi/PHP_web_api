<?php
/**
 * @author: VisionYi
 * @dateTime: 2016-08-26 10:22:13
 * @description: 自製PHP web_api (簡易框架 + 操作資料庫的library)
 */

// Auto loading all library php file
include_once __DIR__ . "/autoload.php";

// 設定初始化 database
require_once __DIR__ . "/database/init.php";

// 設定初始化 web_app
require_once __DIR__ . "/web_app/init.php";

 ?>
