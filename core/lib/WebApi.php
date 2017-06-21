<?php

namespace core\lib;

class WebApi
{
    Const GET = 'GET';
    Const POST = 'POST';

    /**
     * 輸出json格式，中文可已顯示正常
     *
     * @param  array  $data 取的資料
     */
    protected function output(array $data)
    {
        header('Content-Type: application/json;charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * 驗證 http request 的資料是否存在，再把空白與非法字元都濾掉，不存在內容就是 NULL
     * @param  array  $dataField     獲取的資料名稱
     * @param  string $requestMethod http request 的模式
     * @return array  $result        ['data'] 為全部的資料 ex: {'name' => 'data'}
     *                               ['data_empty'] 為'空'或 NULL 的名稱組數 ex: ['name']
     *                               ['data_exist'] 排除'空'與 NULL ex: {'name' => 'data'}
     */
    protected function requestData(array $dataField, $requestMethod)
    {
        $result = [];
        $result['data'] = [];
        $result['data_empty'] = [];
        $result['data_exist'] = [];

        if ($requestMethod === self::POST) {
            $requestMethod = $_POST;
        } else if ($requestMethod === self::GET) {
            $requestMethod = $_GET;
        } else {
            throw new \Exception("第二個參數輸入不正確。");
        }

        foreach ($dataField as $value) {
            $result['data'][$value] = isset($requestMethod[$value]) ? strip_tags(trim($requestMethod[$value])) : null;

            if (empty($result['data'][$value])) {
                array_push($result['data_empty'], $value);
            } else {
                $result['data_exist'][$value] = $result['data'][$value];
            }
        }

        return $result;
    }

    /**
     * [棄用]
     * 檢查當前的頁面印出的所有資料是否為 Json 格式, 不是就發出http標頭的錯誤代碼與訊息
     * 此函式適合放在 function __destruct() 裡面
     * 也可不加上訊息，使用原本http協定自訂的錯誤訊息
     *
     * @param  int    $code         http 協定 錯誤代碼
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
