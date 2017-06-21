<?php

namespace core\lib\database;

use core\lib\database\Profiler;
use core\lib\Conf;
use \PDO;
use \PDOException;

/**
 * 操作資料庫的延伸型 PDO 類別
 */
class MyPDO
{
    protected $dsn;
    protected $username;
    protected $password;
    protected $options;
    protected $pdo;
    protected $stm;

    // 在子類別裡設為 true, 能檢測 debug 所有 sql 指令並顯示錯誤
    protected $debugDB       = false;
    protected $debugDB_trace = false;

    protected $pre_time;
    protected $profiler;

    /**
     * 初始化資料庫的基本資料與串接 PDO 的 dsn 設定
     * 1. 可以直接輸入參數 dsn、username、password、options 的設定
     * 2. 可以都不輸入參數，使用自己預設的設定，檔案路徑為 config/database.php
     * 3. 最後自動連結資料庫
     *
     * @param string $dsn      PDO 的 dsn
     * @param string $username 資料庫的連接用戶名
     * @param string $password 資料庫的密碼
     * @param array  $options  PDO 的 options 額外設定
     */
    public function __construct(
        $dsn = null,
        $username = null,
        $password = null,
        array $options = null
    ) {
        if ($dsn) {
            $this->dsn = $dsn;
            $this->username = $username;
            $this->password = $password;
            $this->options = $options;
        } else {

            $defaultDB = Conf::get('database', 'default_type');
            $connectInfo = Conf::get('database', 'PDO_connections')[$defaultDB];

            $this->dsn = '';
            $this->dsn .= $defaultDB . ':';

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
     * 連結資料庫，如有錯誤會跳 PDOException 例外處理
     */
    protected function connect()
    {
        try {
            $this->pdo = new PDO(
                $this->dsn, $this->username, $this->password, $this->options
            );
        } catch (PDOException $e) {
            echo "Database connection failed !!<br>Error: " . $e->getMessage();
        }
    }

    /**
     * 使用 PDO 的 prepare 函式，如果 $bind_data 有參數不是 []，會使用到 PDO 的 bindValue 函式
     * 會使用到 PDO 的預處理語句 PDOStatement ($this->stm)
     * 最後回傳 PDOStatement 類的 execute()，執行 SQL 語句是否成功
     *
     * @param  string $statement  SQL 語句
     * @param  array  $bind_data 綁定的資料
     * @return bool              執行 SQL 語句是否成功
     */
    protected function prepareBind($statement = '', array $bind_data = [])
    {
        $this->stm = $this->pdo->prepare($statement);

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
     * SQL的查詢語句，使用 $bind_data 可以防止 sql injection
     * ex: query("SELECT column FORM table WHERE column_int > :num", ['num' => 10])
     *
     * @param  string $statement  SQL 語句
     * @param  array  $bind_data 綁定的資料 (可選填)
     * @return array             正確: 查詢的所有資料 fetchAll(PDO::FETCH_ASSOC)
     *         bool              錯誤: 語句錯誤或資料有問題就回傳 false
     */
    public function query($statement = '', array $bind_data = [])
    {
        $this->startProfiler();
        $result = $this->prepareBind($statement, $bind_data);
        $this->endProfiler(__FUNCTION__, $statement, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statement, $bind_data);

        return ($result) ? $this->stm->fetchAll(PDO::FETCH_ASSOC) : $result;
    }

    /**
     * SQL 的新增語句，$data 為欄位名稱與資料，已自動防止 sql injection
     * ex: insert('table', ['column_1' => 'name', 'column_2' => 10])
     *
     * @param  string $table  資料表名稱
     * @param  array  $data   [欄位名稱 => 資料內容]
     * @param  string $nameId id 欄位名稱 (可選填)
     * @return string         正確: 回傳插入的 $nameId 內容或預設的 id
     *         bool           錯誤: 語句錯誤或資料有問題就回傳 false
     */
    public function insert($table = '', array $data = [], $nameId = null)
    {
        $bind_data = [];
        foreach ($data as $key => $value) {
            $bind_data[":$key"] = $value;
        }

        $columns = array_keys($data);
        $bind_key = array_keys($bind_data);
        $statement = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $bind_key) . ")";

        $this->startProfiler();
        $result = $this->prepareBind($statement, $bind_data);
        $this->endProfiler(__FUNCTION__, $statement, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statement, $bind_data);

        return ($result) ? $this->pdo->lastInsertId($nameId) : $result;
    }

    /**
     * SQL 的修改語句，$data 為欄位名稱與資料，已自動防止 sql injection
     * ex: update('table', ['column_1' => 'name', 'column_2' => 10], "id = '001'")
     *
     * @param  string $table       資料表名稱
     * @param  array  $data        [欄位名稱 => 資料內容]
     * @param  string $whereClause sql where 語句
     * @return int                 正確: 回傳受影響的資料筆數
     *         bool                錯誤: 語句錯誤或資料有問題就回傳 false
     */
    public function update($table = '', array $data = [], $whereClause = '')
    {
        $bind_temp = [];
        $bind_data = [];
        foreach ($data as $key => $value) {
            $bind_temp[] = "$key = :$key";
            $bind_data[":$key"] = $value;
        }

        $statement = "UPDATE {$table} SET " . implode(',', $bind_temp) . " WHERE {$whereClause}";

        $this->startProfiler();
        $result = $this->prepareBind($statement, $bind_data);
        $this->endProfiler(__FUNCTION__, $statement, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statement, $bind_data);

        return ($result) ? $this->stm->rowCount() : $result;
    }

    /**
     * SQL 的刪除語句，使用 $bind_data 可以防止 sql injection
     * ex: delete('table', "column_1 = 'test' AND column_2 > :num", ['num' => 10])
     *
     * @param  string $table       資料表名稱
     * @param  string $whereClause sql where 語句
     * @param  array  $bind_data   綁定的資料 (可選填)
     * @return int                 正確: 回傳受影響的資料筆數
     *         bool                錯誤: 語句錯誤或資料有問題就回傳 false
     */
    public function delete($table = '', $whereClause = '', array $bind_data = [])
    {
        $statement = "DELETE FROM {$table} WHERE {$whereClause}";

        $this->startProfiler();
        $result = $this->prepareBind($statement, $bind_data);
        $this->endProfiler(__FUNCTION__, $statement, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statement, $bind_data);

        return ($result) ? $this->stm->rowCount() : $result;
    }

    /**
     * 直接使用 PDO 的 exec()
     *
     * @param  string $statement SQL 語句
     * @return int               回傳受影響的資料筆數
     */
    public function exec($statement = '')
    {
        $this->startProfiler();
        $affected_rows = $this->pdo->exec($statement);
        $this->endProfiler(__FUNCTION__, $statement);
        $this->showDebugMsg($affected_rows, __FUNCTION__, $statement);

        return $affected_rows;
    }

    /**
     * 回傳執行 "SELECT found_rows()" 的語句之結果，前個搜尋 query 時的資料總筆數
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
     * PDO beginTransaction()
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
     * PDO commit()
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
     * PDO rollBack()
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
     * 是否要開啟 debug 功能，檢測 SQL 的語法錯誤，另外在 debug 時是否要顯示出追蹤的檔案路徑
     * 如果開了卻沒顯示出來，代表目前 SQL 都沒有錯誤
     *
     * @param bool  $debugDB   是否要顯示檢測的結果
     * @param bool  $showTrace 是否要顯示追蹤的檔案路徑
     */
    public function setDebug(bool $debugDB, bool $showTrace = false)
    {
        $this->debugDB = $debugDB;
        $this->debugDB_trace = $showTrace;
    }

    /**
     * 取得 PDO 參數
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * 取得 PDO 的預處理語句的參數
     */
    public function getPDOStm()
    {
        return $this->stm;
    }

    /**
     * 取得 PDO dsn 設定參數
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
     * @param Profiler $profiler 資料庫解析器 class
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
     * 會把所以有資訊及錯誤放入解析器中(類式 Log 紀錄器)
     *
     * @param  string     $fun_name   所使用的 SQL 語法 function 之名稱
     * @param  string     $statement  所使用的 SQL 語句
     * @param  array|null $bind_data  所綁定的資料
     */
    protected function endProfiler(
        $fun_name,
        $statement = '',
        array $bind_data = null
    ) {
        $error = is_null($bind_data) ? $this->errorMsg() : $this->errorMsg($this->stm);

        if ($this->profiler) {
            // add an entry to the profiler
            $this->profiler->addContent(
                microtime(true) - $this->pre_time,
                $fun_name,
                $statement,
                $bind_data,
                $error
            );
        }
    }

    /**
     * 此為顯示 SQL 語法錯誤的訊息，由 debugDB 此參數所驅動，都附在所有的 SQL 語句函式裡
     *
     * @param  bool       $result     做 SQL 語法時會有的執行是否成功
     * @param  string     $fun_name   所使用的 SQL 語法函式之名稱
     * @param  string     $statement  所使用的 SQL 語句
     * @param  array|null $bind_data  所綁定的資料
     */
    protected function showDebugMsg(
        $result,
        $fun_name,
        $statement = '',
        array $bind_data = null
    ) {
        if (false === $result && $this->debugDB) {

            $e = new PDOException;
            $error = is_null($bind_data) ? $this->errorMsg() : $this->errorMsg($this->stm);

            echo "<br>======DB debug======";
            echo "<br># Function: $fun_name";
            echo "<br># SQL: $statement";

            if (!empty($bind_data)) {
                echo "<br># Bind_data: ";
                var_export($bind_data);
            }

            if (!empty($error)) {
                echo "<br># Error: $error";
            }

            if ($this->debugDB_trace) {
                echo "<br># Trace: ";
                echo "<br>" . $e->getTraceAsString();
            }

            echo "<br>";
        }
    }

    /**
     * 從 PDO 裡取得錯誤資訊
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
