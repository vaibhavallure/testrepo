<?php

class Allure_Inventory_Adminhtml_Inventory_StockController extends Allure_Inventory_Controller_Action
{

    protected function _initAction ()
    {
        $this->loadLayout()
            ->_setActiveMenu($this->_menu_path)
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Stock'),
                Mage::helper('adminhtml')->__('Manage Stock'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction ()
    {
        $this->_initAction();
        $this->_title($this->__('Inventory Receiving'));
        $this->renderLayout();
    }

    /**
     * save item action
     */
    public function saveAction ()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        // Mage::log("coming here",Zend_log::DEBUG,"demo",true);
        $data = $this->getRequest()->getPost();
        
        $websiteId = 1;
        if (Mage::getSingleton('core/session')->getMyWebsiteId())
            $websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
        $website = Mage::getModel("core/website")->load($websiteId);
        $storeId = $website->getStoreId();
        $stockId = $website->getStockId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $resource = Mage::getSingleton('core/resource');
        
        $writeAdapter = $resource->getConnection('core_write');
        $writeAdapter->beginTransaction();
        
        try {
            foreach ($data['qty'] as $product => $key) {
                $arr = array_filter($data['qty'][$product]);
                if (! empty($arr) && $arr[0]!=0) {
                    $updateStock = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock(
                            $product, $stockId);
                    if (! is_null($updateStock->getItemId()) &&
                             ($updateStock->getItemId() != 0)) {
                        $previousQty = $updateStock->getQty();
                        $newQty = $updateStock->getQty() + $arr[0];
                        $resource = Mage::getSingleton('core/resource');
                        $writeAdapter = $resource->getConnection('core_write');
                        $table = $resource->getTableName(
                                'cataloginventory/stock_item');
                        $query = "update {$table} set  qty = '{$newQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
                        $writeAdapter->query($query);
                        $inventory = Mage::getModel('inventory/inventory');
                        $inventory->setProductId($product);
                        $inventory->setUserId($admin->getUserId());
                        $inventory->setPreviousQty($previousQty);
                        $inventory->setCost($arr['cost']);
                        $inventory->setAddedQty($arr[0]);
                        $inventory->setUpdatedAt(date("Y-m-d H:i:s"));
                        $inventory->setStockId($stockId);
                        $inventory->save();
                    } else {
                        $this->assignWebsitesToProduct($product);
                        $updateStock = Mage::getModel(
                                'cataloginventory/stock_item')->loadByProductAndStock(
                                $product, $stockId);
                        if (! is_null($updateStock->getItemId()) &&
                                 ($updateStock->getItemId() != 0)) {
                            $previousQty = $updateStock->getQty();
                            $newQty = $updateStock->getQty() + $arr[0];
                            $resource = Mage::getSingleton('core/resource');
                            $writeAdapter = $resource->getConnection(
                                    'core_write');
                            $table = $resource->getTableName(
                                    'cataloginventory/stock_item');
                            $query = "update {$table} set  qty = '{$newQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
                            $writeAdapter->query($query);
                            $inventory = Mage::getModel('inventory/inventory');
                            $inventory->setProductId($product);
                            $inventory->setUserId($admin->getUserId());
                            $inventory->setPreviousQty($previousQty);
                            $inventory->setCost($arr['cost']);
                            $inventory->setAddedQty($arr[0]);
                            $inventory->setUpdatedAt(date("Y-m-d H:i:s"));
                            $inventory->setStockId($stockId);
                            $inventory->save();
                        }
                    }
                    if ($arr['cost']) {
                        $product = Mage::getModel('catalog/product')->load(
                                $product);
                        if ($arr['cost'])
                            $product->setStoreId($stockId)->setCost(
                                    $arr['cost']);
                        $product->save();
                    }
                }
            }
            
            $writeAdapter->commit();
        } catch (Exception $e) {
            $writeAdapter->rollback();
        }
        
        Mage::getSingleton('adminhtml/session')->addSuccess("stock updated");
        $this->_redirectReferer();
    }

    public function assignWebsitesToProduct ($_product)
    {
        $logFileName = "product_website_assign.log";
        $debugStatus = true;
        
        $websiteIds = array();
        $stockIds = array();
        
        foreach (Mage::app()->getWebsites() as $website) {
            $websiteIds[] = $website->getId();
            $stockIds[] = $website->getStockId();
        }
        $product = Mage::getModel('catalog/product')->load($_product); // Loading
                                                                       // product
                                                                       // for
                                                                       // Admin
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->joinAttribute('price', 'catalog_product/price', 'entity_id',
                null, 'left', 0);
        $collection->addAttributeToFilter('entity_id',
                array(
                        'in' => array(
                                $_product
                        )
                ));
        $stock = $collection->getFirstItem();
        $websiteIds = $product->getWebsiteIds();
        foreach ($websiteIds as $websiteId) {
            $website = Mage::getModel('core/website')->load($websiteId);
            $storeIds = $website->getStoreIds();
            foreach ($storeIds as $storeId) {
                $product1 = Mage::getModel('catalog/product')->setStoreId(
                        $storeId)->load($_product);
                ;
                if ($product1->getDescription() == $product->getDescription()) {
                    Mage::getSingleton('catalog/product_action')->updateAttributes(
                            array(
                                    $_product
                            ),
                            array(
                                    'description' => $product->getDescription()
                            ), $storeId);
                    Mage::log(
                            "Product Description set: Product Id - " . $_product .
                                     " Store Id - " . $storeId, Zend_Log::DEBUG,
                                    $logFileName, $debugStatus);
                }
                try {
                    $product2 = Mage::getModel('catalog/product')->setStoreId(
                            $storeId)->load($_product);
                    $priceRule = $website->getWebsitePriceRule();
                    Mage::log('priceRule::' . $priceRule, Zend_Log::DEBUG,
                            $logFileName, true);
                    $oldPrice = $stock['price'];
                    $newPrice = $oldPrice * $priceRule;
                    Mage::log('newPrice::' . $newPrice, Zend_Log::DEBUG,
                            $logFileName, true);
                    $product2->setPrice($newPrice)->save();
                } catch (Exception $e) {
                    Mage::log(
                            'Exception Occured to set price::' . $e->getMessage(),
                            Zend_Log::DEBUG, $logFileName, true);
                }
                
                // Mage::getModel('catalog/product_status')->updateProductStatus($_product,
            // $storeId, $productStatus);
            } // End of Store
        }
        foreach ($stockIds as $stockId) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                    $product, $stockId);
            
            $inventory_msg = "Inventory Already exist.";
            if (is_null($stockItem->getItemId()) || $stockItem->getItemId() == 0) {
                $inventory_msg = "New Inventory Created.";
                
                $item = Mage::getModel('cataloginventory/stock_item')->assignProductToNewStockByScript(
                        $product, 1);
                
                if (! is_null($item->getItemId())) {
                    $data = $item->getData();
                    if (array_key_exists('item_id', $data)) {
                        unset($data['item_id']);
                    }
                    /*
                     * if(array_key_exists('qty',$data)){
                     * unset($data['qty']);
                     * }
                     */
                    $data[stock_id] = $stockId;
                    $data['qty'] = 0;
                    
                    $data['manage_stock'] = 1;
                    $data['use_config_manage_stock'] = 0;
                    $data['min_sale_qty'] = 1;
                    $data['use_config_min_sale_qty'] = 0;
                    $data['max_sale_qty'] = 1000;
                    $data['use_config_max_sale_qty'] = 0;
                    
                    $stockItem->addData($data);
                } else {
                    $stockItem->setData('stock_id', $stockId);
                    $stockItem->setData('manage_stock', 1);
                    $stockItem->setData('use_config_manage_stock', 0);
                    $stockItem->setData('min_sale_qty', 1);
                    $stockItem->setData('use_config_min_sale_qty', 0);
                    $stockItem->setData('max_sale_qty', 1000);
                    $stockItem->setData('use_config_max_sale_qty', 0);
                }
                
                $stockItem->save();
            }
            Mage::log(
                    "Stock Item Id - " . $stockItem->getItemId() .
                             " : Stock Id - " . $stockId . " : " . $inventory_msg,
                            Zend_Log::DEBUG, $logFileName, $debugStatus);
        }
    }

    public function lowstockAction ()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function minmaxAction ()
    {
        $this->_title($this->__('Min Max'));
        
        $this->loadLayout();
        $this->renderLayout();
    }

    public function storeAction ()
    {
        $request = $this->getRequest()->getPost();
        $website = 1;
        if ($request['value'])
            $website = $request['value'];
        Mage::getSingleton('core/session')->setMyWebsiteId($website);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    public function transferAction ()
    {
        $this->loadLayout();
        $this->_title($this->__('Inventory Transfer'));
        $this->renderLayout();
    }

    public function updateTransferAction ()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $data = $this->getRequest()->getPost();
        $websiteId = 1;
        if (Mage::getSingleton('core/session')->getMyWebsiteId())
            $websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
        $website = Mage::getModel("core/website")->load($websiteId);
        $stockId = $website->getStockId();
        
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $writeAdapter->beginTransaction();
        
        try {
            foreach ($data['qty'] as $product => $key) {
                $arr = array_filter($data['qty'][$product]);
                if (! empty($arr) && $arr['qty']) {
                    if ($arr['website']) {
                        $previousStoreStock = Mage::getModel(
                                'cataloginventory/stock_item')->loadByProductAndStock(
                                $product, $stockId);
                        $newStoreStock = Mage::getModel(
                                'cataloginventory/stock_item')->loadByProductAndStock(
                                $product, $arr['website']);
                        if (! is_null($newStoreStock->getItemId()) &&
                                 ($newStoreStock->getItemId() != 0)) {
                            
                            // reduce qty from previous store
                            $previousReducedQty = $previousStoreStock->getQty() -
                             $arr['qty'];
                    $resource = Mage::getSingleton('core/resource');
                    $writeAdapter = $resource->getConnection('core_write');
                    $table = $resource->getTableName(
                            'cataloginventory/stock_item');
                    $query = "update {$table} set  qty = '{$previousReducedQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
                    $writeAdapter->query($query);
                    
                    // add qty to new store
                    $newAddedQty = $newStoreStock->getQty() + $arr['qty'];
                    $resource = Mage::getSingleton('core/resource');
                    $writeAdapter = $resource->getConnection('core_write');
                    $table = $resource->getTableName(
                            'cataloginventory/stock_item');
                    $query = "update {$table} set  qty = '{$newAddedQty}' where product_id = '{$product}' AND stock_id = '{$arr['website']}'";
                    $writeAdapter->query($query);
                    
                    $transfer = Mage::getModel('inventory/transfer');
                    $transfer->setProductId($product);
                    $transfer->setUserId($admin->getUserId());
                    $transfer->setQty($arr['qty']);
                    $transfer->setTransferFrom($stockId);
                    $transfer->setTransferTo($arr['website']);
                    $transfer->setUpdatedAt(date("Y-m-d H:i:s"));
                    $transfer->save();
                } else {
                    $this->assignWebsitesToProduct($product);
                    $previousStoreStock = Mage::getModel(
                            'cataloginventory/stock_item')->loadByProductAndStock(
                            $product, $stockId);
                    $newStoreStock = Mage::getModel(
                            'cataloginventory/stock_item')->loadByProductAndStock(
                            $product, $arr['website']);
                    if (! is_null($newStoreStock->getItemId()) &&
                             ($newStoreStock->getItemId() != 0)) {
                        
                        // reduce qty from previous store
                        $previousReducedQty = $previousStoreStock->getQty() -
                         $arr['qty'];
                $resource = Mage::getSingleton('core/resource');
                $writeAdapter = $resource->getConnection('core_write');
                $table = $resource->getTableName('cataloginventory/stock_item');
                $query = "update {$table} set  qty = '{$previousReducedQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
                $writeAdapter->query($query);
                
                // add qty to new store
                $newAddedQty = $newStoreStock->getQty() + $arr['qty'];
                $resource = Mage::getSingleton('core/resource');
                $writeAdapter = $resource->getConnection('core_write');
                $table = $resource->getTableName('cataloginventory/stock_item');
                $query = "update {$table} set  qty = '{$newAddedQty}' where product_id = '{$product}' AND stock_id = '{$arr['website']}'";
                $writeAdapter->query($query);
                
                $transfer = Mage::getModel('inventory/transfer');
                $transfer->setProductId($product);
                $transfer->setUserId($admin->getUserId());
                $transfer->setQty($arr['qty']);
                $transfer->setTransferFrom($stockId);
                $transfer->setTransferTo($arr['website']);
                $transfer->setUpdatedAt(date("Y-m-d H:i:s"));
                $transfer->save();
            }
        }
    } else {
        
        Mage::log(
                "can not transfer stock as product does not belongs to store:" .
                         $arr['website'], Zend_log::DEBUG, "mylogs", true);
    }
}
}
$writeAdapter->commit();
} catch (Exception $e) {
$writeAdapter->rollback();
}
Mage::getSingleton('adminhtml/session')->addSuccess("stock tranfered");
// $this->_redirect('*/*/transfer');
$this->_redirectReferer();
}

public function minmaxUpdateAction()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $data = $this->getRequest()->getPost();
        $websiteId = 1;
        if (Mage::getSingleton('core/session')->getMyWebsiteId())
            $websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
        $website = Mage::getModel("core/website")->load($websiteId);
        $stockId = $website->getStockId();
        $post_data = array_filter($data['qty']);
        
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $writeAdapter->beginTransaction();
        
        try {
            foreach ($post_data as $product => $key) {
                $arr = array_filter($post_data[$product]);
                if (! empty($arr)) {
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product, $stockId);
                    if ($arr['qty'] || $arr['notify_stock_qty']) {
                        $resource = Mage::getSingleton('core/resource');
                        $writeAdapter = $resource->getConnection('core_write');
                        $table = $resource->getTableName('cataloginventory/stock_item');
                        $query = "update {$table} set";
                        if ($arr['qty'])
                            $query .= " qty = '{$arr["qty"]}'";
                        if ($arr['qty'] && $arr['notify_stock_qty'])
                            $query .= ",";
                        if ($arr['notify_stock_qty'])
                            $query .= " notify_stock_qty = '{$arr['notify_stock_qty']}'";
                        $query .= " where product_id = '{$product}' AND stock_id = '{$stockId}'";
                        $writeAdapter->query($query);
                    }
                    
                    $product = Mage::getModel('catalog/product')->setStoreId($stockId)->load($product);
                    $model = Mage::getModel('inventory/minmaxlog');
                    $model->setProductId($product->getId());
                    $model->setOldMin($stockItem->getNotifyStockQty());
                    if ($arr['notify_stock_qty'])
                        $model->setMin($arr['notify_stock_qty']);
                    else
                        $model->setMin($stockItem->getNotifyStockQty());
                    $model->setOldMax($product->getMaxQty());
                    if ($arr['max_qty'])
                        $model->setMax($arr['max_qty']);
                    else
                        $model->setMax($product->getMaxQty());
                    $model->setOldCost($product->getCost());
                    if ($arr['cost'])
                        $model->setCost($arr['cost']);
                    else
                        $model->setCost($product->getCost());
                    
                    $model->setUpdatedAt(date("Y-m-d H:i:s"));
                    $model->setStockId($stockId);
                    $model->setUserId($admin->getUserId());
                    
                    $model->save();
                    
                    if ($arr['max_qty'])
                        $product->setMaxQty($arr['max_qty']);
                    if ($arr['cost'])
                        $product->setStoreId($stockId)->setCost($arr['cost']);
                    $product->save();
                }
            }
            
            $writeAdapter->commit();
        } catch (Exception $e) {
            $writeAdapter->rollback();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess("stock updated");
        $this->_redirectReferer();
    }

public function exportLowstockExcelAction ()
{
$fileName = 'products_lowstock.xml';
$content = $this->getLayout()
->createBlock('inventory/adminhtml_lowstock_grid')
->setSaveParametersInSession(true)
->getExcel($fileName);

$this->_prepareDownloadResponse($fileName, $content);
}

public function exportDownloadsCsvAction ()
{
$fileName = 'products_downloads.csv';
$content = $this->getLayout()
->createBlock('inventory/adminhtml_lowstock_grid')
->setSaveParametersInSession(true)
->getCsv();
$this->_prepareDownloadResponse($fileName, $content);
}
}
