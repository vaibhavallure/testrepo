<?php
/**
 * Message logger
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Helper_Log extends Mage_Core_Helper_Abstract
{
    /**
     * log file name
     */
    const DEBUG_LOG_FILE = 'teamwork_transfer.log';

    /**
     * Allow logging
     *
     * @var bool
     */
    protected $_isEnabled = true;


    /**
     * Check whether logging to file is allowed
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_isEnabled;
    }

    /**
     * Add message to log
     *
     * @param string $msg
     * @param int $level
     * @param string $file
     */
    public function addMessage($msg, $level = null, $file = '')
    {
        if ($this->isEnabled()) {
            $level  = is_null($level) ? Zend_Log::DEBUG : $level;
            $file = empty($file) ? self::DEBUG_LOG_FILE : $file;
            Mage::log($msg, $level, $file);
        }
    }

    /**
     * Add exception message and trace to log
     *
     * @param Exception $ex
     * @param bool $trace
     */
    public function addException($ex, $trace = true)
    {
        if ($this->isEnabled()) {
            $msg = sprintf("Exeption message: %s \n", $ex->getMessage());
            if ($trace) {
                $msg .= sprintf("Exception trace: %s", $ex->getTraceAsString());
            }
            $this->addMessage($msg);
        }
    }

}