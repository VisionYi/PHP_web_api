<?php

return [

    'default'     => 'mysql',

    'connections' => [

        'mysql' => [
            'host'     => 'localhost',      // 資料庫主機名
            'dbname'   => 'school_project', // 使用的DB庫名稱
            'port'     => '3306',           // 資料庫連結port
            'charset'  => 'utf8',           // 資料庫編碼方式(字符集)
            'username' => 'root',           // 資料庫連接用戶名
            'password' => 'mysql',          // 對應的密碼
        ],

        'sqlsrv' => [
            'Server'   => 'localhost',      // 資料庫主機名
            'Database' => '',               // 使用的DB庫名稱
            'username' => '',
            'password' => '',
        ],
    ],
];
