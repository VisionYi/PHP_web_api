<?php

namespace core\lib;

class WebApi
{
    /**
     * 是否有顯示出Error訊息
     * @var boolean
     */
    protected $showError = false;

    /**
     * 取得前端http post時所傳送的json格式Data
     *
     * @return array json轉變的Data
     */
    public function getPostData()
    {
        $dataJson = file_get_contents("php://input");
        return json_decode($dataJson, true);
    }

    /**
     * 輸出json格式，中文可已顯示正常
     *
     * @param  array  $data 取的資料
     */
    public function output(array $data)
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 在頁面上印出的所有東西都console Log出訊息,是Error型式的Log
     * 如果沒有給參數,就使用頁面上的緩存(全部顯示的文字)當成$content
     * [只適合使用在web_api後端偵錯上]
     *
     * @param  string $content 為Log的訊息
     */
    public function errorLog($content = '')
    {
        $replace_text = [
            "\r"   => "\\r",
            "\n"   => "\\n",
            "\t"   => "\\t",
            "<br>" => "\\n",
        ];

        if (empty($content)) {
            $content = ob_get_contents();
        }
        $content = addSlashes($content);

        foreach ($replace_text as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        echo "<script>console.error('$content');</script>";
        $this->showError = true;
    }

    /**
     * 檢查當前的頁面印出的所有資料是否為 Json 格式, 不是就error_log()頁面上所有的文字
     * 此函式適合放在 function __destruct()裡面
     * [只適合使用在web_api後端偵錯上]
     *
     * @param bool $isCheck 設定是否啟動這個function檢查
     */
    public function checkJsonErrorLog(bool $isCheck)
    {
        if ($isCheck) {

            $content = ob_get_contents();
            json_decode($content);

            if (json_last_error() !== JSON_ERROR_NONE
                && !$this->showError
                && !empty($content))
            {
                $alert = "This is not a JSON format: \n";
                $this->errorLog($alert . $content);
                $this->showError = false;
            }
        }
    }

    /**
     * 檢查當前的頁面印出的所有資料是否為 Json 格式, 不是就發出http標頭的錯誤代碼與訊息
     * 此函式適合放在 function __destruct()裡面
     * 也可不加上訊息，使用原本http協定自訂的錯誤訊息
     * [適合使用在前端顯示錯誤訊息上]
     *
     * @param  int    $code      http協定 錯誤代碼
     * @param  string $errorMessage 訊息內容
     */
    public function checkJsonErrorHeader($code, $errorMessage = '')
    {
        $content = ob_get_contents();
        $content = str_replace("<br>", "\n", $content);
        json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE
            && !$this->showError
            && !empty($content))
        {
            @header($_SERVER['SERVER_PROTOCOL'] . " $code $errorMessage", true, $code);

            ob_end_clean();
            echo "This is not a JSON format: \n" . $content;
        }
    }
}
