<?php

require_once 'abstract.php';

class Allure_Shell_Teamwork extends Mage_Shell_Abstract
{
	/**
	 * Run script for flushing varnish cache
	 */
	public function run()
	{
		Mage::app('admin')->setCurrentStore(0);
	    $model = Mage::getModel('allure_teamwork/observer');
	
	    $model->reupdateCustomerToTeamwork();
	}
}

$shell = new Allure_Shell_Teamwork();
$shell->run();