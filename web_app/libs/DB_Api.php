<?php

class DB_Api {

    /**
     * 取得前端http post時所傳送的json格式Data
     * @return array json轉變的Data
     */
    public function get_postData(){
        $dataJson = file_get_contents("php://input");
        return json_decode($dataJson,true);
    }

    /**
     * 輸出json格式
     * @param  array  $data 取的資料
     */
    public function output(array $data){
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
}

 ?>
