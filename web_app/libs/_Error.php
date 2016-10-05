<?php
/**
 * 有錯誤就顯示 Error頁面
 */
class _Error {

	private $error_number;
	private $_file;

	/**
	 * 呼叫 views資料夾下的Error.php
	 * @param string $file   錯誤的檔案名稱或路徑
	 * @param string $number 錯誤代碼
	 */
	function __construct($file = '', $number = '') {
		if (ERROR_View != '') {
			$this->error_number = $number;
			$this->_file = $file;

			$title = 'Error 404';
			echo "<script> document.title = '$title'; </script>";

			require_once ROOT_PATH . '/views/' . ERROR_View;
			exit();
		}
	}

	/**
	 * 此function是給Error.php使用的
	 * 顯示出個別的錯誤訊息.
	 */
	public function error_message() {
		switch ($this->error_number) {
		case 1:
			echo "<b>[Controller]</b> The file: $this->_file.php does not exists .<br>";
			break;
		case 2:
			echo "<b>[Controller]</b> The function: $this->_file() does not exists .<br>";
			break;
		case 3:
			echo "<b>[Model]</b> The file: $this->_file.php does not exists .<br>";
			break;
		case 4:
			echo "<b>[View]</b> The file: $this->_file does not exists .<br>";
			break;
		}
	}
}
?>
