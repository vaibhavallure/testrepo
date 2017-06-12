<?php
require_once("Mage/Adminhtml/controllers/System/StoreController.php");
class Allure_Store_Adminhtml_System_StoreController extends Mage_Adminhtml_System_StoreController
{
	//get website ids list
	private function getWebsiteIds(){
		$websiteIds = array();
		foreach (Mage::app()->getWebsites() as $website) {
			$websiteIds[] = $website->getId();
		}
		return $websiteIds;
	}	
	
	//copy old product data to new website
	private function copyOldProductIntoNewStore($storeId,$websiteId){
		$oldProductCollection = Mage::getModel('catalog/product')->getCollection();
		//$websiteIds = $this->getWebsiteIds();
		foreach($oldProductCollection as $_product):
			try{
				$product = Mage::getModel('catalog/product')->load($_product->getId());
				$websiteIds = $product->getWebsiteIds();
				$storeIds = $product->getStoreIds();
				if (!in_array($websiteId, $websiteIds)) {
					array_push($websiteIds,$websiteId);
					array_unique($websiteIds);
					if(!in_array($storeId, $storeIds)){
						array_push($storeIds,$storeId);
						array_unique($storeIds);
						$product->setStoreIds($storeIds);
					}
					$product->setWebsiteIds($websiteIds)->save();
				}
			}catch(Exception $e){
			}
		endforeach; 
	}	
	
	
	private function AddProductShareToStoreStatus($storeModel,$websiteModel){
		try{
			if($storeModel->getId()!=null && $websiteModel->getId()!=null){
				$productShareObj = Mage::getModel('allure_productsharetostore/productshare')
					->load($websiteModel->getId(),'website_id');
				$helper = Mage::helper('allure_productsharetostore');
				if($productShareObj->getPsId()==0){
					$productShareObj = Mage::getModel('allure_productsharetostore/productshare');
					$productShareObj->setStatus($helper::PENDING);
					$productShareObj->setStatusCode($helper::PENDING_CODE);
					$productShareObj->setWebsiteCode($websiteModel->getCode());
					$productShareObj->setWebsiteId($websiteModel->getId());
					$productShareObj->setStoreId($storeModel->getId());
					$productShareObj->save();
				}else{
					$productShareObj->setWebsiteCode($websiteModel->getCode());
					$productShareObj->setWebsiteId($websiteModel->getId());
					$productShareObj->setStoreId($storeModel->getId());
					$productShareObj->save();
				}
			}
		}catch (Exception $e){
			
		}
	}
	
	
	/*
	 * Stock Management ie. create new stock for
	 * new website. 
	 *
	 */
	private function createStockManagementByWebsite($websiteModel){
		if($websiteModel){
			//$storeCode = $storeModel->getCode();
			$websiteId = $websiteModel->getId();
			if($websiteId==1){
				$stock = Mage::getModel('cataloginventory/stock')->load($websiteId);
				$stock->setWebsiteId($websiteId);
				$stock->setStockName($websiteModel->getName());
				$stock->save();
				$websiteModel->setStockId($stock->getStockId())->save();
			}else{
				$stock = Mage::getModel('cataloginventory/stock')->load($websiteId,'website_id');
				if($stock->getStockId()==null || $stock->getStockId()==0){
					/* $stock->setStockId(null);
					$stock->setStockName($websiteModel->getName());
					$stock->setWebsiteId($websiteId);
					$stock->save(); */
					
					$stockName = $websiteModel->getName();
					$resource     = Mage::getSingleton('core/resource');
					$writeAdapter   = $resource->getConnection('core_write');
					$table        = $resource->getTableName('cataloginventory_stock');
					$query        = "INSERT INTO {$table} (`stock_name`,`website_id`) VALUES ('{$stockName}',{$websiteId});";
					$writeAdapter->query($query);
					$newStock = Mage::getModel('cataloginventory/stock')->load($websiteId,'website_id');
					$websiteModel->setStockId($newStock->getStockId())->save();
				}else{
					$websiteModel->setStockId($stock->getStockId())->save();
				}
			}
			
		}
	}
	
    public function saveAction()
    {
        if ($this->getRequest()->isPost() && $postData = $this->getRequest()->getPost()) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $this->_redirect('*/*/');
                return;
            }
            $session = $this->_getSession();

            try {
                switch ($postData['store_type']) {
                    case 'website':
                        $postData['website']['name'] = $this->_getHelper()->removeTags($postData['website']['name']);
                        $websiteModel = Mage::getModel('core/website');
                        if ($postData['website']['website_id']) {
                            $websiteModel->load($postData['website']['website_id']);
                        }
                        $websiteModel->setData($postData['website']);
                        if ($postData['website']['website_id'] == '') {
                            $websiteModel->setId(null);
                        }

                        $websiteModel->save();
                        
                        //Create stock by website
                        $this->createStockManagementByWebsite($websiteModel);
                        
                        $session->addSuccess(Mage::helper('core')->__('The website has been saved.'));
                        break;

                    case 'group':
                        $postData['group']['name'] = $this->_getHelper()->removeTags($postData['group']['name']);
                        $groupModel = Mage::getModel('core/store_group');
                        if ($postData['group']['group_id']) {
                            $groupModel->load($postData['group']['group_id']);
                        }
                        $groupModel->setData($postData['group']);
                        if ($postData['group']['group_id'] == '') {
                            $groupModel->setId(null);
                        }

                        $groupModel->save();

                        Mage::dispatchEvent('store_group_save', array('group' => $groupModel));

                        $session->addSuccess(Mage::helper('core')->__('The store has been saved.'));
                        break;

                    case 'store':
                        $eventName = 'store_edit';
                        $storeModel = Mage::getModel('core/store');
                        $postData['store']['name'] = $this->_getHelper()->removeTags($postData['store']['name']);
                        if ($postData['store']['store_id']) {
                            $storeModel->load($postData['store']['store_id']);
                        }
                        $storeModel->setData($postData['store']);
                        if ($postData['store']['store_id'] == '') {
                            $storeModel->setId(null);
                            $eventName = 'store_add';
                        }
                        $groupModel = Mage::getModel('core/store_group')->load($storeModel->getGroupId());
                        $storeModel->setWebsiteId($groupModel->getWebsiteId());
                        $storeModel->save();
                        
                        //if($eventName == 'store_add'){
                        	if($postData['store']['is_copy_old_product']==1){
                        		$websiteModel = Mage::getModel('core/website')->load($groupModel->getWebsiteId());
                        		$this->AddProductShareToStoreStatus($storeModel,$websiteModel);
                        		//$this->copyOldProductIntoNewStore($storeModel->getId(),$groupModel->getWebsiteId());
                        	}
                        //}

                        Mage::app()->reinitStores();

                        Mage::dispatchEvent($eventName, array('store'=>$storeModel));

                        $session->addSuccess(Mage::helper('core')->__('The store view has been saved'));
                        break;
                    default:
                        $this->_redirect('*/*/');
                        return;
                }
                $this->_redirect('*/*/');
                return;
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $session->setPostData($postData);
            }
            catch (Exception $e) {
                $session->addException($e, Mage::helper('core')->__('An error occurred while saving. Please review the error log.'));
                $session->setPostData($postData);
            }
            $this->_redirectReferer();
            return;
        }
        $this->_redirect('*/*/');
    }

}

