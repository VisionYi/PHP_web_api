<?php

use core\lib\Log;

return [
    'dirPath'         => BASE_DIR . '/logs',
    'filename'        => 'testLog',
    'filename_date'   => '',
    'extension'       => 'txt',
    'content_date'    => 'Y-m-d H:i:s',
    'timezone'        => 'Asia/Taipei',
    'level_threshold' => Log::DEBUG,
];
