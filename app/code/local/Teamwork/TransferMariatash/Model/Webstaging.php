<?php
class Teamwork_TransferMariatash_Model_Webstaging extends Teamwork_CEGiftcards_Transfer_Model_Webstaging
{
	protected function _createWebOrder()
    {
        $channelId = $this->_getChannelId();
        if(!empty($channelId))
        {
            $billing = $this->_order->getBillingAddress();
            $shipping = $this->_order->getShippingAddress();
            $orderNo = $this->_order->getIncrementId();

            $weborder = new Varien_Object();
			/*Send Magento Status to CHQ to the Sales Order Line - CustomText1*/
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
					'CustomText1'			=> $this->_order->getStatus(),
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

	public function isValidForChq($completedOnly)
    {
		/**/$createdAtLimitation = '2018-04-04';
        if( $this->_order->getCreatedAt() < $createdAtLimitation )
        {
            return false;
        }/**/
        if( !(Mage::helper('teamwork_transfer/webstaging')->isChqZoneUsedAsProcessing()) )
        {
            return false;
        }
        $allowAuthorizeOnly = Mage::helper('teamwork_transfer/webstaging')->allowAuthorizeOnlyPayment( $this->_order->getPayment()->getMethod(), $this->_getChannelId() );
        $authorizedAmount = floatval($this->_order->getPayment()->getBaseAmountAuthorized()); /**/
        $paidAmount = floatval( $this->_order->getPayment()->getBaseAmountPaid() );/**/

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
                if( (!(float)$this->_order->getBaseGrandTotal() ||($paidAmount || ($allowAuthorizeOnly && $authorizedAmount))) && $this->_order->getStatus() != Mage_Sales_Model_Order::STATUS_FRAUD )/**/
                {
                    return true;
                }
            break;
        }
        return false;
    }

    public function generateWeborderFromOrder($order)
    {
        Mage::helper('teamwork_service')->fatalErrorObserver();
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
                if ($this->isValidForChq($completedOnly) ) {
                    $this->_createWebOrder();

                    if (!empty($this->_webOrderId)) {
                        $this->_createWebOrderDiscount();
                        /*fix for possible customization to prevent extra items taxes accumulation*/
                        $this->_lineTaxAmountAccumulator = 0;
                        $this->_createWebOrderItems(); /*should be before $this->_createWebOrderFee()*/
                        $this->_createWebOrderFee();
                        $this->_createWebOrderItemsDiscount(); /*should be after _createWebOrderItems*/
                        $this->_createWebOrderPayment();

                        $this->_db->update( /**/
                            Mage::getSingleton('core/resource')->getTableName('service_weborder'),
                            array('IsReady' => 1),
                            "WebOrderId = '{$this->_webOrderId}'"
                        );
                    }
                    Mage::log("generateWeborderFromOrder:: ORDER #".$this->_order->getIncrementId().", ORDER ID: ".$this->_order->getIncrementId().", STATUS: ".$this->_order->getStatus(),Zend_log::DEBUG,'change_status.log',true);
					Mage::dispatchEvent('sent_in_chq', array('order' => $this->_order));
                }
            }
            catch(Exception $e)
            {
                $this->_getLogger()->addException($e);
            }
        }

        return $this;
    }

    public function getShippingFeeId()
    {
        /**/
		$select = $this->_db->select()
            ->from(array('setship' => Mage::getSingleton('core/resource')->getTableName('service_setting_shipping')), array('feemap.fee_id'))
            ->join(array('feemap' => Mage::getSingleton('core/resource')->getTableName('service_fee_mapping')), 'setship.entity_id = feemap.shipping_id')
        ->where('setship.name = ?', trim($this->_getShippingMethod()));
		$feeId = $this->_db->fetchOne($select);

		if(!empty($feeId))
		{
			return $feeId;
		}
		/**/

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
}
