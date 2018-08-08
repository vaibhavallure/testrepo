<?php
class Teamwork_TransferMariatash_Model_Webstaging extends Teamwork_CEGiftcards_Transfer_Model_Webstaging
{
	
	private $ordersForImport = array(
		'2018001966','2018001543','2018001647','2018001755','2018001631','2018001399','2018002235','2018001826','2018001440','2018001297','2018001747','2018002422-B','2018002284','2018001840','2018002224','2018002182','2018002115','2018002380','2018001839','2018001464','2017008503','2017005730','2017004967','2018001295','2018001193','2017006506','2018001124','2017006896','2017006136','2017004946','2018000782','2017005734','2017007860','2017006935','2017005740','2017008552','2017007092','2018000908','2017005151','2017005728','2017004700','2017008052','2018000823','2018000683','2017007830','2017006957','2017008511','2017005063','2017004844','2017008251','2017006820','100003638','2017007902','2017007302','2017008035','2017004771','2017006867','2017004684','2017005783','2018000236','2017004690','2017006987','2017007925','2017007875','201700266','2017007998','2017005715','2017007691','2018000082','2017004642','2018000521','2017008476','2017008461','2017008102','2017004776','2017005836-B','2017004327','2018001140','2017008241-B','2017004974','2017004657','2017007020','2017006638','2017006632','2017006123','2017004538','2017006894','2017008432','2018000018','2017008351','2017007048','2017005026','2017007843','2017004852','2017005851','2017004676','2017006916','2018000054','2017006251','2017005731','2017005002','2017005159','2017008517','2017007051','2018000555','2018000524','2017004901','2018000047','2017008259','2017003780','2017007879','2017004691','2017007542','2017008172','2017006872','2017006995','2017006895','2017005741','2017006331','2017004897','2017008116','2017007286','2017006328','2017005836','2017007738','2017008220','2017005435','2017004894','2017005733','2017006885','2017004858','2017004711','2018000357','2017007111','2017007903','2017004759','2018000700','2017007543','2018001088-B','2017008195','2017004800','2017005726','2017005722','2017004859','2017005721','2017008051','2017006478','2017005754','2017006208','2018001271','2017006613','2017004383','2017008266','2018000881','2017008046','2017007071','100008683','2017004318','2017004850','2017005727','100009174','100009423','201700016','100005760','201700602','100008180','100009497','100005642','2017004035','201700262','100006386','2017003044','100008564','201701649','201700652','201700977','201700104','100008503','2017003584','2017003891','201701703','201700165','201700271-B','2017003575','2017003278-B','100007119','201700589','2017003616-B','2017003464','100007981','201700023','2017003949','201700412','201700257','2017003148-B','100005790','201702894','100006184','201701652','100009171','201701044','2017003967','100006212','100008697','201700506','201700667','201700442','100006558','2017004086-B','100007076','2017003650','2017003741','2017004271-B','2017003535','2017004283','2017003321','100008662','100008176','100007521','100007170','201700448','2017004275','100009457','2017003632','100009438','201700252','100009472','2017003722','100007942','100005529','100009054','100006425','201701346','100008731','201702871','201700235','100008522','201700848','2017003353','201701314','201700255','201700924','100008514','100007829','2017004010','201700675','201700616-B','100007503','201700479','201700492','201700435','100008596','2017003971','201701092','100008258','100008151','100009121','201701241','100009414','2017003046','100007966','100006183','201700426','2017003938-B','2017004169-B','100009144','2017003867-B','100008696','201702787','201700292','201700202-B','100008008','100006230','100009545','201701547','100006756','201700092','100007197','201700722','201701372','100009193','100006231','100007841','2017003066','100007969','201700269','201700117','2017003254','2017003225','2017003592','100008358','100008087','100007531','100009085','100008707','100006871','201701183','100009491','201700547','100006366','100009453','201701082','201700361','100007311','2017003470-B','201700985','201700735','2017004252','100007134','100009449','201700493','201700024','201700512','100009534','100009282','2017003027-B','201701734-B'
	);
	
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
        if( $this->_order->getCreatedAt() < $createdAtLimitation && !in_array($this->_order->getIncrementId(), $this->ordersForImport))
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
                if( (!(float)$this->_order->getBaseGrandTotal() || ($paidAmount || ($allowAuthorizeOnly && $authorizedAmount))) && $this->_order->getStatus() != Mage_Sales_Model_Order::STATUS_FRAUD )/**/
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
                        
                        $this->_db->update( /**/
                            Mage::getSingleton('core/resource')->getTableName('service_weborder'),
                            array('IsReady' => 1),
                            "WebOrderId = '{$this->_webOrderId}'"
                        );
                    }
                    Mage::log("Order::".$this->_order->getIncrementId()." STATUS::".$this->_order->getStatus(),Zend_log::DEBUG,'change_status.log',true);
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
}