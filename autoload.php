<?php
/**
 * description: http://justericgg.logdown.com/posts/196891-php-series-autoload
 * PSR-0 Standard: https://www.sitepoint.com/autoloading-and-the-psr-0-standard/
 * 適用於 PHP 5.3.0 以後的版本
 *
 * @author https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 * @author VisionYi (加入$fileExtension做附檔名判斷)
 */
function autoload($className) {

    $fileExtension = Array(".php", ".class.php");
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $fileName .= $className;

    foreach ($fileExtension as $ext) {
        if (is_readable($fileName . $ext)) {
            $fileName .= $ext;
            break;
        }
    }

    require $fileName;
}
spl_autoload_register('autoload');
