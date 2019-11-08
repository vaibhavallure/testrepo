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
        
        $routeName = Mage::app()->getRequest()->getRouteName();
        $allowedCarrierArray = array("matrixrate");
        if($routeName == "adminhtml"){
            $allowedCarrierArray = array("flatrate", "freeshipping", "tablerate", "dhl", "fedex", "ups", "usps", "dhlint");
        }
        
        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $storeId);
            
            foreach ($carriers as $carrierCode => $carrierConfig) {
                if(in_array($carrierCode, $allowedCarrierArray)){
                    $this->collectCarrierRates($carrierCode, $request);
                }
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            foreach ($limitCarrier as $carrierCode) {
                if(in_array($carrierCode, $allowedCarrierArray)){
                    $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                    if (!$carrierConfig) {
                        continue;
                    }
                    $this->collectCarrierRates($carrierCode, $request);
                }
            }
        }
        
        return $this;
    }
}

