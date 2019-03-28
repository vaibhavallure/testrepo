<?php

class Allure_Managestock_Model_CatalogInventory_Observer extends Mage_CatalogInventory_Model_Observer
{

    /**
     * Revert quote items inventory data (cover not success order place case)
     * 
     * @param
     *            $observer
     */
    public function revertQuoteInventory ($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $items = $this->_getProductsQty($quote->getAllItems());
        
        $manageStockHelper = Mage::helper('managestock');
        if (Mage::app()->getStore()->isAdmin()) {
            /*
             * foreach ($items as $item){
             * $_SESSION[$manageStockHelper::KEY_CUSTOM_STORE_ID] =
             * $item->getStoreId();
             * break;
             * }
             */
            $_SESSION[$manageStockHelper::KEY_CUSTOM_STORE_ID] = $quote->getStoreId();
        }
        
        Mage::getSingleton('cataloginventory/stock')->revertProductsSale($items);
        
        // Clear flag, so if order placement retried again with success - it
        // will be processed
        $quote->setInventoryProcessed(false);
    }

    /**
     * Return creditmemo items qty to stock
     *
     * @param Varien_Event_Observer $observer
     */
    public function refundOrderInventory ($observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $items = array();
        
        $manageStockHelper = Mage::helper('managestock');
        
        foreach ($creditmemo->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Creditmemo_Item */
            $return = false;
            if ($item->hasBackToStock()) {
                if ($item->getBackToStock() && $item->getQty()) {
                    $return = true;
                }
            } elseif (Mage::helper('cataloginventory')->isAutoReturnEnabled()) {
                $return = true;
            }
            if ($return) {
                
                $storeId = $item->getOrderItem()->getStoreId();
                $_SESSION[$manageStockHelper::KEY_CUSTOM_STORE_ID] = $storeId;
                
                $parentOrderId = $item->getOrderItem()->getParentItemId();
                /* @var $parentItem Mage_Sales_Model_Order_Creditmemo_Item */
                $parentItem = $parentOrderId ? $creditmemo->getItemByOrderId(
                        $parentOrderId) : false;
                $qty = $parentItem ? ($parentItem->getQty() * $item->getQty()) : $item->getQty();
                if (isset($items[$item->getProductId()])) {
                    $items[$item->getProductId()]['qty'] += $qty;
                } else {
                    $items[$item->getProductId()] = array(
                            'qty' => $qty,
                            'item' => null
                    );
                }
            }
        }
        Mage::getSingleton('cataloginventory/stock')->revertProductsSale($items);
    }

    /**
     * Cancel order item
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_CatalogInventory_Model_Observer
     */
    public function cancelOrderItem ($observer)
    {
        $manageStockHelper = Mage::helper('managestock');
        $item = $observer->getEvent()->getItem();
        $storeId = $item->getStoreId();
        $_SESSION[$manageStockHelper::KEY_CUSTOM_STORE_ID] = $storeId;
        
        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() -
                 max($item->getQtyShipped(), $item->getQtyInvoiced()) -
                 $item->getQtyCanceled();
        
        $productId = $item->getProductId();
        if ($item->getId() && $productId && empty($children) && $qty) {
            Mage::getSingleton('cataloginventory/stock')->backItemQty(
                    $productId, $qty);
        }
        
        return $this;
    }

    public function reindexQuoteInventory ($observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $productIds = array();
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }
        
        if (count($productIds)) {
            Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts(
                    $productIds);
        }
        
        // Reindex previously remembered items
        $productIds = array();
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        // Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
        
        $this->_itemsForReindex = array(); // Clear list of remembered items -
                                           // we don't need it anymore
        
        return $this;
    }

    public function saveInventoryData ($observer)
    {
        $product = $observer->getEvent()->getProduct();
        
        if (is_null($product->getStockData())) {
            if ($product->getIsChangedWebsites() ||
                     $product->dataHasChangedFor('status')) {
                Mage::getSingleton('cataloginventory/stock_status')->updateStatus(
                        $product->getId());
            }
            return $this;
        }
        
        $item = $product->getStockItem();
        if (! $item) {
            $item = Mage::getModel('cataloginventory/stock_item');
        }
        $this->_prepareItemForSave($item, $product);
        $item->save();
        return $this;
    }

    /**
     * Prepare stock item data for save
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_CatalogInventory_Model_Observer
     */
    protected function _prepareItemForSave ($item, $product)
    {
        $manageStockHelper = Mage::helper('managestock');
        // set current store into session variable.
        // $manageStockHelper->setStoreId($product->getStoreId());
        // Mage::log(Mage::helper('managestock')->getStockId(),Zend_Log::DEBUG,'abc',true);
        $item->addData($product->getStockData())
            ->setProduct($product)
            ->setProductId($product->getId())
            ->setStockId($item->getStockId());
        
        $storeId = $product->getStoreId();
        if (isset($storeId) && ! empty($storeId)) {
            $websiteId = $manageStockHelper->getWebsiteIdByStoreId($storeId);
            $item->setWebsiteId($websiteId);
        }
        
        // Mage::log("websiteId - ".$websiteId,Zend_Log::DEBUG,'abc',true);
        
        if (! is_null($product->getData('stock_data/min_qty')) &&
                 is_null($product->getData('stock_data/use_config_min_qty'))) {
            $item->setData('use_config_min_qty', false);
        }
        if (! is_null($product->getData('stock_data/min_sale_qty')) &&
                 is_null(
                        $product->getData('stock_data/use_config_min_sale_qty'))) {
            $item->setData('use_config_min_sale_qty', false);
        }
        if (! is_null($product->getData('stock_data/max_sale_qty')) &&
                 is_null(
                        $product->getData('stock_data/use_config_max_sale_qty'))) {
            $item->setData('use_config_max_sale_qty', false);
        }
        if (! is_null($product->getData('stock_data/backorders')) &&
                 is_null($product->getData('stock_data/use_config_backorders'))) {
            $item->setData('use_config_backorders', false);
        }
        if (! is_null($product->getData('stock_data/notify_stock_qty')) &&
                 is_null(
                        $product->getData(
                                'stock_data/use_config_notify_stock_qty'))) {
            $item->setData('use_config_notify_stock_qty', false);
        }
        $originalQty = $product->getData('stock_data/original_inventory_qty');
        if (strlen($originalQty) > 0) {
            $item->setQtyCorrection($item->getQty() - $originalQty);
        }
        if (! is_null($product->getData('stock_data/enable_qty_increments')) &&
                 is_null(
                        $product->getData(
                                'stock_data/use_config_enable_qty_inc'))) {
            $item->setData('use_config_enable_qty_inc', false);
        }
        if (! is_null($product->getData('stock_data/qty_increments')) &&
                 is_null(
                        $product->getData(
                                'stock_data/use_config_qty_increments'))) {
            $item->setData('use_config_qty_increments', false);
        }
        return $this;
    }
}
