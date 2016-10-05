<?php
// initial 所有底層資料夾設定

// 預設根目錄
define('ROOT_PATH', 'web_app');
define('ERROR_View', 'Error.php');

// Auto loading all library php file
require_once 'libs/App.php';
require_once 'libs/_Error.php';
require_once 'libs/DB_Api.php';

$app = new App();

 ?>
