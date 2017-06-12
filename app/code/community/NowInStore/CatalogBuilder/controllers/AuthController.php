<?php
class NowInStore_CatalogBuilder_AuthController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $email = Mage::getStoreConfig('trans_email/ident_general/email');
        $baseUrl = urlencode(Mage::getBaseUrl());
        //$this->getResponse()->setRedirect("https://www.nowinstore.com/auth/magento/callback?baseUrl=$baseUrl");
        $hostname = Mage::app()->getFrontController()->getRequest()->getHttpHost();
        $address = str_replace("\r\n", "<br/>", Mage::getStoreConfig('general/store_information/address'));
	$email = Mage::getStoreConfig('trans_email/ident_general/email');
	$businessName = Mage::getStoreConfig('general/store_information/name');
	$name = Mage::getStoreConfig('trans_email/ident_general/name');
	$phone = Mage::getStoreConfig('general/store_information/phone');
	$version = Mage::getVersion();
	$this->getResponse()->setRedirect("https://www.nowinstore.com/auth/magento/callback?baseUrl=$baseUrl&hostname=$hostname&address=$address&email=$email&businessName=$businessName&name=$name&phone=$phone&version=$version");
    }
}
