<?php
/**
 * @author aws02
 */
class Allure_Salesforce_Model_Observer_Order{	
    /**
     * retunr Allure_Salesforce_Helper_SalesforceClient
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
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllVisibleItems();
        $helper->salesforceLog("order id == ".$order->getId());
        
        $orderId = $order->getId();
        $status = $order->getStatus();
        $customerId = $order->getCustomerId();
        
        $salesforceAccountId = $helper::GUEST_CUSTOMER_ACCOUNT;
        if($customerId){
            $customer = Mage::getModel("customer/customer")->load($customerId);
            $salesforceAccountId = $customer->getSalesforceCustomerId();
            if(!$salesforceAccountId){
                $salesforceAccountId = $helper::GUEST_CUSTOMER_ACCOUNT;
            }
            /* if(!$salesforceAccountId){
                $customer->save();
                $salesforceAccountId = $customer->getSalesforceCustomerId();
            } */
        }
        
        $customerEmail = $order->getCustomerEmail();
        $customerGroup = $order->getCustomerGroupId();
        
        $pricebookId = $helper::RETAILER_PRICEBOOK_ID;
        if($customerGroup == 2){
            $pricebookId = $helper::WHOLESELLER_PRICEBOOK_ID;
        }
        
        $totalQty = $order->getTotalQtyOrdered();
        
        $totalItemCount = $order->getTotalItemCount();
        
        $incrementId = $order->getIncrementId();
        $shipingMethod = $order->getShippingMethod();
        $createdAt = $order->getCreatedAt();
        $counterpointOrderId = $order->getCounterpointOrderId();
        $shippingDescription = $order->getShippingdescription();
        
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
            $salesforceProductId = "";
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
                "sku" => $item->getSku(),
                "order_id" => $item->getOrderId()
            );
            
            $itemArray = array(
                "attributes" => array("type"=>"OrderItem"),
                "PricebookEntryId"=> $salesforcePricebkEntryId,//"01u290000037WAR",
                "quantity"=> $item->getQtyOrdered(),
                "UnitPrice"=> $item->getBasePrice()
            );
            array_push($orderItem["records"],$itemArray);
        }
        
        $salesforceOrderId = $order->getSalesforceOrderId();
        if($salesforceOrderId){
            $requestMethod = "PATCH";
        }else{
            $requestMethod = "POST";
            $request = array();
            $request["order"] = array(
                array(
                    "attributes" => array("type" => "order"),
                    "EffectiveDate" => date("Y-m-d",strtotime($createdAt)),
                    "Status" => $status,
                    "accountId" => $salesforceAccountId,//"0012900000Ls44hAAB",
                    "Pricebook2Id" => $pricebookId, //"01s290000001ivyAAA",//$pricebookId,
                    "BillingCity" => ($billingAddr)?$billingAddr["city"]:"",
                    "BillingCountry" => $countryName,
                    "BillingPostalCode" => ($billingAddr)?$billingAddr["postcode"]:"",
                    "BillingState" => $state,
                    "BillingStreet" => ($billingAddr)?$billingAddr["street"]:"",
                    "ShippingCity" => ($shippingAddr)?$shippingAddr["city"]:"",
                    "ShippingCountry" => $countryNameShip,
                    "ShippingPostalCode" => ($shippingAddr)?$shippingAddr["postcode"]:"",
                    "ShippingState" => $stateShip,
                    "ShippingStreet" => ($shippingAddr)?$shippingAddr["street"]:"",
                    
                    "Shipping_Method__c" => $shippingDescription,
                    "Quantity__c"   => $totalQty,
                    "Item_s_count__c" => $totalItemCount,
                    
                    "Shipping_Amount__c" => $baseShippingAmount,
                    
                    "Total_Refunded_Amount__c" => $baseTotalRefunded,
                    "Tax_Amount__c" => $baseTaxAmount,
                    
                    "Sub_Total__c" => $baseSubtotal,
                    "Discount__c" => $discountAmount,
                    "Discount_Base__c" => $baseDiscountAmount,
                    "Grant_Total__c" => $grandTotal,
                    "Grand_Total_Base__c" => $baseGrandTotal,
                    
                    "Total_Paid__c" => $baseTotalPaid,
                    "Total_Due__c" => $baseTotalDue,
                    
                    //"Name" => "Magento Order #".$incrementId,
                    
                    "Payment_Method__c" => $paymentMethod,
                    "Store__c" => $order->getStoreId(),
                    "Order_Id__c" => $order->getId(),
                    "Increment_Id__c" => $incrementId,
                    "Customer_Group__c" => $customerGroup,
                    "Customer_Email__c" => $customerEmail,
                    "Counterpoint_Order_ID__c" => $counterpointOrderId,
                    "Customer_Note__c" => ($customerNote)?$customerNote:"",
                    
                    "OrderItems" => $orderItem
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
                        $salesforceItemId = $ordItem["Id"];
                        $mOrderItem = $magOrderItemArr[0];
                        $mItemId = $mOrderItem["item_id"];
                        $mOrderId = $mOrderItem["order_id"];
                        $mSku = $mOrderItem["sku"];
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
                
            }
        }
        
    }
    
    
    /**
     * add invoice data into salesforce 
     */
    public function addInvoiceToSalesforce($observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addInvoiceToSalesforce request.");
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
        
            $urlPath = $helper::INVOICE_URL;
            $requestMethod = "GET";
            if($salesforceInvoiceId){
                $requestMethod = "PATCH";
                $urlPath .= "/" . $salesforceInvoiceId;
            }else{
                $requestMethod = "POST";
            }
                    
            $request = array(
                "Discount_Amount__c" => $baseDiscountAmount,
                "Discount_Descrition__c" => "for advertisment",
                "Grand_Total__c" => $baseGrandTotal,
                "Invoice_Date__c" => date("Y-m-d",strtotime($createdAt)),
                "Invoice_Id__c" => $invoiceIncrementId,
                "Order_Date__c" => date("Y-m-d",strtotime($orderDate)),
                "Order_Id__c" => $orderIncrementId,
                "Shipping_Amount__c" => $baseShippingAmount,
                "Status__c" => $status,
                "Subtotal__c" => $baseSubtotal,
                "Tax_Amount__c" => $basTaxAmount,
                "Total_Quantity__c" => $totalQty,
                "Store__c" => $storeId,
                "Order__c" => $salesforceOrderId,
                "Name" => "Invoice for Order #".$orderIncrementId
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
            }else{
                $helper->salesforceLog("salesforce id not updated into invoice.");
            }
            
        }
    }
    
    /**
     * add shipment information into salesforce
     */
    public function addShipmentToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addShipmentToSalesforce request.");
        $shipment = $observer->getEvent()->getShipment();
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();
        
        $order = $shipment->getOrder();
        
        $salesforceOrderId = $order->getSalesforceOrderId();
        $customerId = $shipment->getCustomerId();
        $incrementId = $shipment->getIncrementId();
        $orderIncrementId = $order->getIncrementId();
        
        $totalQty = $shipment->getTotalQty();
        $shippingLabel = $shipment->getShippingLabel();
        
        $weight = $order->getWeight();
        
        $tracksNumCollection = $shipment->getAllTracks();
        $trackNumberArr = array();
        $titlesArr = array();
        foreach ($tracksNumCollection as $track){
            $trackNumberArr[] = $track->getData("track_number");
            $titlesArr[] = $track->getData("title");
        }
        $carrierTitles = implode(",", $titlesArr);
        $trackNums = implode(",", $trackNumberArr);
        
        if(!$salesforceOrderId){
            return;
        }
        
        $requestMethod = "GET";
        $urlPath = $helper::SHIPMENT_URL;
        if($salesforceShipmentId){
            $requestMethod = "PATCH";
            $urlPath .= "/" .$salesforceShipmentId;
        }else{
            $requestMethod = "POST";
        }
        
        $request = array(
            "Customer_Id__c" => $customerId,
            "Increment_ID__c" => $incrementId,
            "Order__c" => $salesforceOrderId,
            "Order_Id__c" => $orderIncrementId,
            "Quantity__c" => $totalQty,
            "Shipping_Label__c" => "",
            "Weight__c" => $weight,
            "Carrier__c" => $carrierTitles,
            "Track_Number__c" => $trackNums,
            "Name" => "Shipment for Order #".$orderIncrementId
        );
        
        $helper->salesforceLog($request);
        
        $response = $helper->sendRequest($helper::SHIPMENT_URL,$requestMethod,$request);
        $responseArr    = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $helper->salesforceLog("order_id :".$order->getId()." shipment_id :".$shipment->getId()." salesforce_Id :".$salesforceId);
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
            $write->query($sql_order);
            $helper->salesforceLog("salesforce id updated into shipment.");
        }
        
        
    }
    
    /**
     * add creditmemo order data into salesforce
     */
    public function addCreditmemoToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("addCreditmemoToSalesforce request.");
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $items = $creditMemo->getAllItems();
        
        $order = $creditMemo->getOrder();
        $salesforceOrderId = $order->getSalesforceOrderId();
        $helper->salesforceLog("salesforc order id :".$salesforceOrderId);
        if(!$salesforceOrderId){
            return ;
        }
        $salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();
        
        $incrementId = $creditMemo->getIncrementId();
        $orderIncrementId = $order->getIncrementId();
        $baseAdjustment = $creditMemo->getBaseAdjustment();
        $createdAt = $creditMemo->getCreatedAt();
        $status = $creditMemo->getState();
        $discountAmount = $creditMemo->getBaseDiscountAmount();
        $grandTotal = $creditMemo->getBaseGrandTotal();
        $orderDate = $order->getCreatedAt();
        $shippingAmount = $creditMemo->getBaseShippingAmount();
        $storeId = $creditMemo->getStoreId();
        $subtotal = $creditMemo->getBaseSubtotal();
        $taxAmount = $creditMemo->getBaseTaxAmount();
        
        
        $requestMethod = "GET";
        $urlPath = $helper::CREDIT_MEMO_URL;
        if($salesforceCreditmemoId){
            $requestMethod = "PATCH";
            $urlPath .= "/" .$salesforceCreditmemoId;
        }else{
            $requestMethod = "POST";
        }
        
        $request = array(
            "Adjustment__c" => $baseAdjustment,
            "Created_At__c" => date("Y-m-d",strtotime($createdAt)),
            "Credit_Memo_Id__c" => $incrementId,
            "Stauts__c" => $status,
            "Discount_Amount__c" => $discountAmount,
            "Grand_Total__c" => $grandTotal,
            "Order_Date__c" => date("Y-m-d",strtotime($orderDate)),
            "Order_Id__c" => $orderIncrementId,
            "Shipping_Amount__c" => $shippingAmount,
            "Store__c" => $storeId,
            "Subtotal__c" => $subtotal,
            "Tax_Amount__c" => $taxAmount,
            "Order__c" => $salesforceOrderId,
            "Name" => "Credit Memo for Order #".$orderIncrementId
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
                    "attributes" => array("type" => "OrderItem"),
                    "id" => $salesforceItemId,
                    "Credit_Memo__c" => $salesforceId
                );
                array_push($cRequest["records"],$tempArr);
            }
            
            $response = $helper->sendRequest($urlPath,$requestMethod,$cRequest);
            $responseArr1 = json_decode($response,true);
            if($responseArr1[0]["success"]){
                $helper->salesforceLog("creditmemo items updated into salesforce.");
                $this->updateOrderData($order);
            }else{
                $helper->salesforceLog("creditmemo items not updated into salesforce.");
            }
        }
        
    }
    
    public function updateOrderData($order){
        $helper = $this->getHelper();
        $helper->salesforceLog("In updateOrderData request");
        if($order){
            $order = Mage::getModel("sales/order")->load($order->getId());
            $salesforceOrderId = $order->getSalesforceOrderId();
            $helper->salesforceLog("salesforce order id :".$salesforceOrderId);
            if(!$salesforceOrderId){
                return ;
            }
            
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
            
            $requestMethod = "PATCH";
            $urlPath = $helper::ORDER_URL . "/" .$salesforceOrderId;
            
            $request = array(
                "Shipping_Amount__c" => $baseShippingAmount,
                
                "Total_Refunded_Amount__c" => $baseTotalRefunded,
                "Tax_Amount__c" => $baseTaxAmount,
                
                "Sub_Total__c" => $baseSubtotal,
                "Discount__c" => $discountAmount,
                "Discount_Base__c" => $baseDiscountAmount,
                "Grant_Total__c" => $grandTotal,
                "Grand_Total_Base__c" => $baseGrandTotal,
                
                "Total_Paid__c" => $baseTotalPaid,
                "Total_Due__c" => $baseTotalDue,
            );
            $helper->salesforceLog("made order update api call to salesforce");
            $response = $helper->sendRequest($urlPath,$requestMethod,$request);
            
        }
    }
}
