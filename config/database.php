<?php

return [

    'default_type'     => 'mysql',

    'PDO_connections' => [

        'mysql' => [
            'host'     => 'localhost',      // 資料庫主機名
            'dbname'   => 'school_project', // 使用的 DB 庫名稱
            'port'     => '3306',           // 資料庫連結 port
            'charset'  => 'utf8',           // 資料庫編碼方式(字符集)
            'username' => 'root',           // 資料庫連接用戶名
            'password' => 'mysql',          // 對應的密碼
            'options'  => [
                PDO::ATTR_EMULATE_PREPARES => false, // 使查詢出來的資料與資料庫的類型相同
            ]
        ],

        'sqlsrv' => [
            'server'   => 'localhost',
            'database' => '',
            'username' => '',
            'password' => '',
        ],
    ],
];
