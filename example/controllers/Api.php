<?php

use core\lib\database\MyPDO;
use core\lib\WebApi;
use core\lib\Log;

class Api extends WebApi
{
    private $DB;

    public function __construct()
    {
        $this->DB = new MyPDO();
        $this->log = new Log();

        // Debug: 檢測SQL的語法錯誤
        $this->DB->setDebug(false);
    }

    public function __destruct()
    {
        $this->DB->closeDB();
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

        $record = $this->DB->query("SELECT api_name From test_api where api_data_int >:QQ",['QQ' => 125]);

        $content = $this->DB->getProfiler()->getContents();
        */
    }
}
