<?php
/**
 * @author allure
 */
class Allure_Counterpoint_Model_Order_Api extends Mage_Api_Model_Resource_Abstract{
    
    protected $_ctpnt_logs_file_name    = "counterpoint_api.log";
    
    protected $_ctpnt_logs_invoice      = "counterpoint_invoice.log";
    protected $_ctpnt_logs_shipment     = "counterpoint_shipment.log";
    
    protected $_ctpnt_logs_update_tkt   = "counterpoint_update_ticket.log";
    protected $_ctpnt_logs_orignal_tkt  = "counterpoint_orignal_ticket.log";
    
    protected $_ctpnt_logs_customer     = "counterpoint_customer.log";
    
    protected $_storeId                 = -1;
    protected $_websiteId               = -1;
    
    protected $_store_vba               = -1;
    protected $_store_vmt               = -1;
    
    protected $_website_vba             = -1;
    protected $_website_vmt             = -1;
    
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
    
    const PAYMENT_METHOD_CASH           = "counterpoint_cash";
    const PAYMENT_METHOD_CREDIT_CARD    = "counterpoint_creditcard";
    const SHIPPING_METHOD               = "counterpoint_storepickupshipping";
    //const SHIPPING_METHOD_NAME          = "Webpos Shipping Storepickup";
    
    const SIMPLE_PRODUCT                = "simple";
    
    const COUNTERPOINT_STORE_NAME       = "counterpoint";
    
    const COUNTERPOINT_STORE_VBA        = "counterpoint_vba";
    const COUNTERPOINT_STORE_VMT        = "counterpoint_vmt";
    
    /**
     * @param $logData
     */
    private function AddLog($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_file_name,true);
    }
    
    private function addLogInvoice($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_invoice,true);
    }
    
    private function addLogPayment($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_shipment,true);
    }
    
    private function addLogUpdateOrder($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_update_tkt,true);
    }
    
    private function addLogOrignalOrder($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_orignal_tkt,true);
    }
    
    private function addLogCustomer($logData){
        Mage::log($logData,Zend_log::DEBUG,$this->_ctpnt_logs_customer,true);
    }
    
    /**
     * @param string $counterpoint_data
     * @return number
     */
    public function test($counterpoint_data,$memory_limit = null,$is_memory_limit = 0){
        $counterpoint_data = utf8_decode($counterpoint_data);
        $counterpoint_data = trim($counterpoint_data,'"');
        $counterpoint_data = stripslashes($counterpoint_data);
        $counterpointData = unserialize($counterpoint_data);
        $this->AddLog("Total Order Count-:".count($counterpointData));
        $this->AddLog("memory limit-:".$memory_limit);
        $this->AddLog("is memory limit-:".$is_memory_limit);
        if($is_memory_limit){
            if(!empty($memory_limit)){
                ini_set('memory_limit', $memory_limit);
                ini_set('max_execution_time', -1);
            }
        }
        $this->importCPSQLOrderIntoMagento($counterpointData);
        $counterpointData = null;
        return 1;
    }
    
    /**
     * add payment data
     */
    public function addPayment($payment_data){
        $payment_data = utf8_decode($payment_data);
        $payment_data = trim($payment_data,'"');
        $payment_data = stripslashes($payment_data);
        $paymentData = unserialize($payment_data);
        $this->addLogInvoice("order count for payment-:".count($paymentData));
        $this->addPaymentData($paymentData);
        return 1;
    }
    
    public function addShipment($shipment_data){
        $shipment_data = utf8_decode($shipment_data);
        $shipment_data = trim($shipment_data,'"');
        $shipment_data = stripslashes($shipment_data);
        $shipmentData = unserialize($shipment_data);
        $this->addLogPayment("order count for shipment-:".count($shipmentData));
        $this->createOrderShipment($shipmentData);
        return 1;
    }
    
    /**
     * add counterpoint orignal ticket no against counterpoint order
     */
    public function updateTicketByOrignalTicket($order_data){
        $order_data = utf8_decode($order_data);
        $order_data = trim($order_data,'"');
        $order_data = stripslashes($order_data);
        $orderData = unserialize($order_data);
        $this->addLogUpdateOrder("order count for update-:".count($orderData));
        $this->updateTicketData($orderData);
        return 1;
    }
    
    /**
     * add missing line item order data 
     */
    public function addOrignalTicket($ticket_data){
        $ticket_data = utf8_decode($ticket_data);
        $ticket_data = trim($ticket_data,'"');
        $ticket_data = stripslashes($ticket_data);
        $ticketData = unserialize($ticket_data);
        $this->addLogOrignalOrder("origanl order count-:".count($ticketData));
        return 1;
    }
    
    private function updateTicketData($orderData){
        $cntT = 0;
        foreach ($orderData as $order_id => $data){
            try{
                $order = Mage::getModel('sales/order')
                                ->load($order_id,'counterpoint_order_id');
                $this->addLogUpdateOrder("order count-:".$cntT);
                if($order->getId()){
                    $orignalTicket = $data['order']['orig_tkt_no'];
                    $order->setCounterpointOrigTktNo($orignalTicket);
                    $order->save();
                    $this->addLogUpdateOrder("orignal tkt no-:".$orignalTicket." save against order id-:".$order->getIncrementId());
                }
            }catch(Exception $e){
                $this->addLogUpdateOrder($e->getMessage());
            }
            $cntT++;
        }
        $this->addLogUpdateOrder("finish...");
    }
    
    private function createOrderShipment($shipmentData){
        $cntShip = 1;
        foreach ($shipmentData as $order_id => $odrData){
            try{
                $order_type = $odrData['order_type'];
                $order = Mage::getModel('sales/order')->load($order_id,'counterpoint_order_id');
                if($order_type == "ord"){
                    $order = Mage::getModel('sales/order')->load($order_id,'counterpoint_orig_tkt_no');
                }
                
                if($order->getId()) {
                    $this->addLogPayment("order increment id-:".$order->getIncrementId());
                    if($order->hasShipments()){
                        $this->addLogPayment("Order has already shipment."); 
                    }else{
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
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                ->addObject($shipment)
                                ->addObject($shipment->getOrder())
                                ->save();
                            $shipmentId = $shipment->getIncrementId();
                            $this->addLogPayment("Order shipment created.shipment increment id-:".$shipmentId);
                            $shipment = null;
                            $order = null;
                        }
                    }
                }else{
                    $this->addLogPayment("Order not created.counterpoint order id-:".$order_id);
                }
            }catch (Exception $e){
                $this->addLogPayment("Exception in createCPOrderShipment");
                $this->addLogPayment("Exception-:".$e->getMessage());
            }
            $this->addLogPayment("count-:".$cntShip);
            $cntShip++;
        }
        $this->addLogPayment("Finish...");
    }
    
    private function addPaymentData($payment_data){
        $seqCnt = 0;
        foreach ($payment_data as $order_id => $payData){
            try{
                $order_type = $payData['order_type'];
                $orderObj = Mage::getModel('sales/order')
                            ->load($order_id,'counterpoint_order_id');
                if($order_type == "ord"){
                    $orderObj = Mage::getModel('sales/order')->load($order_id,'counterpoint_orig_tkt_no');
                }
                
                $this->addLogInvoice("order cnt -:".$seqCnt);
                if($orderObj->getId()){
                    if($orderObj->hasInvoices()){
                        $this->addLogInvoice("Invoice already created.order id-:".$orderObj->getIncrementId());
                    }else{
                        $this->addLogInvoice("Order counterpoint order present.Id-:".$orderObj->getIncrementId());
                        $payments = $payData['payment'];
                        //create invoice for created order
                        $ordered_items = $orderObj->getAllItems();
                        $savedQtys = array();
                        foreach($ordered_items as $item){     //item detail
                            $savedQtys[$item->getItemId()] = $item->getQtyOrdered();
                        }
                        $payCount = count($payments);
                        $paymentCnt = 0;
                        $paymentInfo = array();
                        $totalPaidAmount = 0;
                        foreach ($payments as $payment){
                            $cr_card_no_msk     = $payment['cr_card_no_msk'];
                            $payCodeType        = $payment['pay_cod_typ'];
                            $paycode            = $payment['pay_cod'];
                            $amount             = $payment['home_curncy_amt'];//$payment['amt'];
                            $amountHomeCurncy   = $payment['home_curncy_amt'];
                            $totalPaidAmount    = $totalPaidAmount + $amount;
                            $invoice = Mage::getModel('sales/service_order', $orderObj)
                                                ->prepareInvoice($savedQtys);
                            $invoice->setRequestedCaptureCase(self::ORDER_CAPTURE_CASE);
                            $invoice->register();
                            $invoice->getOrder()->setIsInProcess(true);
                            $invoice->setState(self::INVOICE_STATE);
                            $invoice->setCanVoidFlag(0);
                            
                            //if($payCount > 1){
                                $isShowPay = true;
                                $incAmount = $payment['home_curncy_amt'];//$payment['amt'];
                                if($incAmount <= 0){
                                    $isShowPay = false;
                                }
                                
                                $invoice->setBaseGrandTotal($incAmount);
                                $invoice->setGrandTotal($incAmount);
                                $invoice->setSubtotalInclTax($incAmount);
                                $invoice->setSubtotal($incAmount);
                                $invoice->setBaseSubtotal($incAmount);
                           // }
                            
                            $invoice->save();
                            //if($payCodeType == "E" || $payCodeType =="D"){
                            if(!empty($cr_card_no_msk)){
                                $orderPay = $orderObj->getPayment();
                                if($paymentCnt > 0){
                                    $orderPay = Mage::getModel("sales/order_payment");
                                    $orderPay->setParentId($orderObj->getId());
                                    $orderPay->setAmountPaid($amount);
                                    $orderPay->setBaseAmountPaid($amount);
                                }
                                $orderPay->setMethod("counterpoint_creditcard");
                                $ccType = $this->getPayCode($paycode);
                                $ccLast4 = substr($cr_card_no_msk, -4);
                                $orderPay->setCcType($ccType);
                                $orderPay->setCcLast4($ccLast4);
                                $expireDate = $payment['cr_card_exp_dat'];
                                if(!empty($expireDate)){
                                    $exMonth = date("m",strtotime($expireDate));
                                    $exYear = date("Y",strtotime($expireDate));
                                    $orderPay->setCcExpMonth($exMonth);
                                    $orderPay->setCcExpYear($exYear);
                                }
                                $responseTrn = $this->getDummyResponseOfTransaction($orderObj,$invoice,$payment);
                                $orderPay->setAdditionalInformation($responseTrn);
                                $orderPay->save();
                                
                                $paymentCnt++;
                                $paymentInfo[$invoice->getId()] = array('is_show'=>$isShowPay,'payment_id'=>$orderPay->getId(),'amt'=>$incAmount);
                                
                                if(!empty($payment['processor_client_rcpt'])){
                                    $orderObj->addStatusHistoryComment($payment['processor_client_rcpt']);
                                    $orderObj->addStatusHistoryComment($payment['processor_merch_rcpt']);
                                    $orderObj->save();
                                }
                                $this->createTransaction($orderObj,$payment);
                            }else{
                                $payDesc = $payment['descr'];
                                $changeMethod = $this->getChangePaymentMethod($paycode,$payDesc);
                                if($changeMethod['is_change']){
                                    $methodCode = $changeMethod['method_code'];
                                    $orderPay = $orderObj->getPayment();
                                    if($paymentCnt > 0){
                                        $orderPay = Mage::getModel("sales/order_payment");
                                        $orderPay->setParentId($orderObj->getId());
                                    }
                                    $orderPay->setMethod($methodCode);
                                    $orderPay->save();
                                    $paymentCnt++;
                                    $paymentInfo[$invoice->getId()] = array('is_show'=>$isShowPay,'payment_id'=>$orderPay->getId(),'amt'=>$incAmount);
                                }
                            }
                           
                            $this->addLogInvoice("invoice created.id-:".$invoice->getIncrementId());
                        }
                        $increment_id = $orderObj->getIncrementId();
                        
                        //$shipmentId = $this->createShipment($increment_id);
                        
                        $orderObj->getPayment()->setAdditionalData(serialize($paymentInfo))->save();
                        $counterpointExtraInfo = unserialize($orderObj->getCounterpointExtraInfo());
                        $counterpointExtraInfo['payment_info'] = $payments;
                        $counterpointExtraInfo = serialize($counterpointExtraInfo);
                        $orderObj->setCounterpointExtraInfo($counterpointExtraInfo);
                        
                        $orderObj->setTotalPaid($totalPaidAmount);
                        $orderObj->save();
                    }
                }else{
                    $this->addLogInvoice("Order not created yet.counterpoint_order_id-:".$order_id);
                }
            }catch(Exception $e){
                $this->addLogInvoice("Exception in setPaymentData method");
                $this->addLogInvoice("Exception -: ".$e->getMessage());
            }
            $seqCnt++;
        }
        $this->addLogInvoice("finish...");
    }
    
    
    private function prepareCounterpointSettings(){
        $this->_shippingMethodCode  = self::SHIPPING_METHOD;    //$shippingMethodCode;
        $shippingMethodArr          = Mage::getModel("allure_counterpoint/entity_shippingMethods")->toOptionArray();
        $this->_shippingMethodName  = $shippingMethodArr[$this->_shippingMethodCode]['label'];
        
        $storeVBA = Mage::getModel('allure_virtualstore/store')->load(self::COUNTERPOINT_STORE_VBA,'code');
        if(!$storeVBA){
            $this->AddLog("Invalid store VBA.");
            die;
        }
        
        $storeVMT = Mage::getModel('allure_virtualstore/store')->load(self::COUNTERPOINT_STORE_VMT,'code');
        if(!$storeVMT){
            $this->AddLog("Invalid store VMT.");
            die;
        }
        
        $websiteIdVBA = $storeVBA->getWebsiteId();
        if($websiteIdVBA == 1){
            $this->AddLog("You can't save data to main_website.");
            die;
        }
        
        $websiteIdVMT = $storeVMT->getWebsiteId();
        if($websiteIdVMT == 1){
            $this->AddLog("You can't save data to main_website.");
            die;
        }
        
        $this->_store_vba = $storeVBA->getId();
        $this->_store_vmt = $storeVMT->getId();
        $this->_website_vba = $websiteIdVBA;
        $this->_website_vmt = $websiteIdVMT;
    }
    
    /**
     * @param array $counterpointOrderArr
     */
    private function importCPSQLOrderIntoMagento($counterpointOrderArr){
        try{
            $this->prepareCounterpointSettings();
            $this->AddLog("counterpoint VBA store_id-:".$this->_store_vba);
            $this->AddLog("counterpoint VBA website_id-:".$this->_website_vba);
            $this->AddLog("counterpoint VMT store_id-:".$this->_store_vmt);
            $this->AddLog("counterpoint VMT website_id-:".$this->_website_vmt);
            $count = 1;
            foreach ($counterpointOrderArr as $order_id_key => $order_data_arr){
                $ctrpnt_order_id = $order_id_key;//$this->getCounterpointOrderId($order_id_key);
                //$this->createOrderByUsingCounterpointData($ctrpnt_order_id, $order_data_arr);
                
                $ctpnt_order_id = $ctrpnt_order_id;
                $ctpnt_order_data = $order_data_arr;
                try{
                    $productModel = Mage::getModel("catalog/product");
                    $order_type = $ctpnt_order_data['order_type'];
                    $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'increment_id');
                    if(!$orderObj->getId()){
                        $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'counterpoint_order_id');
                        if($order_type == "ord"){
                            $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'counterpoint_orig_tkt_no');
                        }
                        
                        if(!$orderObj->getId()){
                            $this->AddLog("counterpoint order_id:-".$ctpnt_order_id." not present in magento.");
                            $productsArr = $ctpnt_order_data['item_detail'];
                            $extraOrderDetails = $ctpnt_order_data['order_detail'];
                            
                            $extraInfo = $ctpnt_order_data['extra_data'];
                            $isOrderCreate = false;
                            if($extraInfo['str_id'] == 1){
                                $this->_storeId = $this->_store_vba;
                                $this->_websiteId = $this->_website_vba;
                                $isOrderCreate = true;
                            }elseif($extraInfo['str_id'] == 2){
                                $this->_storeId = $this->_store_vmt;
                                $this->_websiteId = $this->_website_vmt;
                                $isOrderCreate = true;
                            }else{
                                $this->_storeId = $this->_store_vmt;
                                $this->_websiteId = $this->_website_vmt;
                                $isOrderCreate = true;
                            }
                            
                            if(count($productsArr) > 0 && $isOrderCreate){
                                $this->AddLog("store id -:".$this->_storeId);
                                $customerDetailArr = $ctpnt_order_data['customer_detail'];
                                $customer = $this->insertCustomer($customerDetailArr);
                                $billingAddress = $this->getBillingAddressOfCtpnt($customer,$customerDetailArr);
                                
                                $quoteObj = Mage::getModel('sales/quote')
                                ->assignCustomer($customer);
                                $quoteObj = $quoteObj->setStoreId(1);
                                
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
                                    $quoteItem->setStoreId(1);
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
                                
                                $quoteObj->setOldStoreId($this->_storeId);
                                
                                //order status as counterpoint 1
                                $quoteObj->setCreateOrderMethod(self::COUNTERPOINT_ORDER);
                                $quoteObj->setCounterpointOrderId($ctpnt_order_id);
                                
                                $quoteObj->setCounterpointOrderType($order_type);
                                $quoteObj->setCounterpointStrId($extraInfo['str_id']);
                                $quoteObj->setCounterpointStaId($extraInfo['sta_id']);
                                $quoteObj->setCounterpointDrwId($extraInfo['drw_id']);
                                $quoteObj->setCounterpointDocId($extraInfo['doc_id']);
                                $extraInfoSer = serialize($extraInfo);
                                $quoteObj->setCounterpointExtraInfo($extraInfoSer);
                                
                                $quoteObj->setOrderType(self::ORDER_TYPE);
                                $incrementIdQ = $quoteObj->getReservedOrderId();
                                if($incrementIdQ){
                                    $incrementIdQ = "CP-".$incrementIdQ;
                                    $quoteObj->setReservedOrderId($incrementIdQ);
                                }
                                
                                $ccInfo = array();
                                // assign payment method
                                $payment_method  = self::PAYMENT_METHOD_CASH;
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
                                //$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                                if(!$quoteObj->getIsVirtual()) {
                                    $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                                }
                                
                                $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                                
                                $items=$quoteObj->getAllItems();
                                foreach ($items as $item) {
                                    $productId = $productModel->getIdBySku($item->getSku());
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
                                    if($productId){
                                        $orderItem->setData('product_id',$productId);
                                    }
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
                                //$orderObj->setTotalPaid($totalAmmount);
                                
                                //complete the order status
                                $orderObj->setData('state',self::ORDER_STATE)
                                ->setData('status',self::ORDER_STATUS);
                                
                                $orderObj->setCreatedAt($extraOrderDetails['order_date']);
                                try{
                                    $orderObj->save();
                                    $quoteObj->save();
                                    $increment_id = $orderObj->getRealOrderId();
                                    $this->AddLog("new order increment_id-:".$increment_id);
                                    
                                    $quoteObj       = null;
                                    $orderObj       = null;
                                    $customer       = null;
                                    $billingAddress = null;
                                    $productModel   = null;
                                    
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
                    $this->AddLog("Exception in importCPSQLOrderIntoMagento method of Class name is-:".get_class($this));
                    $this->AddLog($e->getMessage());
                    $e = null;
                }
                $orderObj = null;
                
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
            $productModel = Mage::getModel("catalog/product");
            $order_type = $ctpnt_order_data['order_type'];
            $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'increment_id');
            if(!$orderObj->getId()){
               $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'counterpoint_order_id');
               if($order_type == "ord"){
                   $orderObj = Mage::getModel('sales/order')->load($ctpnt_order_id,'counterpoint_orig_tkt_no');
               }
               
               if(!$orderObj->getId()){
                     $this->AddLog("counterpoint order_id:-".$ctpnt_order_id." not present in magento.");
                     $productsArr = $ctpnt_order_data['item_detail'];
                     $extraOrderDetails = $ctpnt_order_data['order_detail'];
                     
                     $extraInfo = $ctpnt_order_data['extra_data'];
                     $isOrderCreate = false;
                     if($extraInfo['str_id'] == 1){
                         $this->_storeId = $this->_store_vba;
                         $this->_websiteId = $this->_website_vba;
                         $isOrderCreate = true;
                     }elseif($extraInfo['str_id'] == 2){
                         $this->_storeId = $this->_store_vmt;
                         $this->_websiteId = $this->_website_vmt;
                         $isOrderCreate = true;
                     }else{
                         $this->_storeId = $this->_store_vmt;
                         $this->_websiteId = $this->_website_vmt;
                         $isOrderCreate = true;
                     }
                     
                     if(count($productsArr) > 0 && $isOrderCreate){
                        $this->AddLog("store id -:".$this->_storeId);
                        $customerDetailArr = $ctpnt_order_data['customer_detail'];
                        $customer = $this->insertCustomer($customerDetailArr);
                        $billingAddress = $this->getBillingAddressOfCtpnt($customer,$customerDetailArr);
                        
                        $quoteObj = Mage::getModel('sales/quote')
                                        ->assignCustomer($customer);
                        $quoteObj = $quoteObj->setStoreId(1);
                        
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
                            $quoteItem->setStoreId(1);
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
                        
                        $quoteObj->setOldStoreId($this->_storeId);
                        
                        //order status as counterpoint 1
                        $quoteObj->setCreateOrderMethod(self::COUNTERPOINT_ORDER); 
                        $quoteObj->setCounterpointOrderId($ctpnt_order_id);
                        
                        $quoteObj->setCounterpointOrderType($order_type);
                        $quoteObj->setCounterpointStrId($extraInfo['str_id']);
                        $quoteObj->setCounterpointStaId($extraInfo['sta_id']);
                        $quoteObj->setCounterpointDrwId($extraInfo['drw_id']);
                        $quoteObj->setCounterpointDocId($extraInfo['doc_id']);
                        $extraInfoSer = serialize($extraInfo);
                        $quoteObj->setCounterpointExtraInfo($extraInfoSer);
                        
                        $quoteObj->setOrderType(self::ORDER_TYPE);
                        $incrementIdQ = $quoteObj->getReservedOrderId();
                        if($incrementIdQ){
                            $incrementIdQ = "CP-".$incrementIdQ;
                            $quoteObj->setReservedOrderId($incrementIdQ);
                        }
                            
                        $ccInfo = array();
                        // assign payment method
                        $payment_method  = self::PAYMENT_METHOD_CASH;
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
                        //$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                        if(!$quoteObj->getIsVirtual()) {
                           $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                        }
                            
                        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                            
                        $items=$quoteObj->getAllItems();
                        foreach ($items as $item) {
                            $productId = $productModel->getIdBySku($item->getSku());
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
                           if($productId){
                               $orderItem->setData('product_id',$productId);
                           }
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
                        //$orderObj->setTotalPaid($totalAmmount);
                            
                        //complete the order status
                        $orderObj->setData('state',self::ORDER_STATE)
                                 ->setData('status',self::ORDER_STATUS);
                            
                        $orderObj->setCreatedAt($extraOrderDetails['order_date']);
                        try{
                              $orderObj->save();
                              $quoteObj->save();
                              $increment_id = $orderObj->getRealOrderId();
                              $this->AddLog("new order increment_id-:".$increment_id);

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
    
    private function getChangePaymentMethod($payMethod,$desc){
        $isChange = false;
        $method_code = "";
        if($payMethod == "CHK"){
            $isChange = true;
            $method_code = "counterpoint_check";
        }elseif($payMethod == "GIFT"){
            $isChange = true;
            $method_code = "counterpoint_gift";
        }elseif($payMethod == "STCR"){
            $isChange = true;
            $method_code = "counterpoint_stcr";
        }elseif($payMethod == "WHOLESALE"){
            $isChange = true;
            $method_code = "counterpoint_wholesale";
        }elseif($payMethod == "WIRED"){
            $isChange = true;
            $method_code = "counterpoint_wired";
        }elseif($payMethod == "PAYPAL"){
            $isChange = true;
            $method_code = "counterpoint_paypal";
        }elseif($payMethod == "CASH"){
            $isChange = true;
            $method_code = "counterpoint_cash";
        }elseif($payMethod == "AUTHORIZE"){
            $isChange = true;
            $method_code = "counterpoint_authorize";
        }elseif($payMethod == "COUP"){
            $isChange = true;
            $method_code = "counterpoint_coup";
        }elseif($payMethod == "INTERNET"){
            $isChange = true;
            $method_code = "counterpoint_internet";
        }elseif($payMethod == "AMAZON"){
            $isChange = true;
            $method_code = "counterpoint_amazon";
        }elseif($payMethod == "BDAY"){
            $isChange = true;
            $method_code = "counterpoint_bday";
        }elseif($payMethod == "PLDGC"){
            $isChange = true;
            $method_code = "counterpoint_pldgc";
        }elseif($payMethod == "£ CASH"){
            $isChange = true;
            $method_code = "counterpoint_poundcash";
        }elseif($desc == "Pound Cash"){
            $isChange = true;
            $method_code = "counterpoint_poundcash";
        }
        
        
        return array('is_change'=>$isChange,'method_code'=>$method_code);
    }
    
    private function getPayCode($payMethod){
        $paycode = "";
        if($payMethod == "MC"){
            $paycode = "MC";
        }elseif($payMethod == "VISA"){
            $paycode = "VI";
        }elseif($payMethod == "AMEX"){
            $paycode = "AM";
        }elseif($payMethod == "DISC"){
            $paycode = "DI";
        }
        return $paycode;
    }
    
    private function createTransaction($order,$payment){
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setOrderId($order->getId());
        $transaction->setOrderPaymentObject($order->getPayment());
        $transaction->setTxnType("capture");
        //$transaction->setTxnId($payment['processor_trans_id']);
        $transaction->setIsClosed(0);
        $additinalInfo = $order->getPayment()->getAdditionalInformation();
        if ($additinalInfo) {
            foreach ($additinalInfo as $key => $value) {
                $transaction->setAdditionalInformation($key, $value);
            }
        }
        $transaction->save();
    }
    
    private function getDummyResponseOfTransaction($order, $invoice,$payment){
        $customerId = $order->getCustomerId();
        $amount     = $payment['amt'];//$order->getBaseGrandTotal();
        $invoiceNumber = $invoice->getIncrementId();
        $accNumber = "XXXX".substr($payment['cr_card_exp_dat'], -4);
        $payMethod = $payment['pay_cod'];
        $cardType = "";
        if($payMethod == "MC"){
            $cardType = "MasterCard";
        }elseif($payMethod == "VISA"){
            $cardType = "VISA";
        }elseif($payMethod == "AMEX"){
            $cardType = "American Express";
        }elseif($payMethod == "DISC"){
            $cardType = "Discover";
        }
        
        $res = array("save" => "1",
            "response_code" => "1",
            "response_subcode" =>"",
            "response_reason_code" => "0",
            "response_reason_text" =>"",
            "approval_code" => "000000",
            "auth_code" => "000000",
            "avs_result_code" => "P",
            "transaction_id" => $payment['processor_trans_id'],
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
            "is_error" =>"");
        return $res;
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
            $email = strtolower($emailName)."@customers.mariatash.com";
        }
        $email = strtolower($email);
        $customer = Mage::getModel('customer/customer')
                          ->setWebsiteId(1)
                          ->loadByEmail($email);
             
        if(!$customer->getId()){
            $groupId = self::GENERAL_CUSTOMER;
            if($group == "B"){
                $groupId = self::WHOLESELLER_CUSTOMER;
            }
            
            $password = $this->generateRandomPassword();
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId(1)
                ->setStoreId(1)
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
    
    
    /**
     * create new customers in magento
     */
    public function addCustomer($customer_data){
        $customer_data = utf8_decode($customer_data);
        $customer_data = trim($customer_data,'"');
        $customer_data = stripslashes($customer_data);
        $customertData = unserialize($customer_data);
        $this->addLogCustomer("Customer count -:".count($customertData));
        $this->addCustomerData($customertData);
        return 1;
    }
    
    private function addCustomerData($customertData){
        $this->prepareCounterpointSettings();
        $cnt = 1;
        foreach ($customertData as $customer){
            try{
                $this->addLogCustomer("customer count -:".$cnt);
                $cnt++;
                $customerAddress    = $customer['customer_detail'];
                $customerDetailArr  = $customerAddress[1];
                $extraInfo  = $customer['extra'];
                
                $storeId    = "";
                $websiteId  = "";
                if($extraInfo['str_id'] == 1){
                    $storeId    = $this->_store_vba;
                    $websiteId  = $this->_website_vba;
                }elseif($extraInfo['str_id'] == 2){
                    $storeId    = $this->_store_vmt;
                    $websiteId  = $this->_website_vmt;
                }else{
                    $storeId    = $this->_store_vmt;
                    $websiteId  = $this->_website_vmt;
                }
                
                $email      = $customerDetailArr['email'];
                $street     = $customerDetailArr['street'];
                $city       = $customerDetailArr['city'];
                $state      = $customerDetailArr['state'];
                $country    = $customerDetailArr['country']?$customerDetailArr['country']:"";
                $zip_code   = $customerDetailArr['zip_code'];
                $phone      = $customerDetailArr['phone'];
                $name       = $customerDetailArr['name'];
                
                $group      = ($extraInfo['nam_typ'])?$extraInfo['nam_typ']:"P";
                
                $name        = explode(" ", $name);
                $firstName  = $name[0];
                $lastName   = $name[0];
                if(count($name) > 1){
                    $lastName = $name[1];
                }
                    
                    if(empty($email)){
                        $emailName = $firstName."".$lastName;
                        $email = strtolower($emailName)."@customers.mariatash.com";
                    }
                    $email = strtolower($email);
                    
                    $this->addLogCustomer("customer email -:".$email);
                    
                    $customer = Mage::getModel('customer/customer')
                        ->setWebsiteId(1)
                        ->loadByEmail($email);
                    
                    if(!$customer->getId()){
                        $groupId = self::GENERAL_CUSTOMER;
                        if($group == "B"){
                            $groupId = self::WHOLESELLER_CUSTOMER;
                        }
                        
                        $password = $this->generateRandomPassword();
                        $customer = Mage::getModel("customer/customer");
                        $customer->setWebsiteId(1)
                            ->setStoreId(1)
                            ->setGroupId($groupId)
                            ->setFirstname($firstName)
                            ->setLastname($lastName)
                            ->setEmail($email)
                            ->setPassword($password)
                            ->setCustomerType(self::COUNTERPOINT_CUSTOMER)  //counterpoint
                            ->save();
                        
                        $_billing_address = array (
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
                        $address->setData($_billing_address)
                            ->setCustomerId($customer->getId())
                            ->setIsDefaultBilling('1');
                            
                        if(count($customerAddress) == 1){
                            $address->setIsDefaultShipping('1');
                        }
                        
                        $address->setSaveInAddressBook('1');
                        $address->save();
                        
                        if(count($customerAddress) == 2){
                            $shipAddr = $customerAddress[2];
                            
                            $firstName  = $shipAddr['fst_nam']; 
                            $lastName   = $shipAddr['lst_nam'];
                            
                            if(!empty($shipAddr['street'])){
                                $_shipping_address = array (
                                    'firstname'  => $firstName,
                                    'lastname'   => $lastName,
                                    'street'     => array (
                                        '0' => $shipAddr['street']
                                    ),
                                    'city'       => $shipAddr['city'],
                                    'postcode'   => $shipAddr['zip_code'],
                                    'country_id' => $shipAddr['country']?$shipAddr['country']:"",
                                    'region' 	=> 	$shipAddr['state'],
                                    'telephone'  => $shipAddr['phone'],
                                    'fax'        => '',
                                );
                                $addressShip = Mage::getModel("customer/address");
                                $addressShip->setData($_shipping_address)
                                    ->setCustomerId($customer->getId())
                                    ->setIsDefaultShipping('1')
                                    ->setSaveInAddressBook('1');
                                $addressShip->save();
                                $this->addLogCustomer("New shipping address add.");
                                $addressShip  = null;
                            }
                        }
                        $address = null;
                        $this->addLogCustomer("New customer create.customer_id-:".$customer->getId());
                    }else{
                        $this->addLogCustomer("customer already present");
                    }
                    $customer = null;
            }catch (Exception $e){
                $this->addLogCustomer("Exception -: ".$e->getMessage());
            }
        }
        $this->addLogCustomer("Finish...");
    }
}