<?php
use database\libs\MyPDO;
use web_app\libs\DB_Api;

class Api extends DB_Api {

	function __construct() {
	    DB_Api::__construct(new MyPDO());

	    // Debug: 檢測SQL的語法錯誤
	    // $this->DB->debugDB_SQL = true;
	}

	function __destruct() {
	    $this->DB->closeDB();

	    // Debug: 檢測印出的資訊是否符合JSON格式,使用JavaScript的console.error()
	    // $this->DB->check_json_error_log();

	    // Debug: 檢測印出的資訊是否符合JSON格式,使用HTTP header顯示錯誤代碼與資訊
	    // $this->DB->check_json_error_header(500, "Internal Server Error!!");

	    // 清除頁面的所有輸出
	    // ob_end_clean();

	    exit();
	}

	public function get($field = '') {
		if (empty($field)) {
			echo "Hellow: http://localhost/api/get";
		} else {
			echo "Hellow: http://localhost/api/get/$field";
		}
	}
}
?>
