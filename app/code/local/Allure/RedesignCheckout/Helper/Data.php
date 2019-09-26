<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{    
    /**
     * Get the gift wrap product details by using sku.
     */
    public function getGiftWrap(){
        try {
            $giftWrapSku = "GIFT_WRAP";
            $_product = Mage::getModel("catalog/product")->loadByAttribute( "sku", $giftWrapSku);
            if($_product){
                return $_product;
            }
        } catch (Exception $e){
            //exception handling
        }
        return null;
    }
}
