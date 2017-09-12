<?php
/**
 * @author allure
 */
class Allure_Counterpoint_Model_Order_Api extends Mage_Api_Model_Resource_Abstract{
    
    protected $_ctpnt_logs_file_name = "counterpoint_api";
    
    /**
     * @param string $counterpoint_data
     * @return number
     */
    public function test($counterpoint_data){
        
        $counterpoint_data = utf8_decode($counterpoint_data);
        $counterpoint_data = trim($counterpoint_data,'"');
        $counterpoint_data = stripslashes($counterpoint_data);
        
        $counterpointData = unserialize($counterpoint_data);
        
        Mage::log("Total order-".count($counterpointData),
            Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
        $this->importCPSQLOrderIntoMagento($counterpointData);
        $counterpointData = null;
        return 1;
    }
    
    /**
     * @param array $counterpointOrderArr
     */
    private function importCPSQLOrderIntoMagento($counterpointOrderArr){
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try{
           $count = 1;
            foreach ($counterpointOrderArr as $order_id_key => $order_data_arr){
                $ctrpnt_order_id = $order_id_key;//$this->getCounterpointOrderId($order_id_key);
                $this->createOrderByUsingCounterpointData($ctrpnt_order_id, $order_data_arr);
                Mage::log("count no-".$count,
                    Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                $count++;
            }
            $counterpointOrderArr = null;
            Mage::log("Finish...",
                Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
        }catch (Exception $e){
            Mage::log("Exception in importCPSQLOrderIntoMagento",Zend_log::DEBUG,
                $this->_ctpnt_logs_file_name,true);
            Mage::log("Exception-".$e,Zend_log::DEBUG,
                $this->_ctpnt_logs_file_name,true);
            $e = null;
        }
    }
    
    /**
     * @param string $ctpnt_order_id_key
     * @return string
     */
    private function getCounterpointOrderId($ctpnt_order_id_key){
        $orderIdArr = explode("-", $ctpnt_order_id_key);
        $_counterpoint_order_id = "".$orderIdArr[0];
        if(strlen($_counterpoint_order_id) < 2 && !empty($orderIdArr[1]))
            $_counterpoint_order_id = "".$orderIdArr[1];
        return $_counterpoint_order_id;
    }
    
    /**
     * @param string $ctpnt_order_id
     * @param array $ctpnt_order_data
     */
    private function createOrderByUsingCounterpointData($ctpnt_order_id,
        $ctpnt_order_data){
        try{
            $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'increment_id');
            if(!$orderObj->getId()){
               $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'counterpoint_order_id');
                if(!$orderObj->getId()){
                     Mage::log("counterpoint order_id:-".$ctpnt_order_id." not present in magento.",
                        Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                    
                     $productsArr = $ctpnt_order_data['item_detail'];
                    
                     if(count($productsArr)>0){
                        $customerDetailArr = $ctpnt_order_data['customer_detail'];
                        $customer = $this->insertCustomer($customerDetailArr);
                        $billingAddress = $this->getBillingAddressOfCtpnt($customer,$customerDetailArr);
                        
                        $_store_id = 1; //main website
                        $quoteObj = Mage::getModel('sales/quote')->assignCustomer($customer);
                        $quoteObj = $quoteObj->setStoreId($_store_id);
                        
                        foreach ($productsArr as $value){
                            $sku = strtoupper($value['sku']);
                            $qty = $value['qty'];
                            $productObj = Mage::getModel('catalog/product');
                            $productObj->setTypeId('simple');
                            $productObj->setTaxClassId(1);
                            $productObj->setSku($sku);
                            $productObj->setName($value['pname']);
                            $productObj->setShortDescription($value['pname']);
                            $productObj->setDescription($value['pname']);
                            $productObj->setPrice($value['prc']);
                                
                            $quoteItem = Mage::getModel("allure_counterpoint/item")
                                ->setProduct($productObj);
                            $quoteItem->setQty($qty);
                                //Mage::log($sku,Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                            $quoteObj->addItem($quoteItem);
                            $productObj = null;
                        }
                        
                        $quoteBillingAddress = Mage::getModel('sales/quote_address');
                        $quoteBillingAddress->setData($billingAddress);
                        $quoteObj->setBillingAddress($quoteBillingAddress);
                        //if product is not virtual
                            if (!$quoteObj->getIsVirtual()) {
                                $shippingAddress = $billingAddress;
                                $quoteShippingAddress = Mage::getModel('sales/quote_address');
                                $quoteShippingAddress->setData($shippingAddress);
                                $quoteObj->setShippingAddress($quoteShippingAddress);
                                // fixed shipping method
                                $quoteObj->getShippingAddress()->setShippingMethod('webpos_shipping_storepickup');
                                //$quoteObj->getShippingAddress()->setCollectShippingRates(true);
                                //$quoteObj->getShippingAddress()->collectShippingRates();
                            }
                            $quoteObj->collectTotals();
                            
                            //$quoteObj->save();
                            $transaction = Mage::getModel('core/resource_transaction');
                            if ($quoteObj->getCustomerId()) {
                               // $transaction->addObject($quoteObj->getCustomer());
                            }
                            
                            $quoteObj->setIsActive(0);
                            
                            //$transaction->addObject($quoteObj);
                            $quoteObj->reserveOrderId();
                            
                            $quoteObj->setCreateOrderMethod(1); //order status as counterpoint 1
                            $quoteObj->setCounterpointOrderId($ctpnt_order_id);
                            $quoteObj->setOrderType("Counterpoint");
                            
                            $incrementIdQ = $quoteObj->getReservedOrderId();
                            if($incrementIdQ){
                                $incrementIdQ = "CP-".$incrementIdQ;
                                $quoteObj->setReservedOrderId($incrementIdQ);
                            }
                            
                            $ccInfo = array();
                            // assign payment method
                            $payment_method = 'codforpos';
                            
                            $quotePaymentObj = $quoteObj->getPayment();
                            $quotePaymentObj->setMethod($payment_method);
                            $quoteObj->setPayment($quotePaymentObj);
                            
                            $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
                            if ($quoteObj->getIsVirtual()) {
                                $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
                            } else {
                                $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
                            }
                            
                            $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
                            
                            $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
                            $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                            if (!$quoteObj->getIsVirtual()) {
                                $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                            }
                            
                            $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                            
                            $items=$quoteObj->getAllItems();
                            
                            foreach ($items as $item) {
                                //@var $item Mage_Sales_Model_Quote_Item
                                $orderItem = $convertQuoteObj->itemToOrderItem($item);
                                if ($item->getParentItem()) {
                                    $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                                }
                                //refunded code
                                if($orderItem->getData('qty_ordered') < 0){
                                    $qtyItem = $orderItem->getData('qty_ordered');
                                    if($qtyItem < 0){
                                        $qtyItem = $qtyItem * (-1);
                                    }
                                    $orderItem->setData('qty_ordered',$qtyItem);
                                    $orderItem->setData('qty_refunded',$qtyItem);
                                    $orderItem->setData('qty_canceled',$qtyItem);
                                   /*  $orderItem->setData('qty_ordered',1);
                                    $orderItem->setData('qty_refunded',1);
                                    $orderItem->setData('qty_canceled',1); */
                                }
                                $orderObj->addItem($orderItem);
                            }
                            
                            $orderObj->setCanShipPartiallyItem(false);
                            
                            $totalDue = $orderObj->getTotalDue();
                            
                            $extraOrderDetails = $ctpnt_order_data['order_detail'];
                            $totalAmmount = $quoteObj->getGrandTotal();
                            $taxAmmount = $extraOrderDetails['tax'];
                            $discountAmount = $extraOrderDetails['dis_amount'];
                            
                            
                            if(1){
                                $totalAmmount =$totalAmmount + $taxAmmount;
                                $orderObj->setTaxAmount($taxAmmount);
                            }
                            if($discountAmount){
                                $discountAmount = 0-$discountAmount;
                                $totalAmmount = $totalAmmount + $discountAmount;
                                $orderObj->setDiscountAmount($discountAmount);
                            }
                            
                            $orderObj->setGrandTotal($totalAmmount);
                            $orderObj->setBaseTaxAmount($taxAmmount);
                            $orderObj->setBaseGrandTotal($totalAmmount);
                            $orderObj->setTotalPaid($totalAmmount);
                            
                            //complete the order status
                            $orderObj->setData('state','complete')
                                ->setData('status','complete');
                            
                            $orderObj->setCreatedAt($extraOrderDetails['order_date']);
                            
                            
                            try {
                                //$transaction->save();
                                $orderObj->save();
                                $quoteObj->save();
                                
                                $increment_id = $orderObj->getRealOrderId();
                                
                                Mage::log("New order id-".$increment_id,Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                                
                                //create invoice for created order
                                $ordered_items = $orderObj->getAllItems();
                                $savedQtys = array();
                                foreach($ordered_items as $item){     //item detail
                                    $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                                }
                                $invoice = Mage::getModel('sales/service_order', $orderObj)->prepareInvoice($savedQtys);
                                $captureCase = "offline";
                                $invoice->setRequestedCaptureCase($captureCase);
                                $invoice->register();
                                $invoice->getOrder()->setIsInProcess(true);
                                
                                $invoice->setState(2);
                                $invoice->setCanVoidFlag(0);
                                
                                $invoice->save();
                                
                                $shipmentId = $this->createShipment($increment_id);
                                
                                $invoice = null;
                                $quoteObj = null;
                                $orderObj = null;
                                $customer = null;
                                $billingAddress = null;
                                
                            } catch (Exception $e){
                                Mage::log("Trans Exception-".$e->getMessage(), Zend_Log::DEBUG,$this->_ctpnt_logs_file_name,true);
                                Mage::throwException('Order Cancelled.');
                                $e = null;
                            }
                    }
                    
                }else{
                    Mage::log("counterpoint order_id:-".$ctpnt_order_id." already created in magento.",
                        Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                }
            }else{
                Mage::log("counterpoint order_id:-".$ctpnt_order_id." present in magento.",
                    Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
            }
                
            
        }catch (Exception $e){
            Mage::log("Exception in createOrderByUsingCounterpointData method of Class name is".get_class($this),
                Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
            Mage::log($e->getMessage(),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
            $e = null;
        }
        $orderObj = null;
    }
    
    
    /**
     * Create new shipment for order
     *
     * @param string $orderIncrementId
     * @param array $itemsQty
     * @param string $comment
     * @param booleam $email
     * @param boolean $includeComment
     * @return string
     */
    private function createShipment($orderIncrementId, $itemsQty = array(), $comment = null, $email = false,
        $includeComment = false
        ) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            
            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_fault('order_not_exists');
            }
            
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_fault('data_invalid', Mage::helper('sales')->__('Cannot do shipment for order.'));
            }
            
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            $shipment = $order->prepareShipment($itemsQty);
            if ($shipment) {
                $shipment->register();
                $shipment->addComment($comment, $email && $includeComment);
                if ($email) {
                    $shipment->setEmailSent(true);
                }
                $shipment->getOrder()->setIsInProcess(true);
                try {
                    $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                    $shipmentId = $shipment->getIncrementId();
                } catch (Mage_Core_Exception $e) {
                    $this->_fault('data_invalid', $e->getMessage());
                    $e = null;
                }
                $shipment = null;
                $order = null;
                return null;//$shipment->getIncrementId();
            }
            return null;
    }
    
    private function generateRandomPassword(){
        $alphabets = range('A','Z');
        $numbers = range('0','9');
        $additional_characters = array('#','@','$');
        $final_array = array_merge($alphabets,$numbers,$additional_characters);
        
        $password = '';
        $length = 6;  //password length
        while($length--) {
            $keyV = array_rand($final_array);
            $password .= $final_array[$keyV];
        }
        return $password;
    }
    
    private function getBillingAddressOfCtpnt($customer,$customerDetailArr){
        $billingAddress = array
        (
            'email' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'telephone' => $customerDetailArr['phone'],
            'street' => $customerDetailArr['street'],
            'country_id' => $customerDetailArr['country']?$customerDetailArr['country']:"",
            'city' => $customerDetailArr['city'],
            'postcode' => $customerDetailArr['zip_code'],
            //'region_id' => "12",
            //'region' => 'California',
            'company' => "",
            'fax' => "",
            'save_in_address_book' => 1
        );
        return $billingAddress;
    }
    
    private function insertCustomer($customerDetailArr){
        
        $email      = $customerDetailArr['email'];
        $street     = $customerDetailArr['street'];
        $city       = $customerDetailArr['city'];
        $state      = $customerDetailArr['state'];
        $country    = $customerDetailArr['country']?$customerDetailArr['country']:"";
        $zip_code   = $customerDetailArr['zip_code'];
        $phone      = $customerDetailArr['phone'];
        $name       = $customerDetailArr['name'];
        
        $name       = explode(" ", $name);
        $firstName  = $name[0];
        $lastName   = $name[0];
        if(count($name) >   1)
            $lastName = $name[1];
        
        if(empty($email)){
            $emailName = $firstName."".$lastName;
            $email = strtolower($emailName)."@mariatash.com";
        }
        $email = strtolower($email);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
             ->loadByEmail($email);
             
        if(!$customer->getId()){
            $groupId = 1;
            $storeId = 1;
            $websiteId = 1;
            
            $password = $this->generateRandomPassword();
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($websiteId)
                ->setStoreId($storeId)
                ->setGroupId($groupId)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setPassword($password)
                ->setCustomerType(1)  //counterpoint
                ->save();
                
            $_custom_address = array (
                'firstname'  => $customer->getFirstname(),
                'lastname'   => $customer->getLastname(),
                'street'     => array (
                    '0' => $street
                ),
                'city'       => $city,
                'postcode'   => $zip_code,
                'country_id' => $country,
                'region' 	=> 	$state,
                'telephone'  => $phone,
                'fax'        => '',
            );
            
            $address = Mage::getModel("customer/address");
            $address->setData($_custom_address)
                ->setCustomerId($customer->getId())
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            $address->save();
            
            Mage::log("New customer create.customer_id:".$customer->getId(),
                Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
        }
        return $customer;
    }
    
}