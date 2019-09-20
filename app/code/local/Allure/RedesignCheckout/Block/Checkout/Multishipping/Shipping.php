<?php
/**
 * 
 * @author allure
 *
 */
require_once ('app/code/core/Mage/Checkout/Block/Multishipping/Shipping.php');
class Shipping extends Mage_Checkout_Block_Multishipping_Shipping
{
    public function addAddressRender($type, $block, $template){
        parent::addItemRender($type, $block, $template);
        return $this;
    }
}

