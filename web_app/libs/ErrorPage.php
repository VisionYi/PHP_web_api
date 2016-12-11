<?php

namespace web_app\libs;
use web_app\Config;

/**
 * 有錯誤就顯示 Error頁面
 */
class ErrorPage
{
	const NO_CONTROLLER  = 1;
	const NO_MODEL = 2;
	const NO_VIEW  = 3;

	protected $error_number;
	protected $_file;

	/**
	 * 呼叫 views資料夾(也可自訂路徑)下的Error檔案
	 *
	 * @param string $file   錯誤的檔案名稱或路徑
	 * @param string $number 錯誤代碼
	 */
	public function page_404($file = '', $number = '')
	{
		if (Config::VIEW_ERROR_404 != '')
		{
			$this->error_number = $number;
			$this->_file = $file;

			$title = 'Error 404';
			echo "<script> document.title = '$title'; </script>";

			require_once Config::VIEW_ERROR_404;
			exit();
		}
	}

	/**
	 * 此function是給 views資料夾(也可自訂路徑)下的Error檔案使用的，顯示出個別的錯誤訊息.
	 */
	public function error_message()
	{
		switch ($this->error_number)
		{
			case self::NO_CONTROLLER :
				echo "<b>[Controller]</b> 檔案: $this->_file 不存在！<br>";
				break;

			case self::NO_MODEL :
				echo "<b>[Model]</b> 檔案: $this->_file 不存在！<br>";
				break;

			case self::NO_VIEW :
				echo "<b>[View]</b> 檔案: $this->_file 不存在！<br>";
				break;
		}
	}
}
