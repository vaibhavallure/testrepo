<?php

class Allure_Managestock_Model_CatalogInventory_Stock_Status extends Mage_CatalogInventory_Model_Stock_Status
{

    /**
     * Assign Stock Status to Product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $stockId
     * @param int $stockStatus
     * @return Mage_CatalogInventory_Model_Stock_Status
     */
    public function assignProduct (Mage_Catalog_Model_Product $product,
            $stockId = 1, $stockStatus = null)
    {
        $manageStockHelper = Mage::helper('managestock');
        if (is_null($stockStatus)) {
            $websiteId = $manageStockHelper->getWebsiteId();
            if (isset($websiteId) && ! empty($websiteId)) {
                // nothing
            } else {
                $websiteId = $product->getStore()->getWebsiteId();
            }
            $stockId = $manageStockHelper->getStockIdByWebsiteId($websiteId);
            // Mage::log($websiteId,Zend_Log::DEBUG,'abc',true);
            $status = $this->getProductStatus($product->getId(), $websiteId,
                    $stockId);
            $stockStatus = isset($status[$product->getId()]) ? $status[$product->getId()] : null;
        }
        
        $product->setIsSalable($stockStatus);
        
        return $this;
    }
}
