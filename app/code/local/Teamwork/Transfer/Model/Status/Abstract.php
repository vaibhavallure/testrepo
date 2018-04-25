<?php
/**
 * Abstract Magento order status manipulation model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Status_Abstract extends Teamwork_Transfer_Model_Abstract
{
    /**
     * Database connection object
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    /**
     * Magento order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Total products count prepared for changing status
     *
     * @var int
     */
    protected $_totalPackageQty;

    /**
     * Weborder's products
     *
     * @var array
     */
    protected $_items = array();

    /**
     * Frontend label for 'entity_id' param.
     * SHOULD BE ASSIGNED IN CHILD CLASSES.
     * For example, 'package_id' or 'status_id'
     *
     * @var string
     */
    protected $_entityIdLabel;


    /**
     * parameters for accessing db tables and columns
     * SHOULD BE ASSIGNED IN CHILD CLASSES.
     */
    protected $_db_table_status;
    protected $_db_field_status_entity_id;
    protected $_db_field_status_weborder_id;
    protected $_db_field_status_status;
    protected $_channelId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Entry point
     */
    public function run($entityId)
    {
        if ($entityId)
        {
            $table = Mage::getSingleton('core/resource')->getTableName($this->_db_table_status);
            $select = $this->_db->select()
                ->from($table)
            ->where($this->_db_field_status_entity_id . ' = ?', $entityId);
            $entityInfo = $this->_db->fetchRow($select);

            $order_information = $this->getOrderInformation($entityInfo[$this->_db_field_status_weborder_id]);

            if(empty($order_information['OrderNo']))
            {
                $this->_addErrorMsg("Weborder " . $entityInfo[$this->_db_field_status_weborder_id] . " doesn't exists", true);
            }
            else
            {
                try
                {
                    $this->_channelId = $order_information['EComChannelId'];
                    $this->init($order_information['OrderNo'], $entityId, $order_information['EComChannelId']);
                    $this->{'set' . ucfirst(strtolower($entityInfo[$this->_db_field_status_status])) }($entityId, $entityInfo);
                }
                catch(Exception $e)
                {
                    $this->_addErrorMsg(sprintf("Exception occured while order #%s status changing: %s", $this->_order->getIncrementId(), $e->getMessage()), false);
                    $this->_getLogger()->addException($e);
                }
            }
        }
        else
        {
            $this->_addErrorMsg(sprintf("Exception occured while trying to change order status: '%s' param is absent", $this->_entityIdLabel), false);
        }

        return $this->getErrorMsgs();
    }

    /**
     * Prepare weborder's products
     *
     * @param array $items
     */
    protected function prepareItem($items)
    {
        if(!empty($items))
        {
            foreach($items as $item)
            {
                $product = Mage::getModel('catalog/product')->load($item['internal_id']);
                $this->_items[$product->getSku()][] = $item;
                $this->_totalPackageQty += $item['Qty'];
            }
        }
    }

    /**
     * Get weborder short info
     *
     * @param string $webOrderId
     *
     * @return array
     */
    protected function getOrderInformation($webOrderId)
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_weborder'), array('OrderNo', 'EComChannelId'))
        ->where('WebOrderId = ?', $webOrderId);
        return $this->_db->fetchRow($select);
    }

    /**
     * If there is no methods for requested status
     *
     * @param string $method
     * @param array $args
     */
    protected function _call($method, $args)
    {
        $this->_addErrorMsg("Status " . $method . " doesn't not existing", true);
    }

    /**
     * Change order status to "canceled"
     *
     * @param string $entityId
     * @param array $entity
     */
    protected function setCanceled($entityId, $entity, $modifiedTypeCancel = false)
    {
        $refundInChq = Mage::helper('teamwork_transfer/webstaging')->refundInChq( $this->_order->getPayment()->getMethod(), $this->_channelId );
        if( $refundInChq || $modifiedTypeCancel )
        {
            $this->createInvoice();
        }
        if( !$this->_order->canCreditmemo() )
        {
            if($this->_order->canCancel())
            {
                $this->_order->cancel();
                try
                {
                    $this->_order->save();
                }
                catch(Exception $e)
                {
                    $this->_addErrorMsg(sprintf("Exception occured while order cancellation #%s: %s", $this->_order->getIncrementId(), $e->getMessage()), true);
                    $this->_getLogger()->addException($e);
                }
                return false;
            }
            $message = sprintf("Refund not allowed for order #%s", $this->_order->getIncrementId());
            $this->_addErrorMsg($message, false);
            $this->_getLogger()->addMessage($message);
            return false;
        }

        $items = array();
        $qtys = array();
        try
        {
            foreach($this->_order->getAllItems() as $item)
            {
                $qty = 0;
                if(!empty($this->_items[$item->getSku()]))
                {
                    if(count($this->_items[$item->getSku()]) == 1)
                    {
                        $package_item = current($this->_items[$item->getSku()]);
                        if(empty($package_item['InternalId']) || ($package_item['InternalId'] == $item->getItemId() || $package_item['InternalId'] == $item->getParentItemId()))
                        {
                            $qty = $package_item['Qty'];
                        }
                    }
                    else
                    {
                        foreach($this->_items[$item->getSku()] as $package_item)
                        {
                            if($package_item['InternalId'] && ($package_item['InternalId'] == $item->getItemId() || $package_item['InternalId'] == $item->getParentItemId()))
                            {
                                $qty = $package_item['Qty'];
                                break;
                            }
                        }
                    }
                }

                if($qty>0)
                {
                    if($item->getQtyInvoiced() == 0)
                    {
                        $item->setQtyCanceled($qty)->save();
                    }
                    else
                    {
                        $items[$item->getItemId()]['qty'] = $qty;
                        $qtys[$item->getItemId()] = $qty;
                    }
                }
            }

            if( !empty($items) )
            {
                $invoice = null;
                if ($invoiceCollection = current($this->_order->getInvoiceCollection()->getData()))
                {
                    $invoice = Mage::getModel('sales/order_invoice')
                        ->load( $invoiceCollection['entity_id'] )
                    ->setOrder($this->_order);
                }
                $payment = $this->_order->getPayment();

                if( !empty($invoice) /* && $invoice->canRefund() */ )
                {
                    $doOffline = 0;
                    if(
                        $refundInChq || 
                        $modifiedTypeCancel ||
                        !$invoice->getTransactionId() ||
                        ( ($this->_order->getTotalQtyOrdered() > $this->_totalPackageQty) && !$payment->canRefundPartialPerInvoice() )
                    )
                    {
                        $doOffline = 1;
                    }

                    $data = array(
                        'items'                    => $items,
                        'do_offline'               => $doOffline,
                        'comment_text'             => Mage::helper('core')->__('Refund was created by teamwork in CHQ request'),
                        'adjustment_positive'      => '0',
                        'adjustment_negative'      => '0',
                        'shipping_amount'          => $this->_getShippingAmount($entity, $entityId, $invoice->getOrder()),
                        'qtys'                     => $qtys
                    );

                    $service = Mage::getModel('sales/service_order', $this->_order);

                    // create credit memo
                    if( $invoice->getId() &&
                        $invoice->getOrder()->canCreditmemo() &&
                        (
                            ($payment->canRefundPartialPerInvoice()
                              && $invoice->canRefund()
                              && $payment->getBaseAmountPaid() > $payment->getBaseAmountRefunded()) || /**/
                            ($payment->canRefund() && !$invoice->getIsUsedForRefund())
                        )
                    )
                    {
                        $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
                    }
                    else
                    {
                        $creditmemo = $service->prepareCreditmemo($data);
                    }
                    
                    
                    $backToStock = true;
                    if( Mage::helper('teamwork_service')->useRealtimeavailability() )
                    {
                        $backToStock = false;
                    }
                    
                    $productIds = array();
                    foreach ($creditmemo->getAllItems() as $creditmemoItem)
                    {
                        $creditmemoItem->setBackToStock($backToStock);
                        $productIds[$creditmemoItem->getProductId()] = $creditmemoItem->getSku();
                    }

                    $creditmemo->setRefundRequested(true);
                    
                    if($modifiedTypeCancel)
                    {
                        $creditmemo->setSkipRefundGiftcards(true);
                    }
                    
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);

                    $creditmemo->register();

                    $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($creditmemo)
                    ->addObject($creditmemo->getOrder());

                    if ($creditmemo->getInvoice())
                    {
                        $transactionSave->addObject($creditmemo->getInvoice());
                    }

                    $transactionSave->save();

                    if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEND_CREDITMEMO_EMAILS))
                    {
                        $creditmemo->sendEmail();
                    }

                    /*check/set is_in_stock*/
                    foreach($productIds as $productId => $skuForLogs)
                    {
                        $stockItemObj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                        if ($stockItemObj->getQty() > 0 && $stockItemObj->getIsInStock() == Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK)
                        {
                            $stockItemObj->setIsInStock(Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK);
                            try
                            {
                                $stockItemObj->save();
                            }
                            catch(Exception $e)
                            {
                                $this->_getLogger()->addMessage(sprintf("Error occured while setting is_in_stock for order#%s, product %s (id: %s)", $this->_order->getIncrementId(), $skuForLogs, $productId));
                                $this->_getLogger()->addException($e);
                            }
                        }
                    }
                }
                else
                {
                    throw new Exception('Order invoice ' . (empty($invoice) ? 'does not exist' : 'does not allow refunds'));
                }
            }
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Exception occured while creditmemo saving for order #%s: %s", $this->_order->getIncrementId(), $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }
    }

    /**
     * Create magento order shipping object and switch order status
     *
     * @param string $entityId
     * @param array $entityInfo
     */
    protected function setShipped($statusId, $statusInfo)
    {
        $this->createInvoice();
        $convert = Mage::getModel('sales/convert_order');
        $totalQty = 0;
        try
        {
            $shipment = $convert->toShipment($this->_order);
            foreach($this->_order->getAllItems() as $item)
            {
                $qty = 0;
                if(!empty($this->_items[$item->getSku()]) && !$item->isDummy(true))
                {
                    if(count($this->_items[$item->getSku()]) == 1)
                    {
                        $package_item = current($this->_items[$item->getSku()]);
                        if(empty($package_item['InternalId']) || $package_item['InternalId'] == $item->getItemId())
                        {
                            $qty = $package_item['Qty'];
                        }
                    }
                    else
                    {
                        foreach($this->_items[$item->getSku()] as $package_item)
                        {
                            if($package_item['InternalId'] && $package_item['InternalId'] == $item->getItemId())
                            {
                                $qty = $package_item['Qty'];
                                break;
                            }
                            elseif(empty($package_item['InternalId']))
                            {
                                $qty = $package_item['Qty'];
                                break;
                            }
                        }
                    }
                }

                if($qty > 0)
                {
                    $ship_item = $convert->itemToShipmentItem($item);
                    $totalQty += $qty;
                    $ship_item->setQty($qty);
                    $shipment->addItem($ship_item);
                }
            }

            $serviceSettingsModel = Mage::getModel('teamwork_service/settings');

            // in this array, we keep all tracking data already added to a shipment. It's needed to prevent adding the same tracking number a few times.
            $addedTrackingData = array();
            foreach ($this->_items as $sku => $orderItems)
            {
                foreach ($orderItems as $orderItem)
                {
                    $carrierCode = Mage_Sales_Model_Order_Shipment_Track::CUSTOM_CARRIER_CODE;

                    $xmlCarrierCode = $this->_getCarrierFromItem($orderItem);
                    foreach(Mage::getSingleton('shipping/config')->getAllCarriers() as $code => $carrier)
                    {
                        if($carrier->isTrackingAvailable() && strtolower($xmlCarrierCode) == strtolower($code))
                        {
                            $carrierCode = $code;
                            break;
                        }
                    }

                    $trackData = array(
                        'carrier_code'    => $carrierCode,
                        'title'           => $orderItem['ShippingMethod'],
                        'number'          => $orderItem['TrackingNumber']
                    );
                    $implodedTrackData = implode(' ', $trackData);

                    // we don't want to add same tracking number a few times, that's why before adding track data, we check whether it wasn't added yet
                    if (!in_array($implodedTrackData, $addedTrackingData))
                    {
                        $track = Mage::getModel('sales/order_shipment_track')->addData($trackData);
                        $shipment->addTrack($track);
                        $addedTrackingData[] = $implodedTrackData;
                    }
                }
            }

            $shipment->setTotalQty($totalQty);
            Mage::register('current_shipment', $shipment);
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);

            Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
            ->save();

            if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEND_SHIPMENT_EMAILS))
            {
                $shipment->sendEmail();
            }

            $this->_order->save();
            Mage::unregister('current_shipment');
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Exception occured while changing order #%s status to 'shipped': %s", $this->_order->getIncrementId(), $e->getMessage()), false);
            $this->_getLogger()->addException($e);
        }
    }
    
    protected function createInvoice()
    {
        if( $this->_order->canInvoice() )
        {
            try
            {
                $invoice = Mage::getModel('sales/service_order', $this->_order)->prepareInvoice();
                if(!$invoice->getTotalQty())
                {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }
                 
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                ->addObject( $invoice->getOrder() );
                 
                $transactionSave->save();
            }
            catch(Mage_Core_Exception $e)
            {
                $this->_addErrorMsg(sprintf("Can not create invoice for order #%s, text of error: %s", $this->_order->getIncrementId(), $e->getMessage()), false);
                $this->_getLogger()->addException($e);
            }
        }
    }
}
