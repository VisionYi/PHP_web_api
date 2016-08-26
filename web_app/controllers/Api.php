<?php
/* 需要 待修改成 try {} */

class Api extends DB_Api {

    private $DB;

    function __construct() {
        require_once 'database/libs/MyPDO.php';
        $this->DB = new MyPDO();
    }
    function __destruct() {
        $this->DB->closeDB();
        exit();
    }

    public function Index($test = '') {
        echo "Hellow Api~~";
    }

}
?>
