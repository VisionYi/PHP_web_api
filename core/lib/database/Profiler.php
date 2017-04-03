<?php

namespace core\lib\database;

/**
 * 資料庫解析器
 */
class Profiler
{
    protected $active   = false;
    protected $contents = [];

    /**
     * 把所有資訊與錯誤內容都加到 $contents此陣列變數裡
     *
     * @param float      $time       處理SQL語句所耗費的時間
     * @param string     $fun_name   所使用的SQL語法function之名稱
     * @param string     $statement  所使用的SQL語句
     * @param array|null $bind_data  所綁定的資料
     * @param string     $error      錯誤訊息
     */
    public function addContent(
        $time,
        $fun_name,
        $statement = '',
        array $bind_data = null,
        $error = null
    ) {
        if (!$this->isActive()) {
            return;
        }

        $e = new \Exception;
        $this->contents[] = [
            'duration'  => number_format($time, 5),
            'function'  => $fun_name,
            'statement' => $statement,
            'bind_data' => $bind_data,
            'error'     => $error,
            'trace'     => $e->getTraceAsString(),
        ];
    }

    /**
     * 取得 解析器的$contents所有資料
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * 設定是否啟動解析器
     *
     * @param bool $active 是否啟動
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * 取得 是否已開啟
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * 重新設定解析器所有資料，把資料都還原
     */
    public function resetContents()
    {
        $this->contents = [];
    }
}
