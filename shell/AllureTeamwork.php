<?php

require_once 'abstract.php';

class Allure_Shell_AllureTeamwork extends Mage_Shell_Abstract
{
    public function run()
	{
        Mage::app('admin')->setCurrentStore(0);

            if($this->getArg('process')=="customers")
               $this->syncCustomers();
            elseif($this->getArg('process')=="orders")
                $this->syncOrders();
            elseif ($this->getArg('process')=="ordersbyday")
                $this->ordersByDay();
            else
                die($this->usageHelp());
	}

	private function syncCustomers()
    {
        $model = Mage::getModel('allure_teamwork/observer');
        $model->syncTeamworkCustomer();
    }
    private function syncOrders()
    {
        $model = Mage::getModel('allure_teamwork/tmobserver');
        $model->synkTeamwokLiveOrders();
    }
    private function ordersByDay()
    {
        $model = Mage::getModel('allure_teamwork/tmobserver');
        $model->syncOrdersByDay();
    }

    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php -f AllureTeamwork.php --process customers or orders or ordersbyday

USAGE;
    }
}

$shell = new Allure_Shell_AllureTeamwork();
$shell->run();


