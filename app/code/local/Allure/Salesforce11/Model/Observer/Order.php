<?php
/**
 * @author aws02
 */
class Allure_Salesforce_Model_Observer_Order{	
    /**
     * return Allure_Salesforce_Helper_SalesforceClient
     */
    private function getHelper(){
        return Mage::helper("allure_salesforce/salesforceClient");
    }
    
    /**
     * add order data into salesforce when order placed into magento
     */
    public function addOrderToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addOrderToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();
        
        //check product is in salesforce or not.if not add into salesforce.
        Mage::getModel("allure_salesforce/observer_product")->addOrderProduct($items);
        
        $helper->salesforceLog("order id == ".$order->getId());
        
        $orderId = $order->getId();
        $status = $order->getStatus();
        $customerId = $order->getCustomerId();
        
        $salesforceAccountId = $helper::GUEST_CUSTOMER_ACCOUNT;
        if($customerId){
            $customer = Mage::getModel("customer/customer")->load($customerId);
            $salesforceAccountId = $customer->getSalesforceCustomerId();
            if(!$salesforceAccountId){
                //$guestAccount = Mage::helper('allure_salesforce')->getGuestAccount();
                //$salesforceAccountId = $guestAccount; //$helper::GUEST_CUSTOMER_ACCOUNT;
                
                //create new account for the customer
                $helper->salesforceLog("from order customer account creating.");
                $customer->save();
                $salesforceAccountId = $customer->getSalesforceCustomerId();
            }
            /* if(!$salesforceAccountId){
                $customer->save();
                $salesforceAccountId = $customer->getSalesforceCustomerId();
            } */
        }
        
        $customerEmail = $order->getCustomerEmail();
        $customerGroup = $order->getCustomerGroupId();
        
        $pricebookId = Mage::helper('allure_salesforce')->getGeneralPricebook(); //$helper::RETAILER_PRICEBOOK_ID;
        if($customerGroup == 2){
            $pricebookId = Mage::helper('allure_salesforce')->getWholesalePricebook(); //$helper::WHOLESELLER_PRICEBOOK_ID;
        }
        
        $totalQty = $order->getTotalQtyOrdered();
        
        $totalItemCount = $order->getTotalItemCount();
        
        $incrementId = $order->getIncrementId();
        $shipingMethod = $order->getShippingMethod();
        $createdAt = $order->getCreatedAt();
        $counterpointOrderId = $order->getCounterpointOrderId();
        $shippingDescription = $order->getShippingDescription();
        
        $subtotal = $order->getSubtotal();
        $baseSubtotal = $order->getBaseSubtotal();
        $grandTotal = $order->getGrandTotal();
        $baseGrandTotal = $order->getBaseGrandTotal();
        $discountAmount = $order->getDiscountAmount();
        $baseDiscountAmount = $order->getBaseDiscountAmount();
        $shippingAmount = $order->getShippingAmount();
        $baseShippingAmount = $order->getBaseShippingAmount();
        
        $taxAmount = $order->getTaxAmount();
        $baseTaxAmount = $order->getBaseTaxAmount();
        
        $totalPaid = $order->getTotalPaid();
        $baseTotalPaid = $order->getBaseTotalPaid();
        $totalRefunded = $order->getTotalRefunded();
        $baseTotalRefunded = $order->getBaseTotalRefunded();
        $totalInvoiced = $order->getTotalInvoiced();
        $baseTotalInvoiced = $order->getBaseTotalInvoiced();
        
        $baseTotalDue = $order->getBaseTotalDue();
        
        $billingAddr = $order->getBillingAddress();
        $shippingAddr = $order->getShippingAddress();
        
        $customerNote = Mage::helper('giftmessage/message')->getEscapedGiftMessage($order);
        
        $paymentMethod  = $order->getPayment()->getMethodInstance()->getTitle();
        
        $state       = "";
        $countryName = "";
        if($billingAddr){
            if($billingAddr['region_id']){
                $region = Mage::getModel('directory/region')
                ->load($billingAddr['region_id']);
                $state = $region->getName();
            }else{
                $state = $billingAddr['region'];
            }
            
            $country = Mage::getModel('directory/country')
            ->loadByCode($billingAddr['country_id']);
            $countryName = $country->getName();
        }
        
        $stateShip       = "";
        $countryNameShip = "";
        if($shippingAddr){
            if($shippingAddr['region_id']){
                $region = Mage::getModel('directory/region')
                ->load($shippingAddr['region_id']);
                $stateShip = $region->getName();
            }else{
                $stateShip = $shippingAddr['region'];
            }
            
            $country = Mage::getModel('directory/country')
            ->loadByCode($shippingAddr['country_id']);
            $countryNameShip = $country->getName();
        } 
        
        $orderItem = array();
        $orderItem["records"] = array();
        
        $magOrderItemArr = array();
        foreach ($items as $item){
            
            $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
            $product = Mage::getModel("catalog/product")->load($productId);
            $salesforcePricebkEntryId = "";
            if($product){
                $salesforcePricebkEntryId = $product->getSalesforceStandardPricebk();
                if($customerGroup == 2){
                    $salesforcePricebkEntryId = $product->getSalesforceWholesalePricebk();
                }
            }
            
            if(!$salesforcePricebkEntryId){
                $helper->salesforceLog("place this order when product has assigned the salesforce id.");
                return ;
            }
            
            
            $magOrderItemArr[] = array("item_id" => $item->getItemId(),
                "salesforce_id" => $item->getSalesforceItemId(),
                "sku"           => $item->getSku(),
                "order_id"      => $item->getOrderId()
            );
            
            $options = $item->getProductOptions()["options"];
            $postLength = "";
            foreach ($options as $option){
                if($option["label"] == "Post Length"){
                    $postLength = $option["value"];
                    break;
                }
            }
            
            $itemArray = array(
                "attributes"        => array("type" => "OrderItem"),
                "PricebookEntryId"  => $salesforcePricebkEntryId,//"01u290000037WAR",
                "quantity"          => $item->getQtyOrdered(),
                "UnitPrice"         => $item->getBasePrice(),
                "Post_Length__c"    => $postLength,
                "Magento_Order_Item_Id__c" => $item->getItemId(),
                "SKU__c"                => $item->getSku(),
                
            );
            array_push($orderItem["records"],$itemArray);
        }
        
        $salesforceOrderId = $order->getSalesforceOrderId();
        $objectType = $helper::ORDER_OBJECT;
        if($salesforceOrderId){
            $requestMethod = "PATCH";
        }else{
            
            $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
            $oldStoreArr = array();
            foreach ($ostores as $storeO){
                $oldStoreArr[$storeO->getId()] = $storeO->getName();
            }
            $oldStoreArr[0] = "Admin";
            
            $requestMethod = "POST";
            $request = array();
            $request["order"] = array(
                array(
                    "attributes"            => array("type" => "order"),
                    "EffectiveDate"         => date("Y-m-d",strtotime($createdAt)),
                    "Status"                => $status,
                    "accountId"             => $salesforceAccountId,    //"0012900000Ls44hAAB",
                    "Pricebook2Id"          => $pricebookId,    //"01s290000001ivyAAA",//$pricebookId,
                    "BillingCity"           => ($billingAddr) ? $billingAddr["city"] : "",
                    "BillingCountry"        => $countryName,
                    "BillingPostalCode"     => ($billingAddr) ? $billingAddr["postcode"] : "",
                    "BillingState"          => $state,
                    "BillingStreet"         => ($billingAddr) ? $billingAddr["street"] : "",
                    "ShippingCity"          => ($shippingAddr) ? $shippingAddr["city"] : "",
                    "ShippingCountry"       => $countryNameShip,
                    "ShippingPostalCode"    => ($shippingAddr) ? $shippingAddr["postcode"] : "",
                    "ShippingState"         => $stateShip,
                    "ShippingStreet"        => ($shippingAddr) ? $shippingAddr["street"] : "",
                    
                    "Shipping_Method__c"    => $shippingDescription,
                    "Quantity__c"           => $totalQty,
                    "Item_s_count__c"       => $totalItemCount,
                    
                    "Shipping_Amount__c"    => $baseShippingAmount,
                    
                    "Total_Refunded_Amount__c"  => $baseTotalRefunded,
                    "Tax_Amount__c"             => $baseTaxAmount,
                    
                    "Sub_Total__c"              => $baseSubtotal,
                    "Discount__c"               => $discountAmount,
                    "Discount_Base__c"          => $baseDiscountAmount,
                    "Grant_Total__c"            => $grandTotal,
                    "Grand_Total_Base__c"       => $baseGrandTotal,
                    
                    "Total_Paid__c"             => $baseTotalPaid,
                    "Total_Due__c"              => $baseTotalDue,
                    
                    //"Name" => "Magento Order #".$incrementId,
                    
                    "Payment_Method__c"         => $paymentMethod,
                    "Store__c"                  => $oldStoreArr[$order->getStoreId()],
                    "Old_Store__c"              => $oldStoreArr[$order->getOldStoreId()],
                    "Order_Id__c"               => $order->getId(),
                    "Increment_Id__c"           => $incrementId,
                    "Customer_Group__c"         => $customerGroup,
                    "Customer_Email__c"         => $customerEmail,
                    "Counterpoint_Order_ID__c"  => $counterpointOrderId,
                    "Customer_Note__c"          => ($customerNote) ? $customerNote : "",
                    "Signature__c"              => ($order->getNoSignatureDelivery()) ? "Yes" : "No",
                    "OrderItems"                => $orderItem
                )
            );
            $urlPath = $helper::ORDER_PLACE_URL;
            $response = $helper->sendRequest($urlPath, $requestMethod, $request);
            $responseArr = json_decode($response,true);
            if($responseArr["done"]){
                $recordes = $responseArr["records"][0];
                $salesforceId = $recordes["Id"];
                $coreResource = Mage::getSingleton('core/resource');
                $write = $coreResource->getConnection('core_write');
                $sql_order = "UPDATE sales_flat_order SET salesforce_order_id='".$salesforceId."' WHERE entity_id ='".$orderId."'";
                $write->query($sql_order);
                $helper->salesforceLog("salesforce id updated into order table.");
                
                $orderItemsArr = $recordes["OrderItems"];
                $count = 0;
                if($orderItemsArr["done"]){
                    $orderItemsList = $orderItemsArr["records"];
                    foreach ($orderItemsList as $ordItem){
                        $salesforceItemId   = $ordItem["Id"];
                        $mOrderItem         = $magOrderItemArr[0];
                        $mItemId            = $mOrderItem["item_id"];
                        $mOrderId           = $mOrderItem["order_id"];
                        $mSku               = $mOrderItem["sku"];
                        if($mOrderId && $mSku){
                            if($salesforceItemId){
                                $sql_order1 = "UPDATE sales_flat_order_item SET salesforce_item_id='".$salesforceItemId.
                                "' WHERE order_id ='".$mOrderId."' AND sku ='".$mSku. "'";
                                $write->query($sql_order1);
                                $helper->salesforceLog("salesforce order item id updated into order item table.");
                            }
                        }
                        $count++;
                    }
                }
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
            }else{
                $helper->addSalesforcelogRecord($objectType,$requestMethod,$order->getId(),$response);
            }
        }
        
    }
    
    
    /**
     * add invoice data into salesforce 
     */
    public function addInvoiceToSalesforce($observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addInvoiceToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $invoice = $observer->getEvent()->getInvoice();
        
        $order = $invoice->getOrder();
        $order = Mage::getModel("sales/order")->load($order->getId());
        $helper->salesforceLog("order id :".$order->getId());
        $salesforceOrderId = $order->getSalesforceOrderId();
        $helper->salesforceLog("salesforce order id :".$salesforceOrderId);
        if($salesforceOrderId){
            
            $baseGrandTotal = $invoice->getBaseGrandTotal();
            $basTaxAmount = $invoice->getBaseTaxAmount();
            $baseShippingAmount = $invoice->getBaseShippingAmount();
            $baseSubtotal = $invoice->getBaseSubtotal();
            $baseDiscountAmount = $invoice->getBaseDiscountAmount();
            $discountDescrption = $invoice->getDiscountDescription();
            $createdAt = $invoice->getCreatedAt();
            $invoiceIncrementId = $invoice->getIncrementId();
            
            $orderDate = $order->getCreatedAt();
            $orderIncrementId = $order->getIncrementId();
            
            $status = $invoice->getState();
            $storeId = $invoice->getStoreId();
            
            $totalQty = $invoice->getTotalQty();
            
            $salesforceInvoiceId = $invoice->getSalesforceInvoiceId();
            
            $objectType = $helper::INVOICE_OBJECT;
        
            $urlPath = $helper::INVOICE_URL;
            $requestMethod = "GET";
            if($salesforceInvoiceId){
                $requestMethod = "PATCH";
                $urlPath .= "/" . $salesforceInvoiceId;
            }else{
                $requestMethod = "POST";
            }
            
            $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
            $oldStoreArr = array();
            foreach ($ostores as $storeO){
                $oldStoreArr[$storeO->getId()] = $storeO->getName();
            }
            $oldStoreArr[0] = "Admin";
                    
            $request = array(
                "Discount_Amount__c"        => $baseDiscountAmount,
                "Discount_Descrition__c"    => "for advertisment",
                "Grand_Total__c"            => $baseGrandTotal,
                "Invoice_Date__c"           => date("Y-m-d",strtotime($createdAt)),
                "Invoice_Id__c"             => $invoiceIncrementId,
                "Order_Date__c"             => date("Y-m-d",strtotime($orderDate)),
                "Order_Id__c"               => $orderIncrementId,
                "Shipping_Amount__c"        => $baseShippingAmount,
                "Status__c"                 => $status,
                "Subtotal__c"               => $baseSubtotal,
                "Tax_Amount__c"             => $basTaxAmount,
                "Total_Quantity__c"         => $totalQty,
                "Store__c"                  => $oldStoreArr[$storeId],
                "Order__c"                  => $salesforceOrderId,
                "Name"                      => "Invoice for Order #".$orderIncrementId
            );
            
            $response       = $helper->sendRequest($urlPath, $requestMethod, $request);
            $responseArr    = json_decode($response,true);
            if($responseArr["success"]){
                $salesforceId = $responseArr["id"];
                $helper->salesforceLog("order_id :".$order->getId()." invoice_id :".$invoice->getId()." $$ salesforce _id :".$salesforceId);
                $coreResource = Mage::getSingleton('core/resource');
                $write = $coreResource->getConnection('core_write');
                $sql_order = "UPDATE sales_flat_invoice SET salesforce_invoice_id='".$salesforceId."' WHERE entity_id ='".$invoice->getId()."'";
                $write->query($sql_order);
                $helper->salesforceLog("salesforce id updated into invoice.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $invoice->getId());
            
                //upload invoice pdf 
                $this->uploadInvoicePdf($order);
            
            }else{
                if($responseArr == ""){
                    $helper->salesforceLog("salesforce id not updated into invoice.");
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $invoice->getId());
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$invoice->getId(),$response);
                }
            }
            
        }
    }
    
    
    public function uploadInvoicePdf($order){
        $helper = $this->getHelper();
        $helper->salesforceLog("uploadInvoicePdf request.");
        try{
            $orderIncrementId = $order->getIncrementId();
            $fileName = "Order_Invoice.pdf";
            
            if($order->getSalesforceUploadedDocId()){
                $helper->salesforceLog("salesforce uploaded doc id updated into order table already.");
                return;
            }
            
            $objectType = $helper::UPLOAD_DOC_OBJECT;
            
            $invoices = $order->getInvoiceCollection();
            if(Mage::helper("core")->isModuleEnabled("Allure_Pdf")){
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices,true);
            }else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }
            
            $filedata =  base64_encode($pdf->render());
            
            $request = array(
                "ParentId" => $order->getSalesforceOrderId(),
                "Name" => $fileName,
                "body" => "$filedata",
                "IsPrivate" => "false"
            );
            
            //$helper->salesforceLog(json_encode($request));
            
            $url = $helper::INVOICE_PDF_URL_UPLOAD;
            $requestMethod = "POST";
            $response = $helper->sendRequest($url , $requestMethod , $request);
            
            $responseArr    = json_decode($response,true);
            if($responseArr["success"]){
                $coreResource = Mage::getSingleton('core/resource');
                $write = $coreResource->getConnection('core_write');
                $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='".$responseArr["id"]."' WHERE entity_id ='".$order->getId()."'";
                $write->query($sql_order);
                $helper->salesforceLog("salesforce uploaded doc id updated into order table.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
            }else{
                $helper->addSalesforcelogRecord($objectType,$requestMethod,$order->getId(),$response);
            }
            
        }catch (Exception $e){
            $helper->salesforceLog("Exception in uploadInvoicePdf - ".$e->getMessage());
        }
    }
    
    
    /**
     * add shipment information into salesforce
     */
    public function addShipmentToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addShipmentToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $shipment = $observer->getEvent()->getShipment();
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();
        
        $order = $shipment->getOrder();
        
        $salesforceOrderId  = $order->getSalesforceOrderId();
        $customerId         = $shipment->getCustomerId();
        $incrementId        = $shipment->getIncrementId();
        $orderIncrementId   = $order->getIncrementId();
        
        $totalQty = $shipment->getTotalQty();
        $shippingLabel = $shipment->getShippingLabel();
        
        $weight = $order->getWeight();
        
        $tracksNumCollection = $shipment->getAllTracks();
        $trackNumberArr = array();
        $titlesArr = array();
        /* foreach ($tracksNumCollection as $track){
            $helper->salesforceLog($track->getData());
            $trackNumberArr[]   = $track->getData("track_number");
            $titlesArr[]        = $track->getData("title");
        } */
        /* $carrierTitles = implode(",", $titlesArr);
        $trackNums = implode(",", $trackNumberArr); */
        
        if(!$salesforceOrderId){
            return;
        }
        
        $objectType = $helper::SHIPMENT_OBJECT;
        $requestMethod = "GET";
        $urlPath = $helper::SHIPMENT_URL;
        if($salesforceShipmentId){
            $requestMethod = "PATCH";
            $urlPath .= "/" .$salesforceShipmentId;
        }else{
            $requestMethod = "POST";
        }
        
        $request = array(
            "Customer_Id__c"    => $customerId,
            "Increment_ID__c"   => $incrementId,
            "Order__c"          => $salesforceOrderId,
            "Order_Id__c"       => $orderIncrementId,
            "Quantity__c"       => $totalQty,
            "Shipping_Label__c" => "",
            "Weight__c"         => $weight,
            //"Carrier__c"        => $carrierTitles,
            //"Track_Number__c"   => $trackNums,
            "Name"              => "Shipment for Order #".$orderIncrementId
        );
        //$helper->salesforceLog($urlPath);
        $helper->salesforceLog($request);
        
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr    = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $salesforceShipmentId = $salesforceId;
            $helper->salesforceLog("order_id :".$order->getId()." shipment_id :".$shipment->getId()." salesforce_Id :".$salesforceId);
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
            $write->query($sql_order);
            $helper->salesforceLog("salesforce id updated into shipment.");
            $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $shipment->getId());
        }else{
            if($responseArr == ""){
                $helper->salesforceLog("salesforce id not updated into shipment.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $shipment->getId());
            }else{
                $helper->addSalesforcelogRecord($objectType,$requestMethod,$shipment->getId(),$response);
            }
        }
        
        if($salesforceShipmentId){
            $helper->salesforceLog("In Track Info");
            $isTrack = false;
            $requestR["records"] = array();
            foreach ($tracksNumCollection as $track){
                if(!$track->getData("salesforce_shipment_track_id")){
                    $isTrack = true;
                    $tArr = array(
                        "attributes"            => array("type" => "Tracking_Information__c","referenceId" => $track->getData("entity_id")),
                        "Magento_Tracker_Id__c" => $track->getData("entity_id"),
                        "Name"                  => $track->getData("title"),
                        "Shipment__c"           => $salesforceShipmentId,
                        "Tracking_Number__c"    => $track->getData("track_number"),
                        "Carrier__c"            => $track->getData("carrier_code")
                    );
                    array_push($requestR["records"],$tArr);
                }
            }
            if($isTrack){
                $helper->salesforceLog("In Track Info request");
                $requestMethod = "POST";
                $urlPath = $helper::SHIPMENT_TRACK_URL;
                $responseT = $helper->sendRequest($urlPath,$requestMethod,$requestR);
                $tResponseArr = json_decode($responseT,true);
                if($tResponseArr["hasErrors"] == false){
                    $results = $tResponseArr["results"];
                    $sql_order = "";
                    foreach ($results as $res){
                        $sql_order .= "UPDATE sales_flat_shipment_track SET salesforce_shipment_track_id='".$res["id"]."' WHERE entity_id ='".$res["referenceId"]."';";
                    }
                    $coreResource = Mage::getSingleton('core/resource');
                    $write1 = $coreResource->getConnection('core_write');
                    //$sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
                    $write1->query($sql_order);
                    //$helper->salesforceLog($sql_order);
                }
            }
            
        }
        
        
    }
    
    /**
     * add creditmemo order data into salesforce
     */
    public function addCreditmemoToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addCreditmemoToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $items      = $creditMemo->getAllItems();
        
        $order = $creditMemo->getOrder();
        $salesforceOrderId = $order->getSalesforceOrderId();
        $helper->salesforceLog("salesforc order id :".$salesforceOrderId);
        if(!$salesforceOrderId){
            return ;
        }
        $salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();
        
        $incrementId            = $creditMemo->getIncrementId();
        $orderIncrementId       = $order->getIncrementId();
        $baseAdjustment         = $creditMemo->getBaseAdjustment();
        $createdAt              = $creditMemo->getCreatedAt();
        $status                 = $creditMemo->getState();
        $discountAmount         = $creditMemo->getBaseDiscountAmount();
        $grandTotal             = $creditMemo->getBaseGrandTotal();
        $orderDate              = $order->getCreatedAt();
        $shippingAmount         = $creditMemo->getBaseShippingAmount();
        $storeId                = $creditMemo->getStoreId();
        $subtotal               = $creditMemo->getBaseSubtotal();
        $taxAmount              = $creditMemo->getBaseTaxAmount();
        
        $objectType = $helper::CREDITMEMO_OBJECT;
        
        $requestMethod  = "GET";
        $urlPath        = $helper::CREDIT_MEMO_URL;
        if($salesforceCreditmemoId){
            $requestMethod  = "PATCH";
            $urlPath        .= "/" .$salesforceCreditmemoId;
        }else{
            $requestMethod = "POST";
        }
        
        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();
        foreach ($ostores as $storeO){
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }
        $oldStoreArr[0] = "Admin";
        
        $request = array(
            "Adjustment__c"         => $baseAdjustment,
            "Created_At__c"         => date("Y-m-d",strtotime($createdAt)),
            "Credit_Memo_Id__c"     => $incrementId,
            "Stauts__c"             => $status,
            "Discount_Amount__c"    => $discountAmount,
            "Grand_Total__c"        => $grandTotal,
            "Order_Date__c"         => date("Y-m-d",strtotime($orderDate)),
            "Order_Id__c"           => $orderIncrementId,
            "Shipping_Amount__c"    => $shippingAmount,
            "Store__c"              => $oldStoreArr[$storeId],
            "Subtotal__c"           => $subtotal,
            "Tax_Amount__c"         => $taxAmount,
            "Order__c"              => $salesforceOrderId,
            "Name"                  => "Credit Memo for Order #".$orderIncrementId
        );
        
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $helper->salesforceLog("Salesforce Id :".$salesforceId);
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='".$salesforceId."' WHERE entity_id ='".$creditMemo->getId()."'";
            $write->query($sql_order);
            $helper->salesforceLog("salesforce id updated into creditmemo.");
            $helper->salesforceLog("order_id :".$order->getId()." creditmemo_id:".$creditMemo->getId()." $$ salesforce_id".$salesforceId);;
            
            $cRequest = array("allOrNone"=>false);
            $cRequest["records"] = array();
            $requestMethod = "PATCH";
            $urlPath = $helper::UPDATE_COMPOSITE_OBJECT_URL;
            foreach ($items as $item){
                $orderItemId = $item->getOrderItemId();
                $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
                if(!$orderItem){
                    continue;
                }
                $salesforceItemId = $orderItem->getSalesforceItemId();
                if(!$salesforceItemId){
                    continue;
                }
                $tempArr = array(
                    "attributes"        => array("type" => "OrderItem"),
                    "id"                => $salesforceItemId,
                    "Credit_Memo__c"    => $salesforceId
                );
                array_push($cRequest["records"],$tempArr);
            }
            
            $response = $helper->sendRequest($urlPath,$requestMethod,$cRequest);
            $responseArr1 = json_decode($response,true);
            if($responseArr1[0]["success"]){
                $helper->salesforceLog("creditmemo items updated into salesforce.");
                $this->updateOrderData($order);
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $creditMemo->getId());
            }else{
                if($responseArr == ""){
                    $helper->salesforceLog("creditmemo items not updated into salesforce.");
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $creditMemo->getId());
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$creditMemo->getId(),$response);
                }
            }
        }
    }
    
    public function updateOrderData($order){
        $helper = $this->getHelper();
        $helper->salesforceLog("In updateOrderData request");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        if($order){
            $order = Mage::getModel("sales/order")->load($order->getId());
            $salesforceOrderId = $order->getSalesforceOrderId();
            $helper->salesforceLog("salesforce order id :".$salesforceOrderId);
            if(!$salesforceOrderId){
                return ;
            }
            
            $subtotal               = $order->getSubtotal();
            $baseSubtotal           = $order->getBaseSubtotal();
            $grandTotal             = $order->getGrandTotal();
            $baseGrandTotal         = $order->getBaseGrandTotal();
            $discountAmount         = $order->getDiscountAmount();
            $baseDiscountAmount     = $order->getBaseDiscountAmount();
            $shippingAmount         = $order->getShippingAmount();
            $baseShippingAmount     = $order->getBaseShippingAmount();
            
            $taxAmount              = $order->getTaxAmount();
            $baseTaxAmount          = $order->getBaseTaxAmount();
            
            $totalPaid              = $order->getTotalPaid();
            $baseTotalPaid          = $order->getBaseTotalPaid();
            $totalRefunded          = $order->getTotalRefunded();
            $baseTotalRefunded      = $order->getBaseTotalRefunded();
            $totalInvoiced          = $order->getTotalInvoiced();
            $baseTotalInvoiced      = $order->getBaseTotalInvoiced();
            
            $baseTotalDue           = $order->getBaseTotalDue();
            
            $requestMethod  = "PATCH";
            $urlPath        = $helper::ORDER_URL . "/" .$salesforceOrderId;
            
            $request = array(
                "Shipping_Amount__c"            => $baseShippingAmount,
                
                "Total_Refunded_Amount__c"      => $baseTotalRefunded,
                "Tax_Amount__c"                 => $baseTaxAmount,
                
                "Sub_Total__c"                  => $baseSubtotal,
                "Discount__c"                   => $discountAmount,
                "Discount_Base__c"              => $baseDiscountAmount,
                "Grant_Total__c"                => $grandTotal,
                "Grand_Total_Base__c"           => $baseGrandTotal,
                
                "Total_Paid__c"                 => $baseTotalPaid,
                "Total_Due__c"                  => $baseTotalDue,
            );
            $helper->salesforceLog("made order update api call to salesforce");
            $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        }
    }
    
    public function deleteShipmentToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("deleteShipmentToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $shipment = $observer->getEvent()->getShipment();
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();
        if(!$salesforceShipmentId){
            $helper->salesforceLog("No delete operation perform on shipment #".$shipment->getIncrementId());
            return ;
        }
        $requestMethod = "DELETE";
        $urlPath = $helper::SHIPMENT_URL . "/" . $salesforceShipmentId;
        $response = $helper->sendRequest($urlPath,$requestMethod,null);
    }
    
    /**
     * add tracking info into salesforce
     */
    public function addTrackingInfoToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $event = $observer->getEvent();
        $track = $event->getTrack();
        $trackingId = $track->getNumber();
        $shipment = $track->getShipment();
        
        $helper->salesforceLog("Tracking Id:".$trackingId);
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();
        if(!$salesforceShipmentId){
            $helper->salesforceLog("Cant add tracking info into salesforce for shipment #".$shipment->getIncrementId());
            return ;
        }
        
        $requestMethod = "POST";
        $urlPath = $helper::SHIPMENT_TRACK_URL_1 ;
        $request = array(
            "Magento_Tracker_Id__c" => $track->getData("entity_id"),
            "Name"                  => $track->getData("title"),
            "Shipment__c"           => $salesforceShipmentId,
            "Tracking_Number__c"    => $track->getData("track_number"),
            "Carrier__c"            => $track->getData("carrier_code")
        );
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr = json_decode($response,true);
        if($responseArr["success"]){
            $sql_order = "UPDATE sales_flat_shipment_track SET salesforce_shipment_track_id='".$responseArr["id"]."' WHERE entity_id ='".$track->getData("entity_id")."';";
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $write->query($sql_order);
        }
    }
    
    /**
     * delete tracking info from salesforce
     */
    public function deleteTrackInfoSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("deleteTrackInfoSalesforce request");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $event = $observer->getEvent();
        $track = $event->getTrack();
        $shipment = $track->getShipment();
        $helper = $this->getHelper();
        $trackSalesforceId = $track->getData("salesforce_shipment_track_id");
        if(!$trackSalesforceId){
            $helper->salesforceLog("No delete operation perform on track number shipment #".$shipment->getIncrementId());
            return ;
        }
        $requestMethod = "DELETE";
        $urlPath = $helper::SHIPMENT_TRACK_URL_1 . "/" . $trackSalesforceId;
        $response = $helper->sendRequest($urlPath,$requestMethod,null);
    }
}
