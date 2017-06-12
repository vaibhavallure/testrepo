<?php

require_once 'abstract.php';

class Allure_Shell_ImportUpdates extends Mage_Shell_Abstract
{
    /**
     * Run script for flushing varnish cache
     */
    public function run()
    {
		Mage::app('admin')->setCurrentStore(0);
		 
		$helper = Mage::helper('urapidflow');
		
		// Import Updates:
		$helper->run(6);
    }
}

$shell = new Allure_Shell_ImportUpdates();
$shell->run();