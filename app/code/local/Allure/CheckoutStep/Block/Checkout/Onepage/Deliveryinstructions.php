<?php
 
class Allure_CheckoutStep_Block_Checkout_Onepage_Deliveryinstructions extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
        $this->getCheckout()->setStepData("deliveryinstructions", array(
            "label"     => Mage::helper("checkout")->__("Delivery Instruction"),
            "is_show"   => $this->isShow()
        ));
        parent::_construct();
    }
}
