<?php
class IWD_OrderManager_Adminhtml_Sales_CustomerController extends IWD_OrderManager_Controller_Abstract
{
    protected function getForm(){
        $result = array('status' => 1);

        $order_id = $this->getOrderId();
        $order = $this->getOrder();
        $fields = Mage::getModel('iwd_ordermanager/order_customer')->CustomerInfoOrderField($order);

        $result['form'] = $this->getLayout()
            ->createBlock('iwd_ordermanager/adminhtml_sales_order_account_form')
            ->setData('order_id', $order_id)
            ->setData('order', $fields)
            ->toHtml();

        return $result;
    }

    protected function updateInfo(){
        $result = array('status' => 1);

        $params = $this->getRequest()->getParams();
        Mage::getModel('iwd_ordermanager/order_customer')->updateOrderCustomer($params);

        return $result;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('iwd_ordermanager/order/actions/edit_account_information');
    }

    public function customerOrderExportCsvAction()
    {
        Mage::register('current_customer',Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id')));

        $fileName   = 'customer_orders.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/customer_edit_tab_orders')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    protected function _prepareDownloadResponse(
        $fileName,
        $content,
        $contentType = 'application/octet-stream',
        $contentLength = null)
    {
        $session = Mage::getSingleton('admin/session');
        if ($session->isFirstPageAfterLogin()) {
            $this->_redirect($session->getUser()->getStartupPageUrl());
            return $this;
        }

        $isFile = false;
        $file   = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                clearstatcache();
                $isFile         = true;
                $file           = $content['value'];
                $contentLength  = filesize($file);
            }
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"', true)
            ->setHeader('Last-Modified', date('r'), true);

        if (!is_null($content)) {
            if ($isFile) {
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $ioAdapter = new Varien_Io_File();
                $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
                $ioAdapter->streamOpen($file, 'r');
                while ($buffer = str_replace("&bull;&nbsp;"," ",strip_tags($ioAdapter->streamRead()))) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
                if (!empty($content['rm'])) {
                    $ioAdapter->rm($file);
                }

                exit(0);
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        return $this;
    }
}
