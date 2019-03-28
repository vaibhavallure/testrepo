<?php

class Allure_Facebook_Block_Button extends Mage_Core_Block_Template
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('allure/facebook/button.phtml');
    }

}
