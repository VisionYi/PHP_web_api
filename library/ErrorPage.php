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
    protected $messageType;
    protected $config = [];

    public function __construct()
    {
        $this->config = require 'config/errorPage.php';
    }

    /**
     * 呼叫 Error page 的檔案或是網址列url
     *
     * @param  string $httpStateCode HTTP狀態碼
     * @param  int    $messageType   錯誤訊息的代碼
     * @param  string $title         頁面的title
     * @param  string $file          錯誤的檔案名稱或路徑
     */
    public function showPage(
        $httpStateCode,
        $messageType = null,
        $title = '',
        $file = '')
    {
        $info = $this->config[$httpStateCode];
        $this->messageType = $messageType;
        $this->filePath = $file;

        $title = empty($title) ? $info['pageTitle'] : $title;
        if (!empty($title)) {
            echo "<script> document.title = '$title'; </script>";
        }


        if (!empty($info['url'])) {
            header('Location: ' . $info['url']);

        } else if (!empty($info['pathFile'])) {
            require_once $info['pathFile'];

        } else {
            echo '<br>請設定 url 或 pathFile 的參數。';
        }

        exit();
    }

    /**
     * 此function是給 views資料夾(也可自訂路徑)下的Error檔案使用的，顯示出個別的錯誤訊息.
     */
    public function errorMessage()
    {
        if (!$this->messageType) {
            return;
        }

        switch ($this->messageType) {
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
