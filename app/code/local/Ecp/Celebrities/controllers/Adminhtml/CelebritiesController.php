<?php

/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Adminhtml_CelebritiesController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ecp_celebrities/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_celebrities/celebrities')->load($id);

        /* @var $model Ecp_Celebrities_Model_Celebrities */

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('celebrities_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('ecp_celebrities/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ecp_celebrities/adminhtml_celebrities_edit'))
                    ->_addLeft($this->getLayout()->createBlock('ecp_celebrities/adminhtml_celebrities_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_celebrities')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            if (!empty($_FILES['default_image']['name'])) {
                $_FILES['default_image']['name'] = preg_replace('|[^a-z0-9\-_\.]+|i', '_', $_FILES['default_image']['name']);
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('default_image');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode 
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders 
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'celebrities';
                    $result=$uploader->save($path, $_FILES['default_image']['name']);
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setNewsData($this->getRequest()->getPost());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }

                //this way the name is saved in DB
                $data['default_image'] = $result['file'];

            }

            $model = Mage::getModel('ecp_celebrities/celebrities');
            /* @var $model Ecp_Celebrities_Model_Celebrities */

            if (is_array($data['default_image'])) {
                $data['default_image'] = basename($data['default_image']['value']);
            }

            if (empty($data['url'])) {
                $data['url'] = trim(str_replace(' ', '_', strtolower($data['celebrity_name']))) . ".html";
            }


//			$id=$this->getRequest()->getParam('id');
//			if($id){
//            	$model->setData($data)->load($this->getRequest()->getParam('id'));
//			}else{
				$model->setData($data)->setId($this->getRequest()->getParam('id'));
//			}
            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();

                $requestPath = $model->getUrl();
                Mage::helper('core/url_rewrite')->validateRequestPath($requestPath);

                /*$urlModel = Mage::getModel('core/url_rewrite');

                $alreadyExists = $urlModel->getCollection()->addFieldToFilter('id_path', 'celebrity/' . $model->getId());
                //QUITA ESTE LOG FELIPON!!!
//                Mage::log($alreadyExists->getData(), null, 'milog.log');

                $urlModel->setIdPath('celebrity/' . $model->getId())
                        ->setTargetPath('celebrities/index/index/id/' . $model->getId())
                        //                    ->setOptions($this->getRequest()->getParam('options'))
                        ->setDescription($model->getCelebrityName() . ' celebrity page')
                        ->setRequestPath($requestPath);

                $urlModel->setIsSystem(0);
                $urlModel->setStoreId(0);
                $urlRewrite = $alreadyExists->getFirstItem();
                if (!empty($urlRewrite))
                    $urlModel->setId($urlRewrite->getId());
                $urlModel->save();*/

                Mage::getModel('index/indexer')->processEntityAction(
                    $model,
                    'ecp_celebrities_indexer',
                    Mage_Index_Model_Event::TYPE_SAVE
                );

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_celebrities')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_celebrities')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('ecp_celebrities/celebrities')->load($this->getRequest()->getParam('id'));
                /* @var $model Ecp_Celebrities_Model_Celebrities */

                /*Mage::getModel('core/url_rewrite')
					->loadByRequestPath($model->getUrl())
                 	->delete();*/
                Mage::getModel('index/indexer')->processEntityAction(
                    $model,
                    'ecp_celebrities_indexer',
                    Mage_Index_Model_Event::TYPE_DELETE
                );

                $model->delete();

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
        $celebritiesIds = $this->getRequest()->getParam('celebrities');
        if (!is_array($celebritiesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($celebritiesIds as $celebritiesId) {
                    $celebrities = Mage::getModel('ecp_celebrities/celebrities')->load($celebritiesId);
                    /* @var $celebrities Ecp_Celebrities_Model_Celebrities */

                    /*Mage::getModel('core/url_rewrite')
                        ->loadByRequestPath($celebrities->getUrl())
                        ->delete();*/
                    Mage::getModel('index/indexer')->processEntityAction(
                        $celebrities,
                        'ecp_celebrities_indexer',
                        Mage_Index_Model_Event::TYPE_DELETE
                    );

                    $celebrities->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($celebritiesIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $celebritiesIds = $this->getRequest()->getParam('celebrities');
        if (!is_array($celebritiesIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($celebritiesIds as $celebritiesId) {
                    $celebrities = Mage::getSingleton('ecp_celebrities/celebrities')
                            ->load($celebritiesId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($celebritiesIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'celebrities.csv';
        $content = $this->getLayout()->createBlock('ecp_celebrities/adminhtml_celebrities_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'celebrities.xml';
        $content = $this->getLayout()->createBlock('ecp_celebrities/adminhtml_celebrities_grid')
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

    public function outfitAction() {
        $this->_initAction()->renderLayout();
    }

    public function editOutfitAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_celebrities/outfits')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }

            /*$tmp = $model->getOutfitImage();
            if (!empty($tmp))
                $model->setOutfitImage('celebrities' . DS . $tmp);*/

            Mage::register('celebrities_outfit_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('ecp_celebrities/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ecp_celebrities/adminhtml_outfits_edit'))
                    ->_addLeft($this->getLayout()->createBlock('ecp_celebrities/adminhtml_outfits_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_celebrities')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newOutfitAction() {
        $this->_forward('editOutfit');
    }

    public function saveOutfitAction() {
        if ($data = $this->getRequest()->getPost()) {
//            Mage::log(,null,'milog.log');
            if (!empty($_FILES['outfit_image']['name'])) {
                $_FILES['outfit_image']['name'] = preg_replace('|[^a-z0-9\-_\.]+|i', '_', $_FILES['outfit_image']['name']);
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('outfit_image');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode 
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders 
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'celebrities';
                    $uploader->save($path, $_FILES['outfit_image']['name']);
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setNewsData($this->getRequest()->getPost());
                    $this->_redirect('*/*/editOutfit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }

                //this way the name is saved in DB
                $data['outfit_image'] = $_FILES['outfit_image']['name'];
            }

            $model = Mage::getModel('ecp_celebrities/outfits');
            /* @var $model Ecp_Celebrities_Model_Celebrities */

            if (is_array($data['outfit_image'])) {
                $data['outfit_image'] = basename($data['outfit_image']['value']);
            }
            
            $model->setData($data)->setId($this->getRequest()->getParam('id'));
            $requestparams = $this->getRequest()->getParams();
            $model->setData('related_products', $requestparams['productsList']);

            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_celebrities')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/outfit', array('id' => $model->getCelebrityId()));
                    return;
                }
                $this->_redirect('*/*/outfit', array('id' => $model->getCelebrityId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/editOutfit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_celebrities')->__('Unable to find item to save'));
        $this->_redirect('*/*/outfit');
    }

    public function massStatusOutfitsAction() {
        $outfitsIds = $this->getRequest()->getParam('celebrities');
        if (!is_array($outfitsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select outfit(s)'));
        } else {
            try {
                foreach ($outfitsIds as $outfitsId) {
                    $outfit = Mage::getModel('ecp_celebrities/outfits')
                        ->load($outfitsId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                    $celebrityId = $outfit->getCelebrityId();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d outfit(s) were successfully updated', count($outfitsIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/outfit/id/'.$celebrityId);
    }

    public function massDeleteOutfitsAction() {
        $outfitsIds = $this->getRequest()->getParam('celebrities');
        if (!is_array($outfitsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select outfit(s)'));
        } else {
            try {
                foreach ($outfitsIds as $outfitsId) {
                    $outfit = Mage::getModel('ecp_celebrities/outfits')->load($outfitsId);
                    $celebrityId = $outfit->getCelebrityId();
                    $outfit->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d outfit(s) were successfully deleted', count($outfitsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/outfit/id/'.$celebrityId);
    }

    public function relatedProductsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('relatedProductsGrid.grid');
//        ->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    public function productgridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('relatedProductsGrid.grid');
//        ->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    public function deleteOutfitAction() {
        if ($this->getRequest()->getParam('outfitId') > 0) {
            try {
                $model = Mage::getModel('ecp_celebrities/outfits')->load($this->getRequest()->getParam('outfitId'));
                $celebrityId = $model->getCelebrityId();
                /* @var $model Ecp_Celebrities_Model_Celebrities */

                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/outfit/id/'.$celebrityId);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/editOutfit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        //$this->_redirect('*/*/');
    }


    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_celebrities');
    }
}