<?php

namespace library\database;

use library\database\Profiler;
use \PDO;
use \PDOException;

/**
 * @todo 待加上註解
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

            if (array_key_exists('username', $connectInfo)
             && array_key_exists('password', $connectInfo)) {

                $this->username = $connectInfo['username'];
                $this->password = $connectInfo['password'];
                unset($connectInfo['username']);
                unset($connectInfo['password']);
            }

            $this->dsn = '';
            $this->dsn .= $config['default'] . ':';

            foreach ($connectInfo as $key => $value) {
                if (!empty($value)) {
                    $this->dsn .= "$key=$value;";
                }
            }
        }

        $this->connect();
    }

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

    protected function prepareBind($statment, array $bind_data = [])
    {
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
                $param = false;
            }

            $this->stm->bindValue($key, $value, $param);
        }
        return $this->stm->execute();
    }

    public function query($statment, array $bind_data = [])
    {
        $this->startProfiler();
        $result = $this->prepareBind($statment, $bind_data);
        $this->endProfiler(__FUNCTION__, $statment, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

        return ($result) ? $this->stm->fetchAll(PDO::FETCH_ASSOC) : $result;
    }

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

    public function delete($table = '', $whereClause = '', array $bind_data = [])
    {
        $statment = "DELETE FROM {$table} WHERE {$whereClause}";

        $this->startProfiler();
        $result = $this->prepareBind($statment, $bind_data);
        $this->endProfiler(__FUNCTION__, $statment, $bind_data);
        $this->showDebugMsg($result, __FUNCTION__, $statment, $bind_data);

        return $result;
    }

    public function exec($statment)
    {
        $this->startProfiler();
        $affected_rows = $this->pdo->exec($statment);
        $this->endProfiler(__FUNCTION__, $statment);
        $this->showDebugMsg($affected_rows, __FUNCTION__, $statment);

        return $affected_rows;
    }

    public function getTotalFoundRows()
    {
        $record = $this->query("SELECT found_rows()");
        return ($record) ? $record[0]['found_rows()'] : $record;
    }

    public function closeDB()
    {
        $this->stm = null;
        $this->pdo = null;
    }

    public function beginTransaction()
    {
        $this->startProfiler();
        $result = $this->pdo->beginTransaction();
        $this->endProfiler(__FUNCTION__);
        return $result;
    }

    public function commit()
    {
        $this->startProfiler();
        $result = $this->pdo->commit();
        $this->endProfiler(__FUNCTION__);
        return $result;
    }

    public function rollBack()
    {
        $this->startProfiler();
        $result = $this->pdo->rollBack();
        $this->endProfiler(__FUNCTION__);
        return $result;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function getDsn()
    {
        return $this->dsn;
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    protected function startProfiler()
    {
        if ($this->profiler) {
            $this->pre_time = microtime(true);
        }
    }

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

    public function setDebugDB(bool $debugDB, bool $showTrace = null)
    {
        $this->debugDB = $debugDB;
        $this->debugDB_trace = $showTrace;
    }

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
