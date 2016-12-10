<?php
namespace database\libs;
use database\libs\Profiler;
use \PDO;
use \PDOException;

/**
 * 待加上註解
 */
class MyPDO {

	private $default_dbms = 'mysql'; 		// 資料庫類型
	private $default_dsn = array(
		'host' => 'localhost', 				// 資料庫主機名
		'dbname' => 'school_project', 		// 使用的DB庫名稱
		'port' => '3306', 					// 資料庫連結port
		'charset' => 'utf8', 				// 資料庫編碼方式(字符集)
	);

	private $dsn = ''; 						// [可以選擇直接填入，會忽略上面的default]
	private $username = 'root'; 			// 資料庫連接用戶名
	private $password = 'mysql'; 			// 對應的密碼
	private $options;
	private $pdo;
	private $stm;

	// 在子類別裡設為true,能檢測debug所有sql指令並顯示錯誤
	private $debugDB = false;
	private $debugDB_trace = false;

	private $pre_time;
	private $profiler;

	function __construct(
		$dsn = null,
		$username = null,
		$password = null,
		array $options = array()
	) {
		if ($dsn) {
			$this->dsn = $dsn;
			$this->username = $username;
			$this->password = $password;
			$this->options = $options;
		}

		if (empty($this->dsn)) {
			$this->dsn = '';
			$this->dsn .= $this->default_dbms . ':';

			foreach ($this->default_dsn as $key => $value) {
				if (!empty($value)) {
					$this->dsn .= "$key=$value;";
				}
			}
		}
		$this->connect();
	}

	private function connect() {
		try {
			$this->pdo = new PDO(
				$this->dsn, $this->username, $this->password, $this->options
			);
		} catch (PDOException $e) {
			$error_msg = "PDO connection failed !! <br>Error: " . $e->getMessage();
			echo $error_msg;
			exit();
		}
	}

	private function _prepare_bind($statment, array $bind_data) {

		// prepare the statment
		$this->stm = $this->pdo->prepare($statment);

		foreach ($bind_data as $key => $value) {
			if (is_int($value)) {
				$param = PDO::PARAM_INT;
			} else if (is_bool($value)) {
				$param = PDO::PARAM_BOOL;
			} else if (is_null($value)) {
				$param = PDO::PARAM_NULL;
			} else if (is_string($value)) {
				$param = PDO::PARAM_STR;
			} else {
				$param = FALSE;
			}

			$this->stm->bindValue($key, $value, $param);
		}
		return $this->stm->execute();
	}

	public function query($statment, array $bind_data = array()) {
		$this->startProfiler();
		$result = $this->_prepare_bind($statment, $bind_data);
		$this->endProfiler(__FUNCTION__, $statment, $bind_data);
		$this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

		$getFetchAll = ($result)? $this->stm->fetchAll(PDO::FETCH_ASSOC) : false;
		return $getFetchAll;
	}

	public function insert($table = '', array $data = array()) {
		$bind_data = array();
		foreach ($data as $key => $value) {
			$bind_data[":$key"] = $value;
		}

		$columns = array_keys($data);
		$bind_val_key = array_keys($bind_data);
		$statment = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $bind_val_key) . ")";

		$this->startProfiler();
		$result = $this->_prepare_bind($statment, $bind_data);
		$this->endProfiler(__FUNCTION__, $statment, $bind_data);
		$this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

		return $result;
	}

	public function update($table = '', array $data = array(), $whereClause = '') {
		$bind_temp = array();
		$bind_data = array();
		foreach ($data as $key => $value) {
			$bind_temp[] = "$key = :$key";
			$bind_data[":$key"] = $value;
		}

		$statment = "UPDATE $table SET " . implode(',', $bind_temp) . " WHERE {$whereClause}";

		$this->startProfiler();
		$result = $this->_prepare_bind($statment, $bind_data);
		$this->endProfiler(__FUNCTION__, $statment, $bind_data);
		$this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

		return $result;
	}

	public function delete($table = '', $whereClause = '', array $bind_data=array()) {
		$statment = "DELETE FROM $table WHERE {$whereClause}";

		$this->startProfiler();
		$result = $this->_prepare_bind($statment, $bind_data);
		$this->endProfiler(__FUNCTION__, $statment, $bind_data);
		$this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

		return $result;
	}

	public function exec($statment) {
		$this->startProfiler();
		$affected_rows = $this->pdo->exec($statment);
		$this->endProfiler(__FUNCTION__, $statment);
		$this->showDebugMsg($affected_rows, __FUNCTION__, $statment);

		return $affected_rows;
	}

	public function getTotal_found_rows() {
		$record = $this->query("SELECT found_rows()");
		$getTotal = ($record)? $record[0]['found_rows()'] : false;

		return $getTotal;
	}

	public function closeDB() {
		$this->stm = null;
		$this->pdo = null;
	}

	public function beginTransaction() {
		$this->startProfiler();
		$result = $this->pdo->beginTransaction();
		$this->endProfiler(__FUNCTION__);
		return $result;
	}

	public function commit() {
		$this->startProfiler();
		$result = $this->pdo->commit();
		$this->endProfiler(__FUNCTION__);
		return $result;
	}

	public function rollBack() {
		$this->startProfiler();
		$result = $this->pdo->rollBack();
		$this->endProfiler(__FUNCTION__);
		return $result;
	}

	public function getPdo() {return $this->pdo;}
	public function getDsn() {return $this->dsn;}
	public function getProfiler() {return $this->profiler;}
	public function setProfiler(Profiler $profiler) {
		$this->profiler = $profiler;
	}

	private function startProfiler() {
		if ($this->profiler) {
			$this->pre_time = microtime(true);
		}
	}

	private function endProfiler(
		$fun_name,
		$statement = null,
		array $bind_data = null
	) {
		$errorMsg = isset($bind_data)? $this->errorMsg($this->stm) : $this->errorMsg();

		if ($this->profiler) {
			// add an entry to the profiler
			$this->profiler->addContent(
				microtime(true) - $this->pre_time,
				$fun_name,
				$statement,
				$bind_data,
				$errorMsg
			);
		}
	}

	public function setDebugDB(bool $debugDB, bool $showTrace = null) {
		$this->debugDB = $debugDB;
		$this->debugDB_trace = $showTrace;
	}

	private function showDebugMsg(
		$result,
		$fun_name,
		$statement = null,
		array $bind_data = null
	) {
		if (false === $result && $this->debugDB) {
			$e = new PDOException;
			$errorMsg = isset($bind_data)? $this->errorMsg($this->stm) : $this->errorMsg();

			echo "<br>======debugDB======";
			echo "<br># Function: $fun_name";
			echo "<br># SQL: $statement";

			if (!empty($bind_data)) {
				echo "<br># Bind_data: ";
				print_r($bind_data);
			}

			if (!empty($errorMsg)) {
				echo "<br># Error: $errorMsg";
			}

			if ($this->debugDB_trace) {
				echo "<br># Trace: ";
				echo "<br>" . $e->getTraceAsString();
			}

			echo "<br>";
		}
	}

	private function errorMsg($stm = null) {
		if ($stm) {
			$error = $stm->errorInfo();
		} else {
			$error = $this->pdo->errorInfo();
		}

		return $error[2];
	}
}
?>
