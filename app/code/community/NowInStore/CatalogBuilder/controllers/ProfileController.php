<?php
class NowInStore_CatalogBuilder_ProfileController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $hostname = Mage::app()->getFrontController()->getRequest()->getHttpHost();
        $address = str_replace("\r\n", "<br/>", Mage::getStoreConfig('general/store_information/address'));
        $jsonData = json_encode(array(
            "business_name" => Mage::getStoreConfig('general/store_information/name'),
            "name" => Mage::getStoreConfig('trans_email/ident_general/name'),
            "email" => Mage::getStoreConfig('trans_email/ident_general/email'),
            "baseUrl" =>  Mage::getBaseUrl(),
            "phone" => Mage::getStoreConfig('general/store_information/phone'),
            "address" => $address
        ));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
