<?php
/**
 * 
 * @author allure
 *
 */
require_once("Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php");
class Allure_RedesignCheckout_Adminhtml_Sales_Order_ShipmentController
extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
        
        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }
            
            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                    );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }
            
            $requestData = $this->getRequest()->getParams();
            if(!empty($requestData["prefer_shipping"])){
                if(isset($requestData["prefer_shipping"]["code"])){
                    $shipment->setPreferedShippingCode($requestData["prefer_shipping"]["code"]);
                }
                if(isset($requestData["prefer_shipping"]["rate"])){
                    $shipment->setPreferedShippingPrice($requestData["prefer_shipping"]["rate"]);
                }
                if(isset($requestData["prefer_shipping"]["description"])){
                    $shipment->setPreferedShippingDescription($requestData["prefer_shipping"]["description"]);
                }
            }
            
            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }
            
            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
            
            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }
            
            $this->_saveShipment($shipment);
            
            $shipment->sendEmail(!empty($data['send_email']), $comment);
            
            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');
            
            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
            
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }
}

