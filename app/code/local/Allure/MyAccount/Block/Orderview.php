<?php

class Allure_MyAccount_Block_Orderview extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }
    
	public function getOrderLinks(){
	    $order = Mage::registry('current_order');
	    $links = array();
	    if ($order->hasInvoices()) {
	        $links['invoice'] = 'Invoices';
	    }
	    if ($order->hasShipments()) {
	        $links['shipment'] = 'Shipment';
	    }
	    if ($order->hasCreditmemos()) {
	        $links['creditmemo'] = 'Creditmemo';
	    }
	    return $links;
	}
}
