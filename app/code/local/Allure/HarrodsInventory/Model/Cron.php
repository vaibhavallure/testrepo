<?php

class Allure_HarrodsInventory_Model_Cron
{
    public function generateHarrodsFiles()
    {
        Mage::helper("harrodsinventory/data")->add_log("model cron : cron call");
        Mage::helper("harrodsinventory/cron")->generateHarrodsFiles();
    }
    public function sendDailySales()
    {
        Mage::helper("harrodsinventory/data")->add_log("model cron daily sales : cron call");
        Mage::helper("harrodsinventory/cron")->sendDailySales();
    }


}