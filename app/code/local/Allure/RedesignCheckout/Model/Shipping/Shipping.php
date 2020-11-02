<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $storeId = $request->getStoreId();
        if (!$request->getOrig()) {
            $request
            ->setCountryId(Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $request->getStore()))
            ->setRegionId(Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $request->getStore()))
            ->setCity(Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $request->getStore()))
            ->setPostcode(Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $request->getStore()));
        }
        
        
        //matrixrate related code
        $isAllowOtherShippingForFrontend = true;
        $isAllowOtherShippingForBackend = true;
        $allowedCarrierArray = array("matrixrate");
        $routeName = Mage::app()->getRequest()->getRouteName();
        /* if (Mage::helper('core')->isModuleEnabled("Allure_Matrixrate")) {
            if(Mage::getStoreConfig('carriers/matrixrate/active', $storeId)){
                $matrixRateHelper = Mage::helper("matrixrate");
                $isShowMatrixRate = $matrixRateHelper->isShowMatrixRate();
                if($isShowMatrixRate){
                    $isAllowOtherShippingForFrontend = $matrixRateHelper->isAllowDefaultShippingMethodsToFrontend();
                    $isAllowOtherShippingForBackend = $matrixRateHelper->isAllowDefaultShippingMethodsToBackend();
                }
            }
        } */
        
        $allowedShippings = array();
        if (Mage::helper('core')->isModuleEnabled("Allure_Shipping")) {
            /* @var Allure_Shipping_Helper_Data $shippingHelper */
            $shippingHelper = Mage::helper("allure_shipping");
            if($shippingHelper->isEnabled()){
                $allowedShippings = $shippingHelper->getShippingConfigMapping();
            }
        }
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        
        if($routeName == "adminhtml"){
            $customerGroupId = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getCustomerGroupId();
        }else{
            $routeName = "frontend";
        }
        
        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $storeId);
            
            /* if($routeName == "adminhtml"){
                foreach ($carriers as $carrierCode => $carrierConfig) {
                    if($isAllowOtherShippingForBackend){
                        $this->collectCarrierRates($carrierCode, $request);
                    }else if(in_array($carrierCode, $allowedCarrierArray)){
                        $this->collectCarrierRates($carrierCode, $request);
                    }
                }
            }else{ //for frontend
                foreach ($carriers as $carrierCode => $carrierConfig) {
                    if($isAllowOtherShippingForFrontend){
                        $this->collectCarrierRates($carrierCode, $request);
                    }else if(in_array($carrierCode, $allowedCarrierArray)){
                        $this->collectCarrierRates($carrierCode, $request);
                    }
                }
            } */
            
            foreach ($carriers as $carrierCode => $carrierConfig) {
                if(count($allowedShippings) > 0){
                    $allowedShippingConfig = isset($allowedShippings[$carrierCode]) ? $allowedShippings[$carrierCode] : array();
                    if(($carrierCode == $allowedShippingConfig['shipping_carrier']) &&
                        in_array($routeName, $allowedShippingConfig['used_in']) &&
                        in_array($customerGroupId, $allowedShippingConfig['customer_group'])){
                            $this->collectCarrierRates($carrierCode, $request);
                    }
                }else{
                    $this->collectCarrierRates($carrierCode, $request);
                }
            }
            
            
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            
            //changes
            /* if($routeName == "adminhtml"){
                foreach ($limitCarrier as $carrierCode) {
                    $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                    if (!$carrierConfig) {
                        continue;
                    }
                    
                    if($isAllowOtherShippingForBackend){
                        $this->collectCarrierRates($carrierCode, $request);
                    }else if(in_array($carrierCode, $allowedCarrierArray)){
                        $this->collectCarrierRates($carrierCode, $request);
                    }
                }
            }else{ //for frontend
                foreach ($limitCarrier as $carrierCode) {
                    $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                    if (!$carrierConfig) {
                        continue;
                    }
                    
                    if($isAllowOtherShippingForFrontend){
                        $this->collectCarrierRates($carrierCode, $request);
                    }else if(in_array($carrierCode, $allowedCarrierArray)){
                        $this->collectCarrierRates($carrierCode, $request);
                    }
                    $this->collectCarrierRates($carrierCode, $request);
                }
                
            } */
            
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                if (!$carrierConfig) {
                    continue;
                }
                
                if(count($allowedShippings) > 0){
                    $allowedShippingConfig = isset($allowedShippings[$carrierCode]) ? $allowedShippings[$carrierCode] : array();
                    if(($carrierCode == $allowedShippingConfig['shipping_carrier']) &&
                        in_array($routeName, $allowedShippingConfig['used_in']) &&
                        in_array($customerGroupId, $allowedShippingConfig['customer_group'])){
                            $this->collectCarrierRates($carrierCode, $request);
                    }
                }else{
                    $this->collectCarrierRates($carrierCode, $request);
                }
                
            }
            
        }
        
        return $this;
    }
}

