<?php

class Allure_GoogleConnect_Block_Button extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $oauth2 = null;
    protected $userInfo = null;

    protected function _construct() {
        parent::_construct();

        $model = Mage::getSingleton('allure_googleconnect/client');

        if(!($this->client = $model->getClient()) ||
                !($this->oauth2 = $model->getOauth2())) 
                return;
        
        $this->userInfo = Mage::registry('allure_googleconnect_userinfo');

        $state = Mage::helper('core/url')->getCurrentUrl();
        if(($referer = Mage::getSingleton('customer/session')->getBeforeAuthUrl(true))){
            $state = $referer;
        }

        $this->client->setState(urlencode($state));


        $this->setTemplate('allure/googleconnect/button.phtml');
    }

    protected function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('googleconnect/index/disconnect');
        }
    }

    protected function _getButtonText()
    {
        if(empty($this->userInfo)) {
            return $this->__('Connect');
        } else {
            return $this->__('Disconnect');
        }
    }

}
