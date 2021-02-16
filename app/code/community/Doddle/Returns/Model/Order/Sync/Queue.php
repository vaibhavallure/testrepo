<?php
class Doddle_Returns_Model_Order_Sync_Queue extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_SYNCHED = 'synched';
    const STATUS_FAILED  = 'failed';

    protected function _construct()
    {
        $this->_init('doddle_returns/order_sync_queue');
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function queueOrder($orderId)
    {
        $this->addData(
            array(
                'order_id' => $orderId,
                'status' => self::STATUS_PENDING,
                'fail_count' => 0
            )
        );

        try {
            $this->save();
        } catch (Exception $e) {
            $this->getLogger()->log(
                sprintf(
                    'Failed to save order sync queue for order ID: %s - %s',
                    $orderId,
                    $e->getMessage()
                )
            );
        }

        return $this;
    }

    /**
     * Cron function to process pending orders in queue, first in first out limited by configured batch size
     */
    public function processPendingOrders()
    {
        $helper = $this->getHelper();

        /** @var Doddle_Returns_Model_Resource_Order_Sync_Queue_Collection $pendingOrders */
        $pendingOrders = $this->getResourceCollection()
            ->addFieldToFilter('status', self::STATUS_PENDING)
            ->setPageSize($helper->getOrderSyncBatchSize())
            ->setCurPage(1)
            ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_ASC);

        $this->pushOrders($pendingOrders);
    }

    /**
     * Cron function to retry failed orders in queue, first in first out limited by configured batch size
     */
    public function retryFailedOrders()
    {
        $helper = $this->getHelper();

        $maxFails = $helper->getOrderSyncMaxFails();

        /** @var Doddle_Returns_Model_Resource_Order_Sync_Queue_Collection $failedOrders */
        $failedOrders = $this->getResourceCollection()
            ->addFieldToFilter('status', self::STATUS_FAILED)
            ->setPageSize($helper->getOrderSyncBatchSize())
            ->setCurPage(1)
            ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_ASC);

        // Allow for infinate retries where max tries config is set to 0
        if ($maxFails > 0) {
            $failedOrders->addFieldToFilter('fail_count', array('lteq' => $helper->getOrderSyncMaxFails()));
        }

        $this->pushOrders($failedOrders);
    }

    /**
     * @param $orderCollection
     */
    protected function pushOrders(Doddle_Returns_Model_Resource_Order_Sync_Queue_Collection $orderQueue)
    {
        $orderIds = $orderQueue->getColumnValues('order_id');

        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getResourceModel('sales/order_collection');

        $orderCollection->addAttributeToFilter(
            $orderCollection->getResource()->getIdFieldName(), array('in' => $orderIds)
        );

        foreach ($orderQueue as $queuedOrder) {
            /** @var Mage_Sales_Model_Order $order */
            $order = $orderCollection->getItemById($queuedOrder->getOrderId());

            // Only push order if sync store config is enabled
            if ($this->getHelper()->getOrderSyncEnabled($order->getStoreId()) == false) {
                continue;
            }

            $orderData = $this->formatOrderForApi($order);

            // Ensure failed response logic is followed if error occurs contacting Doddle API
            $response = false;

            try {
                $response = $this->getApi()->postOrder($orderData);
            } catch (Exception $e) {
                $this->getLogger()->log(
                    sprintf(
                        '(Magento Order ID: %s) %s',
                        $queuedOrder->getOrderId(),
                        $e->getMessage()
                    )
                );
            }

            if ($response !== false) {
                $queuedOrder->setDoddleOrderId($response);
                $queuedOrder->setStatus(self::STATUS_SYNCHED);
            } else {
                $queuedOrder->setStatus(self::STATUS_FAILED);
                $queuedOrder->setFailCount($queuedOrder->getFailCount() + 1);
            }

            // Save in loop to ensure status is recorded should a later push fail
            $this->saveQueuedOrder($queuedOrder);
        }
    }

    /**
     * @param $queuedOrder
     * @return mixed
     */
    protected function saveQueuedOrder($queuedOrder)
    {
        $queuedOrder->save();
        return $queuedOrder;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function formatOrderForApi(Mage_Sales_Model_Order $order)
    {
        $orderData = array(
            "companyId" => $this->getHelper()->getCompanyId(),
            "externalOrderId" => $order->getIncrementId(),
            "orderType" => "EXTERNAL",
            "externalOrderData" => array(
                "purchaseDate" => $order->getCreatedAtDate()->toString('dd-MM-Y')
            ),
            "customer" => array(
                "email" => $order->getCustomerEmail(),
                "name" => $this->getCustomerName($order)
            )
        );

        // Add telephone number if set
        if ($order->getBillingAddress()->getTelephone()) {
            $orderData["customer"]["mobileNumber"] = $order->getBillingAddress()->getTelephone();
        }

        // Add delivery address for physical orders only
        if (!$order->getIsVirtual()) {
            $orderData['externalOrderData']['deliveryAddress'] = $this->formatShippingAddress(
                $order->getShippingAddress()
            );
        }

        /** @var Mage_Sales_Model_Order_Item $orderLine */
        foreach ($order->getAllVisibleItems() as $orderLine) {
            $orderLineData = array(
                "package" => array(
                    "labelValue" => sprintf('%s-%s', $order->getIncrementId(), $orderLine->getId()),
                    "weight" => (float) $orderLine->getRowWeight()
                ),
                "product" => array(
                    "description" => $orderLine->getName(),
                    "sku" => $orderLine->getSku(),
                    "price" => (float) $orderLine->getPrice(),
                    "imageUrl" => Mage::helper('catalog/image')->init($orderLine->getProduct(), 'image')->__toString(),
                    "quantity" => (int) $orderLine->getQtyOrdered(),
                    "isNotReturnable" => (bool) $orderLine->getProduct()->getData("doddle_returns_excluded")
                ),
                "sourceLocation" => array(),
                "destinationLocation" => array(
                    "locationType" => "external"
                ),
                "fulfilmentMethod" => "NONE"
            );

            // Add size attribute if available
            if ($orderLine->getProduct()->getSize()) {
                $orderLineData['product']['size'] = $orderLine->getProduct()->getSize();
            }

            // Add colour attribute if available
            if ($orderLine->getProduct()->getColor() || $orderLine->getProduct()->getColour()) {
                $orderLineData['product']['colour'] = $orderLine->getProduct()->getColor() ?
                    $orderLine->getProduct()->getColor() :
                    $orderLine->getProduct()->getColour();
            }

            $orderData['orderLines'][] = $orderLineData;
        }

        return $orderData;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $shippingAddress
     * @return array
     */
    protected function formatShippingAddress(Mage_Sales_Model_Order_Address $shippingAddress)
    {
        $formattedAddress = array(
            "town" => $shippingAddress->getCity(),
            "postcode" => $shippingAddress->getPostcode() ? $shippingAddress->getPostcode() : "n/a",
            "country" => $shippingAddress->getCountryId()
        );

        // Add area to address only if set in Magento order
        if ($shippingAddress->getRegion()) {
            $formattedAddress["area"] = $shippingAddress->getRegion();
        }

        foreach ($shippingAddress->getStreet() as $index => $streetLine) {
            if ($streetLine) {
                $formattedAddress['line' . ($index + 1)] = $streetLine;
            }
        }

        return $formattedAddress;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getCustomerName(Mage_Sales_Model_Order $order)
    {
        $customerName = array(
            "firstName" => $order->getCustomerFirstname() ? $order->getCustomerFirstname() : "Guest"
        );

        if ($order->getCustomerLastname()) {
            $customerName["lastName"] = $order->getCustomerLastname();
        }

        return $customerName;
    }

    /**
     * @return Doddle_Returns_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('doddle_returns');
    }

    /**
     * @return Doddle_Returns_Helper_Log
     */
    protected function getLogger()
    {
        return Mage::helper('doddle_returns/log');
    }

    /**
     * @return Doddle_Returns_Helper_Api
     */
    protected function getApi()
    {
        return Mage::helper('doddle_returns/api');
    }
}
