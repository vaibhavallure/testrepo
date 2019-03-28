<?php
/**
 * Abstract transfer model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Warning messages
     *
     * @var array
     */
    protected $_warning = array();

    /**
     * Error messages
     *
     * @var array
     */
    protected $_error = array();

    /**
     * Messages logger (see _getLogger() method)
     *
     * @var Teamwork_Transfer_Helper_Log
     */
    protected $__logger = null;

    /**
     * Add warning message to messages array and message logger
     *
     * @param string $msg
     * @param bool $doLog
     */
    protected function _addWarningMsg($msg, $doLog = true)
    {
        if ($doLog) $this->_getLogger()->addMessage($msg, Zend_Log::WARN);
        $this->_warning[] = $msg;
    }

    /**
     * Add array of warning messages to messages array and message logger
     *
     * @param array $msgs
     * @param bool $doLog
     */
    protected function _addWarningMsgs($msgs, $doLog = false)
    {
        if (!is_array($msgs))
        {
            $msgs = array($msgs);
        }
        foreach($msgs as $msg)
        {
            $this->_addWarningMsg($msg, $doLog);
        }
    }

    /**
     * Add error message to messages array and message logger
     *
     * @param string $msg
     * @param bool $doLog
     */
    protected function _addErrorMsg($msg, $doLog = true)
    {
        if ($doLog) $this->_getLogger()->addMessage($msg, Zend_Log::ERR);
        $this->_error[] = $msg;
    }

    /**
     * Add array of error messages to messages array and message logger
     *
     * @param array $msgs
     * @param bool $doLog
     */
    protected function _addErrorMsgs($msgs, $doLog = false)
    {
        if (!is_array($msgs))
        {
            $msgs = array($msgs);
        }
        foreach($msgs as $msg)
        {
            $this->_addErrorMsg($msg, $doLog);
        }
    }

    /**
     * Get message logger object
     * 
     * @return Teamwork_Transfer_Helper_Log
     */
    protected function _getLogger()
    {
        if (is_null($this->__logger))
        {
            $this->__logger = Mage::helper('teamwork_transfer/log');
        }
        return $this->__logger;
    }

    /**
     * Get warning message array
     * 
     * @return array
     */
    public function getWarningMsgs()
    {
        return $this->_warning;
    }

    /**
     * Get error message array
     * 
     * @return array
     */
    public function getErrorMsgs()
    {
        return $this->_error;
    }

    /**
     * Check whether we have at least one warning message
     * 
     * @return bool
     */
    public function hasWarningMsgs()
    {
        return !empty($this->_warning);
    }

    /**
     * Check whether we have at least one error message
     * 
     * @return bool
     */
    public function hasErrorMsgs()
    {
        return !empty($this->_error);
    }

    /**
     * Clean up error messages
     */
    public function cleanUpErrorMsgs()
    {
        $this->_error = array();
    }

    /**
     * Clean up warning messages
     */
    public function cleanUpWarningMsgs()
    {
        $this->_warning = array();
    }

}