<?php

require_once 'abstract.php';

class Allure_Shell_AllureAlert extends Mage_Shell_Abstract
{
    public function run()
    {
        Mage::app('admin')->setCurrentStore(0);

        if($this->getArg('alert'))
        {
            $alertFunction=$this->getArg('alert');
            $model = Mage::getModel('alertservices/alerts');
            $model->$alertFunction();
        }
        else {
            die($this->usageHelp());
        }
    }

    public function usageHelp()
    {
        return <<<USAGE
    Usage:  php AllureAlert.php --alert alertProductPrice / alertSalesOfTwo / alertSalesOfSix / alertCheckoutIssue / alertNullUsers / alertPageNotFound / instaTokenAlert

USAGE;
    }
}

$shell = new Allure_Shell_AllureAlert();
$shell->run();


