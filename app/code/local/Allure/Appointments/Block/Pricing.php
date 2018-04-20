<?php   
class Allure_Appointments_Block_Pricing extends Mage_Core_Block_Template{   
    
    public function getPiercingPricesUsingStore($storeId = 0){
        if(empty($storeId)){
            $storeId = Mage::app()->getRequest()->getParam("store");
        }
        $collection = Mage::getModel("appointments/pricing")->getCollection()
            ->addFieldToFilter('store_id',$storeId);
        return $collection;
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('appointments/pricing.phtml');
        $storeId = Mage::app()->getRequest()->getParam("store");
        $collection    = $this->getPiercingPricesUsingStore($storeId);
        $this->setPricingCollection($collection);
        
        $helper = Mage::helper("appointments/storemapping");
        $configData = $helper->getStoreMappingConfiguration();
        $storeKey = array_search ($storeId, $configData['stores']);
        $storeMap = $configData['store_map'][$storeKey];
        $this->setStoreMap($storeMap);
        $blockIdentifier = $configData['piercing_pricing_block'][$storeKey];
        $this->setCmsBlockId($blockIdentifier);
    }

}