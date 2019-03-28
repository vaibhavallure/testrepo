<?php

class Allure_Catalog_Model_Adminhtml_Observer
{

    /**
     *
     * @return Allure_Catalog_Helper_Data
     */
    protected function getHelper ()
    {
        return Mage::helper('allure_catalog');
    }

    /**
     * Add grid column
     *
     * @param Varien_Event_Observer $observer
     *
     * @return self
     */
    public function addGridColumn (Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (! $block) {
            return $this;
        }
        // $request = Mage::app()->getRequest();
        // $store = $request->getParam('store',0);
        // if($store==0){
        $adminhtmlHelper = $this->getHelper()->getAdminhtmlHelper();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid) {
            $adminhtmlHelper->addQtyProductGridColumn($block);
            $adminhtmlHelper->addBatchPriceProductGridColumn($block);
        }
        // }
        return $this;
    }

    /**
     * Prepare grid
     *
     * @param Varien_Event_Observer $observer
     *
     * @return self
     */
    public function prepareGrid (Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (! $block) {
            return $this;
        }
        
        $adminhtmlHelper = $this->getHelper()->getAdminhtmlHelper();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid) {
            $request = Mage::app()->getRequest();
            $params = $request->getParams();
            $store = $params['store'];
            if (empty($store)) {
                $store = 0;
            }
            // Mage::log($store,Zend_Log::DEBUG,'abc',true);
            $adminhtmlHelper->prepareProductGrid($block, $store);
        }
        return $this;
    }
}
