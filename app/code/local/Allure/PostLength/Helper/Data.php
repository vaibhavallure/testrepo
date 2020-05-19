<?php
class Allure_PostLength_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PLPRODUCT_MESSAGE="All frontals come on a default 6.5mm post. You will receive the post size of your choice with the rest of your order at no charge.";

    public function isPLMessage()
    {
        return self::PLPRODUCT_MESSAGE;
    }
}
	 