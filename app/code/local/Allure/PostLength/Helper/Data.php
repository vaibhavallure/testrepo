<?php
class Allure_PostLength_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PLPRODUCT_MESSAGE="";//The post length selected is free of cost product

    public function isPLMessage()
    {
        return self::PLPRODUCT_MESSAGE;
    }
}
	 