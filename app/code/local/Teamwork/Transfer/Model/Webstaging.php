<?php
/**
 * Weborder model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Webstaging extends Teamwork_Transfer_Model_Abstract
{
    /**
     * Add shipping email to weborder
     *
     * @var bool
     */
    protected $_isSendShipEmail = true;
    
    /**
     * does order just submited
     *
     * @var bool
     */
    protected $_isSubmit = false;

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
     * GUID of currently processed order
     *
     * @var string
     */
    protected $_webOrderId;

    /**
     * GUID of default fee
     *
     * @var string
     */
    protected $_emptyGlobalFee = '0fffffff-ffff-ffff-ffff-ffffffffffff';

    /**
     * GUID of default discount
     *
     * @var string
     */
    protected $_emptyDiscountReason = '1fffffff-ffff-ffff-ffff-ffffffffffff';

    /**
     * Product tax accumulator to fix float rounding bug
     *
     * @var string
     */
    protected $_lineTaxAmountAccumulator = 0;

    /**
     * Time format used in 'ProcessingDate' field in 'service_weborder' table
     *
     * @var string
     */
    protected $_timeFormat = 'Y-m-d H:i:s';
    
    public $salesOrderAfterEvent = 'sales_order_save_after';

    public function _construct()
    {
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * The "sales_order_save_after" event handler (entry point)
     *
     * @param Varien_Event_Observer $observer
     */
    public function activate($observer)
    {
        if( ($observer['event']->getName() == $this->salesOrderAfterEvent) && $this->_isSubmit )
        {
            return;
        }
        elseif( $this->_isSubmit )
        {
            $this->_isSubmit = false;
        }
        
        $this->generateWeborderFromOrder($observer->getEvent()->getOrder());
        $this->ajustPrices($observer->getEvent()->getOrder());
        return $this;
    }

    /**
     *
     */
    public function confirmSubmit($observer)
    {
        $this->_isSubmit = true;
    }

    /**
     * Wraper to create/update weborder with all needed items in staging tables using magento order object
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function generateWeborderFromOrder($order)
    {
        Mage::helper('teamwork_service')->fatalErrorObserver();
        $this->_order = $order;
        
        $channelId = $this->_getChannelId();
        if( !empty($channelId) )
        {
            try
            {
                $select = $this->_db->select()
                    ->from(Mage::getSingleton('core/resource')->getTableName('service_settings'), array('setting_value'))
                    ->where('setting_name = ?', Teamwork_Service_Model_Settings::CONST_COMPLETED_ORDERS)
                ->where('channel_id = ?', $channelId);
                $completedOnly = $this->_db->fetchOne($select);
                
                Mage::dispatchEvent('webstaging_validate_before', array('order' => $this->_order));
                if( $this->isValidForChq($completedOnly) )
                {
                    $this->_createWebOrder();

                    if(!empty($this->_webOrderId))
                    {
                        $this->_createWebOrderDiscount();
                        /*fix for possible customization to prevent extra items taxes accumulation*/
                        $this->_lineTaxAmountAccumulator = 0;
                        $this->_createWebOrderItems(); /*should be before $this->_createWebOrderFee()*/
                        $this->_createWebOrderFee();
                        $this->_createWebOrderItemsDiscount(); /*should be after _createWebOrderItems*/
                        $this->_createWebOrderPayment();
                    }
                }
            }
            catch(Exception $e)
            {
                $this->_getLogger()->addException($e);
            }
        }

        return $this;
    }

    /**
     * Create/update record in "service_weborder" staging table (the main weborder entity)
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function _createWebOrder()
    {
        $channelId = $this->_getChannelId();
        if(!empty($channelId))
        {
            $billing = $this->_order->getBillingAddress();
            $shipping = $this->_order->getShippingAddress();
            $orderNo = $this->_order->getIncrementId();
            
            $weborder = new Varien_Object();
            $weborder->setData(
                array(
                    'WebOrderId'                => $this->_createGuid(),
                    'ProcessingDate'            => gmdate($this->_timeFormat),
                    'EComChannelId'             => $channelId,
                    'DefaultLocationId'         => '',
                    'EComShippingMethod'        => $this->_getShippingMethod(),
                    'OrderNo'                   => $orderNo,
                    'OrderDate'                 => date($this->_timeFormat, Mage::getModel('core/date')->timestamp($this->_order->getCreatedAt())),
                    'Status'                    => ($this->_order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE) ? 'Completed' : 'Processing',
                    'GuestCheckout'             => (int)$this->_order->getCustomerIsGuest(),
                    
                    'WebOrderProcessingArea'    => $this->getGlobalOrderType($channelId),
                    'EComCustomerId'            => $this->_order->getCustomerEmail(),

                    'BillFirstName'             => $billing->getFirstname(),
                    'BillLastName'              => $billing->getLastname(),
                    'BillMiddleName'            => $billing->getMiddlename(),
                    'BillGender'                => $billing->getCustomerGender() ? $billing->getCustomerGender() : 'None',
                    'BillBirthday'              => Mage::helper('teamwork_transfer/webstaging')->getChqFormatedDate($billing->getCustomerDob(), $this->_timeFormat),
                    'BillEmail'                 => $billing->getEmail() ? $billing->getEmail() : $this->_order->getCustomerEmail(),
                    'BillPhone'                 => $billing->getTelephone(),
                    'BillCompany'               => $billing->getCompany(),
                    'BillAddress1'              => $billing->getStreet(1),
                    'BillAddress2'              => $billing->getStreet(2),
                    'BillCity'                  => $billing->getCity(),
                    'BillCountry'               => $billing->getCountry(),
                    'BillPostalCode'            => $billing->getPostcode(),
                    'BillState'                 => $billing->getRegion(),
                    
                    'BillMobilePhone'           => '',
                    'Instruction'               => '',
                    
                    'ShipAddressType'       => $this->_order->getCustomerIsGuest() ? 'Magento 1' : null,
                )
            );
            if( !empty($shipping) )
            {
                $weborder->addData(
                    array
                    (
                        'ShipFirstName'         => $shipping->getFirstname(),
                        'ShipLastName'          => $shipping->getLastname(),
                        'ShipMiddleName'        => $shipping->getMiddlename(),
                        'ShipGender'            => $shipping->getCustomerGender() ? $shipping->getCustomerGender() : 'None',
                        'ShipBirthday'          => Mage::helper('teamwork_transfer/webstaging')->getChqFormatedDate($shipping->getCustomerDob(), $this->_timeFormat),
                        'ShipEmail'             => $this->_isSendShipEmail ? $shipping->getEmail() : null,
                        'ShipPhone'             => $shipping->getTelephone(),
                        'ShipMobilePhone'       => '',
                        'ShipCompany'           => $shipping->getCompany(),
                        'ShipAddress1'          => $shipping->getStreet(1),
                        'ShipAddress2'          => $shipping->getStreet(2),
                        'ShipCity'              => $shipping->getCity(),
                        'ShipCountry'           => $shipping->getCountry(),
                        'ShipPostalCode'        => $shipping->getPostcode(),
                        'ShipState'             => $shipping->getRegion(),
                    )
                );
            }
            Mage::dispatchEvent('add_extra_webstagind_data', array('order' => $this->_order, 'weborder' => $weborder));

            $this->addUndefinedShippingMethod($weborder, $channelId);
            
            $table = Mage::getSingleton('core/resource')->getTableName('service_weborder');
            $select = $this->_db->select()
                ->from($table, array('WebOrderId'))
            ->where('OrderNo = ?', $orderNo);

            if($webOrderId = $this->_db->fetchOne($select))
            {
                $weborder->unsetData('WebOrderId');
                $weborder->unsetData('ProcessingDate');
                $this->_webOrderId = $webOrderId;
                $this->_db->update($table, $weborder->getData(), "OrderNo = '{$orderNo}'");
            }
            else
            {
                $this->_webOrderId = $weborder->getData('WebOrderId');
                $this->_db->insert($table, $weborder->getData());
            }
        }
        else
        {
            $this->_getLogger()->addMessage(sprintf("There is no Channel ID: file: %s; line: %s", __FILE__, __LINE__));
        }
    }

    /**
     * Get channel GUID using store id from magento order object
     *
     * @return string
     */
    protected function _getChannelId()
    {
        $channel_id = null;
        foreach (Mage::app()->getWebsites() as $website)
        {
            foreach ($website->getGroups() as $group)
            {
                $stores = $group->getStores();
                foreach ($stores as $store)
                {
                    if ($store->getId() == $this->_order->getStoreId())
                    {
                        $select = $this->_db->select()
                            ->from(array(Mage::getSingleton('core/resource')->getTableName('service_channel')), array('channel_id'))
                        ->where('channel_name = ?', $store->getCode());

                        $channel_id = $this->_db->fetchOne($select);
                        if( !empty($channel_id) )
                        {
                            return $channel_id;
                        }
                    }
                }
            }
        }
        
        if( empty($channel_id) && Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_PROCESS_UNKNOWN_WEBORDERS) )
        {
            $defaultChannel = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_UNKNOWN_WEBORDER_DEFAULT_CHANNEL);
            if(!empty($defaultChannel))
            {
                return $defaultChannel;
            }
        }
    }
    
    protected function addUndefinedShippingMethod($weborder, $channelId)
    {
        if( $weborder->getData('EComShippingMethod') )
        {
            $table = Mage::getSingleton('core/resource')->getTableName('service_setting_shipping');
            $select = $this->_db->select()
                ->from($table, array('name'))
            ->where('name = ?', $weborder['EComShippingMethod']);

            $rec = $this->_db->fetchOne($select);
            if(!$rec)
            {
                $dataShipping = array(
                    'channel_id'    => $channelId,
                    'name'          => $weborder->getData('EComShippingMethod'),
                    'description'   => $this->_getShippingDescription()
                );
                $this->_db->insert($table, $dataShipping);
            }
        }
    }
    
    /**
     * Get shipping description
     *
     * @return string
     */
    protected function _getShippingDescription()
    {
        $result = $this->_order->getShippingDescription();
        return $result;
    }

    /**
     * Get shipping method
     *
     * @return string
     */
    protected function _getShippingMethod()
    {
        $return = $this->_order->getShippingMethod();
        $shippingName = preg_replace('/([a-zA-Z0-9_]*)(.*)/', '$1', $return);
        /*fix for webshopapps/matrixrate module*/
//        if(stripos($return, 'matrixrate_matrixrate') !== FALSE)
//        {
//            $return = 'matrixrate_matrixrate';
//        }
        return $shippingName;
    }

    /**
     * Add/update the rest of taxes but item taxes to/in 'service_weborder_fee' staging table
     */
    protected function _createWebOrderFee()
    {
        if( $this->_order->getBaseShippingAmount() )/**/
        {
            $data = array(
                'WebOrderId'    => $this->_webOrderId,
                'FeeId'         => $this->getShippingFeeId(),
                //'TaxAmount'   => $this->_order->getBaseShippingTaxAmount(),/**/
                'TaxAmount'     => Mage::helper('teamwork_transfer')->floatSubtraction($this->_order->getBaseTaxAmount(), $this->_lineTaxAmountAccumulator),/**/
                'UnitPrice'     => $this->_order->getBaseShippingAmount(),/**/
                'Qty'           => 1
            );

            $table = Mage::getSingleton('core/resource')->getTableName('service_weborder_fee');

            $select = $this->_db->select()
                ->from($table)
            ->where('WebOrderId = ?', $this->_webOrderId);

            $rec = $this->_db->fetchOne($select);
            if($rec)
            {
                $this->_db->update($table, $data, "WebOrderId = '{$this->_webOrderId}'");
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }   
    }

    /**
     * Add/update order discount record to/in 'service_weborder_discount_reason' table
     * Deprecated
     */
    protected function _createWebOrderDiscount()
    {
        $discountReasonId = $this->_getDiscountReasonId();

        if(!empty($discountReasonId))
        {
            $discount_amount = abs((float)$this->_order->getBaseShippingDiscountAmount());/**/
            if($discount_amount != 0)
            {
                $data = array(
                    'WebOrderId'                 => $this->_webOrderId,
                    'GlobalDiscountReasonId'     => $discountReasonId,
                    'GlobalDiscountAmount'       => $discount_amount
                );
                $table = Mage::getSingleton('core/resource')->getTableName('service_weborder_discount_reason');

                $select = $this->_db->select()
                    ->from($table)
                ->where('WebOrderId = ?', $this->_webOrderId);

                if($this->_db->fetchRow($select))
                {
                    $this->_db->update($table, $data, "WebOrderId = '{$this->_webOrderId}'");
                }
                else
                {
                    $this->_db->insert($table, $data);
                }
            }
        }
    }

    /**
     * Get discount GUID from 'service_discount' table by type (1 - for order discount, 0 - for order items discount)
     *
     * @param int $type
     *
     * @return string
     */
    protected function _getDiscountReasonId($type = 1)
    {
        $select = $this->_db->select()
            ->from(array('disc' => Mage::getSingleton('core/resource')->getTableName('service_discount')), array('discount_id'))
            ->join(array('discst' => Mage::getSingleton('core/resource')->getTableName('service_discount_status')), 'disc.discount_id = discst.discount_id',array())
            ->where('discst.channel_id = ?', $this->_getChannelId())
            ->where('disc.type = ?', $type)
        ->where('discst.enabled = ?', 1);
        
        $discountReasonId = $this->_db->fetchOne($select);
        if(empty($discountReasonId))
        {
            $discountReasonId = $this->_emptyDiscountReason;
        }
        return $discountReasonId;
    }

    /**
     * Add/update order items discount record to/in 'service_weborder_item_line_discount' table
     *
     */
    protected function _createWebOrderItemsDiscount()
    {
        $items = $this->_getOrderItems();
        if(!empty($items))
        {
            $tableWedOrderItemlineDiscount = Mage::getSingleton('core/resource')->getTableName('service_weborder_item_line_discount');
            $tableWedOrderItem = Mage::getSingleton('core/resource')->getTableName('service_weborder_item');
            $discountReasonId = $this->_getDiscountReasonId(0);
            foreach($items as $item)
            {
                $discountAmount = floatval($item->getData('base_discount_amount'));/**/
                if (empty($discountAmount)){
                    continue;
                }

                $select = $this->_db->select()
                    ->from($tableWedOrderItem, array('WebOrderItemId'))
                    ->where('WebOrderId = ?', $this->_webOrderId)
                ->where('InternalId = ?', $item->getId());

                if($webOrderItemId = $this->_db->fetchOne($select))
                {
                    $data = array(
                        'WebOrderItemId'       => $webOrderItemId,
                        'LineDiscountReasonId' => $discountReasonId,
                        'LineDiscountAmount'   => $discountAmount
                    );

                    $select = $this->_db->select()
                        ->from($tableWedOrderItemlineDiscount, array('WebOrderItemId'))
                    ->where('WebOrderItemId = ?', $webOrderItemId);

                    if($webOrderItemIdE = $this->_db->fetchOne($select))
                    {
                        $this->_db->update($tableWedOrderItemlineDiscount, $data, "WebOrderItemId = '{$webOrderItemIdE}'");
                    }
                    else
                    {
                        $this->_db->insert($tableWedOrderItemlineDiscount, $data);
                    }
                } else {
                    throw new Exception(sprintf('Error occured while discount item creating (order item id: %s)', $item->getId()));
                }
            }
        }
    }

    /**
     * Add/update order items records to/in 'service_weborder_item' table
     *
     */
    protected function _createWebOrderItems()
    {
        $items = $this->_getOrderItems();
        if(!empty($items))
        {
            $tableWedOrderItem = Mage::getSingleton('core/resource')->getTableName('service_weborder_item');
            $webOrderItemsGroupId = $this->_createGuid();
            $number = 1;

            $secondaryIdAttrCode = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_WEBORDER_SECONDARY_ID);
            foreach($items as $item)
            {
                $webOrderItemId = $this->_createWebOrderItemGuid($item);
                $note = $this->getItemNote($item);
                
                $itemId = null;
                $product = $this->loadOrderProduct($item, $itemId);
                
                if( !empty($product) && $product->getId() )
                {
                    $price = $item->getBasePrice();/**/
                    $lineTaxAmount = $item->getBaseTaxAmount();/**/
                    /*fix round bug*/
                    $qty = $item->getQtyOrdered();
                    $expectedTotalItemPrice = round($qty * $price + $lineTaxAmount, 2);
                    $price = round($price, 2);
                    /*php float calculation bug fix*/
                    $lineTaxAmount = Mage::helper('teamwork_transfer')->floatSubtraction($expectedTotalItemPrice, ($qty * $price));
                    $this->_lineTaxAmountAccumulator += $lineTaxAmount;
                    
                    if($secondaryIdAttrCode)
                    {
                        $secondaryIdAttrVal = $product->getData($secondaryIdAttrCode);
                        if (!$secondaryIdAttrVal)
                        {
                            $secondaryIdAttrVal = "";
                        }
                    }
                    else
                    {
                        $secondaryIdAttrVal = null;
                    }

                    $data = array(
                        'WebOrderItemId'         => $webOrderItemId,
                        'WebOrderItemsGroupId'   => $webOrderItemsGroupId,
                        'WebOrderId'             => $this->_webOrderId,
                        'InternalId'             => $item->getItemId(),
                        'ItemId'                 => !empty($itemId) ? $itemId : '',
                        'SecondaryId'            => $secondaryIdAttrVal,
                        'OrderQty'               => $qty,
                        'UnitPrice'              => $price,
                        'LineTaxAmount'          => $lineTaxAmount,
                        'TrackingNo'             => '',
                        'LineNo'                 => $number++,
                        'Notes'                  => $note ? $note : ''
                    );

                    $select = $this->_db->select()
                        ->from($tableWedOrderItem, array('WebOrderItemId'))
                        ->where('WebOrderId = ?', $this->_webOrderId)
                    ->where('WebOrderItemId = ?', $webOrderItemId);

                    if($newWebOrderItemId = $this->_db->fetchOne($select))
                    {
                        unset($data['WebOrderItemId']);
                        unset($data['WebOrderItemsGroupId']);
                        $this->_db->update($tableWedOrderItem, $data, "WebOrderId = '{$this->_webOrderId}' AND WebOrderItemId = '{$webOrderItemId}'");
                    }
                    else
                    {
                        $this->_db->insert($tableWedOrderItem, $data);
                    }
                }
                else
                {
                    Mage::throwException('Unknown product - ' . (string)$item->getSku());
                }
            }
        }
    }
    
    protected function getItemNote($item)
    {
        $note = '';
        $itemOptions = $item->getProductOptions();
        if(!empty($itemOptions['options']) && is_array($itemOptions['options']))
        {
            $notes = array();
            foreach ($itemOptions['options'] as $currentItemOptions)
            {
                $notes[] = $currentItemOptions['label'] . ': ' . $currentItemOptions['value'];
            }
            $note = join("\n", $notes);
        }
        return $note;
    }
    
    protected function loadOrderProduct($item, &$itemId)
    {
        $tableItems = Mage::getSingleton('core/resource')->getTableName('service_items');
        $product = null;
        if( $item->getSku() )
        {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
            if(!empty($product) && $product->getId())
            {
                $select = $this->_db->select()
                    ->from(array('i' => $tableItems), array('item_id' => 'i.item_id'))
                ->where('i.internal_id = ?', $product->getId());
                $itemId = $this->_db->fetchOne($select);
            }
        }
        
        if( empty($itemId) )
        {
            $productId = $item->getProductId();
            if( $item->getChildrenItems() )
            {
                $child = current($item->getChildrenItems());
                $productId = $child->getProductId();
            }
            
            if( !empty($productId) )
            {
                $product = Mage::getModel('catalog/product')->load($productId);
                $select = $this->_db->select()
                    ->from(array('i' => $tableItems), array('item_id' => 'i.item_id'))
                ->where('i.internal_id = ?', $productId);
                $itemId = $this->_db->fetchOne($select);
            }
        }
        return $product;
    }

   /*protected function _createWebOrderItemsFee($webOrderItemId, $feeName, $fees)
    {
        if($feeId)
        {
            $this->_db->delete(Mage::getSingleton('core/resource')->getTableName('service_weborder_item_fee'), array(
                'FeeId = ?'             => $feeId,
                'WebOrderItemId = ?'    => $webOrderItemId
            ));
            if(!empty($fees))
            {
                foreach($fees as $fee)
                {
                    $data = array(
                        'WebOrderItemId'       => $webOrderItemId,
                        'FeeId'                => $feeId,
                        'UnitPrice'            => $fee['price'],
                        'TaxAmount'            => (float)0,
                        'Qty'                  => $fee['qty']
                    );

                    $this->_db->insert(Mage::getSingleton('core/resource')->getTableName('service_weborder_item_fee'), $data);
                }
            }
        }
    }*/

    /**
     * Add/update order's payment data to/in 'service_weborder_payment' table
     *
     */
    protected function _createWebOrderPayment()
    {
        $creditCardTypes = array(
            'AE' => 'Amex',
            'VI' => 'Visa',
            'MC' => 'Master',
            'DI' => 'Discover',
            'JCB'=> 'JCB',
            'SM' => 'Maestro',
            'SO' => 'Solo',
            'OT' => 'Undefined'
        );
        
        $billing = $this->_order->getBillingAddress();
        $payments = $this->_order->getPaymentsCollection();
        foreach($payments as $payment)
        {
            $cc = $payment;
            if($payment->getMethod() == Mage_Paygate_Model_Authorizenet::METHOD_CODE && $payment->getMethodInstance()->getCardsStorage()->getCards())
            {
                $cc = current($payment->getMethodInstance()->getCardsStorage()->getCards());
            }

            if($type = $cc->getCcType())
            {
                $cardType = !empty($creditCardTypes[$type]) ? $creditCardTypes[$type] : null;
            }

            if(empty($cardType))
            {
                $cardType = 'Undefined';
            }
            
            $ccExpMonth = 0;
            if( $cc->getCcExpMonth() )
            {
                $ccExpMonth = $cc->getCcExpMonth();
            }
            
            $ccExpYear = 0;
            if( $cc->getCcExpYear() )
            {
                $ccExpYear = $cc->getCcExpYear();
            }
            
            $transactionId = null;
            $isCaptured = ($this->_order->hasInvoices() || !(float)$this->_order->getBaseGrandTotal()) ? 1 : 0;/**/
            $paymentDate = $this->_order->getCreatedAt();
            
            $transactionCollection = Mage::getResourceModel('sales/order_payment_transaction_collection')->addOrderIdFilter( $this->_order->getId() );
            foreach($transactionCollection->load() as $transaction)
            { 
                if( $transaction->getPaymentId() == $payment->getEntityId() &&
                    in_array($transaction->getTxnType(), array(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE))
                )
                {
                    $transactionId = $transaction->getTxnId();
                    if($transaction->getTxnType() == Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE)
                    {
                        $paymentDate = $transaction->getCreatedAt();
                        break;
                    }
                }
            }

            $data = array(
                'WebOrderPaymentId'         => $this->_createGuid(),
                'WebOrderId'                => $this->_webOrderId,
                'CardType'                  => $cardType,
                'EComPaymentMethod'         => $payment->getMethod(),
                'AccountNumber'             => $this->_getAccountNumber($cc, $payment),
                'PaymentAmount'             => Mage::helper('teamwork_transfer/webstaging')->getPaymentPaid($payment),
                'CardExpMonth'              => $ccExpMonth,
                'CardExpYear'               => $ccExpYear,
                'MerchantId'                => null,
                'CardOrderId'               => null,
                'ReferenceNum'              => null,
                'TransactionId'             => $transactionId,
                'IsCaptured'                => $isCaptured,
                'PaymentDate'               => date($this->_timeFormat, Mage::getModel('core/date')->timestamp($paymentDate)),
                'ListOrder'                 => 1,
                'CardholderFirstName'       => $billing->getFirstname(),
                'CardholderLastName'        => $billing->getLastname(),
                'CardholderAddress1'        => $billing->getStreet(1),
                'CardholderAddress2'        => $billing->getStreet(2),
                'CardholderCity'            => $billing->getCity(),
                'CardholderState'           => $billing->getRegion(),
                'CardholderCountryCode'     => $billing->getCountry(),
                'CardholderPostalCode'      => $billing->getPostcode(),
                'LoyaltyRewardPointAmount'  => null
            );
            $table = Mage::getSingleton('core/resource')->getTableName('service_weborder_payment');

            $select = $this->_db->select()
                ->from($table, array('WebOrderPaymentId'))
                ->where('WebOrderId = ?', $this->_webOrderId)
            ->where('EComPaymentMethod = ?', $data['EComPaymentMethod']);

            if($webOrderPaymentId = $this->_db->fetchOne($select))
            {
                $data['WebOrderPaymentId'] = $webOrderPaymentId;
                $this->_db->update($table, $data, "WebOrderId = '{$this->_webOrderId}' AND EComPaymentMethod = '{$data['EComPaymentMethod']}'");
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

     /**
     * Get additional order's payment info
     *
     * @return string|null
     */
    protected function _getAccountNumber($cc, $payment)
    {
        if($cc->getCcLast4())
        {
            return 'XXXX-XXXX-XXXX-' . $cc->getCcLast4();
        }
        elseif( isset(Mage::getConfig()->getNode('modules')->children()->Teamwork_Giftcards) )
        {
            return $payment->getAdditionalInformation('GiftcardNo');
        }
        else
        {
            return null;
        }
    }

    /**
     * Generate unique weborder GUID
     *
     * @return string
     */
    protected function _createGuid($namespace = '')
    {
        return Mage::helper('teamwork_transfer')->generateGuid($namespace);
    }

    /**
     * Generate unique weborder item GUID
     *
     * @return string
     */
    protected function _createWebOrderItemGuid($item)
    {
        $data = '';
        $data .= $item->getItemId();
        $data .= $item->getOrderId();
        $data .= $item->getProductId();
        $data .= $item->getSku();

        $hash = strtolower(hash('ripemd128', md5($data)));
        return Mage::helper('teamwork_service')->getGuidFromString($hash, true);
    }

    /**
     * Gets weborder ID by Magento order number
     *
     * @param  string $orderNo
     *
     * @return string
     */
    protected function _getWeborderIdByOrderNo($orderNo)
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_weborder'), array('WebOrderId'))
        ->where('OrderNo = ?', $orderNo);

        return $this->_db->fetchOne($select);
    }

    /**
     * Resaves each weborder and increases its processing date by 1 second
     *
     * @param  array $orderIds
     */
    public function resendOrdersToChq($orderIds)
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $orderIds))
        ->setPageSize(Mage::getModel('teamwork_service/weborder')->getOrderLimit());

        $webOrderTable = Mage::getSingleton('core/resource')->getTableName('service_weborder');
        $select  = $this->_db->select()->from($webOrderTable, array('MAX(ProcessingDate)'));
        $maxDate = $this->_db->fetchOne($select);

        for ($page = 1; $page <= $orderCollection->getLastPageNumber(); $page++)
        {
            $newDate = date($this->_timeFormat, strtotime($maxDate) + $page);
            foreach ($orderCollection->clear()->setCurPage($page)->load() as $order)
            {
                $this->_webOrderId = '';
                $this->generateWeborderFromOrder($order);
                if ($this->_webOrderId)
                {
                    $this->_db->update($webOrderTable, array('ProcessingDate' => $newDate), array('WebOrderId = ?' => $this->_webOrderId));
                }
            }
        }
    }

    protected function _getOrderItems()
    {
        $allItems = $this->_order->getAllItems();
        $items = array();
        foreach($allItems as $_item)
        {
            if ($_item->getParentItem())
            {
                continue;
            }
            if ($_item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $_item->getHasChildren())
            {
                foreach ($_item->getChildrenItems() as $child)
                {
                    $items[] = $child;
                }
            }
            else
            {
                $items[] = $_item;
            }
        }
        return $items;
    }
    
    public function getGlobalOrderType($channel_id)
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_settings'), array('setting_value'))
            ->where('setting_name = ?', 'WebOrderProcessingArea')
        ->where('channel_id = ?', $channel_id);

        return (int)$this->_db->fetchOne($select);
    }
    
    public function isValidForChq($completedOnly)
    {
        if( !(Mage::helper('teamwork_transfer/webstaging')->isChqZoneUsedAsProcessing()) )
        {
            return false;
        }
        $allowAuthorizeOnly = Mage::helper('teamwork_transfer/webstaging')->allowAuthorizeOnlyPayment( $this->_order->getPayment()->getMethod(), $this->_getChannelId() );
        $authorizedAmount = floatval($this->_order->getPayment()->getBaseAmountAuthorized()); /**/
        $paidAmount = floatval( $this->_order->getPayment()->getBaseAmountPaid() ); /**/
        
        $completedOnly = ($completedOnly == 'false') ? false : true;
        switch($completedOnly)
        {
            case true:
                if( $this->_order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE )
                {
                    return true;
                }
            break;
            case false:
                if( (!(float)$this->_order->getBaseGrandTotal() || ($paidAmount || ($allowAuthorizeOnly && $authorizedAmount))) && $this->_order->getStatus() != Mage_Sales_Model_Order::STATUS_FRAUD )/**/
                {
                    return true;
                }
            break;
        }
        return false;
    }
    
    public function getShippingFeeId()
    {
        $select = $this->_db->select()
            ->from(array('fee' => Mage::getSingleton('core/resource')->getTableName('service_fee')))
            ->join(array('feest' => Mage::getSingleton('core/resource')->getTableName('service_fee_status')), 'fee.fee_id = feest.fee_id')
            ->where('fee.global_level = ?', 1)
            ->where('feest.channel_id = ?', $this->_getChannelId())
        ->where('feest.enabled = ?', 1);

        foreach($this->_db->fetchAll($select) as $rec)
        {
            if(strpos(strtolower($rec['code']), 'ship') !== false)
            {
                return $shipFeeId = $rec['fee_id'];
            }
        }

        return $this->_emptyGlobalFee;
    }
    
    public function ajustPrices($order)
    {
        if($webOrderId = $this->_getWeborderIdByOrderNo($order->getIncrementId()))
        {
            $select = $this->_db->select()
                ->from(array('wi' => Mage::getSingleton('core/resource')->getTableName('service_weborder_item')))
                ->joinLeft(array('wid' => Mage::getSingleton('core/resource')->getTableName('service_weborder_item_line_discount')),
                           'wid.WebOrderItemId = wi.WebOrderItemId')
            ->where('wi.WebOrderId = ?', $webOrderId);
            $acc = 0;
            $dearestLine = false;
            $maxLineTax = 0;
            $items = $this->_db->fetchAll($select);
            foreach($items as $item) {
                $linePrice = $item['UnitPrice'] * $item['OrderQty'] + $item['LineTaxAmount'] - $item['LineDiscountAmount'];
                $acc += $linePrice;
                if ($item['LineTaxAmount'] > $maxLineTax)
                {
                    $maxLineTax = $item['LineTaxAmount'];
                    $dearestLine = $item;
                }
            }
            if ($dearestLine !== false)
            {
                $select = $this->_db->select()
                    ->from(array('wf' => Mage::getSingleton('core/resource')->getTableName('service_weborder_fee')))
                ->where('wf.WebOrderId = ?', $webOrderId);
                $fees = $this->_db->fetchAll($select);
                foreach($fees as $fee) {
                    $acc += $fee['UnitPrice'] * $fee['Qty'] + $fee['TaxAmount'];
                }

                $paid = 0;
                $select = $this->_db->select()
                    ->from(array('wp' => Mage::getSingleton('core/resource')->getTableName('service_weborder_payment')))
                ->where('wp.WebOrderId = ?', $webOrderId);
                
                $payments =$this->_db->fetchAll($select);
                foreach($payments as $payment)
                {
                    $paid += $payment['PaymentAmount'];
                }

                if (round($paid > $acc ? $paid - $acc : $acc - $paid, 2) == 0.01)
                {
                    $newTaxAmount = $paid > $acc ? $dearestLine['LineTaxAmount'] + 0.01 : $dearestLine['LineTaxAmount'] - 0.01;
                    $newTaxAmount = round($newTaxAmount, 2);
                    $weborderItemId = $dearestLine['WebOrderItemId'];
                    $this->_db->update(
                        Mage::getSingleton('core/resource')->getTableName('service_weborder_item'), 
                        array('LineTaxAmount' => $newTaxAmount), 
                    "WebOrderId = '{$webOrderId}' AND WebOrderItemId = '{$weborderItemId}'");
                }

            }

        }
    }
}