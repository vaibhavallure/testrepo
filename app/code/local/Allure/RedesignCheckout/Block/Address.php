<?php
/**
 * 
 * @author allure
 *
 */

class Allure_RedesignCheckout_Block_Address extends Mage_Checkout_Block_Multishipping_Address_Select
{
    protected $address;
    
    public function setAddresss($address){
        $this->address = $address;
    }
    
    public function getAddresss(){
        return $this->address;
    }
}

