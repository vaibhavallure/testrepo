<?php
 
class Mage_Checkout_Block_Onepage_Deliveryoption extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
        $this->getCheckout()->setStepData("delivery_option", array(
            "label"     => Mage::helper("checkout")->__("Delivery Option"),
            "is_show"   => $this->isShow()
        ));
        parent::_construct();
    }
}
