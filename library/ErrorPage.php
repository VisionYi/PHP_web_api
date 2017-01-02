<?php

namespace library;

/**
 * 有錯誤就顯示 Error頁面
 */
class ErrorPage
{
    const NO_CONTROLLER = 1;
    const NO_MODEL      = 2;
    const NO_VIEW       = 3;

    protected $filePath;
    protected $errorCode;
    protected $errorPage = [];

    public function __construct()
    {
        $config = require 'config/app.php';

        $this->errorPage = $config['errorPage'];
    }

    /**
     * 呼叫 views資料夾(也可自訂路徑)下的Error檔案
     *
     * @param string $file   錯誤的檔案名稱或路徑
     * @param string $code   錯誤代碼
     */
    public function page404($file, $code)
    {
        if (!empty($this->errorPage['404'])) {
            $this->errorCode = $code;
            $this->filePath = $file;

            $title = 'Error 404';
            echo "<script> document.title = '$title'; </script>";

            require_once $this->errorPage['404'];
            exit();
        }
    }

    /**
     * 此function是給 views資料夾(也可自訂路徑)下的Error檔案使用的，顯示出個別的錯誤訊息.
     */
    public function errorMessage()
    {
        switch ($this->errorCode) {
            case self::NO_CONTROLLER:
                echo "<b>[Controller]</b> 檔案: $this->filePath 不存在！<br>";
                break;

            case self::NO_MODEL:
                echo "<b>[Model]</b> 檔案: $this->filePath 不存在！<br>";
                break;

            case self::NO_VIEW:
                echo "<b>[View]</b> 檔案: $this->filePath 不存在！<br>";
                break;

            default:
                echo "<b>Expection Error!!!</b>";
                break;
        }
    }
}
