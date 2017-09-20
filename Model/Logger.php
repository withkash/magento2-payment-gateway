<?php
namespace Kash\Gateway\Model;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Logger
{
    protected $logFile = null;
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    public function __construct(
        Config $config,
        File $file,
        DirectoryList $directoryList
    ) {
        $this->file = $file;
        $this->config = $config;
        $this->directoryList = $directoryList;

        $logDir = $directoryList->getPath('log');

        $this->logFile = $logDir . DIRECTORY_SEPARATOR . 'kash.log';
    }

    //log a message to our kash.log
    public function log($msg)
    {
        $this->file->filePutContents($this->logFile, $this->config->x_shop_name." ".date('c')." ".var_export($msg, true)."\n", FILE_APPEND | LOCK_EX);
    }

    public function getLog()
    {
        $result = $this->file->fileGetContents($this->logFile);
        return $result===false ? date('c')." Could not read kash log" : $result;
    }

    /**
     * Erase the log file once it's been sent to our server. In case it's been
     * written to while we're sending it back, erase only the first $length
     * characters and leave the rest for next time.
     */
    public function resetLog($length)
    {
        $file = $this->file->fileOpen($this->logFile, "r+");
        if (!$file) {
            return;
        }

        if ($this->file->fileLock($file, LOCK_EX)) {
            $contents = '';
            while (!$this->file->endOfFile($file)) {
                $contents .= $this->file->fileRead($file, 8192);
            }
            ftruncate($file, 0);
            rewind($file);
            $this->file->fileWrite($file, substr($contents, $length));
            $this->file->fileFlush($file);
            $this->file->fileLock($file, LOCK_UN);
        }
        $this->file->fileClose($file);
    }
}
