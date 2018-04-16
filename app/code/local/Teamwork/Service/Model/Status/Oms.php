<?php
class Teamwork_Service_Model_Status_Oms extends Teamwork_Service_Model_Status_Abstract
{
    // --------------------------- XML constants ---------------------------
        const XML_REQUEST_ID                                = 'RequestId';

        // properties or orders
        const XML_ORDER                                     = 'Order';
        const XML_ORDER_ORDERID                             = 'OrderId';
        const XML_ORDER_STATUS                              = 'Status';

        // properties or order items
        const XML_ORDERITEM                                 = 'OrderItem';
        const XML_ORDERITEM_ORDER_ITEM_ID                   = 'OrderItemId';
        const XML_ORDERITEM_ITEM_ID                         = 'ItemId';
        const XML_ORDERITEM_STATUS                          = 'Status';
        const XML_ORDERITEM_TRACKING_NUMBER                 = 'TrackingNumber';
        const XML_ORDERITEM_SHIPPING_METHOD                 = 'ShippingMethod';
        const XML_ORDERITEM_MAGENTO_SHIPPING_METHOD         = 'MagentoShippingMethod';
        const XML_ORDERITEM_CARRIER                         = 'Carrier';

    // --------------------------- Database constants ---------------------------

        // table 'service_status'
        const DB_TABLE_STATUS                               = 'service_statusoms';
        const DB_FIELD_STATUS_STATUS_ID                     = 'StatusId';
        const DB_FIELD_STATUS_REQUEST_ID                    = 'request_id';
        const DB_FIELD_STATUS_ORDER_ID                      = 'OrderId';
        const DB_FIELD_STATUS_STATUS                        = 'Status';

        // table 'service_status_items'
        const DB_TABLE_STATUS_ITEMS                         = 'service_statusoms_items';
        const DB_FIELD_STATUS_ITEMS_STATUS_ID               = 'StatusId';
        const DB_FIELD_STATUS_ITEMS_ORDER_ITEM_ID           = 'OrderItemId';
        const DB_FIELD_STATUS_ITEMS_ITEM_ID                 = 'ItemId';
        const DB_FIELD_STATUS_ITEMS_STATUS                  = 'Status';
        const DB_FIELD_STATUS_ITEMS_SHIPPING_METHOD         = 'ShippingMethod';
        const DB_FIELD_STATUS_ITEMS_TRACKING_NUMBER         = 'TrackingNumber';
        const DB_FIELD_STATUS_ITEMS_MAGENTO_SHIPPING_METHOD = 'MagentoShippingMethod'; // magento shipping name is what we keep in 'service_setting_shipping' table. It has form like '<carrier>_<shipping_method>'. Firstly, we try to take carrier from this field
        const DB_FIELD_STATUS_ITEMS_CARRIER                 = 'Carrier';               // we try to take carrier from this field if MagentoShippingMethod is empty

    // avaliable order statuses
    const ORDER_STATUS_CANCELED   = 'Canceled';
    const ORDER_STATUS_SHIPPED    = 'Shipped';
    const ORDER_STATUS_MODIFIED   = 'Modified';
    const ORDER_STATUS_PROCESSING = 'Processing';

    protected $_parseErrors = array();

    public function parseXml($xml)
    {
        $this->_xml = simplexml_load_string($xml);

        if(!empty($this->_xml->{self::XML_ORDER}))
        {
            $parsedStatusIds = $this->parseOrders();
            if ($this->_parseErrors)
            {
                return $this->response($this->_parseErrors);
            }

            $response = (empty($parsedStatusIds)) ? '' : $this->_helper->runStatus($parsedStatusIds, true);
            return $this->response($this->_getErrorsFromResponse($response));
        }
        else
        {
            $message = ($this->_xml === false) ? "Wrong input: no XML given" : "Wrong input: given XML doesn't have <" . self::XML_ORDER . "> element";
            return $this->response(array($message));
        }
    }

    protected function parseOrders()
    {
        $result = array();
        $table = self::DB_TABLE_STATUS;
        $this->_requestId = $this->_parser->getElementVal($this->_xml, false, self::XML_REQUEST_ID);

        foreach($this->_xml->{self::XML_ORDER} as $order)
        {
            $order_id     = $this->_parser->getElementVal($order, false, self::XML_ORDER_ORDERID);
            $status_id    = $this->_helper->getGuidFromString($this->_requestId . $order_id);
            $order_status = $this->_parser->getElementVal($order, false, self::XML_ORDER_STATUS);

            $this->_validateOrderStatus($order_id, $order_status);

            $data = array(
              self::DB_FIELD_STATUS_STATUS_ID  => $status_id,
              self::DB_FIELD_STATUS_REQUEST_ID => $this->_requestId,
              self::DB_FIELD_STATUS_ORDER_ID   => $order_id,
              self::DB_FIELD_STATUS_STATUS     => $order_status
            );

            // clean all previous info about order statuses and insert new info
            $this->_db->delete($table, array(self::DB_FIELD_STATUS_ORDER_ID => $order_id));
            $this->_db->insert($table, $data);

            $this->parseOrderItems($order, $status_id, $order_id);
            $result[] = $status_id;
        }

        return $result;
    }

    /**
     * Order can change status from 'Processing' to one of ['Shipped', 'Canceled', 'Modified'].
     * If order with status != 'Processing' changes status, throw exception
     *
     * @param  string $webOrderId
     * @param  string $newOrderStatus
     */
    protected function _validateOrderStatus($webOrderId, $newOrderStatus)
    {
        $currentOrderStatus = $this->_db->getOne(self::DB_TABLE_STATUS, array(self::DB_FIELD_STATUS_ORDER_ID => $webOrderId), self::DB_FIELD_STATUS_STATUS);
        if ($currentOrderStatus && ($newOrderStatus != $currentOrderStatus) && $currentOrderStatus != self::ORDER_STATUS_PROCESSING)
        {
            $this->_parseErrors[] = "Incorrect status change. WebOrder '{$webOrderId}' should not change status from {$currentOrderStatus} to {$newOrderStatus}";
        }

    }

    protected function parseOrderItems($order, $status_id, $order_id = null)
    {
        $table = self::DB_TABLE_STATUS_ITEMS;
        if (!$order_id)
        {
            $order_id = $this->_parser->getElementVal($order, false, self::XML_ORDER_ORDERID);
        }

        foreach($order->{self::XML_ORDERITEM} as $item)
        {
            $data = array(
                self::DB_FIELD_STATUS_ITEMS_STATUS_ID               => $status_id,
                self::DB_FIELD_STATUS_ITEMS_ORDER_ITEM_ID           => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_ORDER_ITEM_ID),
                self::DB_FIELD_STATUS_ITEMS_ITEM_ID                 => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_ITEM_ID),
                self::DB_FIELD_STATUS_ITEMS_STATUS                  => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_STATUS),
                self::DB_FIELD_STATUS_ITEMS_SHIPPING_METHOD         => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_SHIPPING_METHOD),
                self::DB_FIELD_STATUS_ITEMS_TRACKING_NUMBER         => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_TRACKING_NUMBER),
                self::DB_FIELD_STATUS_ITEMS_MAGENTO_SHIPPING_METHOD => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_MAGENTO_SHIPPING_METHOD),
                self::DB_FIELD_STATUS_ITEMS_CARRIER                 => $this->_parser->getElementVal($item, false, self::XML_ORDERITEM_CARRIER)
            );
            $this->_db->insert($table, $data);
        }
    }
}
