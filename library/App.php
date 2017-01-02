<?php

namespace library;

use library\ErrorPage;

/**
 * 1. 解析url 呼叫類別裡的方法函式
 * 2. ex: http://localhost/u1/u2/u3
 *     先呼叫controllers資料夾下的u1.php，u1這個類別
 *     再呼叫裡面的u2 方法 function
 *     再取function($u3='') 直接得到u3得value
 *
 * 3. 沒輸入url的話, 預設為 $config裡的['defaultUrl']
 * 4. 資料夾不存在會有error判斷，導向errorPage
 */
class App
{
    protected $controller;
    protected $method;
    protected $pathControllers;

    public function __construct()
    {
        $config = require 'config/app.php';

        $this->initial($config);
    }

    /**
     * 啟動這個webApi
     */
    public function run()
    {
        $url = $this->parseUrl();
        $this->checkUrlExist($url);

        unset($url[0], $url[1]);
        $params = $url ? array_values($url) : [];

        $controllerObject = new $this->controller;
        call_user_func_array([$controllerObject, $this->method], $params);

        unset($params);
        exit();
    }

    /**
     * 初始化這個類別的所有宣告參數
     *
     * @param array $config 取得設定的config
     */
    protected function initial(array $config)
    {
        if (isset($config['defaultUrl'])) {

            $defaultUrl = explode('/', trim($config['defaultUrl'], '/'));
            $this->controller = $defaultUrl[0];
            $this->method = $defaultUrl[1];
        } else {
            throw new \Exception("Config/app.php: ['defaultUrl'] is not exist!\n");
        }

        $this->pathControllers = trim($config['path']['controllers'], '/') . '/';
    }

    /**
     * 檢查偵錯url網址列是否有存在檔案路徑、類別、方法
     * 如果其中一個有錯就會導向errorPage，不會繼續error過後的程序
     *
     * @param  array  $url url網址，以'/'分開後的所存入的陣列
     * @return string      完整檔案路徑
     */
    protected function checkUrlExist(array $url)
    {
        $path = $this->pathControllers . $this->controller;
        $pathFile = "$path.php";

        // 檢查檔案是否存在
        if (isset($url[0])) {
            $path = $this->pathControllers . $url[0];
            $pathFile = "$path.php";

            if (file_exists($pathFile)) {
                $this->controller = $url[0];
            } else {
                $this->showErrorPage($pathFile);
            }

        } else if (!file_exists($pathFile)) {
            $this->showErrorPage($pathFile);
        }

        require_once $pathFile;

        // 檢查檔案的類別是否存在，class_exists()的第2參數為 autoload是否要啟動
        if (!class_exists($this->controller, false)) {
            $this->showErrorPage("$pathFile, " . $this->controller . ' 類別');
        }

        // 檢查類別的方法是否存在
        if (isset($url[1])) {

            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
            } else {
                $this->showErrorPage("$pathFile, $url[1]() 方法");
            }

        } else if (!method_exists($this->controller, $this->method)) {
            $this->showErrorPage("$pathFile, " . $this->method . '() 方法');
        }

        return $pathFile;
    }

    /**
     * 乎叫ErrorPage這個library->page404()
     *
     * @param string $file 完整名稱或完整的路徑
     */
    protected function showErrorPage($file = '')
    {
        $error = new ErrorPage();
        $error->page404($file, ErrorPage::NO_CONTROLLER);
    }

    /**
     * 抓取網址上的Url,再以'/'間隔分成好幾個字放進array()裡 ,順便過濾多餘的'/'
     *
     * @return array url網址，以'/'分開後的所存入的陣列
     */
    protected function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode(
                '/',
                filter_var(
                    rtrim($_GET['url'], '/'),
                    FILTER_SANITIZE_URL
                )
            );
        } else {
            return [];
        }
    }
}
