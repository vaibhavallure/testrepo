<?php
/**
 * @author allure
 */
class Allure_Counterpoint_Model_Order_Api extends Mage_Api_Model_Resource_Abstract{
    
    protected $_ctpnt_logs_file_name    = "counterpoint_api";
    
    protected $_storeId                 = -1;
    protected $_websiteId               = -1;
    
    protected $_paymentMethod           = null;
    protected $_shippingMethodCode      = null;
    protected $_shippingMethodName      = null;
    
    const STORE_ID                      = 1;
    const WEBSITE_ID                    = 1;
    const TAX_CLASS_ID                  = 1;
    
    const COUNTERPOINT_ORDER            = 1;
    const ORDER_TYPE                    = "Counterpoint";
    const ORDER_STATE                   = "complete";
    const ORDER_STATUS                  = "complete";
    
    const GENERAL_CUSTOMER              = 1;
    const WHOLESELLER_CUSTOMER          = 2;
    const COUNTERPOINT_CUSTOMER         = 1;
    
    const ORDER_CAPTURE_CASE            = "offline";
    const INVOICE_STATE                 = 2;
    
    const PAYMENT_METHOD                = "codforpos";
    const SHIPPING_METHOD               = "webpos_shipping_storepickup";
    const SHIPPING_METHOD_NAME          = "Webpos Shipping Storepickup";
    
    const SIMPLE_PRODUCT                = "simple";
    
    const COUNTERPOINT_STORE_NAME       = "counterpoint";
    
    /**
     * @param $logData
     */
    private function AddLog($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
    }
    
    /**
     * @param string $counterpoint_data
     * @return number
     */
    public function test($counterpoint_data){
        $counterpoint_data = utf8_decode($counterpoint_data);
        $counterpoint_data = trim($counterpoint_data,'"');
        $counterpoint_data = stripslashes($counterpoint_data);
        $counterpointData = unserialize($counterpoint_data);
        $this->AddLog("Total Order Count-:".count($counterpointData));
        $this->importCPSQLOrderIntoMagento($counterpointData);
        $counterpointData = null;
        return 1;
    }
    
    private function prepareCounterpointSettings(){
        $helper             = Mage::helper('allure_counterpoint');
        $storeId            = $helper->getCounterPointStoreId();
        $websiteCode        = $helper->getCounterPointWebsiteCode();
        $paymentMethod      = $helper->getCounterPointPaymentMethod();
        $shippingMethodCode = $helper->getCounterPointShippingMethodCode();
        $this->AddLog("shhipping Method -:".$shippingMethodCode);
        $shippingMethodName = $helper->getCounterPointShippingMethodName();
        
        if(empty($paymentMethod)){
            $this->AddLog("Plase set payment method.Without payment method can't proceed");
            die;
        }
        
        if(empty($shippingMethodCode)){
            $this->AddLog("Plase set Shipping Method.Without shipping method can't proceed");
            die;
        }
        
        /* if(empty($shippingMethodName)){
            $this->AddLog("Plase set Shipping Method Name.Without shipping name can't proceed");
            die;
        } */
        
        if (empty($storeId)){
            $this->AddLog("Please set store_id to save counterpoint data.Can't proceed without stor_id.");
            die;
        }
        //set counterpoint store_id
        $this->_storeId = $storeId;
        $store = Mage::getModel('core/store')->load($this->_storeId);
        if(!$store){
            $this->AddLog("Invalid store.");
            die;
        }
        
        $websiteId = $store->getWebsiteId();
        $website = Mage::getModel('core/website')->load($websiteId);
        if($websiteId == 1){
            $this->AddLog("You can't save data to main_website.");
            die;
        }
        
        if(empty($websiteCode)){
            $this->AddLog("You have not added counterpoint website code.");
            die;
        }
        
        if(!($website->getCode() == $websiteCode)){
            $this->AddLog("Wrong website choose.Please add correct store_id.");
            die;
        }
        //set counterpoint website_id
        $this->_websiteId           = $websiteId;
        $this->_paymentMethod       = $paymentMethod;
        $this->_shippingMethodCode  = $shippingMethodCode;
        $shippingMethodArr = Mage::getModel("allure_counterpoint/entity_shippingMethods")
                                   ->toOptionArray();
        $this->_shippingMethodName  = $shippingMethodArr[$this->_shippingMethodCode]['label'];
    }
    
    /**
     * @param array $counterpointOrderArr
     */
    private function importCPSQLOrderIntoMagento($counterpointOrderArr){
        try{
            $this->prepareCounterpointSettings();
            $this->AddLog("counterpoint store_id-:".$this->_storeId);
            $this->AddLog("counterpoint website_id-:".$this->_websiteId);
            $count = 1;
            foreach ($counterpointOrderArr as $order_id_key => $order_data_arr){
                $ctrpnt_order_id = $order_id_key;//$this->getCounterpointOrderId($order_id_key);
                $this->createOrderByUsingCounterpointData($ctrpnt_order_id, $order_data_arr);
                $this->AddLog("count no-:".$count);
                $count++;
            }
            $counterpointOrderArr = null;
            $this->AddLog("Finish...");
        }catch (Exception $e){
            $this->AddLog("Exception in importCPSQLOrderIntoMagento");
            $this->AddLog("Exception-".$e);
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
                     $this->AddLog("counterpoint order_id:-".$ctpnt_order_id." not present in magento.");
                     $productsArr = $ctpnt_order_data['item_detail'];
                     $extraOrderDetails = $ctpnt_order_data['order_detail'];
                     if(count($productsArr) > 0){
                        $customerDetailArr = $ctpnt_order_data['customer_detail'];
                        $customer = $this->insertCustomer($customerDetailArr);
                        $billingAddress = $this->getBillingAddressOfCtpnt($customer,$customerDetailArr);
                        
                        $quoteObj = Mage::getModel('sales/quote')
                                        ->assignCustomer($customer);
                        $quoteObj = $quoteObj->setStoreId($this->_storeId);
                        
                        foreach ($productsArr as $value){
                            $sku = strtoupper($value['sku']);
                            $qty = $value['qty'];
                            
                            //negative price may be discount
                            //i.e payout or yelp in counterpoint
                            $price = $value['prc'];
                            if($price < 0){
                                if(($sku == "PAYOUT") || ($sku == "YELP") ){
                                    $price = $price * (-1);
                                }
                            }
                            
                            $productObj = Mage::getModel('catalog/product');
                            $productObj->setTypeId(self::SIMPLE_PRODUCT);
                            $productObj->setTaxClassId(self::TAX_CLASS_ID);
                            $productObj->setSku($sku);
                            $productObj->setName($value['pname']);
                            $productObj->setShortDescription($value['pname']);
                            $productObj->setDescription($value['pname']);
                            $productObj->setPrice($price);
                                
                            $quoteItem = Mage::getModel("allure_counterpoint/item")
                                            ->setProduct($productObj);
                            $quoteItem->setQty($qty);
                            $quoteItem->setStoreId($this->_storeId);
                            $quoteObj->addItem($quoteItem);
                            $productObj = null;
                        }
                        
                        $quoteBillingAddress = Mage::getModel('sales/quote_address');
                        $quoteBillingAddress->setData($billingAddress);
                        $quoteObj->setBillingAddress($quoteBillingAddress);
                        //if product is not virtual
                        if(!$quoteObj->getIsVirtual()) {
                            $shippingAddress = $billingAddress;
                            $quoteShippingAddress = Mage::getModel('sales/quote_address');
                            $quoteShippingAddress->setData($shippingAddress);
                            $quoteObj->setShippingAddress($quoteShippingAddress);
                            // fixed shipping method
                            $quoteObj->getShippingAddress()
                                ->setShippingMethod($this->_shippingMethodCode); //self::SHIPPING_METHOD
                            //$quoteObj->getShippingAddress()->setCollectShippingRates(true);
                            //$quoteObj->getShippingAddress()->collectShippingRates();
                        }
                        
                        $quoteObj->collectTotals();
                        
                        //change order total if sku maybe 'yelp' or 'payout'
                        //Start allure-2
                        $quoteItemsObj = $quoteObj->getAllItems();
                        $discountTot    = 0;
                        $isDiscountTot  = false;
                        foreach ($quoteItemsObj as $item) {
                            $kuQ = $item->getSku();
                            if(($kuQ == "YELP") || ($kuQ == "PAYOUT")){
                                $qtyQ = $item->getQty();
                                $priceQ = $item->getPrice();
                                if($qtyQ<0){
                                    $priceQ = $priceQ * $qtyQ * (-1);
                                }else{
                                    $priceQ = $priceQ * $qtyQ * (-1);
                                }
                                $item->setRowTotal($priceQ);
                                $item->setBaseRowTotal($priceQ);
                                $item->setRowTotalInclTax($priceQ);
                                $item->setBaseRowTotalInclTax($priceQ);
                                $item->setTaxableAmount($priceQ);
                                $item->setBaseTaxableAmount($priceQ);
                                $discountTot = $discountTot + ($priceQ);
                                $isDiscountTot = true;
                            }
                        }
                        
                        if($isDiscountTot){
                            $discountTot = $discountTot * 2;
                            $quoteObj->setSubtotal($quoteObj->getSubtotal() + $discountTot);
                            $quoteObj->setBaseSubtotal($quoteObj->getBaseSubtotal() + $discountTot);
                            $quoteObj->setSubtotalWithDiscount($quoteObj->getSubtotalWithDiscount() + $discountTot);
                            $quoteObj->setBaseSubtotalWithDiscount($quoteObj->getBaseSubtotalWithDiscount() + $discountTot);
                            $quoteObj->setGrandTotal($quoteObj->getGrandTotal() + $discountTot);
                            $quoteObj->setBaseGrandTotal($quoteObj->getBaseGrandTotal() + $discountTot);
                        }
                        $quoteObj->save();
                        //End - allure-2
                        
                        $quoteObj->setIsActive(0);
                        $quoteObj->reserveOrderId();
                        //order status as counterpoint 1
                        $quoteObj->setCreateOrderMethod(self::COUNTERPOINT_ORDER); 
                        $quoteObj->setCounterpointOrderId($ctpnt_order_id);
                        $quoteObj->setOrderType(self::ORDER_TYPE);
                        $incrementIdQ = $quoteObj->getReservedOrderId();
                        if($incrementIdQ){
                            $incrementIdQ = "CP-".$incrementIdQ;
                            $quoteObj->setReservedOrderId($incrementIdQ);
                        }
                            
                        $ccInfo = array();
                        // assign payment method
                        $payment_method = $this->_paymentMethod;//self::PAYMENT_METHOD;
                        $quotePaymentObj = $quoteObj->getPayment();
                        $quotePaymentObj->setMethod($payment_method);
                        $quoteObj->setPayment($quotePaymentObj);
                            
                        $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
                        if($quoteObj->getIsVirtual()) {
                           $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
                        }else{
                           $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
                        }
                            
                        $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
                            
                        $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
                        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                        if(!$quoteObj->getIsVirtual()) {
                           $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                        }
                            
                        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                            
                        $items=$quoteObj->getAllItems();
                        foreach ($items as $item) {
                            $orderItem = $convertQuoteObj->itemToOrderItem($item);
                            if($item->getParentItem()) {
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
                           }
                           $orderItem->setData('created_at',$extraOrderDetails['order_date']);
                           $orderItem->setData('updated_at',$extraOrderDetails['order_date']);
                           $orderObj->addItem($orderItem);
                        }
                            
                        $orderObj->setCanShipPartiallyItem(false);
                            
                        $totalDue = $orderObj->getTotalDue();
                            
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
                        
                        if($isDiscountTot){
                            $quoteSubTotal = $quoteObj->getSubtotal();
                            $orderObj->setSubtotal($quoteSubTotal);
                            $orderObj->setBaseSubtotal($quoteSubTotal);
                            $orderObj->setSubtotalInclTax($quoteSubTotal);
                            $orderObj->setBaseSubtotalInclTax($quoteSubTotal);
                        }
                         
                        $orderObj->setShippingDescription($this->_shippingMethodName); //self::SHIPPING_METHOD_NAME
                        $orderObj->setGrandTotal($totalAmmount);
                        $orderObj->setBaseTaxAmount($taxAmmount);
                        $orderObj->setBaseGrandTotal($totalAmmount);
                        $orderObj->setTotalPaid($totalAmmount);
                            
                        //complete the order status
                        $orderObj->setData('state',self::ORDER_STATE)
                                 ->setData('status',self::ORDER_STATUS);
                            
                        $orderObj->setCreatedAt($extraOrderDetails['order_date']);
                        try{
                              $orderObj->save();
                              $quoteObj->save();
                              $increment_id = $orderObj->getRealOrderId();
                              $this->AddLog("new order increment_id-:".$increment_id);
                                
                              //create invoice for created order
                              $ordered_items = $orderObj->getAllItems();
                              $savedQtys = array();
                              foreach($ordered_items as $item){     //item detail
                                  $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                              }
                              $invoice = Mage::getModel('sales/service_order', $orderObj)
                                               ->prepareInvoice($savedQtys);
                              $invoice->setRequestedCaptureCase(self::ORDER_CAPTURE_CASE);
                              $invoice->register();
                              $invoice->getOrder()->setIsInProcess(true);
                              $invoice->setState(self::INVOICE_STATE);
                              $invoice->setCanVoidFlag(0);
                              $invoice->save();
                                
                              $shipmentId = $this->createShipment($increment_id);
                                
                              $invoice = null;
                              $quoteObj = null;
                              $orderObj = null;
                              $customer = null;
                              $billingAddress = null;
                                
                         }catch(Exception $e){
                             $this->AddLog("Exception-".$e->getMessage());
                             $e = null;
                        }
                    }
                }else{
                    $this->AddLog("counterpoint order_id:-".$ctpnt_order_id." already created in magento.");
                }
            }else{
                $this->AddLog("counterpoint order_id:-".$ctpnt_order_id." present in magento.");
            }
        }catch (Exception $e){
            $this->AddLog("Exception in createOrderByUsingCounterpointData method of Class name is-".get_class($this));
            $this->AddLog($e->getMessage());
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
    private function createShipment($orderIncrementId, $itemsQty = array(), 
                $comment = null, $email = false,$includeComment = false) {
        $order = Mage::getModel('sales/order')
                    ->loadByIncrementId($orderIncrementId);
        /**
         * Check order existing
         */
        if(!$order->getId()) {
           $this->_fault('order_not_exists');
        }
            
        /**
         * Check shipment create availability
         */
        if(!$order->canShip()) {
            $this->_fault('data_invalid', Mage::helper('sales')->__('Cannot do shipment for order.'));
        }
            
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $order->prepareShipment($itemsQty);
        if($shipment){
            $shipment->register();
            $shipment->addComment($comment, $email && $includeComment);
            if($email) {
                $shipment->setEmailSent(true);
            }
            $shipment->getOrder()->setIsInProcess(true);
            try{
                $transactionSave = Mage::getModel('core/resource_transaction')
                                        ->addObject($shipment)
                                        ->addObject($shipment->getOrder())
                                        ->save();
                $shipmentId = $shipment->getIncrementId();
            }catch(Mage_Core_Exception $e) {
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
    
    /**
     * @param array $customerDetailArr
     * @return Mage_Customer_Model_Customer
     */
    private function insertCustomer($customerDetailArr){
        $email      = $customerDetailArr['email'];
        $street     = $customerDetailArr['street'];
        $city       = $customerDetailArr['city'];
        $state      = $customerDetailArr['state'];
        $country    = $customerDetailArr['country']?$customerDetailArr['country']:"";
        $zip_code   = $customerDetailArr['zip_code'];
        $phone      = $customerDetailArr['phone'];
        $name       = $customerDetailArr['name'];
        $group      = ($customerDetailArr['nam_typ'])?$customerDetailArr['nam_typ']:"P";
        
        $name        = explode(" ", $name);
        $firstName  = $name[0];
        $lastName   = $name[0];
        if(count($name) > 1)
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
            $groupId = self::GENERAL_CUSTOMER;
            if($group == "B"){
                $groupId = self::WHOLESELLER_CUSTOMER;
            }
            
            $password = $this->generateRandomPassword();
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($this->_websiteId)
                ->setStoreId($this->_storeId)
                ->setGroupId($groupId)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setPassword($password)
                ->setCustomerType(self::COUNTERPOINT_CUSTOMER)  //counterpoint
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
            $this->AddLog("new customer create.customer_id-:".$customer->getId());
        }
        return $customer;
    }
}