<?php

namespace core\lib;

class WebApi
{
    /**
     * 輸出json格式，中文可已顯示正常
     *
     * @param  array  $data 取的資料
     */
    protected function output(array $data)
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * [棄用]
     * 取得前端http post時所傳送的json格式Data
     *
     * @return array json轉變的Data
     */
    protected function getPostData()
    {
        $dataJson = file_get_contents("php://input");
        return json_decode($dataJson, true);
    }

    /**
     * [棄用]
     * 檢查當前的頁面印出的所有資料是否為 Json 格式, 不是就發出http標頭的錯誤代碼與訊息
     * 此函式適合放在 function __destruct()裡面
     * 也可不加上訊息，使用原本http協定自訂的錯誤訊息
     *
     * @param  int    $code      http協定 錯誤代碼
     * @param  string $errorMessage 訊息內容 (可選填)
     */
    protected function checkJsonErrorHeader($code, $errorMessage = '')
    {
        $content = ob_get_contents();
        $content = str_replace("<br>", "\n", $content);
        json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE && !empty($content))
        {
            @header($_SERVER['SERVER_PROTOCOL'] . " $code $errorMessage", true, $code);

            ob_end_clean();
            echo "This is not a JSON format: \n" . $content;
        }
    }
}
