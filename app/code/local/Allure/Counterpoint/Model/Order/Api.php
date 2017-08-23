<?php
/**
 * @author allure
 */
class Allure_Counterpoint_Model_Order_Api extends Mage_Api_Model_Resource_Abstract{
    
    protected $_ctpnt_logs_file_name = "abc";
    
    /**
     * @param string $counterpoint_data
     * @return number
     */
    public function test($counterpoint_data){
        $counterpointData = json_decode($counterpoint_data,true);
        //Mage::log($counterpointData,Zend_log::DEBUG,
          //                                      $this->_ctpnt_logs_file_name,true);
        //Mage::log(count($counterpointData),Zend_log::DEBUG,
            //                                    $this->_ctpnt_logs_file_name,true);
        //foreach ($counterpointData as $orderId=>$data){
            //Mage::log($orderId,Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
       // }
        $this->importCPSQLOrderIntoMagento($counterpointData);
        return 1;
    }
    
    /**
     * @param array $counterpointOrderArr
     */
    private function importCPSQLOrderIntoMagento($counterpointOrderArr){
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try{
           // $connection->beginTransaction();
            foreach ($counterpointOrderArr as $order_id_key => $order_data_arr){
                $ctrpnt_order_id = $this->getCounterpointOrderId($order_id_key);
                $this->createOrderByUsingCounterpointData($ctrpnt_order_id, $order_data_arr);
            }
            //$connection->commit();
        }catch (Exception $e){
           // $connection->rollback();
            Mage::log("Exception in importCPSQLOrderIntoMagento",Zend_log::DEBUG,
                $this->_ctpnt_logs_file_name,true);
            Mage::log("Exception-".$e,Zend_log::DEBUG,
                $this->_ctpnt_logs_file_name,true);
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
        $stratTime=time();
        try{
            $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'increment_id');
            if(!$orderObj->getId()){
                $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'counterpoint_order_id');
                if(!$orderObj->getId()){
                    Mage::log("counterpoint order_id:-".$ctpnt_order_id." not present in magento.",
                        Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                    
                    Mage::log("1:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                    
                    $customerDetailArr = $ctpnt_order_data['customer_detail'];
                    $customer = $this->insertCustomer($customerDetailArr);
                    $billingAddress = $this->getBillingAddressOfCtpnt($customer,$customerDetailArr);
                    Mage::log("2:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                    
                    $_store_id = 1; //main website
                    $quoteObj = Mage::getModel('sales/quote')->assignCustomer($customer);
                    $quoteObj = $quoteObj->setStoreId($_store_id);
                    
                    $productsArr = $ctpnt_order_data['item_detail'];
                    foreach ($productsArr as $value){
                        $sku = strtoupper($value['sku']);
                        $qty = $value['qty'];
                        $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
                        if($productId){
                            $productObj = Mage::getModel('catalog/product')->load($productId);
                        }else {
                            $productObj = Mage::getModel('catalog/product');
                            $productObj->setTypeId('simple');
                            $productObj->setTaxClassId(1);
                            $productObj->setSku($sku);
                            $productObj->setName($value['name']);
                            $productObj->setShortDescription($value['name']);
                            $productObj->setDescription($value['name']);
                            $productObj->setPrice($value['price']);
                        }
                        
                        $params = array();
                        $params['qty'] = $qty;
                        $request = new Varien_Object();
                        $request->setData($params);
                        $quoteObj->addProduct($productObj , $request);
                    }
                    Mage::log("3:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                    
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
                        Mage::log("4:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                        
                        //$quoteObj->save();
                        $transaction = Mage::getModel('core/resource_transaction');
                        if ($quoteObj->getCustomerId()) {
                           // $transaction->addObject($quoteObj->getCustomer());
                        }
                        Mage::log("5:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                        
                        $quoteObj->setIsActive(0);
                        
                        //$transaction->addObject($quoteObj);
                        $quoteObj->reserveOrderId();
                        
                        $quoteObj->setCreateOrderMethod(1); //order status as counterpoint 1
                        $quoteObj->setCounterpointOrderId($ctpnt_order_id);
                        
                        $ccInfo = array();
                        // assign payment method
                        $payment_method = 'codforpos';
                        
                        $quotePaymentObj = $quoteObj->getPayment();
                        $quotePaymentObj->setMethod($payment_method);
                        $quoteObj->setPayment($quotePaymentObj);
                        Mage::log("6:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                        
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
                        Mage::log("7:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                        
                        $items=$quoteObj->getAllItems();
                        
                        foreach ($items as $item) {
                            //@var $item Mage_Sales_Model_Quote_Item
                            $orderItem = $convertQuoteObj->itemToOrderItem($item);
                            if ($item->getParentItem()) {
                                $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                            }
                            $orderObj->addItem($orderItem);
                        }
                        Mage::log("7-1:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                        
                        $orderObj->setCanShipPartiallyItem(false);
                        
                        $totalDue = $orderObj->getTotalDue();
                        
                        $extraOrderDetails = $ctpnt_order_data['order_detail'];
                        $taxAmmount = $extraOrderDetails['tax'];
                        $totalAmmount = $quoteObj->getGrandTotal() + $taxAmmount;
                        
                        $orderObj->setTaxAmount($taxAmmount);
                        $orderObj->setGrandTotal($totalAmmount);
                        $orderObj->setBaseTaxAmount($taxAmmount);
                        $orderObj->setBaseGrandTotal($totalAmmount);
                        
                        //complete the order status
                        $orderObj->setData('state','complete')
                            ->setData('status','complete');
                        
                        $orderObj->setCreatedAt($extraOrderDetails['order_date']);
                        
                        //$transaction->addObject($orderObj);
                        Mage::log("7-2:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                        
                        /* $transaction->addCommitCallback(array($orderObj, 'place'));
                         $transaction->addCommitCallback(array($orderObj, 'save')); */
                        
                        try {
                            //$transaction->save();
                            $orderObj->save();
                            $quoteObj->save();
                            Mage::log("8:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                            
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
                           /*  $transactionSave = Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder());
                            $transactionSave->save(); */
                            Mage::log("9:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                            
                        } catch (Exception $e){
                            Mage::log("Trans Exception-".$e->getMessage(), Zend_Log::DEBUG,$this->_ctpnt_logs_file_name,true);
                            Mage::throwException('Order Cancelled.');
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
            Mage::log($e,Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
        }
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
          //  'country_id' => 'IN',//$customerDetailArr['country'],
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
        $stratTime = time();
        Mage::log("1-a:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
        
        $email      = $customerDetailArr['email'];
        $street     = $customerDetailArr['street'];
        $city       = $customerDetailArr['city'];
        $state      = $customerDetailArr['state'];
        $country    = $customerDetailArr['country'];
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
             Mage::log("1-b:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
             
        if(!$customer->getId()){
            $groupId = 1;
            $storeId = 1;
            
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
                Mage::log("1-b-i:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
                
            $_custom_address = array (
                'firstname'  => $customer->getFirstname(),
                'lastname'   => $customer->getLastname(),
                'street'     => array (
                    '0' => $street
                ),
                'city'       => $city,
                'postcode'   => $zip_code,
               // 'country_id' => 'IN',
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
            Mage::log("1-c:-".(time()-$stratTime),Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
            
            Mage::log("New customer create.customer_id:".$customer->getId(),
                Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
        }
        return $customer;
    }
    
}