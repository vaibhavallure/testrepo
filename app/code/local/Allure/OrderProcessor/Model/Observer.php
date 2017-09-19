<?php

class Allure_OrderProcessor_Model_Observer
{

	public function runProcessOrders ()
	{
		Mage::getModel('allure_orderprocessor/processor')->processOrders();
	}
}
