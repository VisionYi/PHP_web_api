<?php

/**
 * 自製PHP web_api (簡易框架 + 操作資料庫的library)
 *
 * @author Shane Yi
 * @dateTime 2016-08-26 10:22:13
 */

// Auto loading all library php file
require_once __DIR__ . '/autoload.php';

$app = new \library\App();

$app->run();
