<?php
/**
 * @author aws02
 */
class Allure_Salesrule_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FREE_SHIPPING_SKU            = 'allure_salesrule/free_shipping/product_sku';
    const XML_PATH_FREE_SHIPPING_SALES_RULE     = 'allure_salesrule/free_shipping/sales_rule_id';
    const XML_PATH_DONT_APPLY_COUPON_CODE       = 'allure_salesrule/coupon_section/coupon_code';
    
    /**
     * @return @sku string separated with comma
     */
    public function getProductSkuToAvoidFreeShipping(){
        return Mage::getStoreConfig(self::XML_PATH_FREE_SHIPPING_SKU);
    }
    
    /**
     * @return @sales rule_id string separated with comma
     */
    public function getSalesRuleIdToAvoidFreeShipping(){
        return Mage::getStoreConfig(self::XML_PATH_FREE_SHIPPING_SALES_RULE);
    }
    
    /**
     * @return @coupon_code string with separated with comma
     */
    public function getCoupanCodeToAvoidToProduct(){
        return Mage::getStoreConfig(self::XML_PATH_DONT_APPLY_COUPON_CODE);
    }
    
    /**
     * don't apply coupon code like "missyou30" if
     * quote contains actions appiled sku present.
     */
    public function isCoupanCodeValid($rule, $address){
        $isValid       = true;
        $couponCodeStr = strtolower($this->getCoupanCodeToAvoidToProduct());
        $couponCodeArr = explode("," , $couponCodeStr);
        if(count($couponCodeArr) > 0){
            $quote      = $address->getQuote();
            $couponCode = strtolower($quote->getCouponCode());
            if(in_array($couponCode , $couponCodeArr)){
                $coupon = Mage::getModel('salesrule/coupon');
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    $ruleData = unserialize($rule->getData('actions_serialized'));
                    $conditions   = $ruleData['conditions'];
                    foreach ($conditions as $condition){
                        $attribute = $condition['attribute'];
                        $operator  = $condition['operator'];
                        $value     = $condition['value'];
                        
                        if(($attribute == "sku") && ($operator == "!=")){
                            $skuArr = explode("," , $value);
                            $skuArr = array_map('trim',$skuArr);
                            foreach ($quote->getAllItems() as $item){
                                $sku = $item->getSku();
                                if(in_array($sku , $skuArr)){
                                    $isValid = false;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $isValid;
    }
    
    /**
     * don't apply international free shipping if quote
     * contains sku "samplerings" with other sku.
     */
    public function isValidForFreeShipping($rule, $address){
        $value     = strtolower($this->getProductSkuToAvoidFreeShipping());
        $skuArr    = explode("," , $value);
        $skuArr = array_map('trim',$skuArr);
        
        $ruleIds   = $this->getSalesRuleIdToAvoidFreeShipping();
        $ruleIdsArr = explode(",", $ruleIds);
        
        if(in_array($rule->getId(), $ruleIdsArr)){
            $quote     = $address->getQuote();
            $isValid   = false;
            $cnt = count($quote->getAllItems());
            foreach ($quote->getAllItems() as $item){
                $sku = strtolower($item->getSku());
                if(in_array($sku , $skuArr)){
                    $isValid = true;
                    break;
                }
            }
            
            if( ($cnt > 1 && $isValid) || (!$isValid)){
                return true;
            }
            
        }
        return false;
    }
    
}
