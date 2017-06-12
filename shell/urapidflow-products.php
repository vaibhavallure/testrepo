<?php

require_once 'abstract.php';

class Allure_Shell_ImportInventory extends Mage_Shell_Abstract
{
	/**
	 * Run script for flushing varnish cache
	 */
	public function run()
	{
		Mage::app('admin')->setCurrentStore(0);
			
		$helper = Mage::helper('urapidflow');

		// Import Products:
		$helper->run(1);
		
		//Import Extra:
		$helper->run(2);
		
		//Import Wholesale Prices:
		$helper->run(4);
	}
}

$shell = new Allure_Shell_ImportInventory();
$shell->run();