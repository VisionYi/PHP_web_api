<?php

namespace web_app\libs;

use web_app\libs\ErrorPage;
use web_app\Config;

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
class App
{
	protected $def_controller = 'Home';
	protected $def_method = 'Index';
	protected $root_path_controllers = 'main/controllers/';

	function __construct()
	{
		// 初始化所需參數
		$this->init(Config::DEFAULT_URL, Config::ROOT_PATH_CONTROLLERS);
		$controller = $this->def_controller;
		$method = $this->def_method;

		$error = new ErrorPage;
		$url = $this->parseUrl();

		$namespace_path_ctrl = str_replace('/', '\\', '/' . $this->root_path_controllers . $controller);
		$file_ctrl = $this->root_path_controllers . $controller . '.php';

		if (isset($url[0]))
		{
			$namespace_path_ctrl = str_replace('/', '\\', '/' . $this->root_path_controllers . $url[0]);
			$file_ctrl = $this->root_path_controllers . $url[0] . '.php';

			if (!file_exists($file_ctrl))
			{
				$error->page_404($file_ctrl, $error::NO_CONTROLLER);
			}
			else if (!class_exists($namespace_path_ctrl))
			{
				$error->page_404("$file_ctrl, [$url[0]] 類別", $error::NO_CONTROLLER);
			}
			else {
				$controller = $url[0];
			}
		}
		else if (!file_exists($file_ctrl))
		{
			$error->page_404($file_ctrl, $error::NO_CONTROLLER);
		}
		else if (!class_exists($namespace_path_ctrl))
		{
			$error->page_404("$file_ctrl, [$controller] 類別", $error::NO_CONTROLLER);
		}

		$controller = new $namespace_path_ctrl;

		if (isset($url[1]))
		{
			if (method_exists($controller, $url[1]))
			{
				$method = $url[1];
			}
			else {
				$error->page_404("$file_ctrl, [$url[1]] 方法", $error::NO_CONTROLLER);
			}
		}
		else if (!method_exists($controller, $method))
		{
			$error->page_404("$file_ctrl, [$method] 方法", $error::NO_CONTROLLER);
		}

		unset($url[0]);
		unset($url[1]);
		$params = $url ? array_values($url) : [];

		call_user_func_array(array($controller, $method), $params);
		unset($params);
		exit();
	}

	/**
	 * 初始化預設的參數，如果設置為空的話就不代入
	 *
	 * @param  string $deflate_url            預設基本首頁的url
	 * @param  string $deflate_root_path_ctrl 預設Controllers的根目錄
	 */
	protected function init($default_url = '', $default_root_path_ctrl = '')
	{
		if (!empty($default_url))
		{
			$default_url = explode('/', trim($default_url, '/'));
			$this->def_controller = $default_url[0];
			$this->def_method = $default_url[1];
		}

		if (!empty($default_root_path_ctrl))
		{
			$this->root_path_controllers = trim($default_root_path_ctrl, '/') . '/';
		}
	}

	/**
	 * 抓取網址上的Url,再以'/'間隔分成好幾個字放進array()裡 ,順便過濾多餘的'/'
	 *
	 * @return array() Url
	 */
	protected function parseUrl()
	{
		if (isset($_GET['url']))
		{
			return explode(
				'/',
				filter_var(
					rtrim($_GET['url'], '/'),
					FILTER_SANITIZE_URL
				)
			);
		}
		else {
			return array();
		}
	}
}
