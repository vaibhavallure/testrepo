<?php

class Teamwork_Weborder_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_errorlevels = array(1, 4, 16, 64);
    public function fatalErrorObserver()
    {
        if(function_exists('register_shutdown_function'))
        {
            register_shutdown_function(array($this, 'registrateFatalError'));
        }
    }

    public function registrateFatalError()
    {
        if(function_exists('error_get_last'))
        {
            $error = error_get_last();
            if(!empty($error) && in_array($error['type'], $this->_errorlevels))
            {
                Mage::log($error);
            }
        }
    }
}