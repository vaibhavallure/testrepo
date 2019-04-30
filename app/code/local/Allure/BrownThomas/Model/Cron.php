<?php

class Allure_BrownThomas_Model_Cron
{
    public function generateBrownthomasFiles()
    {
        Mage::helper("brownthomas/data")->add_log("model cron : cron call");
        Mage::helper("brownthomas/cron")->generateBrownthomasFiles();
    }
}