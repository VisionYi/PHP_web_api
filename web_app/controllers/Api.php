<?php

class Api extends DB_Api {

    private $DB;

    function __construct() {
        require_once 'database/libs/MyPDO.php';
        $this->DB = new MyPDO();
        // $this->DB->debugDB_SQL = true;
    }

    function __destruct() {
        $this->DB->closeDB();
        $this->check_json_error_log();
        exit();
    }

    public function get($row = '') {

        echo "Hellow api/get";

    }
}
?>
