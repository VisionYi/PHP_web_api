<?php
namespace database\libs;
use \PDO;

/**
 * 待加上註解
 */
class MyPDO {

	public $dbms = DBMS; 				//資料庫類型
	public $host = HOST; 				//資料庫主機名
	public $port = PORT; 				//資料庫連結port
	public $dbName = DBNAME; 			//使用的DB庫名稱
	public $encode = ENCODE; 			//資料庫編碼方式(字符集)
	public $username = USERNAME; 		//資料庫連接用戶名
	public $password = PASSWORD; 		//對應的密碼
	public $pdo;
	public $stmt;

	//在子類別裡設為true,能檢測debug所有sql指令並顯示錯誤
	public $debugDB_SQL = false;

	function __construct() {
		$dsn = "$this->dbms:host=$this->host;dbname=$this->dbName;port=$this->port;charset=$this->encode";
		try {
			$this->pdo = new PDO($dsn, $this->username, $this->password);
			// echo "PDO connection success !! <br><br>";
		}catch(PDOException $e) {
			$error_msg = "PDO connection failed !! <br>Error: ". $e->getMessage();
			echo $error_msg;
			exit();
		}
	}

	private function _prepare_bind($sql, $bind_data, $fun_name) {
		$this->stmt = $this->pdo->prepare($sql);

		foreach ($bind_data as $key => $value) {
			$this->stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$result = $this->stmt->execute();

		if(!$result && $this->debugDB_SQL){
			echo "<br>======debugDB_SQL======";
			echo "<br># Function: $fun_name";
			echo "<br># SQL: $sql";
			$this->showError();
			$this->closeDB();
			$this->debugDB_SQL = true;
		}
		return $result;
	}

	public function dbQuery($sql, array $bind_data=[]) {
		if(!$this->_prepare_bind($sql, $bind_data, __FUNCTION__)){
			return false;
		}
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function dbInsert($table='', array $data=[]) {
		$this->_data = $data;
		$bind_data = [];
		foreach ($this->_data as $key => $value) {
			$bind_data[":$key"] = $value;
		}

		$columns = array_keys($this->_data);
		$bind_val_key = array_keys($bind_data);
		$sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $bind_val_key) . ")";

		return $this->_prepare_bind($sql, $bind_data, __FUNCTION__);
	}

	public function dbUpdate($table="", array $data=[], $whereClause="") {
		$this->_data = $data;
		$bind_temp = [];
		$bind_data = [];
		foreach ($this->_data as $key => $value) {
			$bind_temp[] = "$key = :$key";
			$bind_data[":$key"] = $value;
		}

		$sql = "UPDATE $table SET " . implode(',', $bind_temp) . " WHERE {$whereClause}";

		return $this->_prepare_bind($sql, $bind_data, __FUNCTION__);
	}

	public function dbDelete($table='' ,$whereClause="") {
		$sql = "DELETE FROM $table WHERE {$whereClause}";

		return $this->_prepare_bind($sql, [], __FUNCTION__);
	}

	public function getTotal($records=[]) {
		$total = $this->bindQuery("SELECT found_rows()")[0]['found_rows()'];
		if($total < count($records)){
			$total = count($records);
		}
		return $total;
	}

	public function closeDB(){
		$this->stmt = null;
		$this->pdo = null;
	}

	public function showError() {
		$error = $this->stmt->errorInfo();
		if ($error[2] != '') {
			echo "<br># Error[$error[1]]: $error[2] <br><br>";
		}
	}

	public function dbBegin(){
		$this->pdo->beginTransaction();
	}
	public function dbCommit(){
		try {
			$this->pdo->commit();
		}catch(PDOException $e) {
			echo "<br># " . __FUNCTION__ . " error: " . $e->getMessage() . "[dbBegin]<br>";
		}
	}
	public function dbRollback(){
		try {
			$this->pdo->rollback();
		}catch(PDOException $e) {
			echo "<br># " . __FUNCTION__ . " error: " . $e->getMessage() . "[dbBegin]<br>";
		}
	}
}
?>