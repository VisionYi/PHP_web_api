<?php

use core\lib\database\MyPDO;
use core\lib\WebApi;

class Api extends WebApi
{
    private $DB;

    public function __construct()
    {
        $this->DB = new MyPDO();

        // Debug: 檢測SQL的語法錯誤
        $this->DB->setDebugDB(false);
    }

    public function __destruct()
    {
        $this->DB->closeDB();

        // Debug: 檢測印出的資訊是否符合JSON格式，使用JavaScript的console.error()
        $this->checkJsonErrorLog(false);

        // Debug: 檢測印出的資訊是否符合JSON格式，使用HTTP header顯示錯誤代碼與資訊
        /* $this->checkJsonErrorHeader(500, "Internal Server Error!!"); */
    }

    public function get($field = '')
    {
        if (empty($field)) {
            echo "<h2> http://localhost/api/get </h2>";
        } else {
            echo '<h2> http://localhost/api/get/[$field]</h2>';
            echo '$field = ' . $field . '<br>';
        }

        // 以下測試用的，測DB的library是否有效
        /*
        $this->DB->setProfiler(new \library\database\Profiler);
        $this->DB->getProfiler()->setActive(true);

        $record1 = $this->DB->query("SELECT api_name From test_api where api_data_int >:QQ",['QQ' => 125]);

        $s = $this->DB->getProfiler()->getContents();
        var_export($s);
        var_dump($record1);
        */
    }

    public function test($value='')
    {
        prints($this);
    }
}
