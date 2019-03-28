<?php
class Teamwork_Service_Model_Weborder extends Mage_Core_Model_Abstract
{
    protected $_channel_id, $_from, $_webOrders, $_webOrderId, $_db;
    protected $_payment = array(
        'CreditCard' => array(
            'EComPaymentMethod', 'AccountNumber', 'CardExpMonth', 'CardExpYear', 'PaymentAmount', 'MerchantId', 'CardOrderId', 'ReferenceNum', 'TransactionId', 'IsCaptured', 'PaymentDate', 'ListOrder', 'CardType', 'CardholderFirstName', 'CardholderLastName', 'CardholderAddress1', 'CardholderAddress2', 'CardholderCity', 'CardholderState', 'CardholderCountryCode', 'CardholderPostalCode'
        ),
        'GiftCard' => array(
            'EComPaymentMethod', 'AccountNumber', 'PaymentAmount', 'TransactionId', 'IsCaptured', 'PaymentDate', 'ListOrder', 'CardholderFirstName', 'CardholderLastName', 'CardholderAddress1', 'CardholderAddress2', 'CardholderCity', 'CardholderState', 'CardholderCountryCode', 'CardholderPostalCode'
        ),
        'LoyaltyRewardPoint' => array(
            'EComPaymentMethod', 'AccountNumber', 'PaymentAmount', 'TransactionId', 'IsCaptured', 'PaymentDate', 'ListOrder', 'LoyaltyRewardPointAmount'
        )
    );
    protected $_custom = array(
        'CustomDate', 'CustomFlag', 'CustomLookupValue', 'CustomNumber', 'CustomInteger', 'CustomText'
    );
    protected $_orderLimit = 300;

    public function _construct()
    {
        header('Content-Type: text/xml');
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_init('service/weborder');
    }

    public function generateXml($params)
    {
        $this->prepareParams($params);
        $webOrders = '<?xml version="1.0" encoding="UTF-8"?><WebOrders xmlns="http://microsoft.com/wsdl/types/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"></WebOrders>';
        $this->_webOrders = new SimpleXMLElement($webOrders);

        $order = empty($this->_from) ? 'DESC' : 'ASC';
		
        $results = $this->getTable('service_weborder', array('EComChannelId = ?', 'ProcessingDate >= ?'), array($this->_channel_id, $this->_from), "ProcessingDate {$order}", array('*'), false, $this->_orderLimit);

        if(is_array($results))
        {
            foreach($results as $result)
            {
                $this->_webOrderId  = $result['WebOrderId'];
                if(empty($this->_channel_id))
                {
                    $this->_channel_id = $result['EComChannelId'];
                }
                $webOrder = $this->createAttrNode($this->_webOrders, 'WebOrder', array(
                    'WebOrderId'        => $result['WebOrderId'],
                    'EComChannelId'     => $this->_channel_id,
                    'OrderNo'           => $result['OrderNo'],
                    'OrderDate'         => date("Y-m-d\TH:i:s", strtotime($result['OrderDate'])),
                    'ProcessedDate'     => date("Y-m-d\TH:i:s", strtotime($result['ProcessingDate'])),
                    'Status'            => $result['Status'],
                    'GuestCheckout'     => $result['GuestCheckout'] ? 'true' : 'false'
                ));

                $this->generateWebOrder($webOrder, $result);
                $this->generateCustomer($webOrder, $result);
                $this->generatePayment($webOrder);
                $this->generateGlobalFees($webOrder);
                $this->generateWebOrderItemsGroups($webOrder);
                $this->generateInstruction($webOrder, $result);
            }
        }
        return base64_encode($this->_webOrders->asXML());
    }

    protected function generateWebOrder(&$webOrder, $result)
    {
        $reason = $this->getTable('service_weborder_discount_reason',    array('WebOrderId = ?'), array($this->_webOrderId), false, array('*'), true);

        $this->createAttrNode($webOrder, 'DefaultLocation', array('DefaultLocationId' => $result['DefaultLocationId']));
        $this->createAttrNode($webOrder, 'GlobalDiscountReason', array(
            'GlobalDiscountReasonId'    => $reason['GlobalDiscountReasonId'],
            'GlobalDiscountAmount'      => $reason['GlobalDiscountAmount']
        ));

        if( !empty($result['EComShippingMethod']) )
        {
            $this->createAttrNode($webOrder, 'DefaultShippingMethod', array('EComShippingMethod' => $result['EComShippingMethod']));
        }

        foreach($this->_custom as $arr)
        {
            for($i=1;$i<=6;$i++)
            {
                $this->createValNode($webOrder, $arr.$i, $result[$arr.$i]);
            }
        }
    }

    protected function generateCustomer(&$webOrder, $result)
    {
        $customer = $this->createAttrNode($webOrder, 'Customer', array('EComCustomerId' => $result['EComCustomerId'], 'CustomerId' => $result['CustomerId']), false);
        $result['BillBirthday'] = $result['BillBirthday'] < '1753-01-01 00:00:00' ? '1753-01-01 00:00:00' : $result['BillBirthday'];
        $result['ShipBirthday'] = $result['ShipBirthday'] < '1753-01-01 00:00:00' ? '1753-01-01 00:00:00' : $result['ShipBirthday'];

        $this->createAttrNode($customer, 'BillToInformation', array(
            'AddressId'     => $result['BillAddressId'],
            'AddressType'   => $result['BillAddressType'],
            'FirstName'     => $result['BillFirstName'],
            'LastName'      => $result['BillLastName'],
            'MiddleName'    => $result['BillMiddleName'],
            'Gender'        => $result['BillGender'] == 0 ? 'None' : $result['BillGender'],
            'Birthday'      => date("Y-m-d\TH:i:s", strtotime($result['BillBirthday'])),
            'Email'         => $result['BillEmail'],
            'Phone'         => $result['BillPhone'],
            'MobilePhone'   => $result['BillMobilePhone'],
            'Company'       => $result['BillCompany'],
            'Address1'      => $result['BillAddress1'],
            'Address2'      => $result['BillAddress2'],
            'City'          => $result['BillCity'],
            'Country'       => $result['BillCountry'],
            'PostalCode'    => $result['BillPostalCode'],
            'State'         => $result['BillState']
        ), false);
        $this->createAttrNode($customer, 'ShipToInformation', array(
            'AddressId'     => $result['ShipAddressId'],
            'AddressType'   => $result['ShipAddressType'],
            'FirstName'     => $result['ShipFirstName'],
            'LastName'      => $result['ShipLastName'],
            'MiddleName'    => $result['ShipMiddleName'],
            'Gender'        => $result['ShipGender'] == 0 ? 'None' : $result['ShipGender'],
            'Birthday'      => date("Y-m-d\TH:i:s", strtotime($result['ShipBirthday'])),
            'Email'         => $result['ShipEmail'],
            'Phone'         => $result['ShipPhone'],
            'MobilePhone'   => $result['ShipMobilePhone'],
            'Company'       => $result['ShipCompany'],
            'Address1'      => $result['ShipAddress1'],
            'Address2'      => $result['ShipAddress2'],
            'City'          => $result['ShipCity'],
            'Country'       => $result['ShipCountry'],
            'PostalCode'    => $result['ShipPostalCode'],
            'State'         => $result['ShipState']
        ), false);
    }

    protected function generatePayment(&$webOrder)
    {
        $results = $this->getTable('service_weborder_payment', array('WebOrderId = ?'), array($this->_webOrderId), 'EComPaymentMethod');
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
                    if($v == 'PaymentDate')
                    {
                        $array[$v] = date("Y-m-d\TH:i:s", strtotime($result[$v]));
                        continue;
                    }
                    $array[$v] = $result[$v];
                }
                $this->createAttrNode($payment, $type, $array, false);
            }
        }
    }

    public function generateGlobalFees(&$webOrder)
    {
        $globalFees = $this->createValNode($webOrder, 'GlobalFees', false, false);
        $results = $this->getTable('service_weborder_fee', array('WebOrderId = ?'), array($this->_webOrderId));
        if($results)
        {
            foreach($results as $result)
            {
                $this->createAttrNode($globalFees, 'GlobalFee', array(
                    'FeeId'         => $result['FeeId'],
                    'UnitPrice'     => $result['UnitPrice'],
                    'TaxAmount'     => $result['TaxAmount'],
                    'Qty'           => $result['Qty']
                ), false);
            }
        }
    }

    public function generateWebOrderItemsGroups(&$webOrder)
    {
        $webOrderItemsGroups = $this->createValNode($webOrder, 'WebOrderItemsGroups', false, false);
        $results = $this->getTable('service_weborder_item', array('WebOrderId = ?'), array($this->_webOrderId), 'WebOrderItemsGroupId', array('WebOrderItemsGroupId'));

        if(!empty($results))
        {
            $ids = array();
            foreach($results as $result)
            {
                if(in_array($result['WebOrderItemsGroupId'], $ids))
                {
                    continue;
                }
                $ids[] = $result['WebOrderItemsGroupId'];

                $webOrderItemsGroup = $this->createAttrNode($webOrderItemsGroups, 'WebOrderItemsGroup', array('WebOrderItemsGroupId' => $result['WebOrderItemsGroupId']));
                $items = $this->getTable('service_weborder_item', array('WebOrderItemsGroupId = ?'), array($result['WebOrderItemsGroupId']));
                $webOrderItems = $this->createValNode($webOrderItemsGroup, 'WebOrderItems', false, false);
                foreach($items as $item)
                {
                    if (empty($item['ItemId']) && !Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_WEBORDER_SECONDARY_ID)) continue;

                    $line_discount    = $this->getTable('service_weborder_item_line_discount', array('WebOrderItemId = ?'), array($item['WebOrderItemId']), false, array('*'), true);
                    $itemFees         = $this->getTable('service_weborder_item_fee', array('WebOrderItemId = ?'), array($item['WebOrderItemId']));

                    $webOrderItem = $this->createAttrNode($webOrderItems, 'WebOrderItem', array(
                        'WebOrderItemId'    => $item['WebOrderItemId'],
                        'ItemId'            => $item['ItemId'],
                        'OrderQty'          => $item['OrderQty'],
                        'UnitPrice'         => $item['UnitPrice'],
                        'LineTaxAmount'     => $item['LineTaxAmount'],
                        'TrackingNo'        => $item['TrackingNo'],
                        'LineNo'            => $item['LineNo'],

                        /*'DeliveryMethod' => (string) $webOrder->DefaultShippingMethod['EComShippingMethod'] == 'freeshipping_freeshipping'
                            ? 'StorePickUp'
                        : 'Ship',*/
                    ), false);

                    if (Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_WEBORDER_SECONDARY_ID))
                    {
                        $webOrderItem->addAttribute('SecondaryId', is_null($item['SecondaryId']) ? '' : $item['SecondaryId']);
                    }

                    $this->createAttrNode($webOrderItem, 'LineDiscount', array(
                        'LineDiscountReasonId'  => $line_discount['LineDiscountReasonId'],
                        'LineDiscountAmount'    => $line_discount['LineDiscountAmount']
                    ));

                    $this->createValNodeWithCDATA($webOrderItem, 'Notes', $item['Notes']);

                    $fees = $this->createValNode($webOrderItem, 'Fees', false, false);
                    if($itemFees)
                    {
                        foreach($itemFees as $itemFee)
                        {
                            $this->createAttrNode($fees, 'Fee', array(
                                'FeeId'         => $itemFee['FeeId'],
                                'UnitPrice'     => $itemFee['UnitPrice'],
                                'TaxAmount'     => $itemFee['TaxAmount'],
                                'Qty'           => $itemFee['Qty']
                            ), false);
                        }
                    }
                    foreach($this->_custom as $arr)
                    {
                        for($i=1;$i<=6;$i++)
                        {
                            $this->createValNode($webOrderItem, $arr.$i, $item[$arr.$i]);
                        }
                    }
                }
            }
        }
    }

    public function generateInstruction(&$webOrder, $result)
    {
        $this->createValNode($webOrder, 'Instruction',  htmlspecialchars($result['Instruction']), false);
        $this->getTable('service_weborder_fee', array('WebOrderId = ?'), array($this->_webOrderId));
    }

    public function prepareParams($params)
    {
        if(!empty($params))
        {
            $params = explode(";", $params);
            $this->_channel_id = $params[0];
            $this->_from = date('Y-m-d H:i:s', strtotime($params[1]));
        }
    }

    public function createAttrNode(&$parent, $child, $attribute=null, $canBeNull=true)
    {
        $new = $parent->addChild($child);
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

    public function getOrderLimit()
    {
        return $this->_orderLimit;
    }
}
