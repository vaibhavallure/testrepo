<?php
class Teamwork_CEGiftcards_Model_Exception extends Mage_Core_Exception
{
    protected $_visibleOnFrontend = true;

    public function isVisibleOnFrontend($vis = null)
    {
        if (!is_null($vis)) {
            $this->_visibleOnFrontend = $vis ? true : false;
        }
        if (Mage::app()->getStore(null)->getCode() == "admin") {
            return true;
        }
        return $this->_visibleOnFrontend;
    }
}
