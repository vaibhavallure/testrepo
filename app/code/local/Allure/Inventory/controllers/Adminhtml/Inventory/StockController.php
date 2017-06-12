<?php


class Allure_Inventory_Adminhtml_Inventory_StockController extends Allure_Inventory_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu($this->_menu_path)
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Stock'), Mage::helper('adminhtml')->__('Manage Stock')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
    	$this->_initAction();
    	$this->_title($this->__('Inventory Receiving'));
    		$this->renderLayout();
    }


    /**
     * save item action
     */
    public function saveAction() {
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	// Mage::log("coming here",Zend_log::DEBUG,"demo",true);
    	$data = $this->getRequest()->getPost();
    	
    	$websiteId=1;
    	if(Mage::getSingleton('core/session')->getMyWebsiteId())
    		$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
    	$website=Mage::getModel( "core/website" )->load($websiteId);
    	$storeId=$website->getStoreId();
    	$stockId=$website->getStockId();
    	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    	
    	$resource     = Mage::getSingleton('core/resource');
    	$writeAdapter   = $resource->getConnection('core_write');
    	$writeAdapter->beginTransaction();
    	try {
    		foreach ($data['qty'] as $product=>$key){
    			$arr=array_filter($data['qty'][$product]);
    			if(!empty($arr)){
    				$updateStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$stockId);
    				if(!is_null($updateStock->getItemId()) && ($updateStock->getItemId()!=0)){
    					$previousQty=$updateStock->getQty();
    					$newQty=$updateStock->getQty()+$arr[0];
    					$resource     = Mage::getSingleton('core/resource');
    					$writeAdapter   = $resource->getConnection('core_write');
    					$table        = $resource->getTableName('cataloginventory/stock_item');
    					$query        = "update {$table} set  qty = '{$newQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
    					$writeAdapter->query($query);
    					$inventory=Mage::getModel('inventory/inventory');
    					$inventory->setProductId($product);
    					$inventory->setUserId($admin->getUserId());
    					$inventory->setPreviousQty($previousQty);
    					$inventory->setAddedQty($arr[0]);
    					$inventory->setUpdatedAt(date("Y-m-d H:i:s"));
    					$inventory->setStockId($stockId);
    					$inventory->save();
    				}
    				if($arr['cost']){
    					$product = Mage::getModel ( 'catalog/product' )->load ($product);
    					if($arr['cost'])
    						$product->setStoreId($stockId)->setCost($arr['cost']);
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

    public function lowstockAction(){
    	$this->loadLayout();
    	$this->renderLayout();
    }
    public function minmaxAction()
    {
    	$this->_title($this->__('Min Max'));
    	 
    	$this->loadLayout();
    	$this->renderLayout();
    }
    public function storeAction()
    {
    	$request = $this->getRequest()->getPost();
    	$website=1;
    	if($request['value'])
    		$website=$request['value'];
        Mage::getSingleton('core/session')->setMyWebsiteId($website);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    public function transferAction()
    {
    	$this->loadLayout();
    	$this->_title($this->__('Inventory Transfer'));
    	$this->renderLayout();
    }
    public function updateTransferAction()
    {
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	$data = $this->getRequest()->getPost();
    	$websiteId=1;
    	if(Mage::getSingleton('core/session')->getMyWebsiteId())
    		$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
    	$website=Mage::getModel( "core/website" )->load($websiteId);
    	$stockId=$website->getStockId();
    	
    	$resource     = Mage::getSingleton('core/resource');
    	$writeAdapter   = $resource->getConnection('core_write');
    	$writeAdapter->beginTransaction();
    	
    	try {
    		foreach ($data['qty'] as $product=>$key){
    			$arr=array_filter($data['qty'][$product]);
    			if(!empty($arr) && $arr['qty']){
    				if($arr['website'])
    				{
    					$previousStoreStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$stockId);
    					$newStoreStock=Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$arr['website']);
    					if(!is_null($newStoreStock->getItemId()) && ($newStoreStock->getItemId()!=0)){
    						
    						
    						//reduce qty from previous store
    						$previousReducedQty=$previousStoreStock->getQty()-$arr['qty'];
    						$resource     = Mage::getSingleton('core/resource');
    						$writeAdapter   = $resource->getConnection('core_write');
    						$table        = $resource->getTableName('cataloginventory/stock_item');
    						$query        = "update {$table} set  qty = '{$previousReducedQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
    						$writeAdapter->query($query);
    						
    						
    						//add qty to new store
    						$newAddedQty=$newStoreStock->getQty()+$arr['qty'];
    						$resource     = Mage::getSingleton('core/resource');
    						$writeAdapter   = $resource->getConnection('core_write');
    						$table        = $resource->getTableName('cataloginventory/stock_item');
    						$query        = "update {$table} set  qty = '{$newAddedQty}' where product_id = '{$product}' AND stock_id = '{$arr['website']}'";
    						$writeAdapter->query($query);
    						
    						$transfer=Mage::getModel('inventory/transfer');
    						$transfer->setProductId($product);
    						$transfer->setUserId($admin->getUserId());
    						$transfer->setQty($arr['qty']);
    						$transfer->setTransferFrom($stockId);
    						$transfer->setTransferTo($arr['website']);
    						$transfer->setUpdatedAt(date("Y-m-d H:i:s"));
    						$transfer->save();
    						
    					}
    					else{
    						
    						Mage::log("can not transfer stock as product does not belongs to store:".$arr['website'],Zend_log::DEBUG,"mylogs",true);
    					}
    				}else {
    					
    					Mage::log("can not transfer stock as product does not belongs to store:".$arr['website'],Zend_log::DEBUG,"mylogs",true);
    				}
    			}
    		}
    		$writeAdapter->commit();
    		
    	} catch (Exception $e) {
    		$writeAdapter->rollback();
    	}
    	Mage::getSingleton('adminhtml/session')->addSuccess("stock tranfered");
    	//$this->_redirect('*/*/transfer');
    	$this->_redirectReferer();
    }
    public function minmaxUpdateAction(){
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	$data = $this->getRequest()->getPost();
    	$websiteId=1;
    	if(Mage::getSingleton('core/session')->getMyWebsiteId())
    		$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
    	$website=Mage::getModel( "core/website" )->load($websiteId);
    	$stockId=$website->getStockId();
    	$post_data=array_filter($data['qty']);
    	
    	$resource     = Mage::getSingleton('core/resource');
    	$writeAdapter   = $resource->getConnection('core_write');
    	$writeAdapter->beginTransaction();
    	
    	try {
    		foreach ($post_data as $product=>$key){
    			$arr=array_filter($post_data[$product]);
    			if(!empty($arr)){
    				$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product,$stockId);
    				if($arr['qty'] || $arr['notify_stock_qty'])
    				{
    					$resource     = Mage::getSingleton('core/resource');
    					$writeAdapter   = $resource->getConnection('core_write');
    					$table        = $resource->getTableName('cataloginventory/stock_item');
    					$query        = "update {$table} set";
    					if($arr['qty'])
    						$query.=" qty = '{$arr["qty"]}'";
    						if($arr['qty'] && $arr['notify_stock_qty'])
    							$query.=",";
    							if($arr['notify_stock_qty'])
    								$query.=" notify_stock_qty = '{$arr['notify_stock_qty']}'";
    								$query.=" where product_id = '{$product}' AND stock_id = '{$stockId}'";
    								$writeAdapter->query($query);
    				}
    				$product = Mage::getModel ( 'catalog/product' )->load ($product);
    				if($arr['max_qty'])
    					$product->setMaxQty($arr['max_qty']);
    					if($arr['cost'])
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
    public function exportLowstockExcelAction()
    {
    	$fileName   = 'products_lowstock.xml';
    	$content    = $this->getLayout()->createBlock('inventory/adminhtml_lowstock_grid')
    	->setSaveParametersInSession(true)
    	->getExcel($fileName);
    
    	$this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportDownloadsCsvAction()
    {
    	$fileName   = 'products_downloads.csv';
    	$content    = $this->getLayout()->createBlock('inventory/adminhtml_lowstock_grid')
    	->setSaveParametersInSession(true)
    	->getCsv();
    	$this->_prepareDownloadResponse($fileName, $content);
    }
}
