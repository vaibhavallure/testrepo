<?php
class Teamwork_Weborder_Model_Weborder extends Mage_Core_Model_Abstract
{
    protected $modifiedFromDate, $_webOrders, $_db;
    protected $_payment = array(
        'CreditCard'            => array('EComPaymentMethod', 'AccountNumber', 'CardExpMonth', 'CardExpYear', 'PaymentAmount', 'MerchantId', 'CardOrderId', 'ReferenceNum', 'TransactionId', 'ListOrder', 'CardType', 'CardholderFirstName', 'CardholderLastName', 'CardholderAddress1', 'CardholderAddress2', 'CardholderCity', 'CardholderState', 'CardholderCountryCode', 'CardholderPostalCode'),
        'GiftCard'              => array('EComPaymentMethod', 'AccountNumber', 'PaymentAmount', 'TransactionId', 'ListOrder', 'CardholderFirstName', 'CardholderLastName', 'CardholderAddress1', 'CardholderAddress2', 'CardholderCity', 'CardholderState', 'CardholderCountryCode', 'CardholderPostalCode'),
        'LoyaltyRewardPoint'    => array('EComPaymentMethod', 'AccountNumber', 'PaymentAmount', 'TransactionId', 'ListOrder', 'LoyaltyRewardPointAmount')
    );
    protected $_custom = array('CustomDate', 'CustomFlag', 'CustomLookupValue', 'CustomNumber', 'CustomInteger', 'CustomText');
    protected $_orderLimit = 300;

    public function _construct()
    {
        //header('Content-Type: text/xml');
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function generateXml($params, $order=null)
    {
        $this->prepareParams($params);
        $webOrders = '<?xml version="1.0" encoding="UTF-8"?><WebOrders xmlns="http://microsoft.com/wsdl/types/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"></WebOrders>';
        $this->_webOrders = new SimpleXMLElement($webOrders);
        
        $orderCollection = array();
        if(!$order)
        {
            $orderCollection = Mage::getModel('sales/order')->getCollection();
            if( !empty($this->modifiedFromDate) )
            {
                $orderCollection->addFieldToFilter('updated_at', array('gteq' => $this->modifiedFromDate));
            }
            $orderCollection->addOrder('updated_at', Varien_Data_Collection::SORT_ORDER_ASC);
            $orderCollection->setPageSize($this->_orderLimit);
            $orderCollection->setCurPage(1);
        }
        else
        {
            if( $order->getData() )
            {
                $orderCollection = array($order);
            }
        }

        foreach($orderCollection as $order)
        {
            $amountPaid = (float)$order->getPayment()->getAmountPaid();
            $grandTotal = (float)$order->getPayment()->getGrandTotal();
            if( (empty($amountPaid) && $grandTotal>0) || $order->getPayment()->getStatus() == Mage_Sales_Model_Order::STATUS_FRAUD )
            {
                continue;
            }
            $webOrderDataGenerator = Mage::getModel('teamwork_weborder/webstaging');
            $result = $webOrderDataGenerator->createWebOrder($order);
            if (!empty($result))
            {
                $webOrder = $this->createAttrNode($this->_webOrders, 'WebOrder', array(
                    'OrderNo'           => $result['OrderNo'],
                    'WebOrderId'        => $result['WebOrderId'],
                    'OrderDate'         => date("Y-m-d\TH:i:s", strtotime($order->getCreatedAt())),
                    'ModifiedDate'      => date("Y-m-d\TH:i:s", strtotime($order->getUpdatedAt())),
                    'Status'            => $result['Status'],
                    'GuestCheckout'     => $result['GuestCheckout'] ? 'true' : 'false'
                ),false);
                $this->generateWebOrder($webOrder, $result, $webOrderDataGenerator);
                $this->generateCustomer($webOrder, $result);
                $this->generatePayment($webOrder, $webOrderDataGenerator);
                $this->generateGlobalFees($webOrder, $webOrderDataGenerator);
                $this->generateWebOrderItemsGroups($webOrder, $webOrderDataGenerator);
                $this->generateWebOrderCreditMemos($webOrder, $webOrderDataGenerator);
                $this->generateShippings($webOrder, $webOrderDataGenerator);
                $this->generateInstruction($webOrder, $result);
            }
            unset($webOrderDataGenerator);
            unset($result);
        }

        return base64_encode($this->_webOrders->asXML());
    }
    
    public function resendOrdersToChq($orderIds)
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $orderIds))
        ->setPageSize( $this->_orderLimit );
        $orders->save();
    }

    protected function generateWebOrder(&$webOrder, $result, $webOrderDataGenerator)
    {
        $this->createAttrNode($webOrder, 'DefaultLocation', array('DefaultLocationId' => $result['DefaultLocationId']));
        
        $globalDiscount = $webOrderDataGenerator->createWebOrderDiscount();
        $this->createAttrNode($webOrder, 'GlobalDiscountReason', array(
            'GlobalDiscountAmount' => $globalDiscount['GlobalDiscountAmount']
        ));
        
        if( !empty($result['EComShippingMethod']) )
        {
            $this->createAttrNode($webOrder, 'DefaultShippingMethod', array('EComShippingMethod' => $result['EComShippingMethod']));
        }

        /* foreach($this->_custom as $arr)
        {
            for($i=1;$i<=6;$i++)
            {
                $this->createValNode($webOrder, $arr.$i, Null);
            }
        } */
    }

    protected function generateCustomer(&$webOrder, $result)
    {
        $customer = $this->createAttrNode($webOrder, 'Customer', array('EComCustomerId' => $result['EComCustomerId']), false);
        $result['BillBirthday'] = $result['BillBirthday'] < '1753-01-01 00:00:00' ? '1753-01-01 00:00:00' : $result['BillBirthday'];
        $result['ShipBirthday'] = $result['ShipBirthday'] < '1753-01-01 00:00:00' ? '1753-01-01 00:00:00' : $result['ShipBirthday'];

        $this->createAttrNode($customer, 'BillToInformation', array(
            'FirstName'     =>    $result['BillFirstName'],
            'LastName'      =>    $result['BillLastName'],
            'MiddleName'    =>    $result['BillMiddleName'],
            'Gender'        =>    $result['BillGender'] == 0 ? 'None' : $result['BillGender'],
            'Birthday'      =>    date("Y-m-d\TH:i:s", strtotime($result['BillBirthday'])),
            'Email'         =>    $result['BillEmail'],
            'Phone'         =>    $result['BillPhone'],
            'MobilePhone'   =>    $result['BillMobilePhone'],
            'Company'       =>    $result['BillCompany'],
            'Address1'      =>    $result['BillAddress1'],
            'Address2'      =>    $result['BillAddress2'],
            'City'          =>    $result['BillCity'],
            'Country'       =>    $result['BillCountry'],
            'PostalCode'    =>    $result['BillPostalCode'],
            'State'         =>    $result['BillState']
        ), false);
        $this->createAttrNode($customer, 'ShipToInformation', array(
            'FirstName'     =>    $result['ShipFirstName'],
            'LastName'      =>    $result['ShipLastName'],
            'MiddleName'    =>    $result['ShipMiddleName'],
            'Gender'        =>    $result['ShipGender'] == 0 ? 'None' : $result['ShipGender'],
            'Birthday'      =>    date("Y-m-d\TH:i:s", strtotime($result['ShipBirthday'])),
            'Email'         =>    $result['ShipEmail'],
            'Phone'         =>    $result['ShipPhone'],
            'MobilePhone'   =>    $result['ShipMobilePhone'],
            'Company'       =>    $result['ShipCompany'],
            'Address1'      =>    $result['ShipAddress1'],
            'Address2'      =>    $result['ShipAddress2'],
            'City'          =>    $result['ShipCity'],
            'Country'       =>    $result['ShipCountry'],
            'PostalCode'    =>    $result['ShipPostalCode'],
            'State'         =>    $result['ShipState']
        ), false);
    }

    protected function generatePayment(&$webOrder, $webOrderDataGenerator)
    {
        $results = $webOrderDataGenerator->createWebOrderPayment();
        $payments = $this->createValNode($webOrder, 'Payments', false, false);
        if($results)
        {
            foreach($results as $result)
            {
                if($result['EComPaymentMethod'] == 'teamwork_giftcards')
                {
                    $type = 'GiftCard';
                }
                elseif($result['EComPaymentMethod'] == 'loyaltypoint')
                {
                    $type = 'LoyaltyRewardPoint';
                }
                else
                {
                    $type = 'CreditCard';
                }
                if(!isset($$type))
                {
                    $payment = $this->createValNode($payments, $type.'s', false, false);
                }
                $$type = true;

                foreach($this->_payment[$type] as $v)
                {
                    $array[$v] = $result[$v];
                }
                $this->createAttrNode($payment, $type, $array, false);
            }
        }
    }

    public function generateGlobalFees(&$webOrder, $webOrderDataGenerator)
    {
        $globalFees = $this->createValNode($webOrder, 'GlobalFees', false, false);
        $results = $webOrderDataGenerator->createWebOrderFee();
        if($results)
        {
            foreach($results as $result)
            {
                $this->createAttrNode($globalFees, 'GlobalFee', array(
                    'UnitPrice'     => $result['UnitPrice'],
                    'TaxAmount'     => $result['TaxAmount'],
                    'Qty'           => $result['Qty']
                ), false);
            }
        }
    }

    public function generateWebOrderItemsGroups(&$webOrder, $webOrderDataGenerator)
    {
        $webOrderItemsGroups = $this->createValNode($webOrder, 'WebOrderItemsGroups', false, false);
        $webOrderItemsGroup = $this->createAttrNode($webOrderItemsGroups, 'WebOrderItemsGroup', false, false);
        
        $items = $webOrderDataGenerator->createWebOrderItems();
        $WebOrderItems = $this->createValNode($webOrderItemsGroup, 'WebOrderItems', false, false);
        foreach($items as $item_r)
        {
            $item = $item_r['weborder_item_data'];
            $line_discount = $webOrderDataGenerator->getWebOrderItemDiscount($item_r['order_item']);
            
            $itemFees = false;

            $webOrderItem = $this->createAttrNode($WebOrderItems, 'WebOrderItem', array(
                'ItemIdentifier'    => $item['ItemIdentifier'],
                'OrderQty'          => $item['OrderQty'],
                'СancelledQty'      => $item['СancelledQty'],
                'ShippedQty'        => $item['ShippedQty'],
                'UnitPrice'         => $item['UnitPrice'],
                'LineTaxAmount'     => $item['LineTaxAmount'],
                'OrderItemId'       => $item['OrderItemId'],
                'LineNo'            => $item['LineNo'],
                'IsVirtual'         => $item['IsVirtual'],
            ), false);

            $this->createAttrNode($webOrderItem, 'LineDiscount', array(
                'LineDiscountAmount'    => $line_discount['LineDiscountAmount']
            ));

            $this->createValNodeWithCDATA($webOrderItem, 'Notes', $item['Notes']);

            $fees = $this->createValNode($webOrderItem, 'Fees', false, false);

            /* foreach($this->_custom as $arr)
            {
                for($i=1;$i<=6;$i++)
                {
                    $this->createValNode($webOrderItem, $arr.$i, Null);
                }
            } */
        }
    }
    
    
    public function generateWebOrderCreditMemos(&$webOrder, $webOrderDataGenerator)
    {
        $webOrderItemsGroups = $this->createValNode($webOrder, 'CreditMemos', false, false);
        $creditMemos = $webOrderDataGenerator->createCreditMemos();
        if( !empty($creditMemos) )
        {
            foreach($creditMemos as $creditMemo)
            {
                $webOrderCreditMemo = $this->createValNode($webOrderItemsGroups, 'CreditMemo', false, false);
                $this->createValNode($webOrderCreditMemo, 'RefundShipping', $creditMemo['refund_shipping']);
                $this->createValNode($webOrderCreditMemo, 'AdjustmentRefund', $creditMemo['adjustment_refund']);
                $this->createValNode($webOrderCreditMemo, 'AdjustmentFee', $creditMemo['adjustment_fee']);
            }
        }
    }

    public function generateShippings(&$webOrder, $webOrderDataGenerator)
    {
        $shippingsNode = $this->createValNode($webOrder, 'Shippings', false, false);
        $shippments = $webOrderDataGenerator->createShippments();
        if( !empty($shippments) )
        {
            foreach($shippments as $shippment)
            {
                $shippingNode = $this->createAttrNode($shippingsNode, 'Shipping', array('ShippingDate' => $shippment['date']), false);
                $itemsNode = $this->createValNode($shippingNode, 'WebOrderItems', false, false);
                $trackingNode = $this->createValNode($shippingNode, 'Trackings', false, false);
                foreach($shippment['trackings'] as $tracking)
                {
                    $this->createValNode($trackingNode, 'Tracking', $tracking);
                }
                foreach($shippment['items'] as $itemId => $item)
                {
                    $this->createAttrNode($itemsNode, 'WebOrderItem', $item, false);
                }
            }
        }
    }
    
    public function generateInstruction(&$webOrder, $result)
    {
        $this->createValNode($webOrder, 'Instruction',  htmlspecialchars($result['Instruction']), false);
    }

    public function prepareParams($params)
    {
        if(!empty($params))
        {
            $this->modifiedFromDate = date('Y-m-d H:i:s', strtotime($params));
        }
    }

    public function createAttrNode(&$parent, $child, $attribute=null, $canBeNull=true, $value=false)
    {
        if(!$value)
        {
            $new = $parent->addChild($child);
        }
        else
        {
            $new = $parent->addChild($child, $value);
        }
        
        if($attribute)
        {
            foreach($attribute as $k => $v)
            {
                if(!$v && $canBeNull)
                {
                    $new->addAttribute('xsi:nil', 'true', 'http://www.w3.org/2001/XMLSchema-instance');
                    break;
                }
                else
                {
                    $new->addAttribute($k, $v);
                }
            }
        }
        
        return $new;
    }

    public function createValNodeWithCDATA(&$parent, $child, $value = false)
    {
        $new = $parent->addChild($child);

        if ($new !== NULL)
        {
            $node = dom_import_simplexml($new);
            $no   = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }

        return $new;
    }

    public function createValNode(&$parent, $child, $value=false, $canBeNull=true)
    {
        if($value)
        {
            $new = $parent->addChild($child, $value);
        }
        elseif($canBeNull)
        {
            $new = $parent->addChild($child);
            $new->addAttribute('xsi:nil', 'true', 'http://www.w3.org/2001/XMLSchema-instance');
        }
        else
        {
            $new = $parent->addChild($child);
        }
        return $new;
    }

    public function getTable($table, $where=false, $value=false, $order=false, $select=array('*'), $one=false, $limit=false)
    {
        $table = Mage::getSingleton('core/resource')->getTableName($table);
        $select = $this->_db->select()->from($table, $select);
        if(is_array($where))
        {
            foreach($where as $k=>$v)
            {
                if(!empty($value[$k]))
                {
                    $select->where($where[$k], $value[$k]);
                }
            }
        }
        if($order)
        {
            $select->order($order);
        }
        if($limit)
        {
            $select->limit($limit);
        }

        $results = $this->_db->fetchAll($select);
        if($one)
        {
            return current($results);
        }
        return $results;
    }

    /*public function getOrderLimit()
    {
        return $this->_orderLimit;
    }*/
}