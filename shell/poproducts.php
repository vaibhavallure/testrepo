<?php

require_once 'abstract.php';

class Allure_Shell_POProducts extends Mage_Shell_Abstract
{
	/**
	 * Run script for flushing varnish cache
	 */
	public function run()
	{
		Mage::app('admin')->setCurrentStore(0);
	    $model = Mage::getModel('allure_category/observer');
		//$model = Mage::getModel('core/email_queue');
		//$model->send();
		// Import Products:
		$model->setProductToChildCategory();
	}
}

$shell = new Allure_Shell_POProducts();
$shell->run();