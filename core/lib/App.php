<?php

namespace core\lib;

use core\lib\ErrorPage;
use core\lib\Conf;

/**
 * 1. 解析url 呼叫類別裡的方法函式
 * 2. ex: http://localhost/u1/u2/u3
 *     先呼叫 controllers 資料夾下的 u1.php u1 這個類別
 *     再呼叫裡面的 u2 方法 function
 *     再取 function($u3='') 直接得到 u3 value
 *
 * 3. 沒輸入 url 的話, 預設為 $config 裡的 ['defaultUrl']
 * 4. 資料夾不存在會有 error 判斷，導向 errorPage
 */
class App
{
    protected $controller;
    protected $method;
    protected $pathControllers;

    public function __construct()
    {
        $config = Conf::get('app');
        $this->initial($config);
    }

    /**
     * 啟動 webApi
     * new controller 類別，再把方法裡的 params 參數依序帶入 url[2].url[3]..的每個數值
     * 註: controller 類別裡的方法的每個參數都要有預設值喔！
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
     * @param array $config 取得設定的 config
     */
    protected function initial(array $config)
    {
        $defaultUrl = explode('/', trim($config['defaultUrl'], '/'));
        $this->controller = $defaultUrl[0];
        $this->method = $defaultUrl[1];

        $this->pathControllers = rtrim($config['path']['controllers'], '\\/') . '/';
    }

    /**
     * 檢查偵錯 url 網址列是否有存在檔案路徑、類別、方法
     * 如果其中一個有錯就會導向 errorPage，不會繼續 error 過後的程序
     * 如果 url[0] 存在的話，就會加載(require_once)這個 Controller 的檔案路徑($pathFile)
     *
     * @param  array  $url       url 網址，以'/'分開後的所存入的陣列
     * @return string $pathFile  完整檔案路徑
     */
    protected function checkUrlExist(array $url)
    {
        $path = $this->pathControllers . $this->controller;
        $pathFile = "$path.php";

        // 檢查檔案是否存在
        if (isset($url[0])) {
            $path = $this->pathControllers . $url[0];
            $pathFile = "$path.php";

            if (is_file($pathFile)) {
                $this->controller = $url[0];
            } else {
                $this->showErrorPage($pathFile);
            }

        } else if (!is_file($pathFile)) {
            $this->showErrorPage($pathFile);
        }

        require_once $pathFile;

        // 檢查檔案的類別是否存在，class_exists() 的第 2 參數為 autoload 是否要啟動
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
     * 抓取網址上的Url，濾掉 GET & hash 字符
     * 再以'/'間隔分成好幾個字放進 array() 裡，順便過濾多餘的'/'
     *
     * @return array url 網址，以'/'分開後的所存入的陣列
     */
    protected function parseUrl()
    {
        $url = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);

        if (!empty($url)) {
            return explode('/', trim($url, '/'));
        } else {
            return [];
        }
    }

    /**
     * 乎叫 ErrorPage library->page404()
     *
     * @param string $filePath 完整名稱或完整的路徑
     */
    protected function showErrorPage($filePath = '')
    {
        $error = new ErrorPage();
        $error->showPage('404', ErrorPage::NO_CONTROLLER, $filePath);
    }
}
