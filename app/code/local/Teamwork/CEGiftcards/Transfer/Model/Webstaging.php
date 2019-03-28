<?php

class Teamwork_CEGiftcards_Transfer_Model_Webstaging extends Teamwork_Transfer_Model_Webstaging
{
    public function generateWeborderFromOrder($order)
    {
        $this->_order = $order;
        $channelId = $this->_getChannelId();
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_settings'), array('setting_value'))
            ->where('setting_name = ?', Teamwork_Service_Model_Settings::CONST_COMPLETED_ORDERS)
        ->where('channel_id = ?', $channelId);

        if ($this->isValidForChq($this->_db->fetchOne($select)))
        {
            /*set shipping as billing if no shipping*/
            $billing = $order->getBillingAddress();
            $shipping = $order->getShippingAddress();
            //$old_isSendShipEmail = $this->_isSendShipEmail;
            if (!$shipping && $billing) {
                $shipping = clone $billing;
                $shipping->unsetData('entity_id');
                if (!$shipping->getEmail()) $shipping->setEmail($order->getCustomerEmail());
                $order->setShippingAddress($shipping);
                $this->_isSendShipEmail = true;
            }

            /*generate giftcards if needed*/
            $observerObject = new Varien_Event_Observer();
            $observerObject->setEvent(new Varien_Object());
            $observerObject->getEvent()->setOrder($order);
            Mage::getSingleton("teamwork_cegiftcards/observer")->generateGiftCards($observerObject);
        }
        return parent::generateWeborderFromOrder($order);
    }

    protected function _createWebOrderItems()
    {
        parent::_createWebOrderItems();

        $order = $this->_order;
        $gcItems = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                $gcItems[$item->getId()] = $item;
//                $options = $item->getProductOptions();
//                if (array_key_exists('giftcard_type', $options) 
//                    && $options['giftcard_type'] != Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL) {
//                        $gcItems[$item->getId()] = $item;
//                }
            }
        }
        //if contains gc items
        if (count($gcItems)) {
            //remove gc items from service_weborder_item
            $tableWedOrderItem = Mage::getSingleton('core/resource')->getTableName('service_weborder_item');
            $r = $this->_db->delete($tableWedOrderItem, array('InternalId in (?)'  => array_keys($gcItems), 'WebOrderId = ?' => $this->_webOrderId));

            //fix lineno
            $select = $this->_db->select()
                        ->from($tableWedOrderItem)
                        ->where('WebOrderId = ?', $this->_webOrderId);
            $addedItems = $this->_db->fetchAll($select);
            $webOrderItemsGroupId = "";
            $number = 1;
            foreach($addedItems as $addedItem) {
                $webOrderItemsGroupId = $addedItem['WebOrderItemsGroupId'];
                if ($addedItem['LineNo'] != $number) {
                    $this->_db->update($tableWedOrderItem, array('LineNo' => $number), "WebOrderId = '{$this->_webOrderId}' AND WebOrderItemId = '{$addedItem['WebOrderItemId']}'");
                }
                $number++;

            }
            if (!$webOrderItemsGroupId) {
                $webOrderItemsGroupId = $this->_createGuid();
            }

            $tableItems = Mage::getSingleton('core/resource')->getTableName('service_items');
            
            foreach($gcItems as $gcItem) {

                $itemOptions = $gcItem->getProductOptions();
                $generatedCodes = (isset($itemOptions['giftcard_created_codes']) ? $itemOptions['giftcard_created_codes'] : array());
                $qty = $gcItem->getQtyOrdered();
                $skipCodNumChecking = false;
                if (array_key_exists('giftcard_type', $itemOptions) 
                    && $itemOptions['giftcard_type'] == Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL) {
                        $skipCodNumChecking = true;
                }
                
                if ($skipCodNumChecking || count($generatedCodes) == $qty) {

                    $product = $gcItem->getProduct();

                    $price = $gcItem->getBasePrice();/**/

                    $lineTaxAmount = $gcItem->getBaseTaxAmount();/**/
                    /*fix round bug*/
                    $expectedTotalItemPrice = round($qty * $price + $lineTaxAmount, 2);
                    $price = round($price, 2);
                    /*php float calculation bug fix*/
                    $lineTaxAmount = Mage::helper('teamwork_transfer')->floatSubtraction($expectedTotalItemPrice, ($qty * $price));
                    /* $this->_lineTaxAmountAccumulator += $lineTaxAmount; */

                    $taxPerItem = (intval($lineTaxAmount / $qty * 100) / 100);
                    $taxError = $lineTaxAmount - $taxPerItem * $qty;
                    $note = '';

                    for($i = 0; $i < $qty; $i++) {

                        $webOrderItemId = $this->_createGCGuid($gcItem, $i);

                        //$itemId = $this->_createGCGuid($gcItem, $i + $qty);
                        
                        $select = $this->_db->select()
                            ->from(array('i' => $tableItems), array('item_id' => 'i.item_id'))
                        ->where('i.internal_id = ?', $product->getId());

                        $itemId = $this->_db->fetchOne($select);
                        

                        $data = array(
                            'WebOrderItemId'         => $webOrderItemId,
                            'WebOrderItemsGroupId'   => $webOrderItemsGroupId,
                            'WebOrderId'             => $this->_webOrderId,
                            'InternalId'             => $gcItem->getItemId(),
                            'ItemId'                 => $itemId ? $itemId : "",
                            'OrderQty'               => 1,
                            'UnitPrice'              => $price,
                            'LineTaxAmount'          => $taxPerItem + $taxError,
                            'TrackingNo'             => '',
                            'LineNo'                 => $number++,
                            'Notes'                  => $note ? $note : $price
                        );
                        $taxError = 0;
                        $this->_db->insert($tableWedOrderItem, $data);

                    }
                }


            }
        }

    }

    protected function _createGCGuid($item, $n = 0)
    {
        $data = '';
        $data .= $item->getItemId();
        $data .= $item->getOrderId();
        $data .= $item->getProductId();
        $data .= $item->getSku();
        $data .= $n;

        $hash = strtolower(hash('ripemd128', md5($data)));
        $guid = '' .
                substr($hash, 0, 8) .
                '-' .
                substr($hash, 8, 4) .
                '-' .
                substr($hash, 12, 4) .
                '-' .
                substr($hash, 16, 4) .
                '-' .
                substr($hash, 20, 12) .
                '';
        return $guid;
    }


    protected function _createWebOrderPayment()
    {
        parent::_createWebOrderPayment();
        $order  = $this->_order;


        //get gc paid amount
        $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
        if (!count($appliedGCs) && $order->getQuoteId()) {
            $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addQuoteFilter($order->getQuoteId());
        }
        if (count($appliedGCs)) {
            $table = Mage::getSingleton('core/resource')->getTableName('service_weborder_payment');
            $configObject = Mage::getConfig();
            $paymentCode = (string)$configObject->getNode('teamwork_cegiftcards/payment_name');
            //remove previously added gc and free payments
            $this->_db->delete($table, "WebOrderId='{$this->_webOrderId}' AND (EComPaymentMethod='{$paymentCode}' OR EComPaymentMethod='free')");
            $billing = $this->_order->getBillingAddress();

            foreach ($appliedGCs as $appliedGC) {
//                $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
//                $paidByGC = 0;
//                foreach($invoiceLinkCollection as $invoiceLink) {
//                    $paidByGC += $invoiceLink->getData('amount_used');
//                }

                //$paidByGC = $this->_getPaidByGCs($order, array($appliedGC));
                $paidByGC = $appliedGC->getData("amount");
                if ($paidByGC) {
                    //get transaction ids
                    $transactionIds = array();
                    $transactions = Mage::getModel('teamwork_cegiftcards/giftcard_transaction')->getCollection()->addGCLinkFilter($appliedGC);
                    foreach($transactions as $transaction) {
                        $transactionIds[] = $transaction->getData('transaction_id');
                    }
                    //get gc_code
                    $gcCode = $appliedGC->getData('gc_code');


                    $cardType = "Undefined";
                    $CcExpMonth = 0;
                    $CcExpYear = 0;


                    $data = array(
                        'WebOrderPaymentId'         => $this->_createGuid(),
                        'WebOrderId'                => $this->_webOrderId,
                        'CardType'                  => $cardType,
                        'EComPaymentMethod'         => $paymentCode,
                        'AccountNumber'             => $gcCode,
                        'PaymentAmount'             => $paidByGC,// * Mage::helper('teamwork_transferistore')->getCurrencyValue(),
                        'CardExpMonth'              => $CcExpMonth,
                        'CardExpYear'               => $CcExpYear,
                        'MerchantId'                => null,
                        'CardOrderId'               => null,
                        'ReferenceNum'              => null,
                        'TransactionId'             => implode(';',$transactionIds),
                        'PaymentDate'               => date($this->_timeFormat, Mage::getModel('core/date')->timestamp( $this->_order->getCreatedAt() )),
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

                    $this->_db->insert($table, $data);

                }

            }
        }

    }

//    protected function _getPaidByGCs($order, $appliedGCs = null)
//    {
//        $paidByGCs = 0;
//        if (is_null($appliedGCs)) {
//            $appliedGCs = Mage::getModel('teamwork_cegiftcards/giftcard_link')->getCollection()->addOrderFilter($order);
//        }
//        
//        if (count($appliedGCs)) {
//            foreach ($appliedGCs as $appliedGC) {
//                $invoiceLinkCollection = Mage::getModel('teamwork_cegiftcards/order_invoice_link')->getCollection()->addGCLinkFilter($appliedGC);
//                foreach($invoiceLinkCollection as $invoiceLink) {
//                    $paidByGCs += $invoiceLink->getData('amount_used');
//                }
//            }
//        }
//        return $paidByGCs;
//    }

}
