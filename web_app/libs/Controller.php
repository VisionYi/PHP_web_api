<?php

class Controller {

	private $_view, $_layout;
	private $_css = array();
	private $_js = array();
	private $_var_data = array();

	/**
	 * 呼叫models資料夾下的 $model.php, 再new這個$model class
	 * @param string $model model的class名稱
	 */
	public function Model($model = '') {
		if ($model != '' && file_exists(ROOT_PATH . '/models/' . $model . '.php')) {
			require_once ROOT_PATH . '/models/' . $model . '.php';
			return new $model();
		} else {
			new _Error($model, 3);
		}
	}

	/**
	 * 如果有layout: 呼叫views資料夾下的 $layout
	 * 如果沒有layout: 呼叫views資料夾下的 $view
	 * 將array $var_data的Key值變成字符號,內容為value值
	 *
	 * @param string $view   views底下的View_PATH路徑
	 * @param string $layout views底下的layout_PATH路徑
	 * @param array $var_data 給views的Data
	 */
	public function View($view = '', $layout = '', array $var_data = []) {
		if (!file_exists(ROOT_PATH . '/views/' . $view)) {
			new _Error($view, 4);
		}

		$this->_var_data = $var_data;
		foreach ($var_data as $key => $value) {
			$$key = $value;
		}

		$this->_view = $view;
		$this->_layout = $layout;

		if ($this->_layout != '') {
			require_once ROOT_PATH . '/views/' . $this->_layout;
		} else {
			require_once ROOT_PATH . '/views/' . $this->_view;
		}
	}

	/**
	 * 此function是給layout.php使用的
	 * 呼叫views資料夾下的 $view
	 * 將array $_var_data的Key值變成字符號,內容為value值
	 */
	public function get_view() {
		foreach ($this->_var_data as $key => $value) {
			$$key = $value;
		}
		require_once ROOT_PATH . '/views/' . $this->_view;
	}

	public function set_css(array $css = []) {$this->_css = $css;}
	public function set_js(array $js = []) {$this->_js = $js;}
	public function get_css() {
		foreach ($this->_css as $value) {
			echo "<link rel='stylesheet' type='text/css' href='$value'/>";
		}
	}

	public function get_js() {
		foreach ($this->_js as $value) {
			echo "<script type='text/javascript' src='$value'></script>";
		}
	}
}
?>
