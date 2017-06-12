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
 * @package     Ecp_Shippingtext
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Shippingtext
 *
 * @category    Ecp
 * @package     Ecp_Shippingtext
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Shippingtext_Adminhtml_ShippingtextController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ecp_shippingtext/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ecp_shippingtext/shippingtext')->load($id);
        /* @var $model Ecp_Shippingtext_Model_Shippingtext */

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('shippingtext_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('ecp_shippingtext/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ecp_shippingtext/adminhtml_shippingtext_edit'))
                    ->_addLeft($this->getLayout()->createBlock('ecp_shippingtext/adminhtml_shippingtext_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_shippingtext')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        $_cachedBlockName = array('top_cart'=>'top_cart',
            'product_viewed'=>'product_viewed',
            'top_links'=>'top_links',
            'shippingtext'=>'shippingtext',
            'messages'=>'messages');
        foreach ($_cachedBlockName as $cachedBlockName) {
            $cacheId = Mage::helper('ecp_ajaxify')->getBlockCacheId($cachedBlockName);
            Mage::app()->getCache()->remove($cacheId);
            Mage::log($cachedBlockName,null,'controller.log');
        }
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('ecp_shippingtext/shippingtext');
            /* @var $model Ecp_Shippingtext_Model_Shippingtext */

            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                if((int)$model->getStatus() == 1){
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $writeConnection->query('update ecp_shippingtext set status = 2');
                }
                
                $model->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ecp_shippingtext')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ecp_shippingtext')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('ecp_shippingtext/shippingtext');
                /* @var $model Ecp_Shippingtext_Model_Shippingtext */

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
        $shippingtextIds = $this->getRequest()->getParam('shippingtext');
        if (!is_array($shippingtextIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($shippingtextIds as $shippingtextId) {
                    $shippingtext = Mage::getModel('ecp_shippingtext/shippingtext')->load($shippingtextId);
                    /* @var $shippingtext Ecp_Shippingtext_Model_Shippingtext */
                    $shippingtext->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($shippingtextIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $shippingtextIds = $this->getRequest()->getParam('shippingtext');
        if (!is_array($shippingtextIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($shippingtextIds as $shippingtextId) {
                    $shippingtext = Mage::getSingleton('ecp_shippingtext/shippingtext')
                            ->load($shippingtextId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($shippingtextIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'shippingtext.csv';
        $content = $this->getLayout()->createBlock('ecp_shippingtext/adminhtml_shippingtext_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'shippingtext.xml';
        $content = $this->getLayout()->createBlock('ecp_shippingtext/adminhtml_shippingtext_grid')
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


    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/ecp_shippingtext');
    }
}