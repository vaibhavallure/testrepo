<?php

class Ecp_Celebrities_Block_Send extends Mage_Core_Block_Template
{
    /**
     * Retrieve Max Recipients
     *
     * @return int
     */
    public function getMaxRecipients()
    {
        return Mage::helper('sendfriend')->getMaxRecipients();
    }
    
    /**
     * Return send friend model
     *
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    protected function _getSendfriendModel()
    {
        $model  = Mage::getModel('sendfriend/sendfriend');
        $model->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr(true));
        $model->setCookie(Mage::app()->getCookie());
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        return $model;
    }
    
    /**
     * Retrieve Send URL for Form Action
     *
     * @return string
     */
    public function getSendUrl()
    {
        return Mage::getUrl('celebrities/index/sendmail', array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
    }
    
    /**
     * Check if user is allowed to send
     *
     * @return boolean
     */
    public function canSend()
    {
        return !$this->_getSendfriendModel()->isExceedLimit();
    }
    
}