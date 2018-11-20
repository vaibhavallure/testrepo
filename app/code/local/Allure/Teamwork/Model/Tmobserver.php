<?php


/**
 * @author allure
 */
class Allure_Teamwork_Model_Tmobserver{	
    const TM_URL = "/services/orders";
    const TOKEN = "OUtNUUhIV1V2UjgxR0RwejV0Tmk0VllneEljNTRZWHdLNHkwTERwZXlsaz0=";
    
    const NEW_YORK_OFFSET = 5;
    
    protected $teamwork_sync_log = "teamwork_sync_data.log";
    
    private function isTeamworkDataTransferToSalesforce(){
        $status =  Mage::helper("allure_teamwork")->getTeamworkSalesforceStatus();
        if(!$status){
            $this->addLog("Teamwork data transfer to salesforce disabled.");
        }
        return $status;
    }
    
    private function addLog($data){
        Mage::log($data,Zend_Log::DEBUG,$this->teamwork_sync_log,true);
    }
    
    public function synkTeamwokLiveOrders(){
        $this->addLog("Teamwork sync request call at - ".gmdate("Y-m-d h:i:s"));
        $helper = Mage::helper("allure_teamwork");
        if(!$helper->getTeamworkSyncStatus()){
            $this->addLog("Teamwork live data sync disabled.");
            return;
        }
        $urlPath = $helper->getTeamworkSyncDataUrl();
        $requestURL = $urlPath . self::TM_URL;
        $token = trim($helper->getTeamworkSyncDataToken());
        $sendRequest = curl_init($requestURL);
        curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($sendRequest, CURLOPT_HEADER, false);
        curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$token
        ));
        
        $requestArgs = null;
        // convert requestArgs to json
        if ($requestArgs != null) {
            $json_arguments = json_encode($requestArgs);
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
        } 
        $response  = curl_exec($sendRequest);
        //$this->addLog(json_decode($response,true));
        /* $modelObj = Mage::getModel("allure_teamwork/tmorder")
        ->load("5401FC1F-D2E2-4617-AD2B-92676D2B844A","tm_receipt_id");
        $response = json_encode(array(unserialize($modelObj->getTmdata()))); */
        
        $this->addDataIntoSystem($response);
    }
    
    public function addDataIntoSystem($response){
        try{
            if(!$response){
                return ;
            }
            
            $responseArr = json_decode($response,true);
            $this->addLog("count - ".count($responseArr));
            $ordCnt = 0;
            foreach ($responseArr as $object){
                $ordCnt++;
                $receiptId = $object["order_detail"]["ReceiptId"];
                $this->addLog("cnt - ".$ordCnt." TM-ID :".$receiptId." fetched.");
                try{
                    $modelObj = Mage::getModel("allure_teamwork/tmorder")
                        ->load($receiptId,"tm_receipt_id");
                    if(!$modelObj->getEntityId()){
                        $modelObj = Mage::getModel("allure_teamwork/tmorder");
                        $modelObj->setTmReceiptId($receiptId)
                            ->setTmdata(serialize($object))
                            ->save();
                        $this->addLog("TMID :".$receiptId." data added");
                    }
                    $responseArr = array($object);
                    $this->createOrder($responseArr);
                }catch (Exception $ee){
                    $this->addLog("01 - Exc - ".$ee->getMessage());
                }
            }
        }catch (Exception $e){
            $this->addLog("02 - Exc - ".$e->getMessage());
        }
        $this->addLog("Finish...");
        
    }
    
    public function createOrder($responseArr){
        /* if(!$responseArr){
            return ;
        } */
        
        //$responseArr = json_decode($response,true);
        /* $modelObj = Mage::getModel("allure_teamwork/tmorder")
        ->load("CA3AD4BA-2C46-480D-B114-01CCEA865BE9","tm_receipt_id"); */
        //->load("0DC417FE-B9D0-4F2E-82BD-F74D191BFF7E","tm_receipt_id");
        /* $data = unserialize($modelObj->getTmdata());
        $responseArr = array($data); */
        
        $this->addTeamworkProduct($responseArr);
        
        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();
        $utcOffsetArr = array();
        foreach ($ostores as $storeO){
            $oldStoreArr[$storeO->getTmLocationCode()] = $storeO->getId();
            $utcOffsetArr[$storeO->getTmLocationCode()] = $storeO->getUtcOffset();
        }
        
        $alphabets = range('A','Z');
        $numbers = range('0','9');
        $additional_characters = array('#','@','$');
        $final_array = array_merge($alphabets,$numbers,$additional_characters);
        
        foreach ($responseArr as $object){
            $orderDetails = $object["order_detail"];
            $customerDetails = $object["customer_details"];
            $productDetails = $object["product_details"];
            $paymentDetails = $object["payment_details"];
            $extaDetails = $object["extra_details"];
            
            $receiptId = $orderDetails["ReceiptId"];
            
            $websiteId = 1;
            $storeId   = 1;
            
            try{
                $email = $customerDetails["EMail1"] ? $customerDetails["EMail1"] :$customerDetails["EMail2"];
                $email = trim($email);
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);
                //create customer if not in magento
                
                $tmOrderObj = Mage::getModel("allure_teamwork/tmorder")
                    ->load($receiptId,"tm_receipt_id");    
                  
                if(!$customer->getId()){
                    $password = '';
                    $length = 8;  //password length
                    while($length--) {
                        $keyV = array_rand($final_array);
                        $password .= $final_array[$keyV];
                    }
                    
                    //set temp session for create teamwork customer
                    Mage::getSingleton("core/session")->setIsTeamworkCustomer(1);
                    
                    $createdAtArr = explode(".", trim($customerDetails["RecModified"]));
                    $createdAt = $createdAtArr[0];
                    $group = ($customerDetails["CustomFlag1"]) ? 2 : 1;
                    
                    $customer = Mage::getModel("customer/customer");
                    $customer->setWebsiteId($websiteId)
                        ->setStoreId($storeId)
                        ->setGroupId($group)
                        ->setFirstname($customerDetails["FirstName"])
                        ->setLastname($customerDetails["LastName"])
                        ->setEmail($email)
                        ->setCreatedAt($createdAt)
                        ->setPassword($password)
                        ->setPasswordConfirmation($password)
                        ->setPasswordCreatedAt(time())
                        ->setCustomerType(20)  //teamwork db - 20
                        ->setTeamworkCustomerId($customerDetails["CustomerId"])
                        ->setIsTeamworkCustomer(1)
                        ->setTwAcceptMarketing($customerDetails["AcceptMarketing"])
                        ->setTwAcceptTransactional($customerDetails["AcceptTransactional1"])
                        ->save();
                    
                    //updated customer status 
                    if($tmOrderObj->getEntityId())  {
                        $tmOrderObj->setCustomerStatus("create")->save();
                    }
                    
                    $this->addLog($receiptId." : customer create id :".$customer->getId()." email - ".$email);
                    
                    if($customer->getId()){
                        $_custom_address = array (
                            'firstname'  => $customer->getFirstname(),
                            'lastname'   => $customer->getLastname(),
                            'region' 	=> 	$state,
                        );
                        $phone = ($customerDetails["Phone1"]) ? $customerDetails["Phone1"] : $customerDetails["Phone2"];
                        if($customerDetails["City"]){
                            $_custom_address["city"] = $customerDetails["City"];
                        }
                        if($customerDetails["PostalCode"]){
                            $_custom_address["postcode"] = $customerDetails["PostalCode"];
                        }
                        if($phone){
                            $_custom_address["telephone"] = $phone;
                        }
                        if($customerDetails["COUNTRY"]){
                            $_custom_address["country_id"] = $customerDetails["COUNTRY"];
                        }
                        if($customerDetails["State"]){
                            $_custom_address["region"] = $customerDetails["State"];
                        }
                        
                        $streetArr = array();
                        if($customerDetails["Address1"]){
                            $streetArr[] = $customerDetails["Address1"];
                        }
                        if($customerDetails["Address2"]){
                            $streetArr[] = $customerDetails["Address2"];
                        }
                        
                        /* foreach ($streetArr as $street){
                            $_custom_address[] = trim($street);
                        } */
                        if(count($streetArr) > 0){
                            $_custom_address["street"] = $streetArr;
                        }
                        
                        $address = Mage::getModel("customer/address");
                        $address->setData($_custom_address)
                            ->setCustomerId($customer->getId())
                            ->setIsDefaultBilling('1')
                            ->setIsDefaultShipping('1')
                            ->setSaveInAddressBook('1');
                        $address->save();
                        $this->addLog("customer address create id :".$address->getId());
                    }
                }else{
                    $customer->setTwAcceptMarketing($customerDetails["AcceptMarketing"])
                    ->setTwAcceptTransactional($customerDetails["AcceptTransactional1"])
                    ->save();
                    if($tmOrderObj->getEntityId())  {
                        $tmOrderObj->setCustomerStatus("already")->save();
                    }
                }
            }catch (Exception $e){
                $this->addLog("03 - Exc - :".$e->getMessage());
            }
            Mage::getSingleton("core/session")->setIsTeamworkCustomer(0);
            
            $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
            if($orderObj->getId()){
                Mage::log("Receipt Id:".$receiptId." Order Id:".$orderObj->getId()." present",Zend_log::DEBUG,$_logFile,true);
                continue;
            }
            
            $email = trim($orderDetails["EmailAddress"]);
            if(empty($email)){
                $this->addLog("Email Id is Empty.ReceiptId - ".$receiptId);
                continue;
            }
            
            try{
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(1)
                    ->loadByEmail($email);
                
                if($customer->getId()){
                    //$billingAddress = $customer->getDefaultBillingAddress();
                    
                    $billingAddress = Mage::getModel('sales/quote_address')
                        ->setFirstname($customer->getFirstname())
                        ->setLastname($customer->getLastname());
                    $sellCity = trim($orderDetails["SellToCity"]);
                    if($orderDetails["SellToCity"]){
                        $billingAddress->setCity($sellCity);
                    }
                    
                    $sellState = trim($orderDetails["SellToState"]);
                    if($orderDetails["SellToState"]){
                        if(strlen($sellState) >= 3){
                            $billingAddress->setRegion($sellState);
                        }else{
                            $billingAddress->setRegionId($sellState);
                        }
                    }
                    
                    $sellPostcode = trim($orderDetails["SellToPostalCode"]);
                    if($orderDetails["SellToPostalCode"]){
                        $billingAddress->setPostcode($sellPostcode);
                    }
                    
                    $countryCode = trim($orderDetails["SellToCountryCode"]);
                    if($orderDetails["SellToCountryCode"]){
                        $billingAddress->setCountryId($countryCode);
                    }
                    
                    $address1 = trim($orderDetails["SellToAddress1"]);
                    $address2 = trim($orderDetails["SellToAddress2"]);
                    $streetArr1 = array();
                    if($address1){
                        $streetArr1[] = $address1;
                    }
                    if($address2){
                        $streetArr1[] = $address2;
                    }
                    if(count($streetArr1) > 0){
                        $billingAddress->setStreet($streetArr1);
                    }
                    
                    
                    $telephone1 = trim($orderDetails["SellToPhone1"]);
                    $telephone2 = trim($orderDetails["SellToPhone2"]);
                    $telephone = ($telephone1) ? $telephone1 : $telephone2;
                    if($telephone){
                        $billingAddress->setTelephone($telephone);
                    }
                    
                    $quoteObj = Mage::getModel('sales/quote')
                        ->assignCustomer($customer);
                    $quoteObj = $quoteObj->setStoreId(1);
                    $discountTot    = 0;
                    $isDiscountTot  = false;
                    $productDetails = $object["product_details"];
                    $extraOrderDetails = $object["extra_details"];
                    
                    $productArr = array();
                    foreach ($productDetails as $tmProduct){
                        if($tmProduct["LineExtDiscountAmount"] > 0){
                            $isDiscountTot = true;
                            $discountTot += $tmProduct["LineExtDiscountAmount"];
                        }
                        $styleNo = trim($tmProduct['StyleNo']);
                        $tsku = ($tmProduct['SKU'])?$tmProduct['SKU']:$styleNo;
                        $sku = strtoupper(trim($tsku));
                        $qty = $tmProduct['Qty'];
                        
                        $tempQty = $qty;
                        $totalAmtT = trim($orderDetails["TotalAmountWithTax"]);
                        if($qty < 0){
                            $qty = $qty * (-1);
                        }
                        
                        $origPriceWoutTax = $tmProduct["OriginalPriceWithoutTax"];
                        $origPriceWithTax = $tmProduct["OriginalPriceWithTax"];
                        $pTaxAmt = $origPriceWithTax - $origPriceWoutTax;
                        $taxPer = 0;
                        if($pTaxAmt > 0){
                            $taxPer = (100 * $pTaxAmt) /$origPriceWithTax;
                            $taxPer = round($taxPer,2);
                        }
                        
                        $tmItemId = trim($tmProduct["ReceiptItemId"]);
                        $productArr[$tmItemId] = array(
                            "orig_price_tax" => $origPriceWithTax,
                            "tax" => ($pTaxAmt * $qty),
                            "single_tax" => $pTaxAmt,
                            "tax_per" => $taxPer,
                            "row_total" => $tmProduct["OriginalPriceWithoutTax"],
                            "disc" => $tmProduct["LineExtDiscountAmount"],
                            "temp_qty" => $tempQty
                        );
                        
                        $price = $origPriceWoutTax;
                        
                        $productObj = Mage::getModel('catalog/product');
                        $productObj->setTypeId("simple");
                        $productObj->setSku($sku);
                        $productObj->setName($tmProduct['Description4']);
                        $productObj->setShortDescription($tmProduct['Description4']);
                        $productObj->setDescription($tmProduct['Description4']);
                        $productObj->setPrice($price);
                        
                        $quoteItem = Mage::getModel("allure_counterpoint/item")
                        ->setProduct($productObj);
                        $quoteItem->setQty($qty);
                        
                        $quoteItem->setStoreId(1);
                        $quoteItem->setOtherSysQty($tempQty);
                        $quoteItem->setTwItemId($tmItemId);
                        $quoteObj->addItem($quoteItem);
                        $productObj = null;
                    }
                    
                    //$quoteBillingAddress = Mage::getModel('sales/quote_address');
                    //$quoteBillingAddress->setData($billingAddress);
                    $quoteObj->setBillingAddress($billingAddress);
                    if(!$quoteObj->getIsVirtual()) {
                        $shippingAddress = $billingAddress;
                        //$quoteShippingAddress = Mage::getModel('sales/quote_address');
                        //$quoteShippingAddress->setData($shippingAddress);
                        $quoteObj->setShippingAddress($shippingAddress);
                        // fixed shipping method
                        $quoteObj->getShippingAddress()
                        ->setShippingMethod("tm_storepickupshipping");
                    }
                    $quoteObj->setCreateOrderMethod(2);
                    $quoteObj->collectTotals();
                    
                    if($isDiscountTot){
                        $discountTot = $discountTot  ;
                    }
                    
                    $otherSysCur     = trim($orderDetails["CODE"]);
                    $otherSysCurCode = trim($orderDetails["CurrencyCode"]);
                    $quoteObj->setOtherSysCurrency($otherSysCur);
                    $quoteObj->setOtherSysCurrencyCode($otherSysCurCode);
                    $quoteObj->setOtherSysExtraInfo(json_encode($extraOrderDetails,true));
                    $quoteObj->setTeamworkReceiptId($receiptId);
                    $quoteObj->setCreateOrderMethod(2);
                    
                    $quoteObj->save();
                    
                    $quoteObj->setIsActive(0);
                    //$quoteObj->reserveOrderId();
                    
                    //$incrementIdQ = $quoteObj->getReservedOrderId();
                    $incrementIdQ = $extaDetails["ReceiptNum"];
                    if($incrementIdQ){
                        $incrementIdQ = "TW-".$incrementIdQ;
                        $quoteObj->setReservedOrderId($incrementIdQ);
                    }
                    
                    if(strtoupper($otherSysCur) != "MT"){
                        $quoteObj->setData('base_currency_code',$otherSysCurCode)
                        ->setData('global_currency_code',"USD")
                        ->setData('quote_currency_code',$otherSysCurCode)
                        ->setData('store_currency_code',$otherSysCurCode);
                    }
                    
                    $currencyRates = Mage::getModel('directory/currency')->getCurrencyRates($otherSysCurCode, "USD");
                    if(!empty($currencyRates["USD"])){
                        $this->addLog("currency rate {$otherSysCurCode} to USD is {$currencyRates["USD"]}");
                        $quoteObj->setStoreToBaseRate($currencyRates["USD"])
                        ->setStoreToQuoteRate(1)
                        ->setBaseToGlobalRate($currencyRates["USD"])
                        ->setBaseToQuoteRate(1);
                    }
                    
                    $payment_method  = "tm_pay_cash";
                    $quotePaymentObj = $quoteObj->getPayment();
                    $quotePaymentObj->setMethod($payment_method);
                    $quoteObj->setPayment($quotePaymentObj);
                    
                    $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
                    if($quoteObj->getIsVirtual()) {
                        $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
                    }else{
                        $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
                    }
                    
                    $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
                    if(!$quoteObj->getIsVirtual()) {
                        $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                    }
                    
                    $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                    
                    $tTax = 0;
                    
                    $items=$quoteObj->getAllItems();
                    foreach ($items as $item) {
                        $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
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
                        }
                        
                        if($productId){
                            $orderItem->setData('product_id',$productId);
                        }
                        
                        $iSku = $item->getTwItemId();//$item->getSku();
                        $taxI = $productArr[$iSku]["tax"];
                        
                        $singleTax = $productArr[$iSku]["single_tax"];
                        $rowTotal = $productArr[$iSku]["row_total"];
                        
                        $taxPer = $productArr[$iSku]["tax_per"];
                        
                        $orderItem->setData("price_incl_tax",$singleTax);
                        $orderItem->setData("base_price_incl_tax",$singleTax);
                        
                        $orderItem->setData("row_total_incl_tax",$taxI);
                        $orderItem->setData("base_row_total_incl_tax",$taxI);
                        
                        $orderItem->setData("tax_amount",$taxI);
                        $orderItem->setData("tax_percent",$taxPer);
                        
                        $orderItem->setData("base_tax_amount",$taxI);
                        
                        $disc = $productArr[$iSku]["disc"];
                        $temQty = $productArr[$iSku]["temp_qty"];
                        $orderItem->setData("other_sys_qty",$temQty);
                        if($disc){
                            //$disc *= (-1);
                            $orderItem->setData("discount_amount",$disc);
                            $orderItem->setData("base_discount_amount",$disc);
                        }
                        $tTax += $taxI;
                        $orderObj->addItem($orderItem);
                    }
                    
                    $createAtStr = explode(".", trim($orderDetails["RecCreated"]));
                    //trim($orderDetails["StateDate"]);
                    $createAt = $createAtStr[0];
                    $orderObj->setCreatedAt($createAt);
                    $orderObj->setCanShipPartiallyItem(false);
                    $totalDue = $orderObj->getTotalDue();
                    $totalAmmount = $quoteObj->getGrandTotal();
                    $taxAmmount     = $tTax;//$orderDetails['TAX'];
                    $discountAmount = $discountTot;
                    
                    if(1){
                        $totalAmmount =$totalAmmount + $taxAmmount;
                        $orderObj->setTaxAmount($taxAmmount);
                    }
                    
                    if($discountAmount){
                        $discountAmount = 0 - $discountAmount;
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
                    
                    $orderObj->setShippingDescription("Store Pickup"); //self::SHIPPING_METHOD_NAME
                    $orderObj->setGrandTotal($totalAmmount);
                    $orderObj->setBaseTaxAmount($taxAmmount);
                    $orderObj->setBaseGrandTotal($totalAmmount);
                    
                    /* if(strtoupper($otherSysCur) != "MT"){
                        $orderObj->setData('base_currency_code',$otherSysCurCode)
                            ->setData('global_currency_code',$otherSysCurCode)
                            ->setData('order_currency_code',$otherSysCurCode)
                            ->setData('store_currency_code',$otherSysCurCode);
                    } */
                    
                    $extStoreName = $extraOrderDetails["Name"];
                    $locationCode = $extraOrderDetails["LocationCode"];
                    $utcOffset    = $extraOrderDetails["UTCOffset"];
                    $oldStoreId = $oldStoreArr[$locationCode];
                    if(!$oldStoreId){
                        $storeName = str_replace(' ', '', $extStoreName);
                        $storeName = strtolower(str_replace(' ', '', $storeName));
                        $storeObj = Mage::getModel("allure_virtualstore/store");
                        $tmHelper = Mage::helper("allure_teamwork");
                        $tmWebsiteId = $tmHelper->getTeamworkWebsiteId();
                        $tmGroupId = $tmHelper->getTeamworkMagentoGroupId();
                        
                        $storeObj->setWebsiteId($tmWebsiteId)
                            ->setGroupId($tmGroupId)
                            ->setCode($storeName)
                            ->setName($extStoreName)
                            ->setTmLocationCode($locationCode)
                            ->setUtcOffset($utcOffset)
                            ->save();
                        $oldStoreId = $storeObj->getId();
                        $this->addLog("Teamwork new store created. Store Id - ".$oldStoreId);
                    }
                    
                    //set date
                    $calculatedOffset = self::NEW_YORK_OFFSET;
                    if(trim($locationCode) != 1){
                        $timeDate = strtotime($createAt);
                        $oldUtcOffset = $utcOffsetArr[$locationCode];
                        $tmUtcOffset = (!empty($oldUtcOffset)) ? $oldUtcOffset : $utcOffset;
                        if(!empty($oldUtcOffset)){
                            $calculatedOffset += $oldUtcOffset;
                            $offset = intval($calculatedOffset);
                            $orderDate = strtotime("{$offset} hour", $timeDate);
                            $newCreateAt = date('Y-m-d H:i:s', $orderDate);
                            $orderObj->setCreatedAt($newCreateAt);
                        }
                    }
                    
                    /* $oldUtcOffset = $utcOffsetArr[$locationCode];
                    $tmUtcOffset = (!empty($oldUtcOffset)) ? $oldUtcOffset : $utcOffset;
                    if(!empty($oldUtcOffset)){
                        $websiteTimeZone = Mage::getStoreConfig('general/locale/timezone');
                        $tmTimeZone = $this->offsetToTZ($oldUtcOffset);
                        if($tmTimeZone){
                            $tmOrderCreatedate = $this->convertTimeZone($createAt,$tmTimeZone, $websiteTimeZone);
                            $orderObj->setCreatedAt($tmOrderCreatedate);
                        }
                    } */
                    
                    $orderObj->setData('old_store_id',$oldStoreId);
                    
                    //complete the order status
                    $orderObj->setData('state',"processing")
                        ->setData('status',"processing");
                    
                        
                    $orderObj->save();
                    $quoteObj->save();
                    
                    if($tmOrderObj){
                        $tmOrderObj->setOrderStatus("create")->save();
                    }
                    
                    $this->addLog("Order create. Order Id:".$orderObj->getId());
                    $dataArr = array($object);
                    $this->createInvoice($dataArr);
                    $this->createCreditMemo($dataArr);
                    $this->createShipment($dataArr);
                    $tmOrderObj = null;
                }
            }catch (Exception $et){
                $this->addLog("04 - Exc - ".$et->getMessage());
            }
        }
    }
    
    
    private function offsetToTZ($offset) {
        switch((string) $offset) {
            case '-04:30' : return 'America/Caracas'; break;
            case '-03:30' : return 'Canada/Newfoundland'; break;
            case '+03:30' : return 'Asia/Tehran'; break;
            case '+04:30' : return 'Asia/Kabul'; break;
            case '+05:30' : return 'Asia/Kolkata'; break;
            case '+05:45' : return 'Asia/Kathmandu'; break;
            case '+09:30' : return 'Australia/Darwin'; break;
        }
        $offset = (int) str_replace(array('0',0,':00',00,'30',30,'45',45,':','+'),'', (string) $offset);
        
        $offset = $offset*60*60;
        var_dump($offset);
        $abbrarray = timezone_abbreviations_list();
        //var_dump($abbrarray);
        foreach ($abbrarray as $abbr) {
            foreach($abbr as $city) {
                if($city['offset'] == $offset) {
                    return $city['timezone_id'];
                }
            }
        }
        return false;
    }
    
    private function convertTimeZone($oTime, $oTimeZone, $nTimeZone)
    {
        date_default_timezone_set($oTimeZone);  //Change default timezone to old timezone within this function only.
        $originalTime = new DateTime($oTime);
        $originalTime->setTimeZone(new DateTimeZone($nTimeZone)); //Convert to desired TimeZone.
        date_default_timezone_set($nTimeZone) ; //Reset default TimeZone according to your global settings.
        return $originalTime->format('Y-m-d h:i:s'); //Return converted TimeZone.
    } 
    
    
    private function createInvoice($object){
        $this->addLog("In create invoice method");
        try{
            $websiteId = 1;
            $existCnt = 0;
            $nonExistCnt = 0;
            
            $paymentMethodsArr = array(
                "LIBERTY TILL"  => "tm_pay_liberty_till",
                "WIRE TRANSFER" => "tm_pay_wire_transfer",
                "CC REFUND"     => "tm_pay_cc_refund",
                "STORE CREDIT"  => "tm_pay_store_credit",
                "GENIUS CHARGE" => "tm_pay_genius_charge",
                "GIFT CARD"     => "tm_pay_gift_card",
                "CASH"          => "tm_pay_cash",
                "CASHEURO"      => "tm_pay_casheuro",
                "AUTHORIZE.NET" => "tm_pay_authrize",
                "GENIUS REFUND" => "tm_pay_genius_refund",
                "DEPOSIT"       => "tm_pay_deposit",
                "OFFLINE CREDIT CARD" => "tm_pay_offline_credit_card",
                "CREDIT CARD" => "tm_pay_credit_card",
                "PAYPAL" => "tm_pay_paypal",
                "CASHUK" => "tm_pay_cashuk"
            );
            
            $creditPaymentsArr = array(
                "tm_pay_genius_charge",
                "tm_pay_genius_refund",
                "tm_pay_authrize",
                "tm_pay_credit_card",
                "tm_pay_offline_credit_card"
            );
            
            $cardCodeArr = array(
                "Discover" => "DISC",
                "Master" => "MC",
                "Visa" => "VI",
                "Amex" => "AE"
            );
            
            $invCnt = 0;
            
            foreach ($object as $oData){
                try{
                    $invCnt ++;
                    $receiptId = $oData["order_detail"]["ReceiptId"];
                    $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                    if(!$orderObj->getId()){
                        $this->addLog($invCnt." - ReceiptId:".$receiptId." order not created.");
                        continue;
                    }
                    
                    $tmOrderObj = Mage::getModel("allure_teamwork/tmorder")
                        ->load($receiptId,"tm_receipt_id");  
                    
                    $orderId = $orderObj->getId();
                    $ordered_items = $orderObj->getAllItems();
                    $savedQtys = array();
                    $isPending = false;
                    foreach($ordered_items as $item){     //item detail
                        $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                        $otherSysQty = $item->getOtherSysQty();
                        if($otherSysQty < 0){
                            $isPending = true;
                        }
                    }
                    
                    if($orderObj->hasInvoices()){
                        $this->addLog($invCnt." - invoice already present. order id:".$orderId);
                    }else{
                        $paymentDetails = $oData["payment_details"];
                        $cntPayments = count($paymentDetails);
                        $totalPaidAmount = 0;
                        $paymentCnt = 0;
                        $paymentInfo = array();
                        foreach ($paymentDetails as $paymentData){
                            $payment_method = trim($paymentData["PaymentMethodCode"]);
                            $paymentCode = $paymentMethodsArr[$payment_method];
                            $paidAmt    = $paymentData["PaymentAmount"];
                            $changeAmt  = $paymentData["ChangeAmount"];
                            $paidAmt = ($paidAmt < 0 )? $paidAmt * (-1): $paidAmt;
                            $incAmount = ($paidAmt != 0)? $paidAmt : $changeAmt * (-1);
                            $invoice = Mage::getModel('sales/service_order', $orderObj)
                            ->prepareInvoice($savedQtys);
                            $invoice->setRequestedCaptureCase("offline");
                            $invoice->register();
                            $invoice->getOrder()->setIsInProcess(true);
                            $state = 2;
                            
                            $invoice->setState($state);
                            $invoice->setCanVoidFlag(0);
                            
                            $amount = $incAmount;
                            if($changeAmt != 0){
                                $invoice->setBaseGrandTotal($incAmount);
                                $invoice->setGrandTotal($incAmount);
                                $invoice->setSubtotalInclTax($incAmount);
                                $invoice->setSubtotal($incAmount);
                                $invoice->setBaseSubtotal($incAmount);
                            }
                            elseif($paidAmt > $orderObj->getGrandTotal()){
                                $amount = $paidAmt;
                                $invoice->setBaseGrandTotal($amount);
                                $invoice->setGrandTotal($amount);
                                $invoice->setSubtotalInclTax($amount);
                                $invoice->setSubtotal($amount);
                                $invoice->setSubtotal($amount);
                                $invoice->setTaxAmount(0);
                                $invoice->setBaseTaxAmount(0);
                            }else{
                                if(!$isPending && $paidAmt < $orderObj->getGrandTotal()){
                                    $amount = $paidAmt;
                                    $invoice->setBaseGrandTotal($amount);
                                    $invoice->setGrandTotal($amount);
                                    $invoice->setSubtotalInclTax($amount);
                                    $invoice->setSubtotal($amount);
                                    $invoice->setSubtotal($amount);
                                    $invoice->setTaxAmount(0);
                                    $invoice->setBaseTaxAmount(0);
                                }else{
                                    $amount = $orderObj->getGrandTotal();
                                }
                            }
                            
                            $isShowPay = true;
                            if($incAmount <= 0){
                                $isShowPay = false;
                            }
                            
                            $invoice->save();
                            $invoiceNumber  = $invoice->getIncrementId();
                            $customerId     = $orderObj->getCustomerId();
                            $this->addLog($invCnt." - ReceiptId:".$receiptId." order id:".$orderId." Invoice No:".$invoiceNumber);
                            $orderPay = $orderObj->getPayment();
                            
                            if($paymentCode == "tm_pay_cash"){
                                //not need to change payment method
                                if($paymentCnt > 0){
                                    $orderPay = Mage::getModel("sales/order_payment");
                                }
                                $orderPay->setParentId($orderObj->getId());
                                $orderPay->setAmountPaid($amount);
                                $orderPay->setBaseAmountPaid($amount);
                                $orderPay->setMethod($paymentCode);
                                $orderPay->save();
                                $paymentCnt++;
                            }else{
                                $isCreditTransaction = false;
                                if(in_array($paymentCode,$creditPaymentsArr)){
                                    $isCreditTransaction = true;
                                }
                                
                                if($paymentCnt > 0){
                                    $orderPay = Mage::getModel("sales/order_payment");
                                }
                                
                                $orderPay->setParentId($orderObj->getId());
                                $orderPay->setAmountPaid($amount);
                                $orderPay->setBaseAmountPaid($amount);
                                
                                $orderPay->setMethod($paymentCode);
                                $orderPay->save();
                                $paymentCnt++;
                                
                                //for credit payments.
                                if($isCreditTransaction){
                                    $ccLast4 = $paymentData["AccountNumberSearch"];
                                    $cardExpMonth = $paymentData["CardExpMonth"];
                                    $cardExpYear  = $paymentData["CardExpYear"];
                                    $cardType = $paymentData["CardTypeDescription"];
                                    $cardType = ($cardCodeArr[$cardType])?$cardCodeArr[$cardType]:$cardType;
                                    if($cardType){
                                        $orderPay->setCcType($cardType);
                                    }
                                    
                                    if(($cardExpMonth)){
                                        $orderPay->setCcExpMonth($cardExpMonth);
                                    }
                                    
                                    if(($cardExpYear)){
                                        $orderPay->setCcExpYear($cardExpYear);
                                    }
                                    
                                    $transactionId = $paymentData["CardOrderId"];
                                    
                                    $this->addLog("TransactionId:".$transactionId);
                                    
                                    $orderPay->setLastTransId($cardExpYear);
                                    
                                    $accNumber = $paymentData["AccountNumberSearch"];
                                    if($accNumber){
                                        $accNumber = "XXXX".$accNumber;
                                        $orderPay->setCcLast4($ccLast4);
                                    }
                                    
                                    $this->addLog("accNumber:".$accNumber);
                                    $responseTrn = array(
                                        "save" => "1",
                                        "response_code" => "1",
                                        "response_subcode" =>"",
                                        "response_reason_code" => "0",
                                        "response_reason_text" =>"",
                                        "approval_code" => "000000",
                                        "auth_code" => "000000",
                                        "avs_result_code" => "P",
                                        "transaction_id" => $transactionId,
                                        "reference_transaction_id" =>"",
                                        "invoice_number" => $invoiceNumber,
                                        "description" =>"",
                                        "amount" => $amount,
                                        "method" => "CC",
                                        "transaction_type" => "auth_capture",
                                        "customer_id" => $customerId,
                                        "md5_hash" => "2D28AC7293F21CC59888CFD8B92014EB",
                                        "card_code_response_code" =>"",
                                        "cavv_response_code" =>"",
                                        "acc_number" => $accNumber,
                                        "card_type" =>$cardType ,
                                        "split_tender_id" =>"",
                                        "requested_amount" =>"",
                                        "balance_on_card" =>"",
                                        "profile_id" => "",
                                        "payment_id" => "",
                                        "is_fraud" =>"",
                                        "is_error" =>""
                                    );
                                    
                                    $orderPay->setAdditionalInformation($responseTrn);
                                    $orderPay->save();
                                    
                                    $transaction = Mage::getModel('sales/order_payment_transaction');
                                    $transaction->setOrderId($orderObj->getId());
                                    $transaction->setOrderPaymentObject($orderPay);
                                    $transaction->setTxnType("capture");
                                    $transaction->setTxnId($transactionId);
                                    $transaction->setIsClosed(0);
                                    $additinalInfo = $orderPay->getAdditionalInformation();
                                    if ($additinalInfo) {
                                        foreach ($additinalInfo as $key => $value) {
                                            $transaction->setAdditionalInformation($key, $value);
                                        }
                                    }
                                    $transaction->save();
                                    
                                    $this->addLog("Transaction:".$transaction->getId());
                                }
                                
                            }
                            
                            $paymentInfo[$invoice->getId()] = array('is_show'=>$isShowPay,'payment_id'=>$orderPay->getId(),'amt'=>$incAmount);
                            //invoice flag
                        }
                        
                        $orderObj->getPayment()->setAdditionalData(serialize($paymentInfo))->save();
                        $orderObj->setTotalPaid($orderObj->getGrandTotal());
                        $orderObj->setData('state',"processing")
                            ->setData('status',"processing");
                        
                        $orderObj->save();
                        if($this->isTeamworkDataTransferToSalesforce()){
                            Mage::getModel("allure_salesforce/observer_order")->addInvoiceIntoSalesforce($orderObj->getId());
                        }
                        
                        if($tmOrderObj->getEntityId()){
                            $tmOrderObj->setInvoiceStatus("create")->save();
                        }
                    }
                    
                }catch (Exception $e){
                    $this->addLog("05 - Exc - ".$e->getMessage());
                }
            }
            
        }catch (Exception $e){
            $this->addLog("06 - Exc - ".$e->getMessage());
        }
        
    }
    
    
    private function createShipment($object){
        $shipCnt = 0;
        foreach ($object as $oData){
            try{
                $shipCnt ++;
                $receiptId = $oData["order_detail"]["ReceiptId"];
                $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                if(!$orderObj->getId()){
                    $this->addLog($shipCnt." - ReceiptId:".$receiptId." order not created.");
                    continue;
                }
                $orderId = $orderObj->getId();
                if (!$orderObj->canShip()) {
                    $this->addLog($shipCnt." - Order Id:".$orderId." Shipment can't create for this order.");
                    continue;
                }
                $isRefundItem = false;
                $qtys = array();
                $cntI = 0;
                $refCnt = 0;
                foreach ($orderObj->getAllItems() as $item) {
                    $cntI++;
                    $otherSysQty = $item->getOtherSysQty();
                    if($otherSysQty < 0){
                        $isRefundItem = true;
                        $refCnt++;
                        //break;
                    }
                    $qtys[$item->getId()] = $item->getQtyOrdered();
                }
                
                if($cntI == $refCnt){
                    $this->addLog($shipCnt." - Order Id:".$orderId." Shipment can't create for this order.Refunded Item present.");
                    continue;
                }
                $shipment = Mage::getModel('sales/service_order', $orderObj)
                ->prepareShipment($qtys);
                // Register Shipment
                $shipment->register();
                
                $shipment->getOrder()->setIsInProcess(true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($orderObj)
                ->save();
                
                $shipment->setEmailSent(true);
                
                $orderObj->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                $orderObj->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
                
                $orderObj->save();
                if($this->isTeamworkDataTransferToSalesforce()){
                    Mage::getModel("allure_salesforce/observer_order")->addTeamworkShipmentToSalesforce($shipment->getId());
                }
                
                $tmOrderObj = Mage::getModel("allure_teamwork/tmorder")
                ->load($receiptId,"tm_receipt_id");  
                if($tmOrderObj->getEntityId()){
                    $tmOrderObj->setShipmentStatus("create")->save();
                }
                
                $this->addLog($shipCnt." - shipment created. Shipment Id:".$shipment->getId()." Order Id:".$orderId);
            }catch (Exception $e){
                $this->addLog("07 Exc - ".$e->getMessage());
            }
        }
    }
    
    
    private function createCreditMemo($object){
        $credmCnt = 0;
        $this->addLog("In createCreditMemo");
        foreach ($object as $oData){
            try{
                $credmCnt++;
                $receiptId = $oData["order_detail"]["ReceiptId"];
                $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                if(!$orderObj->getId()){
                    $this->addLog($credmCnt." - Receipt Id:".$receiptId." Order not created.");
                    continue;
                }
                $orderId = $orderObj->getId();
                if (!$orderObj->canCreditmemo()) {
                    $this->addLog($credmCnt." - Order Id:".$orderId." Cannot create credit memo for the order.");
                    continue;
                }
                $savedData = array();
                $qtys = array();
                $backToStock = array();
                $ordered_items = $orderObj->getAllItems();
                $tempArr = array();
                foreach($ordered_items as $item){     //item detail
                    $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                    $otherSysQty = $item->getOtherSysQty();
                    if($otherSysQty < 0){
                        $tempArr[$item->getId()] = $item->getQtyOrdered();
                    }
                }
                
                $data["qtys"] = $tempArr;
                if(count($tempArr) <= 0){
                    $this->addLog($credmCnt." - Order Id:".$orderId." Order is not applicable to creditmemo.");
                    continue;
                }
                
                $invoice = null;
                $invoiceCollection = $orderObj->getInvoiceCollection();
                foreach($invoiceCollection as $invoice1){
                    $invoice = $invoice1;
                }
                
                $service = Mage::getModel('sales/service_order', $orderObj);
                if (0 && $invoice) {
                    $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data)->save();
                    $creditmemo->refund();
                } else {
                    $creditmemo = $service->prepareCreditmemo($data);//->save();
                    foreach ($creditmemo->getAllItems() as $item) {
                        $item->register();
                    }
                    $creditmemo->save();
                    $creditmemo->refund();
                    //$creditmemo->setState(2)->save();
                }
                
                /**
                 * Process back to stock flags
                 */
                foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                    $orderItem = $creditmemoItem->getOrderItem();
                    $parentId = $orderItem->getParentItemId();
                    if (isset($backToStock[$orderItem->getId()])) {
                        $creditmemoItem->setBackToStock(true);
                    } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                        $creditmemoItem->setBackToStock(true);
                    } elseif (empty($savedData)) {
                        $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                    } else {
                        $creditmemoItem->setBackToStock(false);
                    }
                }
                
                Mage::getModel('core/resource_transaction')
                ->addObject($creditmemo)
                ->addObject($orderObj)
                ->save();
                
                if($this->isTeamworkDataTransferToSalesforce()){
                    Mage::getModel("allure_salesforce/observer_order")->addTeamworkCreditmemoToSalesforce($creditmemo->getId());
                }
                
                $tmOrderObj = Mage::getModel("allure_teamwork/tmorder")
                ->load($receiptId,"tm_receipt_id");
                if($tmOrderObj->getEntityId()){
                    $tmOrderObj->setCreditmemoStatus("create")->save();
                }
                
                $this->addLog($credmCnt." - credit memo id:".$creditmemo->getId());
            }catch (Exception $e){
                $this->addLog("08 Exc - ".$e->getMessage());
            }
        }
    }
    
    
    private function addTeamworkProduct($response){
        $this->addLog("In addTeamworkProduct");
        foreach ($response as $object){
            $productDetails = $object["product_details"];
            $productArr = array();
            foreach ($productDetails as $tmProduct){
                $styleNo = trim($tmProduct['StyleNo']);
                $tsku = ($tmProduct['SKU'])?$tmProduct['SKU']:$styleNo;
                $sku = strtoupper(trim($tsku));
                $origPriceWithTax = $tmProduct["OriginalPriceWithTax"];
                $name = $tmProduct['Description4'];
                $price = $tmProduct["OriginalPriceWithoutTax"];
                $tmItemId = $tmProduct["ITEMID"];                
                $productId = Mage::getModel("catalog/product")->getIdBySku($sku);
                
                if(!$productId){
                    $productObj = Mage::getModel("allure_teamwork/tmproduct")
                    ->load($tmItemId,"tm_item_id");
                    if(!$productObj->getEntityId()){
                        $productObj = Mage::getModel("allure_teamwork/tmproduct")
                        ->setTmItemId($tmItemId)
                        ->setName($name)
                        ->setSku($sku)
                        ->setPrice($price)
                        ->save();
                        $this->addLog("Product add into teamwork product table. Sku - ".$sku);
                    }
                    $productObj = null;
                }
            }
        }
    }
    
}

    
    
    
    