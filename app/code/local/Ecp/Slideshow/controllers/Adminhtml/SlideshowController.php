<?php

/**
 * Ecp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Slideshow
 *
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Slideshow_Adminhtml_SlideshowController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ecp_slideshow/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_slideshow/slideshow')->load($id);
        /* @var $model Ecp_Slideshow_Model_Slideshow */

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('slideshow_data', $model);
            
            $this->loadLayout();
            $this->_setActiveMenu('ecp_slideshow/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ecp_slideshow/adminhtml_slideshow_edit'))
                    ->_addLeft($this->getLayout()->createBlock('ecp_slideshow/adminhtml_slideshow_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_slideshow')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() { 
        if ($data = $this->getRequest()->getPost()) {
            $path = Mage::getBaseDir('media') . DS . 'slideshow';
            $validExtensions = array('jpg', 'jpeg', 'gif', 'png');
            $uuid = uniqid();
 
            try {
				$id111= $this->getRequest()->getParam('id');
                if (isset($data['slide_thumb']['delete'])){
                    if(isset($_FILES['slide_thumb']['name']) && $_FILES['slide_thumb']['name'] != '') {
                        unlink($path . DS . basename($data['slide_thumb']['value']));
                        $data['slide_thumb'] = '';
                    }else Mage::throwException ('You are tryin\'g to delete a thumbnail image without replacing it');
                }
  
                //Removig size check as per Max request
              
                
               /*  if($_FILES['slide_background']['size']){
                    if(((int)$_FILES['slide_background']['size']/1024) > 300){
                        Mage::throwException ('Images can\'t be greather than 300KB');
                    } */
                    /*$ext = pathinfo($_FILES['slide_thumb']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException (' format image');*/
                    

                    
              /*   } */
                
              /*   if(isset($_FILES['slide_thumb']['size'])){
                    if(((int)$_FILES['slide_thumb']['size']/1024) > 300){
                        Mage::throwException ('Images can\'t be greather than 300KB');
                    } */

                    
                    /*$ext = pathinfo($_FILES['slide_background']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');*/
                    
              /*   } */
                
                if (isset($_FILES['slide_thumb']['name']) && $_FILES['slide_thumb']['name'] != '') {
                    try {
  
                        $uploader = new Varien_File_Uploader('slide_thumb');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $uploader->addValidateCallback('validate_thumb', $this, 'isRealImage');
                        $nameImage = $uuid . "-thumb" . substr($_FILES['slide_thumb']['name'], -4);
					if (empty($_FILES['slide_background']['name'] )&& empty($id111)){
                    Mage::throwException('Please upload a background too');
                    }
                    else{
                        $uploader->save($path, $nameImage);
						}
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['slide_thumb'] = $nameImage;
                }
                

                if (isset($data['slide_background']['delete'])) {
                    unlink($path . DS . basename($data['slide_background']['value']));
                    $data['slide_background'] = '';
                }
                
                if (isset($_FILES['slide_background']['name']) && $_FILES['slide_background']['name'] != '') {
                    try {
                        /* Create a uniqueid for rename the files */

                        
                        $uploader = new Varien_File_Uploader('slide_background');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
						$uploader->addValidateCallback('validate_background', $this, 'isRealImage');
                        $nameImage = $uuid . "-bg" . substr($_FILES['slide_background']['name'], -4);
					if (empty($_FILES['slide_thumb']['name']) && empty($id111)){
                    Mage::throwException('Please upload a thumbnail too');
                    }
                    else{
                        $uploader->save($path, $nameImage);
						}
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['slide_background'] = $nameImage;
                }

                if (is_array($data['slide_background']))
                    $data['slide_background'] = basename($data['slide_background']['value']);

                if (is_array($data['slide_thumb']))
                    $data['slide_thumb'] = basename($data['slide_thumb']['value']);



                $model = Mage::getModel('ecp_slideshow/slideshow');
                /* @var $model Ecp_Slideshow_Model_Slideshow */

                $model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));
               
                if ($model->getCreated_date() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreated_Date(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                //Save content and Background in DB
                $model->setSlideContent($data['slide_content']);   
                $model->setBackground($data['background']);   
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_slideshow')->__('Item was successfully saved'));
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
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_slideshow')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('ecp_slideshow/slideshow');
                /* @var $model Ecp_Slideshow_Model_Slideshow */

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $slideshowIds = $this->getRequest()->getParam('slideshow');
        if (!is_array($slideshowIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($slideshowIds as $slideshowId) {
                    $slideshow = Mage::getModel('ecp_slideshow/slideshow')->load($slideshowId);
                    /* @var $slideshow Ecp_Slideshow_Model_Slideshow */
                    $slideshow->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($slideshowIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $slideshowIds = $this->getRequest()->getParam('slideshow');
        if (!is_array($slideshowIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($slideshowIds as $slideshowId) {
                    $slideshow = Mage::getSingleton('ecp_slideshow/slideshow')
                            ->load($slideshowId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($slideshowIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'slideshow.csv';
        $content = $this->getLayout()->createBlock('ecp_slideshow/adminhtml_slideshow_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'slideshow.xml';
        $content = $this->getLayout()->createBlock('ecp_slideshow/adminhtml_slideshow_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    public function isRealImage($file) {
        $size = getimagesize($file);
        if (!is_array($size) || $size[0] == 0)
            Mage::throwException ('Impossible to upload invalid file with valid extension as banner image');
        return true;
    }


    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_slideshow');
    }
}
