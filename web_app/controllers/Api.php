<?php
use database\libs\MyPDO;
use web_app\libs\DB_Api;

class Api extends DB_Api {
	private $DB;

	function __construct() {
		$this->DB = new MyPDO();

	    // Debug: 檢測SQL的語法錯誤
	    // $this->DB->setDebugDB(true);
	}

	function __destruct() {
	    $this->DB->closeDB();

	    // Debug: 檢測印出的資訊是否符合JSON格式,使用JavaScript的console.error()
	    $this->check_json_error_log();

	    // Debug: 檢測印出的資訊是否符合JSON格式,使用HTTP header顯示錯誤代碼與資訊
	    // $this->check_json_error_header(500, "Internal Server Error!!");

	    exit();
	}

	public function get($field = '') {
		if (empty($field)) {
			echo "Hellow: http://localhost/api/get";
		} else {
			echo "Hellow: http://localhost/api/get/$field";
		}

		// $this->DB->setProfiler(new database\libs\Profiler);
		// $this->DB->getProfiler()->setActive(true);

		// $record1 = $this->DB->query("SELECT api_name From test_api where api_data_int >:QQ",['QQ' => 125]);

		// $s = $this->DB->getProfiler()->getContents();
		// var_dump($s);
		// var_dump($record1);
	}
}
?>
