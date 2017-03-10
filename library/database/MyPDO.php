<?php

namespace library\database;

use library\database\Profiler;
use \PDO;
use \PDOException;

/**
 * 操作資料庫的延伸型PDO類別
 */
class MyPDO
{
    protected $dsn;
    protected $username;
    protected $password;
    protected $options;
    protected $pdo;
    protected $stm;

    // 在子類別裡設為true,能檢測debug所有sql指令並顯示錯誤
    protected $debugDB       = false;
    protected $debugDB_trace = false;

    protected $pre_time;
    protected $profiler;

    /**
     * 初始化資料庫的基本資料 & 串接PDO的dsn設定
     * 1. 可以直接輸入參數dsn、username、password、options的設定
     * 2. 可以都不輸入參數，使用自己預設的設定，檔案路徑為 config/database.php
     * 3. 最後自動連結資料庫
     *
     * @param string $dsn      PDO的dsn
     * @param string $username 資料庫的連接用戶名
     * @param string $password 資料庫的密碼
     * @param array  $options  PDO的options額外設定
     */
    public function __construct(
        $dsn = null,
        $username = null,
        $password = null,
        array $options = []
    ) {
        if ($dsn) {
            $this->dsn = $dsn;
            $this->username = $username;
            $this->password = $password;
            $this->options = $options;
        } else {

            $config = require 'config/database.php';
            $connectInfo = $config['connections'][$config['default']];

            $this->dsn = '';
            $this->dsn .= $config['default'] . ':';

            foreach ($connectInfo as $key => $value) {

                if (!empty($value)) {
                    if ($key === 'username') {
                        $this->username = $value;
                    } else
                    if ($key === 'password') {
                        $this->password = $value;
                    } else
                    if ($key === 'options') {
                        $this->options = $value;
                    }else {
                        $this->dsn .= "$key=$value;";
                    }
                }
            }
        }

        $this->connect();
    }

    /**
     * 連結資料庫，如有錯誤會跳PDOException例外處理
     */
    protected function connect()
    {
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

    /**
     * 使用PDO的prepare函式，如果$bind_data有參數不是[]，會使用到PDO的bindValue函式
     * 會使用到PDO的預處理語句PDOStatment類 ($this->stm)
     * 最後回傳PDOStatment類的execute()，執行SQL語句是否成功
     *
     * @param  string $statment  SQL語句
     * @param  array  $bind_data 綁定的資料
     * @return bool              執行SQL語句是否成功
     */
    protected function prepareBind($statment = '', array $bind_data = [])
    {
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
                $param = false;
            }

            $this->stm->bindValue($key, $value, $param);
        }
        return $this->stm->execute();
    }

    /**
     * SQL的查詢語句，使用$bind_data可以防止sql injection
     * ex: query("SELECT column FORM table WHERE column_int > :num", ['num' => 10])
     *
     * @param  string $statment  SQL語句
     * @param  array  $bind_data 綁定的資料
     * @return array             正確: 查詢的所有資料 fetchAll(PDO::FETCH_ASSOC)
     *         bool              錯誤: 語句錯誤或資料有問題就回傳false
     */
    public function query($statment = '', array $bind_data = [])
    {
        $this->startProfiler();
        $result = $this->prepareBind($statment, $bind_data);
        $this->endProfiler(__FUNCTION__, $statment, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

        return ($result) ? $this->stm->fetchAll(PDO::FETCH_ASSOC) : $result;
    }

    /**
     * SQL的新增語句，$data為欄位名稱與資料，已自動防止sql injection了
     * ex: insert('table', ['column_1' => 'name', 'column_2' => 10])
     *
     * @param  string $table 資料表名稱
     * @param  array  $data  [欄位名稱 => 資料內容]
     * @return bool          是否執行成功
     */
    public function insert($table = '', array $data = [])
    {
        $bind_data = [];
        foreach ($data as $key => $value) {
            $bind_data[":$key"] = $value;
        }

        $columns = array_keys($data);
        $bind_key = array_keys($bind_data);
        $statment = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $bind_key) . ")";

        $this->startProfiler();
        $result = $this->prepareBind($statment, $bind_data);
        $this->endProfiler(__FUNCTION__, $statment, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

        return $result;
    }

    /**
     * SQL的修改語句，$data為欄位名稱與資料，已自動防止sql injection了
     * ex: update('table', ['column_1' => 'name', 'column_2' => 10], "id = '001'")
     *
     * @param  string $table       資料表名稱
     * @param  array  $data        [欄位名稱 => 資料內容]
     * @param  string $whereClause sql的where語句
     * @return bool                是否執行成功
     */
    public function update($table = '', array $data = [], $whereClause = '')
    {
        $bind_temp = [];
        $bind_data = [];
        foreach ($data as $key => $value) {
            $bind_temp[] = "$key = :$key";
            $bind_data[":$key"] = $value;
        }

        $statment = "UPDATE {$table} SET " . implode(',', $bind_temp) . " WHERE {$whereClause}";

        $this->startProfiler();
        $result = $this->prepareBind($statment, $bind_data);
        $this->endProfiler(__FUNCTION__, $statment, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

        return $result;
    }

    /**
     * SQL的刪除語句，使用$bind_data可以防止sql injection
     * ex: delete('table', "column_1 = 'test' AND column_2 > :num", ['num' => 10])
     *
     * @param  string $table       資料表名稱
     * @param  string $whereClause sql的where語句
     * @param  array  $bind_data   綁定的資料
     * @return bool                是否執行成功
     */
    public function delete($table = '', $whereClause = '', array $bind_data = [])
    {
        $statment = "DELETE FROM {$table} WHERE {$whereClause}";

        $this->startProfiler();
        $result = $this->prepareBind($statment, $bind_data);
        $this->endProfiler(__FUNCTION__, $statment, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

        return $result;
    }

    /**
     * 直接使用PDO的exec()函式
     *
     * @param  string $statment SQL語句
     * @return int              回傳受影響的資料筆數
     */
    public function exec($statment = '')
    {
        $this->startProfiler();
        $affected_rows = $this->pdo->exec($statment);
        $this->endProfiler(__FUNCTION__, $statment);
        $this->showDebugMsg($affected_rows, __FUNCTION__, $statment);

        return $affected_rows;
    }

    /**
     * 回傳執行"SELECT found_rows()"的語句之結果，前個搜尋query時的資料總筆數
     *
     * @return int 資料筆數
     */
    public function getTotalFoundRows()
    {
        $record = $this->query("SELECT found_rows()");
        return ($record) ? $record[0]['found_rows()'] : $record;
    }

    /**
     * 關閉資料庫
     */
    public function closeDB()
    {
        $this->stm = null;
        $this->pdo = null;
    }

    /**
     * PDO的beginTransaction()函式
     *
     * @return bool 成功或失敗
     */
    public function beginTransaction()
    {
        $this->startProfiler();
        $result = $this->pdo->beginTransaction();
        $this->endProfiler(__FUNCTION__);
        return $result;
    }

    /**
     * PDO的commit()函式
     *
     * @return bool 成功或失敗
     */
    public function commit()
    {
        $this->startProfiler();
        $result = $this->pdo->commit();
        $this->endProfiler(__FUNCTION__);
        return $result;
    }

    /**
     * PDO的rollBack()函式
     *
     * @return bool 成功或失敗
     */
    public function rollBack()
    {
        $this->startProfiler();
        $result = $this->pdo->rollBack();
        $this->endProfiler(__FUNCTION__);
        return $result;
    }

    /**
     * 是否要開啟debug功能，檢測SQL的語法錯誤，另外在debug時是否要顯示出追蹤的檔案路徑
     * 如果開了卻沒顯示出來，代表目前SQL都沒有錯誤
     *
     * @param bool  $debugDB   是否要顯示檢測的結果
     * @param bool  $showTrace 是否要顯示追蹤的檔案路徑
     */
    public function setDebugDB(bool $debugDB, bool $showTrace = false)
    {
        $this->debugDB = $debugDB;
        $this->debugDB_trace = $showTrace;
    }

    /**
     * 取得 PDO參數
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 取得 PDO的預處理語句的參數
     */
    public function getPrepare_stm()
    {
        return $this->stm;
    }

    /**
     * 取得 PDO的dsn設定參數
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * 取得 解析器
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * 加入資料庫解析器
     *
     * @param Profiler $profiler 資料庫解析器class
     */
    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * 開始解析器的計算時間
     */
    protected function startProfiler()
    {
        if ($this->profiler) {
            $this->pre_time = microtime(true);
        }
    }

    /**
     * 結束解析器的計算時間
     * 會把所以有資訊及錯誤放入解析器中(類式Log紀錄器)
     *
     * @param  string     $fun_name   所使用的SQL語法function之名稱
     * @param  string     $statement  所使用的SQL語句
     * @param  array|null $bind_data  所綁定的資料
     */
    protected function endProfiler(
        $fun_name,
        $statement = null,
        array $bind_data = null
    ) {
        $errorMsg = ($bind_data) ? $this->errorMsg($this->stm) : $this->errorMsg();

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

    /**
     * 此為顯示SQL語法錯誤的訊息，由debugDB此參數所驅動，都附在所有的SQL語句function裡
     *
     * @param  bool       $result     做SQL語法時會有的執行是否成功
     * @param  string     $fun_name   所使用的SQL語法function之名稱
     * @param  string     $statement  所使用的SQL語句
     * @param  array|null $bind_data  所綁定的資料
     */
    protected function showDebugMsg(
        $result,
        $fun_name,
        $statement = null,
        array $bind_data = null
    ) {
        if (false === $result && $this->debugDB) {

            $e = new PDOException;
            $errorMsg = ($bind_data) ? $this->errorMsg($this->stm) : $this->errorMsg();

            echo "<br>======debugDB======";
            echo "<br># Function: $fun_name";
            echo "<br># SQL: $statement";

            if (!empty($bind_data)) {
                echo "<br># Bind_data: ";
                var_export($bind_data);
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

    /**
     * 從PDO裡取得錯誤資訊
     *
     * @param  statement $stm 預處理的語句內容
     * @return string         錯誤訊息
     */
    protected function errorMsg($stm = null)
    {
        if ($stm) {
            $error = $stm->errorInfo();
        } else {
            $error = $this->pdo->errorInfo();
        }

        return $error[2];
    }
}
