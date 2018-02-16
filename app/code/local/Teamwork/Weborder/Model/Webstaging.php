<?php
class Teamwork_Weborder_Model_Webstaging extends Mage_Core_Model_Abstract
{
    /**
     * Magento order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
    public $twWeborderId = 'tw_guid';
    
    /**
     * Product tax accumulator to fix float rounding bug 
     *
     * @var string
     */
    protected $_lineTaxAmountAccumulator = 0;

    public function createWebOrder($order)
    {
        $this->_order = $order;
        return $this->_createWebOrder();
    }
    
    protected function _createWebOrder()
    {
        $billing = $this->_order->getBillingAddress();
        $shipping = $this->_order->getShippingAddress();
        $orderNo = $this->_order->getIncrementId();
        $billBirthday = $billing->getCustomerDob() ? date("Y-m-dTH:i:s", strtotime($billing->getCustomerDob())) : '1753-01-01T00:00:00';
        $shipBirthday = empty($shipping) ? '' : ($shipping->getCustomerDob() ? date("Y-m-dTH:i:s", strtotime($shipping->getCustomerDob())) : '1753-01-01T00:00:00');

        $data = array(
            'DefaultLocationId'     => '',
            'EComShippingMethod'    => $this->_getShippingMethod(),
            'OrderNo'               => $orderNo,
            'WebOrderId'            => $this->_order->getData($this->twWeborderId),
            'Status'                => $this->_order->getStatus(),
            'GuestCheckout'         => (int)$this->_order->getCustomerIsGuest(),
            
            'EComCustomerId'        => $this->_order->getCustomerEmail(),
            
            'BillFirstName'         => $billing->getFirstname(),
            'BillLastName'          => $billing->getLastname(),
            'BillMiddleName'        => $billing->getMiddlename(),
            'BillGender'            => $billing->getCustomerGender() ? $billing->getCustomerGender() : 'None',
            'BillBirthday'          => ((empty($billBirthday) || substr($billBirthday, 0, 4) == '0000') ? '' : $billBirthday),
            'BillEmail'             => $billing->getEmail() ? $billing->getEmail() : $this->_order->getCustomerEmail(),
            'BillPhone'             => $billing->getTelephone(),
            'BillMobilePhone'       => '',
            'BillCompany'           => $billing->getCompany(),
            'BillAddress1'          => $billing->getStreet(1),
            'BillAddress2'          => $billing->getStreet(2),
            'BillCity'              => $billing->getCity(),
            'BillCountry'           => $billing->getCountry(),
            'BillPostalCode'        => $billing->getPostcode(),
            'BillState'             => $billing->getRegion(),

            'ShipFirstName'         => empty($shipping) ? '' : $shipping->getFirstname(),
            'ShipLastName'          => empty($shipping) ? '' : $shipping->getLastname(),
            'ShipMiddleName'        => empty($shipping) ? '' : $shipping->getMiddlename(),
            'ShipGender'            => empty($shipping) ? '' : ($shipping->getCustomerGender() ? $shipping->getCustomerGender() : 'None'),
            'ShipBirthday'          => ((empty($shipBirthday) || substr($shipBirthday, 0, 4) == '0000') ? '' : $shipBirthday),
            'ShipEmail'             => empty($shipping) ? '' : $shipping->getEmail(),
            'ShipPhone'             => empty($shipping) ? '' : $shipping->getTelephone(),
            'ShipMobilePhone'       => '',
            'ShipCompany'           => empty($shipping) ? '' : $shipping->getCompany(),
            'ShipAddress1'          => empty($shipping) ? '' : $shipping->getStreet(1),
            'ShipAddress2'          => empty($shipping) ? '' : $shipping->getStreet(2),
            'ShipCity'              => empty($shipping) ? '' : $shipping->getCity(),
            'ShipCountry'           => empty($shipping) ? '' : $shipping->getCountry(),
            'ShipPostalCode'        => empty($shipping) ? '' : $shipping->getPostcode(),
            'ShipState'             => empty($shipping) ? '' : $shipping->getRegion(),
            'Instruction'           => ''
        );
        return $data;
    }

    /**
     * Get shipping method
     *
     * @return string
     */
    protected function _getShippingMethod()
    {
        $return = $this->_order->getShippingMethod();
        /*fix for webshopapps/matrixrate module*/
        if(stripos($return, 'matrixrate_matrixrate') !== FALSE)
        {
            $return = 'matrixrate_matrixrate';

        }
        return $return;
    }
    
    public function createWebOrderFee()
    {
        return array(array(
            'TaxAmount'     => $this->floatSubtraction($this->_order->getShippingTaxAmount(), $this->_lineTaxAmountAccumulator),
            'UnitPrice'     => $this->_order->getShippingAmount(),
            'Qty'           => 1
        ));
    }

    public function createWebOrderDiscount()
    {
        return $this->_createWebOrderDiscount();
    }

    protected function _createWebOrderDiscount()
    {
        $orderDiscount = array(
            'GlobalDiscountAmount'   => null,
        );

        $discountAmount = abs((float)$this->_order->getShippingDiscountAmount());
        if( !empty($discountAmount) )
        {
            $orderDiscount['GlobalDiscountAmount'] = $discountAmount;
        }
        return $orderDiscount;
    }

    public function getWebOrderItemDiscount($orderItem)
    {
        $itemDiscount = array(
            'LineDiscountAmount' => null,
        );
        
        $discountAmount = floatval( $orderItem->getData('discount_amount') );
        if( !empty($discountAmount) )
        {
            $itemDiscount['LineDiscountAmount'] = $discountAmount;
        }
        return $itemDiscount;
    }

    public function createWebOrderItems()
    {
        return $this->_createWebOrderItems();
    }

    protected function _createWebOrderItems()
    {
        $items = $this->_getOrderItems();
        $result = array();
        if(!empty($items))
        {
            $identifierSource = Mage::getStoreConfig(Teamwork_Weborder_Model_Source::ADMIN_MINIMAL_SETTING_MAGENTO_ATTRIBUTE);
            
            $lineNo = 1;
            foreach($items as $item)
            {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
                if( empty($product) && $item->getChildId() )
                {
                    $product = Mage::getModel('catalog/product')->load($item->getChildId());
                }
                elseif( empty($product) && !$item->getChildId() )
                {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                }
                
                if(!empty($product) && $product->getId())
                {
                    $price = $item->getPrice();
                    $lineTaxAmount = $item->getTaxAmount();
                    
                    /*fix round bug*/
                    $qty = $item->getQtyOrdered();
                    $expectedTotalItemPrice = round($qty * $price + $lineTaxAmount, 2);
                    $price = round($price, 2);
                    /*php float calculation bug fix*/
                    $lineTaxAmount = $this->floatSubtraction($expectedTotalItemPrice, ($qty * $price));
                    $this->_lineTaxAmountAccumulator += $lineTaxAmount;

                    $data = array(
                        'ItemIdentifier'         => $product->getData($identifierSource),
                        'OrderQty'               => $qty,
                        'СancelledQty'           => max( $item->getQtyCanceled(), $item->getQtyRefunded() ),
                        'ShippedQty'             => $item->getQtyShipped(),
                        'UnitPrice'              => $price,
                        'LineTaxAmount'          => $lineTaxAmount,
                        'LineNo'                 => $lineNo++,
                        'OrderItemId'            => $item->getItemId(),
                        'IsVirtual'              => $item->getIsVirtual() ? 1 : 0,
                        'Notes'                  => $this->createNote($item),
                    );
                    /* print_r($data); */
                    $result[] = array(
                        'weborder_item_data'    => $data,
                        'order_item'            => $item,
                    );
                }
            }
        }
        
        /* exit(); */
        return $result;
    }
    
    public function createWebOrderPayment()
    {
        return $this->_createWebOrderPayment();
    }
    
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
        
        $result = array();
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

            if($cc->getCcExpMonth() == '')
            {
                $CcExpMonth = 0;
            }
            else
            {
                $CcExpMonth = $cc->getCcExpMonth();
            }

            if($cc->getCcExpYear() == '')
            {
                $CcExpYear = 0;
            }
            else
            {
                $CcExpYear = $cc->getCcExpYear();
            }

            $billing = $this->_order->getBillingAddress();

            $transactionId = null;
            $transCollection = Mage::getResourceModel('sales/order_payment_transaction_collection');
            $transCollection->addOrderIdFilter($this->_order->getId());

            foreach($transCollection->load() as $trans)
            {
                if(!$trans->getParentTxnId())
                {
                    $transactionId = $trans->getTxnId();
                }
            }

            $data = array(
                'CardType'                  => $cardType,
                'EComPaymentMethod'         => $payment->getMethod(),
                'AccountNumber'             => $this->_getAccountNumber($cc, $payment),
                'PaymentAmount'             => (float)$payment->getAmountPaid(),
                'CardExpMonth'              => $CcExpMonth,
                'CardExpYear'               => $CcExpYear,
                'MerchantId'                => null,
                'CardOrderId'               => null,
                'ReferenceNum'              => null,
                'TransactionId'             => $transactionId,
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
            
            $result[] = $data; 
        }
        return $result;
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

    /**
     * Get message logger object
     * 
     * @return Teamwork_Transfer_Helper_Log
     */
    protected function _getLogger()
    {
        if (is_null($this->__logger))
        {
            $this->__logger = Mage::helper('teamwork_weborder/log');
        }
        return $this->__logger;
    }
    
    /**
     * Safe php float subtract operation
     *
     * @param float $left_operand
     * @param float $right_operand
     *
     * @return string|float
     */
    public function floatSubtraction($left_operand, $right_operand)
    {
        /*use safe subtraction if php bcsub function is available in current php realization*/
        if(function_exists('bcsub'))
        {
            $result = bcsub(floatval($left_operand), floatval($right_operand), 6);
        }
        /*will be better to make round due possible php subtract problem in other case*/
        else
        {
            $result = round(floatval($left_operand) - floatval($right_operand), 6);
        }
        return $result;
    }
    
    public function createNote($item)
    {
        $note = '';
        
        $itemOptions = $item->getProductOptions();
        if(!empty($itemOptions['options']) && is_array($itemOptions['options']))
        {
            $currentItemOptions = current($itemOptions['options']);
            $note = $currentItemOptions['label'] . ' ' . $currentItemOptions['value'];
        }
        return $note;
    }
    
    public function createCreditMemos()
    {
        $creditMemos = Mage::getResourceModel('sales/order_creditmemo_collection')->addFieldToFilter('order_id', $this->_order->getId());
        $result = array();
        foreach($creditMemos as $creditMemo)
        {
            $result[$creditMemo->getId()] = array(
                'refund_shipping'       => $creditMemo->getShippingAmount(),
                'adjustment_refund'     => $creditMemo->getAdjustmentPositive(),
                'adjustment_fee'        => $creditMemo->getAdjustmentNegative()
            );
        }
        return $result;
    }
    
    public function createShippments()
    {
        $result = array();
        if( $this->_order->hasShipments() )
        {
            $shipments = $this->_order->getShipmentsCollection();
            foreach($shipments as $shipment)
            {
                $result[$shipment->getId()] = array('date' => $shipment->getCreatedAt(), 'trackings' => array(), 'items' => array());
                foreach($shipment->getAllItems() as $item)
                {
                    if( !$item->getOrderItem()->isDummy(true) )
                    {
                        $result[$shipment->getId()]['items'][] = array(
                            'ItemIdentifier'    => $item->getData(Mage::getStoreConfig(Teamwork_Weborder_Model_Source::ADMIN_MINIMAL_SETTING_MAGENTO_ATTRIBUTE)),
                            'OrderItemId'       => $item->getOrderItemId(),
                            'ShippedQty'        => $item->getQty(),
                        );
                    }
                }
                foreach($shipment->getAllTracks() as $tracking)
                {
                    $result[$shipment->getId()]['trackings'][] = $tracking->getTrackNumber();
                }
            }
        }
        return $result;
    }
}