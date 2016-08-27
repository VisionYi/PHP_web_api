<?php

class MyPDO {

	private $dbms = ''; 			//數據庫類型
	private $host = ''; 			//數據庫主機名
	private $port = 3306; 			//數據庫port
	private $dbName = ''; 			//使用的數據庫
	private $encode = ''; 			//數據庫編碼方式(字符集)
	private $user = ''; 			//數據庫連接用戶名
	private $pass = ''; 			//對應的密碼
	private $_data = [];
	public $pdo;
	public $stmt;

	function __construct() {
		$dsn = "$this->dbms:host=$this->host;dbname=$this->dbName;port=$this->port;charset=$this->encode";
		try {
			$this->pdo = new PDO($dsn, $this->user, $this->pass);
			// echo "PDO connection success !! <br><br>";
		} catch (PDOException $e) {
			echo "PDO connection failed !! <br>Error : " . $e->getMessage();
			exit;
		}
	}

	private function _prepare_bind($sql, $bind_data) {
		$this->stmt = $this->pdo->prepare($sql);

		foreach ($bind_data as $key => $value) {
			$this->stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		return $this->stmt->execute();
	}

	public function bindQuery($sql, array $bind_data=[]) {
		if($this->_prepare_bind($sql, $bind_data) === false){
			return false;
		}
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function bindInsert($table='', array $data=[]) {
		$this->_data = $data;
		$insertId = $this->getLast_Id($table) + 1;
		$bind_data = [];
		foreach ($this->_data as $key => $value) {
			$bind_data[":$key"] = $value;
		}

		$columns = array_keys($this->_data);
		$bind_val_key = array_keys($bind_data);
		$sql = "INSERT INTO $table (id," . implode(',', $columns) . ") VALUES ($insertId," . implode(',', $bind_val_key) . ")";

		return $this->_prepare_bind($sql, $bind_data);
	}

	public function bindUpdate($table="", array $data=[], $whereClause="") {
		$this->_data = $data;
		$bind_temp = [];
		$bind_data = [];
		foreach ($this->_data as $key => $value) {
			$bind_temp[] = "$key = :$key";
			$bind_data[":$key"] = $value;
		}

		$sql = "UPDATE $table SET " . implode(',', $bind_temp) . " WHERE {$whereClause} ";

		return $this->_prepare_bind($sql, $bind_data);
	}

	public function dbDelete($table='' ,$whereClause="") {
		$sql = "DELETE FROM $table WHERE {$whereClause}";
		return $this->pdo->exec($sql);
	}

	public function getLast_Id($table='') {
		$sql = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";
		foreach ($this->pdo->query($sql) as $row) {
			return $row['id'];
		}
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
			echo "Error code : " . $error[1] . '<br>';
			echo "Error string :" . $error[2] . '<br>';
		}
	}
}
?>
