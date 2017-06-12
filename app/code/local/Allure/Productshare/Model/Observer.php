<?php

class Allure_Productshare_Model_Observer
{

    /*
     * Run product script for different website.
     */
    static $_lock_status = 0;

    const RELEASE = 0;

    const PENDING = 1;

    const PROCESSING = 2;

    const COMPLETE = 3;

    private function getHelper ()
    {
        return Mage::helper('productshare');
    }

    private function getProductCollection ()
    {
        return Mage::getModel('catalog/product')->getCollection()->setStoreId(1);
    }

    private function acquireLock ()
    {
        $status = false;
        if (self::$_lock_status == self::RELEASE)
            $status = true;
        return $status;
    }

    private function getStatusCode ($status)
    {
        $helper = $this->getHelper();
        $status_code = $helper::NONE_CODE;
        switch ($status) {
            case 0:
                $status_code = $helper::NONE_CODE;
                break;
            case 1:
                $status_code = $helper::PENDING_CODE;
                break;
            case 2:
                $status_code = $helper::PROCESSING_CODE;
                break;
            case 3:
                $status_code = $helper::COMPLETE_CODE;
                break;
            default:
                $status_code = $helper::NONE_CODE;
        }
        return $status_code;
    }

    private function updateProductshareStatue ($productShareObj, $status)
    {
        if ($productShareObj->getPsId() != 0) {
            $productShareObj = Mage::getModel('productshare/productshare')->load($productShareObj->getPsId());
            $statusCode = $this->getStatusCode($status);
            $helper = $this->getHelper();
            if ($status == $helper::COMPLETE) {
                $productShareObj->setLastUpdatedProduct(0);
            }
            $productShareObj->setStatus($status);
            $productShareObj->setStatusCode($statusCode);
            $productShareObj->save();
            self::$_lock_status = $status;
        }
    }

    private function getProductShareStore ()
    {
        $_collection = Mage::getModel('productshare/productshare')->getCollection();
        $_collection->addFieldToFilter('status', array(
                'in' => array(
                        1,
                        2
                )
        ));
        $_collection->getFirstItem();
        return $_collection;
    }

    private function getProductShareStoreRun ()
    {
        $_collection = Mage::getModel('productshare/productshare')->getCollection();
        $_collection->getFirstItem();
        return $_collection;
    }

    private function shareProductToStore ($product, $newWebsiteId, $newStoreId)
    {
        try {
            // print_r($product->getId()."<br>");
            // $product =
            // Mage::getModel('catalog/product')->load($product->getId());
            $websiteIds = $product->getWebsiteIds();
            $storeIds = $product->getStoreIds();
            // var_dump(get_class($product->getData()));die;
            if (! in_array($newWebsiteId, $websiteIds)) {
                array_push($websiteIds, $newWebsiteId);
                array_unique($websiteIds);
                $product->setWebsiteIds($websiteIds);
                $product->save();
                Mage::log("product id - " . $product->getId() . " website id add", Zend_Log::DEBUG, 'abc', 
                        true);
            }
            
            // copy the details of the products to new store
            if (! in_array($newStoreId, $storeIds)) {
                array_push($storeIds, $newStoreId);
                array_unique($storeIds);
                $newProduct = Mage::getModel('catalog/product')->load($product->getId())
                    ->setStoreIds($storeIds)
                    -> // new store ids with old ids
save();
                Mage::log("product id - " . $newProduct->getId() . " store id add", Zend_Log::DEBUG, 'abc', 
                        true);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'abc', true);
            // print_r($e->getMessage());
        }
    }

    private function updateProductPrice ($product, $newStoreId, $priceRule = 1)
    {
        try {
            // Mage::log($priceRule,Zend_Log::DEBUG,'abc',true);
            // if($product->getTypeId() != "configurable"){
            $product = $product->setStoreId($newStoreId)
                ->setPrice($product->getPrice() * $priceRule)
                ->save();
            Mage::log("product id - " . $product->getId() . " price updated", Zend_Log::DEBUG, 'abc', true);
            // }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'abc', true);
            // print_r($e->getMessage());
        }
    }

    private function copyProductInventory ($product, $newStockId, $newWebsiteId)
    {
        try {
            // check product stock of website present or not.
            $stockItem = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                    $product, $newStockId);
            if (! $stockItem)
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                
                // Mage::log($stockItem->getId(),Zend_Log::DEBUG,'abc',true);
                // $stockItem->assignProductToNewStockByScript($product ,
            // $newStockId);
            $item = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript($product, 
                    1);
            $data = $item->getData();
            
            if (array_key_exists('item_id', $data)) {
                unset($data['item_id']);
            }
            
            $data[stock_id] = $newStockId;
            $data[website_id] = $newWebsiteId;
            $stockItem->addData($data);
            $stockItem->save();
            
            Mage::log("Stock item id - " . $stockItem->getItemId() . "  updated", Zend_Log::DEBUG, 'abc', 
                    true);
            
            // var_dump($data);
            
            /*
             * $stockItem->setData('is_in_stock', 1);
             * $stockItem->setData('stock_id', $newStockId);
             * $stockItem->setData('store_id', $newStoreId);
             * $stockItem->setData('manage_stock', 1);
             * $stockItem->setData('use_config_manage_stock', 0);
             * $stockItem->setData('min_sale_qty', 1);
             * $stockItem->setData('use_config_min_sale_qty', 0);
             * $stockItem->setData('max_sale_qty', 1000);
             * $stockItem->setData('use_config_max_sale_qty', 0);
             * $stockItem->setData('qty', $item->getQty());
             * $stockItem->save();
             */
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'abc', true);
            // print_r($e->getMessage());
        }
    }

    public function shareProductsToWebsite ()
    {
        // Mage::log('In cron',Zend_Log::DEBUG,'abc',true);die;
        $helper = $this->getHelper();
        Mage::log(date('Y-m-d H:m:s'), Zend_Log::DEBUG, 'abc', true);
        Mage::log('lock acquire status -' . self::$_lock_status, Zend_Log::DEBUG, 'abc', true);
        if ($this->acquireLock()) {
            $_share_product_store = $this->getProductShareStore();
            $shareProductObj = Mage::getModel('productshare/productshare');
            try {
                foreach ($_share_product_store as $shareProductItem) {
                    $shareProductObj = $shareProductObj->load($shareProductItem->getPsId());
                }
                if ($shareProductObj->getPsId() != 0) {
                    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                    $websiteCode = $shareProductObj->getWebsiteCode();
                    $_productCollection = $this->getProductCollection();
                    $newWebsiteId = $shareProductObj->getWebsiteId();
                    $webisteModel = Mage::getModel('core/website')->load($newWebsiteId);
                    $newStockId = $webisteModel->getStockId();
                    $newStoreId = $shareProductObj->getStoreId();
                    $priceRule = $webisteModel->getWebsitePriceRule();
                    $this->updateProductshareStatue($shareProductObj, $helper::PROCESSING);
                    foreach ($_productCollection as $_product) {
                        $_product = Mage::getModel('catalog/product')->load($_product->getId()); // 10095
                        $this->shareProductToStore($_product, $newWebsiteId, $newStoreId);
                        $this->updateProductPrice($_product, $newStoreId, $priceRule);
                        $this->copyProductInventory($_product, $newStockId, $newWebsiteId);
                    }
                    $this->updateProductshareStatue($shareProductObj, $helper::COMPLETE);
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::DEBUG, 'abc', true);
            }
        }
        self::$_lock_status = self::RELEASE;
        Mage::log('lock release status -' . self::$_lock_status, Zend_Log::DEBUG, 'abc', true);
    }

    public function shareAvailableProductsToStoreRun ()
    {
        $helper = $this->getHelper();
        Mage::log(date('Y-m-d H:m:s'), Zend_Log::DEBUG, 'abc', true);
        $_share_product_store = $this->getProductShareStoreRun();
        $shareProductObj = Mage::getModel('productshare/productshare');
        try {
            foreach ($_share_product_store as $shareProductItem) {
                $shareProductObj = $shareProductObj->load($shareProductItem->getPsId());
            }
            if ($shareProductObj->getPsId() != 0) {
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $_productCollection = $this->getProductCollection();
                Mage::log("product count - " . count($_productCollection), Zend_Log::DEBUG, 'abc', true);
                $newWebsiteId = $shareProductObj->getWebsiteId();
                $webisteModel = Mage::getModel('core/website')->load($newWebsiteId);
                $newStockId = $webisteModel->getStockId();
                $newStoreId = $shareProductObj->getStoreId();
                $priceRule = $webisteModel->getWebsitePriceRule();
                Mage::log("price rule - " . $priceRule, Zend_Log::DEBUG, 'abc', true);
                // $this->updateProductshareStatue($shareProductObj,$helper::PROCESSING);
                foreach ($_productCollection as $_product) {
                    $_product = Mage::getModel('catalog/product')->load($_product->getId()); // 10095
                    $this->shareProductToStore($_product, $newWebsiteId, $newStoreId);
                    $this->updateProductPrice($_product, $newStoreId, $priceRule);
                    $this->copyProductInventory($_product, $newStockId, $newWebsiteId);
                }
                // $this->updateProductshareStatue($shareProductObj,$helper::COMPLETE);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'abc', true);
        }
    }

    public function shareAvailableProductsToStoreAdditional ($_productCollection, $storeId, $newWebsiteId)
    {
        // Mage::log('In cron',Zend_Log::DEBUG,'abc',true);die;
        Mage::log(date('Y-m-d H:m:s'), Zend_Log::DEBUG, 'abc', true);
        Mage::log("additinal script start", Zend_Log::DEBUG, 'abc', true);
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $webisteModel = Mage::getModel('core/website')->load($newWebsiteId);
            $newStockId = $webisteModel->getStockId();
            $newStoreId = $storeId;
            $priceRule = $webisteModel->getWebsitePriceRule();
            foreach ($_productCollection as $product) {
                $_product = Mage::getModel('catalog/product')->load($product); // 10095
                $this->shareProductToStore($_product, $newWebsiteId, $newStoreId);
                $this->updateProductPrice($_product, $newStoreId, $priceRule);
                $this->copyProductInventory($_product, $newStockId, $newWebsiteId);
            }
            Mage::log("additinal script end", Zend_Log::DEBUG, 'abc', true);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'abc', true);
        }
    }

    public function shareAvailableProductsToStore ()
    {
        Mage::log("Product Share Time  - " . date('Y-m-d h:i:s'), Zend_Log::DEBUG, 'product_share', true);
        $helper = $this->getHelper();
        $resource     = Mage::getSingleton('core/resource');
        $writeAdapter   = $resource->getConnection('core_write');
        try {
            $_share_product_store = $this->getProductShareStore();
            $shareProductObj = Mage::getModel('productshare/productshare');
            foreach ($_share_product_store as $shareProductItem) {
                $shareProductObj = $shareProductObj->load($shareProductItem->getPsId());
            }
            if ($shareProductObj->getPsId() != 0) {
                $newWebsiteId = $shareProductObj->getWebsiteId();
                $webisteModel = Mage::getModel('core/website')->load($newWebsiteId);
                $newStockId = $webisteModel->getStockId();
                $newStoreId = $shareProductObj->getStoreId();
                $priceRule = $webisteModel->getWebsitePriceRule();
                Mage::log("Website Price Rule - " . $priceRule, Zend_Log::DEBUG, 'product_share', true);
                
                if ($shareProductObj->getStatus() == $helper::PENDING) {
                    $websiteIds = array(
                            $newWebsiteId
                    );
                    $productIds = Mage::getResourceModel('catalog/product_collection')->getAllIds();
                    Mage::getModel('catalog/product_website')->addProducts($websiteIds, $productIds);
                    
                    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                    // $this->updateProductshareStatue($shareProductObj,$helper::PROCESSING);
                    $shareProductObj->setStatus($helper::PROCESSING);
                    $statusCode = $this->getStatusCode($helper::PROCESSING);
                    $shareProductObj->setStatusCode($statusCode)->save();
                    
                    $writeAdapter->beginTransaction();
                    $recordIndex = 1;
                    foreach ($productIds as $_product) {
                        if ($shareProductObj->getExecution()) {
                            $product = Mage::getModel('catalog/product')->load($_product);
                            if (! empty($newStoreId)) {
                                $product->setStoreId($newStoreId)
                                    ->setPrice($product->getPrice() * $priceRule)
                                    ->save();
                                Mage::log(
                                        "Product Id - " . $product->getId() . " : Website Id : " .
                                                 $newWebsiteId . " Price Updated.", Zend_Log::DEBUG, 
                                                'product_share', true);
                            }
                            $stockItem = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                                    $product, $newStockId);
                            
                            $inventory_msg = "Inventory Already exist.";
                            if (is_null($stockItem->getItemId()) || $stockItem->getItemId() == 0) {
                                $inventory_msg = "New Inventory Created.";
                                $stockItem = Mage::getModel('cataloginventory/stock_item');
                                
                                $item = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                                        $product, 1);
                                $data = $item->getData();
                                
                                if (array_key_exists('item_id', $data)) {
                                    unset($data['item_id']);
                                }
                                
                                $data['stock_id'] = $newStockId;
                                $data['website_id'] = $newWebsiteId;
                                $data['qty'] = 0;
                                $stockItem->addData($data);
                                $stockItem->save();
                            }
                            
                            Mage::log("Stock Item Id - " . $stockItem->getItemId() . " : " . $inventory_msg, 
                                    Zend_Log::DEBUG, 'product_share', true);
                            $shareProductObj->setLastUpdatedProduct($_product)->save();
                            
                            if (($recordIndex % 500) == 0) {
                            	$writeAdapter->commit();
                            	$writeAdapter->beginTransaction();
                            }
                            $recordIndex += 1;
                        }
                    }
                    // $this->updateProductshareStatue($shareProductObj,$helper::COMPLETE);
                    
                    $shareProductObj->setStatus($helper::COMPLETE);
                    $statusCode = $this->getStatusCode($helper::COMPLETE);
                    // $shareProductObj->setLastUpdatedProduct(0);
                    $shareProductObj->setStatusCode($statusCode)->save();
                    $writeAdapter->commit();
                    Mage::log("share product status completed. ", Zend_Log::DEBUG, 'product_share', true);
                } else {
                    if ($shareProductObj->getStatus() == $helper::PROCESSING) {
                        $lastUpdatedProduct = $shareProductObj->getLastUpdatedProduct();
                        $lastProduct = $shareProductObj->getLastProduct();
                        
                        if ($lastUpdatedProduct != 0) {
                            $collection = Mage::getModel('catalog/product')->getCollection()->setStoreId(1);
                            $collection->addAttributeToFilter('entity_id', 
                                    array(
                                            'gteq' => $lastUpdatedProduct
                                    ));
                            
                            if ($lastProduct != 0 && $lastUpdatedProduct <= $lastProduct) {
                                $collection->addAttributeToFilter('entity_id', array(
                                        'lteq' => $lastProduct
                                ));
                            }
                            
                            $productIds = $collection->getAllIds();
                            
                            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                            $shareProductObj->setStatus($helper::PROCESSING);
                            $statusCode = $this->getStatusCode($helper::PROCESSING);
                            $shareProductObj->setStatusCode($statusCode)->save();
                            
                            $writeAdapter->beginTransaction();
                            $recordIndex = 1;
                            foreach ($productIds as $_product) {
                                if ($shareProductObj->getExecution()) {
                                    $product = Mage::getModel('catalog/product')->load($_product);
                                    if (! empty($newStoreId)) {
                                        $product->setStoreId($newStoreId)
                                            ->setPrice($product->getPrice() * $priceRule)
                                            ->save();
                                        Mage::log(
                                                "Product Id - " . $product->getId() . " : Website Id : " .
                                                         $newWebsiteId . " Price Updated.", Zend_Log::DEBUG, 
                                                        'product_share', true);
                                    }
                                    $stockItem = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                                            $product, $newStockId);
                                    
                                    $inventory_msg = "Inventory Already exist.";
                                    if (is_null($stockItem->getItemId()) || $stockItem->getItemId() == 0) {
                                        $inventory_msg = "New Inventory Created.";
                                        $stockItem = Mage::getModel('cataloginventory/stock_item');
                                        
                                        $item = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                                                $product, 1);
                                        $data = $item->getData();
                                        
                                        if (array_key_exists('item_id', $data)) {
                                            unset($data['item_id']);
                                        }
                                        
                                        $data['stock_id'] = $newStockId;
                                        $data['website_id'] = $newWebsiteId;
                                        $data['qty'] = 0;
                                        $stockItem->addData($data);
                                        $stockItem->save();
                                    }
                                    
                                    Mage::log("Stock Item Id - " . $stockItem->getItemId() . " : " .
                                             $inventory_msg, Zend_Log::DEBUG, 'product_share', true);
                                    $shareProductObj->setLastUpdatedProduct($_product)->save();
                                    
                                    if (($recordIndex % 500) == 0) {
                                    	$writeAdapter->commit();
                                    	$writeAdapter->beginTransaction();
                                    }
                                    $recordIndex += 1;
                                }
                            }
                            // $this->updateProductshareStatue($shareProductObj,$helper::COMPLETE);
                            
                            $shareProductObj->setStatus($helper::COMPLETE);
                            $statusCode = $this->getStatusCode($helper::COMPLETE);
                            // $shareProductObj->setLastUpdatedProduct(0);
                            $shareProductObj->setStatusCode($statusCode)->save();
                            
                            $writeAdapter->commit();
                            Mage::log("share product status completed. ", Zend_Log::DEBUG, 'product_share', 
                                    true);
                        }
                    }
                }
            }
        } catch (Exception $e) {
        	$writeAdapter->rollback();
            Mage::log("Exception Msg - " . $e->getMessage(), Zend_Log::DEBUG, 'product_share', true);
        }
    }

    public function updatePriceByStoreProduct ($store, $startProduct, $lastProduct)
    {
        try {
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToFilter('entity_id', array(
                    'gteq' => $startProduct
            ));
            $collection->addAttributeToFilter('entity_id', array(
                    'lteq' => $lastProduct
            ));
            $productIds = $collection->getAllIds();
            
            $websiteId = Mage::getModel('core/store')->load($store)->getWebsiteId();
            if (isset($websiteId) && ! empty($websiteId)) {
                $websiteModel = Mage::getModel('core/website')->load($websiteId);
                $priceRule = $websiteModel->getWebsitePriceRule();
                Mage::log("Website Id : " . $websiteId, Zend_Log::DEBUG, 'product_share', true);
                Mage::log("Website Price Rule : " . $priceRule, Zend_Log::DEBUG, 'product_share', true);
                
                foreach ($productIds as $_product) {
                    $product = Mage::getModel('catalog/product')->load($_product);
                    if (! empty($store)) {
                        $product->setStoreId($store)
                            ->setPrice($product->getPrice() * $priceRule)
                            ->save();
                        Mage::log(
                                "Product Id - " . $product->getId() . " : Website Id : " . $websiteId .
                                         " Price Updated.", Zend_Log::DEBUG, 'product_share', true);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log("Exception Msg - " . $e->getMessage(), Zend_Log::DEBUG, 'product_share', true);
        }
    }
}
