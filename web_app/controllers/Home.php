<?php

namespace web_app\controllers;
use database\libs\MyPDO;

class Home
{
    public function Index()
    {
        echo "<h2>PHP_web_api</h2>";

        // Testing DB connection
        $DB = new MyPDO();

        if ($DB->getPdo())
        {
            echo "<br><b>DB connection success !!</b>";
        }
    }
}
