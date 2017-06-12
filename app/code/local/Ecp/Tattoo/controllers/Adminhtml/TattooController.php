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
 * @package     Ecp_Tattoo
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tattoo
 *
 * @category    Ecp
 * @package     Ecp_Tattoo
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tattoo_Adminhtml_TattooController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ecp_tattoo/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_tattoo/tattoo_artist')->load($id);
        /* @var $model Ecp_Tattoo_Model_Tattoo */

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('tattoo_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('ecp_tattoo/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ecp_tattoo/adminhtml_tattoo_edit'))
                    ->_addLeft($this->getLayout()->createBlock('ecp_tattoo/adminhtml_tattoo_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_tattoo')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            
            if(isset($data['image']['delete'])){
                $data['image'] = '';
            }
            
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('image');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'tattoo';
                    $result=$uploader->save($path, $_FILES['image']['name']);
                } catch (Exception $e) {
                    
                }

                //this way the name is saved in DB
                $data['image'] = 'tattoo' . DS . $result['file'];
            }

            $model = Mage::getModel('ecp_tattoo/tattoo_artist');
            /* @var $model Ecp_Tattoo_Model_Tattoo */

            if (is_array($data['image'])) {
                $data['image'] = $data['image']['value'];
            }

            if (empty($data['url'])) {
                $data['url'] = trim(str_replace(' ', '_', strtolower($data['name']))).".html";
            }

            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();

                $requestPath = $model->getUrl();
                Mage::helper('core/url_rewrite')->validateRequestPath($requestPath);

                /*$urlModel = Mage::getModel('core/url_rewrite');

                $alreadyExists = $urlModel->getCollection()->addFieldToFilter('id_path', 'tattoos/' . $model->getId());

                $urlModel->setIdPath('tattoos/' . $model->getId())
                        ->setTargetPath('ecptattoo/index/index/id/' . $model->getId())
                        ->setDescription($model->getName() . ' artist page')
                        ->setRequestPath($requestPath);

                $urlModel->setIsSystem(0);
                $urlModel->setStoreId(0);
                $urlRewrite = $alreadyExists->getFirstItem();
                if (!empty($urlRewrite))
                    $urlModel->setId($urlRewrite->getId());
                $urlModel->save();*/

                Mage::getModel('index/indexer')->processEntityAction(
                    $model,
                    'ecp_tattoo_indexer',
                    Mage_Index_Model_Event::TYPE_SAVE
                );

                $categories = $this->getRequest()->getParam('tattoo');
                $images = $this->getRequest()->getParam('tattoo');

                $images = json_decode($images['media_gallery']['images']);

                $i = 0;
                foreach ($images as $image) {
                    $imageModel = Mage::getModel('ecp_tattoo/tattoo_artist_work')->load($image->value_id);
                    $tmp = $imageModel->getData();
                    
                    if (!empty($tmp)) {
                        if ($image->removed == 1) {
                            $imageModel->delete();
                        } else {
                            $imageModel->setImage($image->url)
                                    ->setSortorder($image->position)
                                    ->setEnabled(($image->disabled==1)?0:1)
                                    ->setCategoryartist($categories['category'][$i])
                                    ->setLabel($image->label)
                                    ->save();
                        }
                    } else {
                        Mage::getModel('ecp_tattoo/tattoo_artist_work')
                                ->setTattooArtistId($model->getId())
                                ->setImage($image->url)
                                ->setSortorder($image->position)
                                ->setCategoryartist($categories['category'][$i])
                                ->setLabel($image->label)
                                ->save();
                    }
                    $i++;
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_tattoo')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_tattoo')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('ecp_tattoo/tattoo_artist')->load($this->getRequest()->getParam('id'));
                /* @var $model Ecp_Tattoo_Model_Tattoo */

                /*Mage::getModel('core/url_rewrite')
					->loadByRequestPath($model->getUrl())
                 	->delete();*/
                Mage::getModel('index/indexer')->processEntityAction(
                    $model,
                    'ecp_tattoo_indexer',
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
        $tattooIds = $this->getRequest()->getParam('tattoo');
        if (!is_array($tattooIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($tattooIds as $tattooId) {
                    $tattoo = Mage::getModel('ecp_tattoo/tattoo_artist')->load($tattooId);
                    /* @var $tattoo Ecp_Tattoo_Model_Tattoo */

                    Mage::getModel('index/indexer')->processEntityAction(
                        $tattoo,
                        'ecp_tattoo_indexer',
                        Mage_Index_Model_Event::TYPE_DELETE
                    );
                    /*Mage::getModel('core/url_rewrite')
                        ->loadByRequestPath($tattoo->getUrl())
                        ->delete();*/

                    $tattoo->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($tattooIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $tattooIds = $this->getRequest()->getParam('tattoo');
        if (!is_array($tattooIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($tattooIds as $tattooId) {
                    $tattoo = Mage::getModel('ecp_tattoo/tattoo_artist')
                            ->load($tattooId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($tattooIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'tattoo.csv';
        $content = $this->getLayout()->createBlock('ecp_tattoo/adminhtml_tattoo_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'tattoo.xml';
        $content = $this->getLayout()->createBlock('ecp_tattoo/adminhtml_tattoo_grid')
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
    
    public function consultationsAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function seeConsultationAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_tattoo/tattoo_consultations')->load($id);
        /* @var $model Ecp_Tattoo_Model_Tattoo */

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('consultation_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('ecp_tattoo/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ecp_tattoo/adminhtml_consultations_edit'))
                    /*->_addLeft($this->getLayout()->createBlock('ecp_tattoo/adminhtml_consultations_edit_tabs'))*/;

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_tattoo')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_tattoo');
    }
}