<?php
class Ecp_Wishlist_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getWishlistSimpleProduct($item)
    {
        $simpleProduct = null;
    	$optionsItem = $item->getOptionsByCode();

        if(isset($optionsItem['info_buyRequest'])){
            $simpleProductId = $optionsItem['info_buyRequest']->getProductId();
            $simpleProduct = Mage::getModel('catalog/product')->load($simpleProductId);
        }

        return $simpleProduct;
    }
    
    public function getSupperAttributes($item, $simpleProduct = false)
    {
    	$superAttributes = $item->getBuyRequest()->getSuperAttribute();   	
    	return $superAttributes;
    }
    
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _isCustomerLogIn()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }
    
 	public function isLogin(){
 		if ($this->_isCustomerLogIn()) {
 			return true;
 		} else {
 			return false;
 		} 	
 	}   
    
}