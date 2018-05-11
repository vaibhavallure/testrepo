<?php
/**
 * @author aws02
 * To Process some sales rule customization
 */
class Allure_Salesrule_Model_Validator extends Ebizmarts_BakerlooLoyalty_Model_Validator
{
    /**
     * process some salesrule data
     * i.e. International free shipping - avoid to sample ring with other product.
     * Avoid missyou30 coupon or other coupon i.e provided by admin configuration
     * with applied sku i.e present into the quote
     * 
     */
    protected function _canProcessRule($rule, $address)
    {
        $helper = Mage::helper("allure_salesrule");
        if ($rule->getCouponType() != Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if (strlen($couponCode)) {
                if(!$helper->isCoupanCodeValid($rule, $address)){
                    return false;
                }
            }
        }else{
            if($helper->isValidForFreeShipping($rule, $address)){
                return false;
            }
        }
        
        return parent::_canProcessRule($rule, $address);
    }
    
}
