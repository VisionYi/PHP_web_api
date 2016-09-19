<?php

class Api extends DB_Api {

    private $DB;

    function __construct() {
        require_once 'database/libs/MyPDO.php';
        $this->DB = new MyPDO();
        // $this->DB->debugDB_SQL = true;       //debug檢測SQL的語法錯誤

        ob_start();
    }

    function __destruct() {
        $this->DB->closeDB();
        // $this->check_json_error_log();       //檢測當前Api印出的資訊 是否符合JSON格式
        // $this->check_json_error_header(500, "Internal Server Error!!");

        // ob_end_clean();
        exit();
    }

    public function get($field = '') {

        if(empty($field)) {
            echo "Hellow api/get";
        }else{
            echo "Hellow api/get/$field";
        }

    }
}
?>
