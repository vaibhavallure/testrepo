<?php

class Allure_MyAccount_Block_Myproducts extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        //$this->setTemplate('sales/order/history.phtml');
        Mage::app()->getFrontController()->getAction()->getLayout()
        	->getBlock('root')->setHeaderTitle(Mage::helper('myaccount')->__('My Products'));
    }
}
