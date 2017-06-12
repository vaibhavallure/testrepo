<?php

class Ecp_Familycolors_Adminhtml_FamilycolorsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction(){
        $this->loadLayout()
            ->_setActiveMenu('familycolors/familycolors')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

    public function indexAction() {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('familycolors_adminhtml/familycolors'));
        $this->renderLayout();
    }

    public function familycolorsAction(){
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('familycolors_adminhtml/familycolors'));
        $this->renderLayout();
    }
	
	public function deleteAction()
	{

        $model = Mage::getModel('ecp_familycolors/familycolors')->load($this->getRequest()->getParam('id'));
        if ($model) {
            try { 
                $title = $model->getTitle();
                $model->delete();
                
                // delete attribute option
                $attribute_code = 'master_color'; 
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
                $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                ->setAttributeFilter($attribute->getId())
                                ->setStoreFilter($attribute->getStoreId())
                                ->load();
                foreach ($collection as $option) {
                    $optionLabel = $option->getValue();
                    if ($title==$optionLabel) {
                       $option->delete();  // TODO: need to check if there is more than one store view here
                    }
                }                
                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage);
                $this->_redirect('*/*/');
                return;
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
            $this->_redirect('*/*/familycolors');
            return;
        } else {
            Mage::getSingleton('adminhtml/session')->addError("No ID specified");
            $this->_redirect('*/*/');
            return;
        }        
        
	}
    
    public function newAction(){
    	$this->_forward('edit');

    }
    
    public function editAction($id = 0){
	$colorFamilyId     = $this->getRequest()->getParam('id');
        $familycolorsModel  = Mage::getModel('ecp_familycolors/familycolors')->load($colorFamilyId);
 
        if ($familycolorsModel->getId() || $colorFamilyId == 0 || $id == 0) {
            Mage::register('familycolors_data', $familycolorsModel);
            $this->loadLayout();
            $this->_setActiveMenu('familycolors/familycolors');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('familycolors_adminhtml/familycolors_edit'))
                 ->_addLeft($this->getLayout()->createBlock('familycolors_adminhtml/familycolors_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('familycolors')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }

    }

    public function saveAction(){
        if ( $this->getRequest()->getPost() ) {
        	$postData = $this->getRequest()->getPost(); 
			if(
                (!Mage::helper('familycolors')->IsNullOrEmptyString($postData['color_apparel'][0]) && !Mage::helper('familycolors')->IsNullOrEmptyString($postData['metal_color'][0]))
				|| (!Mage::helper('familycolors')->IsNullOrEmptyString($postData['diamond_color'][0]) && !Mage::helper('familycolors')->IsNullOrEmptyString($postData['color_apparel'][0]))
				|| (!Mage::helper('familycolors')->IsNullOrEmptyString($postData['diamond_color'][0]) && !Mage::helper('familycolors')->IsNullOrEmptyString($postData['metal_color'][0]))
                ){
					
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please only select colors from one type'));
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
			} elseif (Mage::helper('familycolors')->IsNullOrEmptyString($postData['color_apparel'][0]) 
						&& Mage::helper('familycolors')->IsNullOrEmptyString($postData['metal_color'][0]) && Mage::helper('familycolors')->IsNullOrEmptyString($postData['metal_color'][0])){
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select at least one color'));
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
			}
			if(!Mage::helper('familycolors')->IsNullOrEmptyString($postData['color_apparel'][0]) ){
				   try {

                $postData['color_apparel'] = serialize($postData['color_apparel']);
                $familycolorsModel = Mage::getModel('ecp_familycolors/familycolors');
                if(isset($_FILES['image']['name']) and (file_exists($_FILES['image']['tmp_name']))) {
                    try {
                        $uploader = new Varien_File_Uploader('image');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . "familycolors" . DS;
                        $uploader->save($path, $_FILES['image']['name']);
                        $postData['image'] = 'familycolors/' . $_FILES['image']['name'];
                        }catch(Exception $e) {
                        }

                } else {
                    if(isset($postData['image']['delete']) && $postData['image']['delete'] == 1){
                        $postData['image'] = '';
                    } else {
                        unset($postData['image']);
                    }
                }
              
                $familycolorsModel
                        ->setData($postData)
                        ->setId($this->getRequest()->getParam('id'))
                        ->save();
						
				 /* add master_color to attribute values */ 
				
				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','master_color');
				$valueId = $this->_getMasterColorValue($attributeId);
				
				if (!$valueId) {
					$option = array();
					$option['attribute_id'] = $attributeId; //manufacturer
					$option['value']['option0'][0] = $postData["title"];
					$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
					$setup->addAttributeOption($option);
					
					$valueId = $this->_getMasterColorValue($attributeId);
				}
                /* add master_color to attribute values */ 
				
                
				if ($postData['color_apparel']) {
					$productCollection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('color_apparel', array('in'=> unserialize($postData['color_apparel'])))
						->addAttributeToSelect('master_color');

				 //var_dump($productCollection->getSelect()->assemble()); die();
					   foreach ($productCollection as $product) { 
							if ($product->getMasterColor()) {
								$familycolors = explode(',',$product->getMasterColor());
							} else {
								$familycolors = array();
							}
							if (!in_array($valueId,$familycolors)) { 
						
							   $familycolors[] = $valueId ;
							   $product->setMasterColor(implode(',',$familycolors));
								
							   Mage::dispatchEvent(
													'catalog_product_prepare_save',
													array('product' => $product, 'request' => $this->getRequest())
												  );
							try {
							   $product->save();
							  } catch (Exception $e) {  var_dump($e->getMessage()); die(); }
							 
							 }
					   }
					   
					$productCollection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('master_color', array('finset'=> $valueId))
						->addAttributeToFilter('color_apparel', array('nin'=> unserialize($postData['color_apparel'])))
						->addAttributeToSelect('master_color');
						
					 foreach ($productCollection as $product) { 
							if ($product->getMasterColor()) {
								$familycolors = explode(',',$product->getMasterColor());
							} else {
								$familycolors = array();
							}
							if ($position = array_search($valueId,$familycolors)) { 
						
							   unset($familycolors[$position]);
							   
							   $product->setMasterColor(implode(',',$familycolors));
								
							   Mage::dispatchEvent(
													'catalog_product_prepare_save',
													array('product' => $product, 'request' => $this->getRequest())
												  );
							try {
							   $product->save();
							  } catch (Exception $e) {  var_dump($e->getMessage());die;}
							 
							 }
					   }
				}
			   
			  
				
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData(false);
 
                $this->_redirect('*/*/familycolors');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData($this->getRequest()->getPost());
                $this->_redirect('*/*/editfamilycolors', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
			}elseif(!Mage::helper('familycolors')->IsNullOrEmptyString($postData['metal_color'][0]) ){
				 try {

                $postData['metal_color'] = serialize($postData['metal_color']);
                $familycolorsModel = Mage::getModel('ecp_familycolors/familycolors');
                if(isset($_FILES['image']['name']) and (file_exists($_FILES['image']['tmp_name']))) {
                    try {
                        $uploader = new Varien_File_Uploader('image');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . "familycolors" . DS;
                        $uploader->save($path, $_FILES['image']['name']);
                        $postData['image'] = 'familycolors/' . $_FILES['image']['name'];
                        }catch(Exception $e) {
                        }

                } else {
                    if(isset($postData['image']['delete']) && $postData['image']['delete'] == 1){
                        $postData['image'] = '';
                    } else {
                        unset($postData['image']);
                    }
                }
              
                $familycolorsModel
                        ->setData($postData)
                        ->setId($this->getRequest()->getParam('id'))
                        ->save();
						
				 /* add master_color to attribute values */ 
				
				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','master_color');
				$valueId = $this->_getMasterColorValue($attributeId);
				
				if (!$valueId) {
					$option = array();
					$option['attribute_id'] = $attributeId; //manufacturer
					$option['value']['option0'][0] = $postData["title"];
					$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
					$setup->addAttributeOption($option);
					
					$valueId = $this->_getMasterColorValue($attributeId);
				}
                /* add master_color to attribute values */ 
				
                
				if ($postData['metal_color']) {
					$productCollection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('metal_color', array('in'=> unserialize($postData['metal_color'])))
						->addAttributeToSelect('master_color');

				 //var_dump($productCollection->getSelect()->assemble()); die();
					   foreach ($productCollection as $product) { 
							if ($product->getMasterColor()) {
								$familycolors = explode(',',$product->getMasterColor());
							} else {
								$familycolors = array();
							}
							if (!in_array($valueId,$familycolors)) { 
						
							   $familycolors[] = $valueId ;
							   $product->setMasterColor(implode(',',$familycolors));
								
							   Mage::dispatchEvent(
													'catalog_product_prepare_save',
													array('product' => $product, 'request' => $this->getRequest())
												  );
							try {
							   $product->save();
							  } catch (Exception $e) {  var_dump($e->getMessage()); die(); }
							 
							 }
					   }
					   
					$productCollection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('master_color', array('finset'=> $valueId))
						->addAttributeToFilter('metal_color', array('nin'=> unserialize($postData['metal_color'])))
						->addAttributeToSelect('master_color');
						
					 foreach ($productCollection as $product) { 
							if ($product->getMasterColor()) {
								$familycolors = explode(',',$product->getMasterColor());
							} else {
								$familycolors = array();
							}
							if ($position = array_search($valueId,$familycolors)) { 
						
							   unset($familycolors[$position]);
							   
							   $product->setMasterColor(implode(',',$familycolors));
								
							   Mage::dispatchEvent(
													'catalog_product_prepare_save',
													array('product' => $product, 'request' => $this->getRequest())
												  );
							try {
							   $product->save();
							  } catch (Exception $e) {  var_dump($e->getMessage());die;}
							 
							 }
					   }
				}
			   
			  
				
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData(false);
 
                $this->_redirect('*/*/familycolors');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData($this->getRequest()->getPost());
                $this->_redirect('*/*/editfamilycolors', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
			}elseif(!Mage::helper('familycolors')->IsNullOrEmptyString($postData['diamond_color'][0]) ){
				         				   try {

                $postData['diamond_color'] = serialize($postData['diamond_color']);
                $familycolorsModel = Mage::getModel('ecp_familycolors/familycolors');
                if(isset($_FILES['image']['name']) and (file_exists($_FILES['image']['tmp_name']))) {
                    try {
                        $uploader = new Varien_File_Uploader('image');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . "familycolors" . DS;
                        $uploader->save($path, $_FILES['image']['name']);
                        $postData['image'] = 'familycolors/' . $_FILES['image']['name'];
                        }catch(Exception $e) {
                        }

                } else {
                    if(isset($postData['image']['delete']) && $postData['image']['delete'] == 1){
                        $postData['image'] = '';
                    } else {
                        unset($postData['image']);
                    }
                }
              
                $familycolorsModel
                        ->setData($postData)
                        ->setId($this->getRequest()->getParam('id'))
                        ->save();
						
				 /* add master_color to attribute values */ 
				
				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','master_color');
				$valueId = $this->_getMasterColorValue($attributeId);
				
				if (!$valueId) {
					$option = array();
					$option['attribute_id'] = $attributeId; //manufacturer
					$option['value']['option0'][0] = $postData["title"];
					$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
					$setup->addAttributeOption($option);
					
					$valueId = $this->_getMasterColorValue($attributeId);
				}
                /* add master_color to attribute values */ 
				
                
				if ($postData['diamond_color']) {
					$productCollection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('diamond_color', array('in'=> unserialize($postData['diamond_color'])))
						->addAttributeToSelect('master_color');

				 //var_dump($productCollection->getSelect()->assemble()); die();
					   foreach ($productCollection as $product) { 
							if ($product->getMasterColor()) {
								$familycolors = explode(',',$product->getMasterColor());
							} else {
								$familycolors = array();
							}
							if (!in_array($valueId,$familycolors)) { 
						
							   $familycolors[] = $valueId ;
							   $product->setMasterColor(implode(',',$familycolors));
								
							   Mage::dispatchEvent(
													'catalog_product_prepare_save',
													array('product' => $product, 'request' => $this->getRequest())
												  );
							try {
							   $product->save();
							  } catch (Exception $e) {  var_dump($e->getMessage()); die(); }
							 
							 }
					   }
					   
					$productCollection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToFilter('master_color', array('finset'=> $valueId))
						->addAttributeToFilter('diamond_color', array('nin'=> unserialize($postData['diamond_color'])))
						->addAttributeToSelect('master_color');
						
					 foreach ($productCollection as $product) { 
							if ($product->getMasterColor()) {
								$familycolors = explode(',',$product->getMasterColor());
							} else {
								$familycolors = array();
							}
							if ($position = array_search($valueId,$familycolors)) { 
						
							   unset($familycolors[$position]);
							   
							   $product->setMasterColor(implode(',',$familycolors));
								
							   Mage::dispatchEvent(
													'catalog_product_prepare_save',
													array('product' => $product, 'request' => $this->getRequest())
												  );
							try {
							   $product->save();
							  } catch (Exception $e) {  var_dump($e->getMessage());die;}
							 
							 }
					   }
				}
			   
			  
				
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData(false);
 
                $this->_redirect('*/*/familycolors');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setfamilycolorsData($this->getRequest()->getPost());
                $this->_redirect('*/*/editfamilycolors', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
			}
        }
        $this->_redirect('*/*/familycolors');
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('importedit/adminhtml_familycolors_grid')->toHtml()
        );
    }
	
	protected function _getMasterColorValue($attributeId)
	{		
		$attribute	 = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		$postData	 = $this->getRequest()->getPost(); 
		
		$attributeOptions = $attribute ->getSource()->getAllOptions();

		$valueId = false;
		foreach ($attributeOptions as $option) {
			$label = $option["label"];
			if ($postData["title"] == $label) {  
				$found = true;
				$valueId = $option["value"];
				break;
			} 
		}
		
		return $valueId;
	}

    

    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return true;
    }
}