<?php
/**
 * 公共函示庫 - debug 專用
 * 打印出變數或物件的內容 (已經過格式化)
 *
 * @param mixed $var 變數
 */

function var_dump_pre($mixed = null)
{
    ob_start();
    var_dump($mixed);
    $content = ob_get_contents();
    ob_end_clean();
    $content = str_replace("=>\n "," =>",$content);

    echo "<pre style='position:relative; z-index:1000; padding:8px; border:1px solid #aaa; border-radius:3px; background:#f5f5f5; font-size:14px; line-height:18px;'>{$content}</pre>";
}

function dump_debug($input, $collapse = false)
{
    $recursive = function ($data, $level = 0) use (&$recursive, $collapse) {
        global $argv;

        $isTerminal = isset($argv);

        $type = !is_string($data) && is_callable($data) ? "Callable" : ucfirst(gettype($data));
        $type_data = null;
        $type_color = null;
        $type_length = null;

        switch ($type) {
            case "String":
                $type_color = "green";
                $type_length = strlen($data);
                $type_data = "\"" . htmlentities($data) . "\"";
                break;

            case "Double":
            case "Float":
                $type = "Float";
                $type_color = "#0099c5";
                $type_length = strlen($data);
                $type_data = htmlentities($data);
                break;

            case "Integer":
                $type_color = "red";
                $type_length = strlen($data);
                $type_data = htmlentities($data);
                break;

            case "Boolean":
                $type_color = "#92008d";
                $type_length = strlen($data);
                $type_data = $data ? "TRUE" : "FALSE";
                break;

            case "NULL":
                $type_length = 0;
                break;

            case "Array":
                $type_length = count($data);
                break;

            case "Object":
                $type_length = count($data);
        }

        if (in_array($type, ["Object", "Array"])) {
            $notEmpty = false;

            foreach ($data as $key => $value) {
                if (!$notEmpty) {
                    $notEmpty = true;

                    if ($isTerminal) {
                        echo $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "\n";

                    } else {
                        $id = substr(md5(rand() . ":" . $key . ":" . $level), 0, 8);

                        echo "<a href=\"javascript:toggleDisplay('" . $id . "');\" style=\"text-decoration:none\">";
                        echo "<span style='line-height:20px; color:#369'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>";
                        echo "</a>";
                        echo "<span id=\"plus" . $id . "\" style=\"display: " . ($collapse ? "inline" : "none") . ";\">&nbsp;&#10549;</span>";
                        echo "<div id=\"container" . $id . "\" style=\"display: " . ($collapse ? "" : "inline") . ";\">";
                        echo "<br />";
                    }

                    for ($i = 0; $i <= $level; $i++) {
                        echo $isTerminal ? "|    " : "<span style='line-height:20px; color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    }

                    echo $isTerminal ? "\n" : "<br />";
                }

                for ($i = 0; $i <= $level; $i++) {
                    echo $isTerminal ? "|    " : "<span style='line-height:20px; color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                echo $isTerminal ? "[" . $key . "]&nbsp;&nbsp;=>&nbsp;&nbsp;" : "<span style='line-height:20px; color:black'>[" . $key . "]&nbsp;&nbsp;=>&nbsp;&nbsp;</span>";

                call_user_func($recursive, $value, $level + 1);
            }

            if ($notEmpty) {
                for ($i = 0; $i <= $level; $i++) {
                    echo $isTerminal ? "|    " : "<span style='line-height:20px; color:black'>|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                if (!$isTerminal) {
                    echo "</div>";
                }

            } else {
                echo $isTerminal ?
                $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " :
                "<span style='line-height:20px; color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";
            }

        } else {
            echo $isTerminal ?
            $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " :
            "<span style='line-height:20px; color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";

            if ($type_data != null) {
                echo $isTerminal ? $type_data : "<span style='line-height:20px; color:" . $type_color . "'>" . $type_data . "</span>";
            }
        }

        echo $isTerminal ? "\n" : "<br />";
    };

    if (!defined("DUMP_DEBUG_SCRIPT")) {
        define("DUMP_DEBUG_SCRIPT", true);

        echo '<script language="Javascript">function toggleDisplay(id) {';
        echo 'var state = document.getElementById("container"+id).style.display;';
        echo 'document.getElementById("container"+id).style.display = state == "inline" ? "none" : "inline";';
        echo 'document.getElementById("plus"+id).style.display = state == "inline" ? "inline" : "none";';
        echo "}</script>" . "\n";
    }

    echo "<div style=\"position:relative; z-index:1000; padding:8px; margin: 12px 0; border:1px solid #aaa; border-radius:3px; background:#f5f5f5;\">";
    call_user_func($recursive, $input);
    echo '</div>';
}
