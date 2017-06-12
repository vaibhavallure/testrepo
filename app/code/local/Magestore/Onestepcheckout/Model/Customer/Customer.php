<?php

class Magestore_Onestepcheckout_Model_Customer_Customer extends Amasty_Customerattr_Model_Rewrite_Customer
{    

    /**
     * Validate customer attribute values.
     * For existing customer password + confirmation will be validated only when password is set (i.e. its change is requested)
     *
     * @return bool
     */
    public function validate()
    {
        if(Mage::helper('onestepcheckout')->enabledOnestepcheckout()){
			return true;
		}	
		
		return parent::validate();
    }
}
