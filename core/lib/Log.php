<?php

namespace core\lib;

use core\lib\Conf;
use \Exception;

/**
 * @todo 待加上註解
 */
class Log
{
    /**
     * Detailed debug information
     */
    const EMERGENCY = 1;
    const ALERT     = 2;
    const CRITICAL  = 3;
    const ERROR     = 4;
    const WARNING   = 5;
    const NOTICE    = 6;
    const INFO      = 7;
    const DEBUG     = 8;

    protected $levels = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARNING   => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG',
    ];

    protected $options = [
        'filename'        => '',
        'filename_date'   => 'Y-m-d',
        'extension'       => 'txt',
        'content_date'    => 'Y-m-d H:i:s',
        'timezone'        => 'Asia/Taipei',
        'level_threshold' => self::DEBUG,
    ];

    protected $fileHandle;

    public function __construct($dirPath = null, array $options = [])
    {
        if (is_null($dirPath)) {
            $this->options = array_merge($this->options, Conf::get('log'));
            $dirPath = $this->options['dirPath'];
        } else {
            $this->options = array_merge($this->options, $options);
        }

        date_default_timezone_set($this->options['timezone']);
        $this->setFileHandle($this->getFilePath($dirPath), 'a');
    }

    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    protected function getFilePath($dirPath)
    {
        $dirPath = rtrim($dirPath, '\\/') . '/';

        if (!file_exists($dirPath)) {
            mkdir($dirPath, '0777', true);
        }

        return $dirPath . $this->options['filename'] . date($this->options['filename_date']) . '.' . $this->options['extension'];
    }

    protected function setFileHandle($filePath, $writeMode)
    {
        $this->fileHandle = fopen($filePath, $writeMode);

        if (!$this->fileHandle) {
            throw new Exception('檔案不能被打開，請確認權限設定。');
        }
    }

    protected function write($level, $message, array $context = [])
    {
        if ($level > $this->options['level_threshold']) {
            return;
        }

        $date = date($this->options['content_date']);
        $message = "[{$date}] [{$this->levels[$level]}] {$message}" . PHP_EOL;

        if (!empty($context)) {
            $message .= $this->contextToString($context) . PHP_EOL;
        }

        if (false === fwrite($this->fileHandle, $message)) {
            throw new Exception('檔案不能寫入，請確認權限設定。');
        }
    }

    protected function contextToString(array $context)
    {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= preg_replace(array(
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ), array(
                '=> $1',
                'array()',
                '    '
            ), str_replace('array (', 'array(', var_export($value, true)));
            $export .= PHP_EOL;
        }
        return str_replace(array('\\\\', '\\\''), array('\\', '\''), rtrim($export));
    }

    public function log($level, $message, array $context = [])
    {
        $this->write($level, (string) $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->write(self::DEBUG, (string) $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->write(self::INFO, (string) $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->write(self::NOTICE, (string) $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->write(self::WARNING, (string) $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->write(self::ERROR, (string) $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->write(self::CRITICAL, (string) $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->write(self::ALERT, (string) $message, $context);
    }

    public function emergency($message, array $context = [])
    {
        $this->write(self::EMERGENCY, (string) $message, $context);
    }
}
