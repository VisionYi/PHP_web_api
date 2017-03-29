<?php
/**
 * 公共函示庫 - debug專用
 * 打印出 變數或物件的內容(已經過格式化)
 *
 * @param mixed $var 變數
 */
function prints($var)
{
    if (is_bool($var)) {
        var_dump($var);
    } else if (is_null($var)) {
        var_dump($vsr);
    } else {
        echo "
            <pre style='position: relative;
                        z-index: 1000;
                        padding: 8px;
                        border: 1px solid #aaa;
                        border-radius: 3px;
                        background: #f5f5f5;
                        font-size: 14px;
                        line-height: 18px;
                        opacity: 0.9;'>" .
                print_r($var, true) .
            "</pre>";
    }
}
