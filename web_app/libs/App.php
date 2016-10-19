<?php
namespace web_app\libs;
use web_app\libs\Error;

/**
 * 1. 解析url 呼叫類別裡的方法函式
 * 2. ex: u1/u2/u3,
 * 		先呼叫controllers資料夾下的u1.php(u1這個class)
 * 		再呼叫裡面的u2 function
 * 		再取function($u3='') 直接得到u3得value
 *
 * 3. 沒輸入url的話, 預設為 /Home/Index
 * 4. 資料夾不存在會有error判斷
 */
class App {

	function __construct() {

		$deflate_url = explode('/', trim(DEFLATE_URL, '/'));
		$controller = $deflate_url[0];
		$method = $deflate_url[1];

		$url = $this->parseUrl();

		if (isset($url[0])) {
			if (file_exists(ROOT_PATH . '/controllers/' . $url[0] . '.php')) {
				$controller = $url[0];
				unset($url[0]);
			} else {
				new Error($url[0], 1);
			}

		} else
		if (!file_exists(ROOT_PATH . '/controllers/' . $controller . '.php')) {
			new Error($controller, 1);
		}

		require_once ROOT_PATH . '/controllers/' . $controller . '.php';
		$controller = new $controller;

		if (isset($url[1])) {
			if (method_exists($controller, $url[1])) {
				$method = $url[1];
				unset($url[1]);
			} else {
				new Error($url[1], 2);
			}

		} else
		if (!method_exists($controller, $method)) {
			new Error($method, 2);
		}

		$params = $url ? array_values($url) : [];
		call_user_func_array([$controller, $method], $params);
		unset($params);
		exit();
	}

	/**
	 * 抓取網址上的Url,再以'/'間隔分成好幾個字放進array()裡 ,順便過濾多餘的'/'
	 * @return array() Url
	 */
	private function parseUrl() {

		if (isset($_GET['url'])) {
			return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
		}
	}
}
?>
