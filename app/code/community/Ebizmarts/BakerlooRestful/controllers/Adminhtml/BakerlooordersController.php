<?php

class Ebizmarts_BakerlooRestful_Adminhtml_BakerlooordersController extends Mage_Adminhtml_Controller_Action
{
    private $order;
    private $orderManager;

    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array(),
        $orderModel = null,
        $orderManager = null
    )
    {
        parent::__construct($request, $response, $invokeArgs);

        if (is_null($orderModel)) {
            $this->order = Mage::getModel('bakerloo_restful/order');
        } else {
            $this->order = $orderModel;
        }

        if (is_null($orderManager)) {
            $this->orderManager = Mage::getModel('bakerloo_restful/orderManagement');
        } else {
            $this->orderManager = $orderManager;
        }
    }

    public function indexAction()
    {

        $this->_title($this->__('Orders'))
             ->_title($this->__('POS'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerlooorders_grid')->toHtml()
        );
    }

    public function itemsAction()
    {

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerlooorders_edit_tab_items_grid')->toHtml()
        );
    }

    /**
     * Export data to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'pos_orders.csv';
        $content = $this->getLayout()
                        ->createBlock('bakerloo_restful/adminhtml_bakerlooorders_grid')
                        ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function editAction()
    {

        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('bakerloo_restful/order');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError($this->__('This order no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $this->__("Editing order #%s", $model->getId()) : $this->__('New Order'));
        $this->_title($this->__('Orders'))
             ->_title($this->__('POS'));

        // Restore previously entered form data from session
        $data = $this->_getSession()->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('bakerlooorder', $model);

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $this->renderLayout();
    }

    public function saveAction()
    {

        if ($this->getRequest()->isPost()) {
            try {
                $postData = $this->getRequest()->getPost('order');

                $order = Mage::getModel('bakerloo_restful/order')->load((int)$postData['id']);

                if (!$order->getId()) {
                    $this->_getSession()->addError(Mage::helper('bakerloo_restful')->__('The order does not exist.'));
                } else {
                    try {
                        $order->addData($postData)->save();

                        $this->_getSession()->addSuccess(Mage::helper('bakerloo_restful')->__('The order has been saved.'));
                    } catch (Exception $e) {
                        $this->_getSession()->addError($e->getMessage());
                    }
                }

                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setUserData($postData);

                $this->_redirect('*/*/edit/');

                return;
            }
        }

        $this->_redirect('adminhtml/bakerlooorders/');
    }

    public function deleteAction()
    {
        $orderId = (int)$this->getRequest()->getParam('id');

        if ($orderId) {
            $order = Mage::getModel('bakerloo_restful/order')
                       ->load($orderId);
            try {
                $order->delete();
                $this->_getSession()->addSuccess($this->__('The order has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/bakerlooorders/');
    }

    public function massPlaceAction() {
        $helper = Mage::helper('bakerloo_restful');
        $ids = $this->getRequest()->getParam('order');

        if (!is_array($ids)) {
            $this->_getSession()->addError($helper->__('Please select at least one order.'));
        } else {
            foreach ($ids as $id) {
                try {
                    $order = $this->placeOrder($id);
                    $this->_getSession()->addSuccess($helper->__('Order #%s placed OK.', $id));
                } catch (Exception $e) {
                    $this->_getSession()->addError($helper->__('Error submitting order #%s: %s', $id, $e->getMessage()));
                }
            }
        }

        $this->_redirect('*/*/');
    }

    public function placeAction() {
        $orderId = (int)$this->getRequest()->getParam('id');
        $postData = $this->getRequest()->getPost('order', array());

        if ($orderId) {
            try {
                $order = $this->placeOrder($orderId, $postData);
                $this->_getSession()->addSuccess(Mage::helper('bakerloo_restful')->__('Order created correctly #%s', $order->getOrderIncrementId()));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        if ($orderId) {
            $this->_redirect('adminhtml/bakerlooorders/edit', array('id' => $orderId));
        } else {
            $this->_redirect('adminhtml/bakerlooorders/');
        }
    }

    /**
     * @param $orderId
     * @param $orderData
     * @return $order
     */
    public function placeOrder($orderId, $orderData = array()) {
        $helper = Mage::helper('bakerloo_restful');
        $order = $this->order->load($orderId);

        if (!empty($orderData)) {
            $order->addData($orderData)->save();
        }

        // Check POS order exists
        if (!$order->getId()) {
            Mage::throwException($helper->__('The order does not exist.'));
        }

        // Check order hasn't been processed
        if ($order->getOrderId()) {
            Mage::throwException($helper->__('This order is already processed.'));
        }

        // Place order
        $objResponse = $this->orderManager->place($order->getId());

        if (!is_array($objResponse)) {
            $order->setFailMessage($objResponse)->save();
            Mage::throwException($helper->__('Could not process order, please try again. Response: %s', $objResponse));
        }

        if (isset($objResponse['error'])) {
            $message = $objResponse['error']['message'];
            $order->setFailMessage($message)->save();

            Mage::throwException($helper->__('Could not process order, please try again. Error: %s', $message));
        }

        if ((isset($objResponse['order_status']) && $objResponse['order_status'] == "notsaved") or !isset($objResponse['order_number'])) {
            $message = '';

            if (isset($objResponse['error_message'])) {
                $message = $objResponse['error_message'];
                $order->setFailMessage($message)->save();
            }

            Mage::throwException($helper->__('Could not save order, please try again. Error message: "%s"', $message));
        }

        $order->setRealCreatedAtToParent();
        return $order;
    }

    public function downloadreceiptAction()
    {
        $id = (int)$this->getRequest()->getParam('receipt');

        if ($id) {
            $receipt = Mage::getModel('bakerloo_email/queue')->load($id);

            if ($receipt->getId()) {
                $order = Mage::getModel('sales/order')->load($receipt->getOrderId());

                $file =
                    Mage::getBaseDir('var') . DS . 'pos' . DS . $order->getStoreId() . DS . 'receipts' . DS . $receipt->getAttachment();

                if (file_exists($file)) {
                    $content = array(
                        'type' => 'filename',
                        'value' => $file,
                    );

                    $this->_prepareDownloadResponse($receipt->getAttachment(), $content, 'image/jpeg');
                }
            }
        }

        $this->_getSession()->addError($this->__('Receipt does not exist.'));

        $this->_redirect('adminhtml/bakerlooorders');
        return;
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
                $acl = 'ebizmarts_pos/orders/remove';
                break;
            case 'exportCsv':
                $acl = 'ebizmarts_pos/orders/export';
                break;
            case 'save':    //save and edit request share same permissions
            case 'edit':
                $acl = 'ebizmarts_pos/orders/edit';
                break;
            case 'place':
                $acl = 'ebizmarts_pos/orders/retry';
                break;
            default:
                $acl = 'ebizmarts_pos/orders/list';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
