<?php

class Ebizmarts_BakerlooRestful_Model_Api_Shipments extends Ebizmarts_BakerlooRestful_Model_Api_Api
{

    protected $_model = "sales/order_shipment";

    public function checkPostPermissions()
    {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/shipments/create'));
    }

    public function _createDataObject($id = null, $data = null)
    {
        $result = array();

        if (is_null($data)) {
            $shipment = $this->getOrderShipment()->load($id);
        } else {
            $shipment = $data;
        }

        if ($shipment->getId()) {
            $shipmentItems = array();

            foreach ($shipment->getItemsCollection() as $item) {
                $shipmentItems[]= array(
                    'product_id' => (int)$item->getProductId(),
                    'qty'        => ($item->getQty() * 1),
                    'price'      => (float)$item->getPrice(),
                    'name'       => $item->getName(),
                    'sku'        => $item->getSku(),
                );
            }

            $result = array(
                            "entity_id"            => (int)$shipment->getId(),
                            "increment_id"         => $shipment->getIncrementId(),
                            "created_at"           => $this->formatDateISO($shipment->getCreatedAt()),
                            "updated_at"           => $this->formatDateISO($shipment->getUpdatedAt()),
                            "store_id"             => (int)$shipment->getStoreId(),
                            "order_id"             => (int)$shipment->getOrderId(),
                            "products"             => $shipmentItems,
            );
        }

        return $result;
    }

    /**
     * Create a shipment.
     *
     * @return $this|void
     */
    public function post()
    {

        parent::post();

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload(true);

        if (!isset($data['order_id'])) {
            Mage::throwException(Mage::helper('bakerloo_restful')->__("Invalid parameter 'order_id'."));
        }

        $order = $this->getSalesOrder()->load($data['order_id']);

        /**
         * Check order existing
         */
        if (!$order->getId()) {
            Mage::throwException(Mage::helper('bakerloo_restful')->__('The order no longer exists.'));
        }
        /**
         * Check shipment is available to create separate from invoice
         */
        if ($order->getForcedDoShipmentWithInvoice()) {
            Mage::throwException(Mage::helper('bakerloo_restful')->__('Cannot do shipment for the order separately from invoice.'));
        }
        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            Mage::throwException(Mage::helper('bakerloo_restful')->__('Cannot do shipment for the order.'));
        }

        $shipment = $this->getServiceOrder($order)->prepareShipment();

        $shipment->register();

        //Flags.
        $commentCustomerNotify    = (isset($data['comment_customer_notify']) and ((bool)$data['comment_customer_notify']) );
        $commentVisibleOnFrontend = (isset($data['is_visible_on_front']) and ((bool)$data['is_visible_on_front']) );
        $sendEmail                = (isset($data['send_email']) and ((bool)$data['send_email']) );

        $comment = '';
        if (isset($data['comment_text'])) {
            $shipment->addComment(
                $data['comment_text'],
                $commentCustomerNotify,
                $commentVisibleOnFrontend
            );
            if ($commentCustomerNotify) {
                $comment = $data['comment_text'];
            }
        }

        if ($sendEmail) {
            $shipment->setEmailSent(true);
        }

        $shipment->getOrder()->setCustomerNoteNotify($sendEmail);

        //Save shipment.
        $shipment->getOrder()->setIsInProcess(true);
        $this->getTransactionResource()
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        $shipment->sendEmail($sendEmail, $comment);

        return $this->_createDataObject($shipment->getId());
    }

    public function getServiceOrder($order)
    {
        return Mage::getModel('sales/service_order', $order);
    }

    public function getTransactionResource()
    {
        return Mage::getModel('core/resource_transaction');
    }

    public function getOrderShipment()
    {
        return Mage::getModel($this->_model);
    }
}
