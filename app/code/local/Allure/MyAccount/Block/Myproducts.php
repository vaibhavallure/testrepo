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
    
    public function getPurchasedItems(){
    	
    	$collection = Mage::getResourceModel('sales/order_item_collection')
    	->addAttributeToSelect('*');
    	$collection->getSelect()->join( array('orders'=> sales_flat_order),
    			'orders.entity_id=main_table.order_id',array('orders.customer_email','orders.customer_id'));
    	
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	
    	$collection->addFieldToFilter('customer_id',$customer->getId());
    	//$collection->getSelect()->group('main_table.product_id');
    	
    	return $collection;
    	//}
    }
}
