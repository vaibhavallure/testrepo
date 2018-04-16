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
        /**/$createdAtLimitation = '2018-03-01';
        if( $this->_order->getCreatedAt() < $createdAtLimitation )
        {
            return false;
        }/**/
        if( !(Mage::helper('teamwork_transfer/webstaging')->isChqZoneUsedAsProcessing()) )
        {
            return false;
        }
        $allowAuthorizeOnly = Mage::helper('teamwork_transfer/webstaging')->allowAuthorizeOnlyPayment( $this->_order->getPayment()->getMethod(), $this->_getChannelId() );
        $authorizedAmount = floatval($this->_order->getPayment()->getAmountAuthorized());
        $paidAmount = floatval( $this->_order->getPayment()->getAmountPaid() );
        
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
                if( (!(float)$this->_order->getGrandTotal() || ($paidAmount || ($allowAuthorizeOnly && $authorizedAmount))) && $this->_order->getStatus() != Mage_Sales_Model_Order::STATUS_FRAUD )
                {
                    return true;
                }
            break;
        }
        return false;
    }
}