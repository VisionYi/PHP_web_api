<?php

namespace core\lib;

use core\lib\Conf;

/**
 * 有錯誤就顯示 Error 頁面
 */
class ErrorPage
{
    const NO_CONTROLLER = 1;
    const NO_MODEL      = 2;
    const NO_VIEW       = 3;

    protected $filePath;
    protected $messageType;

    /**
     * 呼叫 Error page 的檔案或是網址列 url
     *
     * @param  string $httpStateCode HTTP 狀態碼
     * @param  int    $messageType   此類別的錯誤訊息之代碼
     * @param  string $title         頁面的 title
     * @param  string $file          錯誤的檔案名稱或路徑
     */
    public function showPage(
        $httpStateCode,
        $messageType,
        $filePath,
        $title = null)
    {
        $info = Conf::get('errorPage', $httpStateCode);
        $this->messageType = $messageType;
        $this->filePath = $filePath;

        $title = is_null($title) ? $info['pageTitle'] : $title;
        echo "<script> document.title = '$title'; </script>";

        // 已url網址為優先使用
        if (!empty($info['url'])) {
            header('Location: ' . $info['url']);

        } else if (file_exists($info['filePath'])) {
            require_once $info['filePath'];

        } else {
            throw new \Exception('請設定\'url\'或\'filePath\'的參數。');
        }

        exit();
    }

    /**
     * 此函式是給 views 資料夾(也可自訂路徑)下的 Error 檔案使用的，顯示出個別的錯誤訊息
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
                echo "<b>Exception Error!!!</b>";
        }
    }
}
