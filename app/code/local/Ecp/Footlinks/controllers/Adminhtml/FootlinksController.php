<?php

class Ecp_Footlinks_Adminhtml_FootlinksController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('footlinks/links')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_footlinks/footlinks')->load($id);

        if ($model->getData() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('footlinks_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('footlinks/links');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('footlinks/adminhtml_footlinks_edit'))
                    ->_addLeft($this->getLayout()->createBlock('footlinks/adminhtml_footlinks_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('footlinks')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($this->getRequest()->getPost()) {
            
            $data = $this->getRequest()->getPost();
            
            $model = Mage::getModel('ecp_footlinks/footlinks');
            
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            if($data['type']==1){
                $model->setData('url_value','');
            }elseif($data['type']==2){
                $model->setData('url_value',$data['url_value']);                
                $model->setData('block_value','');
            }
                        
            if(!isset($data['use_for_seo_text'])){
                $model->setData('block_for_seo_default','');
                $model->setData('block_for_home_seo','');
                $model->setData('use_for_seo_text',0);
            }else{
                $model->setData('url_value','');                
                $model->setData('block_value','');
                $model->setData('use_for_seo_text',1);
                $model->setData('type',0);
            }
            
            $model->setData('link',$data['link']);
            
            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('footlinks')->__('Link was successfully saved '));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }else{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('footlinks')->__('Unable to find link to save'));
            $this->_redirect('*/*/');
        }
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                    $model = Mage::getModel('ecp_footlinks/footlinks');
                    /*@var $model Ecp_Footlinks_Model_Footlinks */

                    $model->setId($this->getRequest()->getParam('id'))
                            ->delete();

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Link was successfully deleted'));
                    $this->_redirect('*/*/');
            } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        
    }

    public function massStatusAction() {
        
    }

    public function exportCsvAction() {
        
    }

    public function exportXmlAction() {
        
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        
    }
    
    public function sortAction(){
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('footlinks/adminhtml_footlinks_sortorder'));
        $this->renderLayout();
    }
    
    public function saveSortAction(){
        
        $data = $this->getRequest()->getParams();
        foreach($data as $key=>$value){
            if($key!=='key'){ 
                $model = Mage::getModel('ecp_footlinks/footlinks')->load($key);
                $model->setData('sort_order',$value);
                $model->save();
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('footlinks')->__('Sort order was successfully saved'));
        $this->_redirect('*/*/sort');
    }
            

    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return true;
    }
}