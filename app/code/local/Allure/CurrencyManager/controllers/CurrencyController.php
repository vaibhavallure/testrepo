<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

require_once 'Mage/Directory/controllers/CurrencyController.php';
class Allure_CurrencyManager_CurrencyController extends Mage_Directory_CurrencyController
{

    public function switchAction()
    {
        if ($curency = (string) $this->getRequest()->getParam('to')) {
            $curency = strtoupper($curency);

            $oldCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            
            Mage::app()->getStore()->setCurrentCurrencyCode($curency);
            
            $info = array(
                'from' => $oldCurrency,
                'to' => $curency
            );
            $flag=Mage::getStoreConfigFlag('currencymanager/logs/log_status');
            if($flag){
                Mage::log(json_encode($info), Zend_Log::DEBUG, 'allure_currencymanager.log', true);
            }
            Mage::getSingleton('customer/session')->setCurrencyChangeInformation($info);
        }
        $webmode = $this->getRequest()->getParam('webs');
        if(isset($webmode) && !empty($webmode))
        	$this->getResponse()->setRedirect(Mage::getBaseUrl().'webpos');
        else
        	$this->_redirectReferer(Mage::getBaseUrl());
    }

    protected function _redirectReferer($defaultUrl = null)
    {
        if($this->getRequest()->getParam('categoryid'))
        {
            $categoryId=$this->getRequest()->getParam('categoryid');
            $categoryLink = Mage::getModel("catalog/category")->load($categoryId)->getUrl();
            $this->getResponse()->setRedirect($categoryLink);
            return $this;
        }

        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
        }

        $this->getResponse()->setRedirect($refererUrl);
        return $this;
    }

    protected function _getRefererUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        //$refererUrl = Mage::helper('core')->escapeUrl($refererUrl);

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }
}
