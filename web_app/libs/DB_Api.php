<?php

class DB_Api {

    private $show_error = false;
    /**
     * 取得前端http post時所傳送的json格式Data
     * @return array json轉變的Data
     */
    public function get_postData(){
        $dataJson = file_get_contents("php://input");
        return json_decode($dataJson, true);
    }

    /**
     * 輸出json格式
     * @param  array  $data 取的資料
     */
    public function output($data){
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 在頁面上印出的所有東西都console Log出訊息,是Error型式的Log
     * 如果沒有給參數,就使用頁面上的緩存(全部顯示的文字)當成$content
     * [只適合使用在web_api後端上]
     *
     * @param  string $content 為Log的訊息
     */
    public function error_log($content = '') {
        $replace_text = [
            "'" => "\'",
            "\r" => "\\r",
            "\n" => "\\n",
            "\t" => "\\t",
            "<br>" => "\\n"
        ];

        if(empty($content)){
            $content = ob_get_contents();
        }
        foreach ($replace_text as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        echo "<script>console.error('$content');</script>";
        $this->show_error = true;
    }

    /**
     * 檢查當前的頁面印出的所有資料是否為 Json 格式, 不是就error_log()頁面上所有的文字
     * 此函式適合放在 function __destruct()裡面
     * [只適合使用在web_api後端上]
     *
     */
    public function check_json_error_log() {
        $content = ob_get_contents();
        json_decode($content);

        if(json_last_error() !== JSON_ERROR_NONE && !$this->show_error && !empty($content)){
            $alert = "This is not a JSON format: \n";
            $this->error_log($alert . $content);
            $this->show_error = false;
        }
    }

    /**
     * 檢查當前的頁面印出的所有資料是否為 Json 格式, 不是就發出http標頭的錯誤代碼與訊息
     * 此函式適合放在 function __destruct()裡面
     * 也可不加上訊息，使用原本http協定自訂的錯誤訊息
     * [適合使用在前端上]
     *
     * @param  int    $code      http協定 錯誤代碼
     * @param  string $error_msg 訊息內容
     */
    public function check_json_error_header($code , $error_msg = '') {
        $content = ob_get_contents();
        $content = str_replace("<br>", "\n", $content);
        json_decode($content);

        if(json_last_error() !== JSON_ERROR_NONE && !$this->show_error && !empty($content)){
            @header($_SERVER['SERVER_PROTOCOL']." $code $error_msg", true, $code);

            ob_end_clean();
            echo "This is not a JSON format: \n" . $content;
        }
    }
}

 ?>
