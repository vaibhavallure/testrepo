<?php
/**
 * Entrepids
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
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Press
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Press
 *
 * @category    Ecp
 * @package     Ecp_Press
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Press_Adminhtml_PressController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('ecp_press/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('ecp_press/press')->load($id);
                /*@var $model Ecp_Press_Model_Press */

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('press_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('ecp_press/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('ecp_press/adminhtml_press_edit'))
				->_addLeft($this->getLayout()->createBlock('ecp_press/adminhtml_press_edit_tabs'));

			$this->renderLayout();
		} else {
                    
                    
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_press')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $path = Mage::getBaseDir('media') . DS . 'press';
            $validExtensions = array('jpg', 'jpeg', 'gif', 'png');
            /* Create a uniqueid for rename the files */
            $uuid = uniqid();

            try {

                if (isset($data['image_one']['delete'])){
                    if(isset($_FILES['image_one']['name']) && $_FILES['image_one']['name'] != '') {
                        unlink($path . DS . basename($data['image_one']['value']));
                        $data['image_one'] = '';
                    }else Mage::throwException ('You are tryin\'g to delete a thumbnail image without replacing it');
                }

                if(isset($_FILES['image_one']['size']) && $_FILES['image_two']['size'] && $_FILES['image_tree']['size']  && $_FILES['image_four']['size'] && $_FILES['image_five']['size']
                    && $_FILES['image_six']['size'] && $_FILES['image_seven']['size'] && $_FILES['image_eight']['size'] && $_FILES['image_nine']['size'] && $_FILES['image_ten']['size']
                    ){
                    if(((int)$_FILES['image_one']['size']/1024) > 1024 || ((int)$_FILES['image_two']['size']/1024) > 1024
                            || ((int)$_FILES['image_tree']['size']/1024) > 1024 || ((int)$_FILES['image_four']['size']/1024) > 1024 
           ||((int)$_FILES['image_five']['size']/1024) > 1024
                        || ((int)$_FILES['image_six']['size']/1024) > 1024
                        ||((int)$_FILES['image_seven']['size']/1024) > 1024
                        ||((int)$_FILES['image_eight']['size']/1024) > 1024
                        ||((int)$_FILES['image_nine']['size']/1024) > 1024
                        ||((int)$_FILES['image_ten']['size']/1024) > 1024
                    ){
                        Mage::throwException ('Images can\'t be greather than 1 mb');
                    }
                    $ext = pathinfo($_FILES['image_one']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_two']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_tree']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_four']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_five']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_six']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_seven']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_eight']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_nine']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    $ext = pathinfo($_FILES['image_ten']['name'], PATHINFO_EXTENSION);
                    if(!in_array($ext,$validExtensions)) Mage::throwException ('Invalid format image');
                    
                    
                }
                
                if (isset($_FILES['image_one']['name']) && $_FILES['image_one']['name'] != '') {
                    try {
                        $uploader = new Varien_File_Uploader('image_one');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-one" . substr($_FILES['image_one']['name'], strrpos($_FILES['image_one']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_one'] = $nameImage;
                }
                

                if (isset($data['image_two']['delete'])) {
                    unlink($path . DS . basename($data['image_two']['value']));
                    $data['image_two'] = '';
                }
                
                if (isset($_FILES['image_two']['name']) && $_FILES['image_two']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_two');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-two" . substr($_FILES['image_two']['name'],  strrpos($_FILES['image_two']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_two'] = $nameImage;
                }
                
                if (isset($data['image_tree']['delete'])) {
                    unlink($path . DS . basename($data['image_tree']['value']));
                    $data['image_tree'] = '';
                }
                
                if (isset($_FILES['image_tree']['name']) && $_FILES['image_tree']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_tree');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-tree" . substr($_FILES['image_tree']['name'],  strrpos($_FILES['image_tree']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_tree'] = $nameImage;
                }
                
                if (isset($data['image_four']['delete'])) {
                    unlink($path . DS . basename($data['image_four']['value']));
                    $data['image_four'] = '';
                }
                
                if (isset($_FILES['image_four']['name']) && $_FILES['image_four']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_four');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-four" . substr($_FILES['image_four']['name'],  strrpos($_FILES['image_four']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_four'] = $nameImage;
                }
                
                if (isset($data['image_five']['delete'])) {
                    unlink($path . DS . basename($data['image_five']['value']));
                    $data['image_five'] = '';
                }
                
                if (isset($_FILES['image_five']['name']) && $_FILES['image_five']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_five');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-five" . substr($_FILES['image_five']['name'],  strrpos($_FILES['image_five']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_five'] = $nameImage;
                }
                
                if (isset($data['image_six']['delete'])) {
                    unlink($path . DS . basename($data['image_six']['value']));
                    $data['image_six'] = '';
                }
                
                if (isset($_FILES['image_six']['name']) && $_FILES['image_six']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_six');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-six" . substr($_FILES['image_six']['name'],  strrpos($_FILES['image_six']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_six'] = $nameImage;
                }
                
                if (isset($data['image_seven']['delete'])) {
                    unlink($path . DS . basename($data['image_seven']['value']));
                    $data['image_seven'] = '';
                }
                
                if (isset($_FILES['image_seven']['name']) && $_FILES['image_seven']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_seven');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-seven" . substr($_FILES['image_seven']['name'],  strrpos($_FILES['image_seven']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_seven'] = $nameImage;
                }
                
                if (isset($data['image_eight']['delete'])) {
                    unlink($path . DS . basename($data['image_eight']['value']));
                    $data['image_eight'] = '';
                }
                
                if (isset($_FILES['image_eight']['name']) && $_FILES['image_eight']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_eight');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-eight" . substr($_FILES['image_eight']['name'],  strrpos($_FILES['image_eight']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_eight'] = $nameImage;
                }
                
                if (isset($data['image_nine']['delete'])) {
                    unlink($path . DS . basename($data['image_nine']['value']));
                    $data['image_nine'] = '';
                }
                
                if (isset($_FILES['image_nine']['name']) && $_FILES['image_nine']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_nine');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-nine" . substr($_FILES['image_nine']['name'],  strrpos($_FILES['image_nine']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_nine'] = $nameImage;
                }
                
                if (isset($data['image_ten']['delete'])) {
                    unlink($path . DS . basename($data['image_ten']['value']));
                    $data['image_ten'] = '';
                }
                
                if (isset($_FILES['image_ten']['name']) && $_FILES['image_ten']['name'] != '') {
                    try {
                        
                        $uploader = new Varien_File_Uploader('image_ten');
                        $uploader->setAllowedExtensions($validExtensions);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $nameImage = $uuid . "-ten" . substr($_FILES['image_ten']['name'],  strrpos($_FILES['image_ten']['name'],'.'));
                        $uploader->save($path, $nameImage);
                    } catch (Exception $e) {
                        Mage::throwException($e->getMessage());
                    }
                    $data['image_ten'] = $nameImage;
                }
                
                if (is_array($data['image_two']))
                    $data['image_two'] = basename($data['image_two']['value']);

                if (is_array($data['image_one']))
                    $data['image_one'] = basename($data['image_one']['value']);
                
                if (is_array($data['image_tree']))
                    $data['image_tree'] = basename($data['image_tree']['value']);
                
                if (is_array($data['image_four']))
                    $data['image_four'] = basename($data['image_four']['value']);
                
                if (is_array($data['image_five']))
                      $data['image_five'] = basename($data['image_five']['value']);
                
                if (is_array($data['image_six']))
                    $data['image_six'] = basename($data['image_six']['value']);
                
                if (is_array($data['image_seven']))
                    $data['image_seven'] = basename($data['image_seven']['value']);
                
                if (is_array($data['image_eight']))
                    $data['image_eight'] = basename($data['image_eight']['value']);
                
                if (is_array($data['image_nine']))
                    $data['image_nine'] = basename($data['image_nine']['value']);
                
                if (is_array($data['image_ten']))
                    $data['image_ten'] = basename($data['image_ten']['value']);

                if (empty($data['image_one']) /*|| empty($data['image_two'])*/)
                    Mage::throwException('Thumbnail Image invalid');

                $model = Mage::getModel('ecp_press/press');
                /* @var $model Ecp_Slideshow_Model_Slideshow */
              /*   echo "<pre>";
                print_r($data);
                die; */
                $model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));
                if ($model->getCreated_date == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreated_Date(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_press')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_press')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('ecp_press/press');
                                /*@var $model Ecp_Press_Model_Press */
				 
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
        $pressIds = $this->getRequest()->getParam('press');
        if(!is_array($pressIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($pressIds as $pressId) {
                    $press = Mage::getModel('ecp_press/press')->load($pressId);
                    /*@var $press Ecp_Press_Model_Press */
                    $press->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($pressIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $pressIds = $this->getRequest()->getParam('press');
        if(!is_array($pressIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($pressIds as $pressId) {
                    $press = Mage::getSingleton('ecp_press/press')
                        ->load($pressId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($pressIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'press.csv';
        $content    = $this->getLayout()->createBlock('ecp_press/adminhtml_press_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'press.xml';
        $content    = $this->getLayout()->createBlock('ecp_press/adminhtml_press_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_press');
    }
}