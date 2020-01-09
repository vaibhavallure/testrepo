<?php
     
    class Webgility_Ecc_Model_Ecc extends Mage_Core_Model_Abstract
    {
        public function _construct()
        {
            parent::_construct();
            $this->_init('ecc/ecc');
        }
    }