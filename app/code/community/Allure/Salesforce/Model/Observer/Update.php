<?php

/**
 * @author aws02
 */
class Allure_Salesforce_Model_Observer_Update
{
    /**
     * return Allure_Salesforce_Helper_SalesforceClient
     */
    private function getHelper()
    {
        return Mage::helper("allure_salesforce/salesforceClient");
    }

    /**
     * @return Allure_Salesforce_Helper_Data
     */
    public function getDataHelper(){
        return Mage::helper('allure_salesforce');
    }

    public function getRequestData()
    {
        $helper = $this->getHelper();
        $dataHelper = $this->getDataHelper();
        $helper->salesforceUpdateLog("SObject collection request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $lastRunTime = $dataHelper->getLastRunTime();
        if(empty($lastRunTime)){
            $lastRunTime = new DateTime("15 minutes ago");  //static right now only for test purpose
        }

        $orders = $this->getUpdatedOrdersData($lastRunTime);

        $orderItems = $orders["OrderItems"]["records"];

        unset($orders["OrderItems"]);

        $customers = $this->getCustomersUpdateData($lastRunTime);

        $invoices = $this->getInvoicesUpdateData($lastRunTime);

        $creditMemos = $this->getCreditMemoUpdateData($lastRunTime);

        $shipments = $this->getShipmentUpdateData($lastRunTime);

        $shipmentTrackings = $this->getTrackingInfoUpdateData($lastRunTime);
        $combinedData = array($orders, $orderItems, $customers, $invoices, $creditMemos, $shipments, $shipmentTrackings);
        print_r(json_encode($combinedData));die;
    }

    public function sendCompositeRequest($requestData,$lastRunTime)
    {
        $helper = $this->getHelper();
        $dataHelper = $this->getDataHelper();
        $chunkedReqArray = array_chunk($requestData,200);
        foreach($chunkedReqArray as $reqArray) {
            $request = array("allOrNone"=>false);
            $request["records"] = $reqArray;
            $urlPath = $helper::UPDATE_COMPOSITE_OBJECT_URL;
            $dataHelper->setLastRunTime(new DateTime());
            $response = $helper->sendRequest($urlPath, "PATCH", $request);
            $responseArr = json_decode($response,true);
            $time = $lastRunTime->format('Y-m-d H:i:s');
            if($responseArr[0]["success"]){
                $helper->salesforceLog("bulk upation was succesfull");
                $helper->addSalesforcelogRecord("BULK UPDATE","PATCH","BULKUP-".$time,$response);
            }else{
                if($responseArr == ""){
                    $helper->salesforceLog("bulk updation failed");
                    $helper->addSalesforcelogRecord("BULK UPDATE","PATCH","BULKUP-".$time,$response);
                }else{
                    $helper->addSalesforcelogRecord("BULK UPDATE","PATCH","BULKUP-".$time,$response);
                }
            }
        }
    }

    /**
     * add order data into salesforce when order placed into magento
     * @param DateTime $lastRunTime
     * @return Array
     */
    public function getUpdatedOrdersData(DateTime $lastRunTime)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("getUpdatedOrders request.");

        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('updated_at', array('from' => $lastRunTime));

        $orderList = array();
        foreach ($orders as $order) {

            $orderId = $order->getId();
            $orderStatus = $order->getStatus();
            $helper->salesforceUpdateLog("Order Id {$orderId} Status - " . $orderStatus);
            /* if(!$orderStatus){
                return ;
            } */

//            if (Mage::registry('sales_order_save_after_' . $orderId)) {
//                return $this;
//            }
//            Mage::register('sales_order_save_after_' . $orderId, true);


            $items = $order->getAllVisibleItems();

            //check product is in salesforce or not.if not add into salesforce.
            $isTeamwork = false;
            $createOrderMethod = $order->getCreateOrderMethod();
            if ($createOrderMethod) {
                $isTeamwork = true;
                $status = Mage::helper("allure_teamwork")->getTeamworkSalesforceStatus();
                if (!$status) {
                    Mage::log("Teamwork data transfer to salesforce disabled.", Zend_Log::DEBUG, "salesforce.log", true);
                    return;
                }
            }
            //Mage::getModel("allure_salesforce/observer_product")->addOrderProduct($items, $isTeamwork);

            $helper->salesforceUpdateLog("order id == " . $order->getId());

            $orderId = $order->getId();
            $status = ($order->getStatus()) ? $order->getStatus() : "pending";
            $customerId = $order->getCustomerId();

            $salesforceAccountId = $helper::GUEST_CUSTOMER_ACCOUNT;
            if ($customerId) {
                $customer = Mage::getModel("customer/customer")->load($customerId);
                $salesforceAccountId = $customer->getSalesforceCustomerId();
//                if (!$salesforceAccountId) {
//                    //$guestAccount = Mage::helper('allure_salesforce')->getGuestAccount();
//                    //$salesforceAccountId = $guestAccount; //$helper::GUEST_CUSTOMER_ACCOUNT;
//
//                    //create new account for the customer
//                    $helper->salesforceUpdateLog("from order customer account creating.");
//                    //$customer->save();
//                    $salesforceAccountId = Mage::getModel("allure_salesforce/observer_customer")
//                        ->addCustomerToSalesforce($customer);
//                }
                $helper->salesforceUpdateLog("account id  - " . $salesforceAccountId);
                /* if(!$salesforceAccountId){
                    $customer->save();
                    $salesforceAccountId = $customer->getSalesforceCustomerId();
                } */
            }

            $customerEmail = $order->getCustomerEmail();
            $customerGroup = $order->getCustomerGroupId();

            $pricebookId = Mage::helper('allure_salesforce')->getGeneralPricebook(); //$helper::RETAILER_PRICEBOOK_ID;
            if ($customerGroup == 2) {
                $pricebookId = Mage::helper('allure_salesforce')->getWholesalePricebook(); //$helper::WHOLESELLER_PRICEBOOK_ID;
            }

            $totalQty = $order->getTotalQtyOrdered();

            $totalItemCount = $order->getTotalItemCount();

            $incrementId = $order->getIncrementId();
            $shipingMethod = $order->getShippingMethod();
            $createdAt = $order->getCreatedAt();
            $counterpointOrderId = $order->getCounterpointOrderId();
            $shippingDescription = $order->getShippingDescription();

            //for teamwork currency rate
            $currencyRate = 1;
            if ($order->getCreateOrderMethod() == 2) {
                $currencyRate = $order->getStoreToBaseRate();
                if (!$currencyRate) {
                    $currencyRate = 1;
                }
            }

            $subtotal = $order->getSubtotal() * $currencyRate;
            $baseSubtotal = $order->getBaseSubtotal() * $currencyRate;
            //$grandTotal = $order->getGrandTotal() * $currencyRate;
            $grandTotal = $order->getGrandTotal();
            $baseGrandTotal = $order->getBaseGrandTotal() * $currencyRate;
            //$discountAmount = $order->getDiscountAmount() * $currencyRate;
            $discountAmount = $order->getDiscountAmount();
            $baseDiscountAmount = $order->getBaseDiscountAmount() * $currencyRate;
            //$shippingAmount = $order->getShippingAmount() * $currencyRate;
            $shippingAmount = $order->getShippingAmount();
            $baseShippingAmount = $order->getBaseShippingAmount() * $currencyRate;

            //$taxAmount = $order->getTaxAmount() * $currencyRate;
            $taxAmount = $order->getTaxAmount();
            $baseTaxAmount = $order->getBaseTaxAmount() * $currencyRate;

            $totalPaid = $order->getTotalPaid() * $currencyRate;
            $baseTotalPaid = $order->getBaseTotalPaid() * $currencyRate;
            $totalRefunded = $order->getTotalRefunded() * $currencyRate;
            $baseTotalRefunded = $order->getBaseTotalRefunded() * $currencyRate;
            $totalInvoiced = $order->getTotalInvoiced() * $currencyRate;
            $baseTotalInvoiced = $order->getBaseTotalInvoiced() * $currencyRate;

            $baseTotalDue = $order->getBaseTotalDue() * $currencyRate;

            $billingAddr = $order->getBillingAddress();
            $shippingAddr = $order->getShippingAddress();

            $customerNote = Mage::helper('giftmessage/message')->getEscapedGiftMessage($order);

            $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();

            $state = "";
            $countryName = "";
            if ($billingAddr) {
                if ($billingAddr['region_id']) {
                    $region = Mage::getModel('directory/region')
                        ->load($billingAddr['region_id']);
                    $state = $region->getName();
                } else {
                    $state = $billingAddr['region'];
                }

                $bcountryNm = $billingAddr['country_id'];
                if ($bcountryNm) {
                    if (strlen($bcountryNm) > 3) {
                        $countryName = $bcountryNm;
                    } else {
                        $country = Mage::getModel('directory/country')
                            ->loadByCode($billingAddr['country_id']);
                        if ($country->getId()) {
                            $countryName = $country->getName();
                        }
                    }
                }

            }

            $stateShip = "";
            $countryNameShip = "";
            if ($shippingAddr) {
                if ($shippingAddr['region_id']) {
                    $region = Mage::getModel('directory/region')
                        ->load($shippingAddr['region_id']);
                    $stateShip = $region->getName();
                } else {
                    $stateShip = $shippingAddr['region'];
                }

                $scountryNm = $shippingAddr['country_id'];
                if ($scountryNm) {
                    if (strlen($scountryNm) > 3) {
                        $countryNameShip = $scountryNm;
                    } else {
                        $country = Mage::getModel('directory/country')
                            ->loadByCode($shippingAddr['country_id']);
                        if ($country->getId()) {
                            $countryNameShip = $country->getName();
                        }
                    }
                }
            }

            $createOrderMethod = $order->getCreateOrderMethod();
            $isTeamworkOrder = false;
            if ($createOrderMethod == 2) {
                $isTeamworkOrder = true;
            }

            $orderItem = array();
            $orderItem["records"] = array();

            $magOrderItemArr = array();
            foreach ($items as $item) {

                $salesforcePricebkEntryId = "";
                if ($isTeamwork) {
                    $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                    if ($productId) {
                        $product = Mage::getModel("catalog/product")->load($productId);
                        if ($product) {
                            $salesforcePricebkEntryId = $product->getSalesforceStandardPricebk();
                            if ($customerGroup == 2) {
                                $salesforcePricebkEntryId = $product->getSalesforceWholesalePricebk();
                            }
                        }
                    } else {
                        $tmProduct = Mage::getModel("allure_teamwork/tmproduct")
                            ->load($item->getSku(), "sku");
                        if ($tmProduct->getId()) {
                            $salesforcePricebkEntryId = $tmProduct->getSalesforceStandardPricebk();
                            if ($customerGroup == 2) {
                                $salesforcePricebkEntryId = $tmProduct->getSalesforceWholesalePricebk();
                            }
                        }
                    }
                } else {
                    $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                    $product = Mage::getModel("catalog/product")->load($productId);
                    if ($product) {
                        $salesforcePricebkEntryId = $product->getSalesforceStandardPricebk();
                        if ($customerGroup == 2) {
                            $salesforcePricebkEntryId = $product->getSalesforceWholesalePricebk();
                        }
                    }
                }

                $helper->salesforceUpdateLog("product id - " . $item->getSku() . " salesforce pricebook id - " . $salesforcePricebkEntryId);

                if (!$salesforcePricebkEntryId) {
                    $helper->salesforceUpdateLog("place this order when product has assigned the salesforce id.");
                    return;
                }


                $magOrderItemArr[] = array("item_id" => $item->getItemId(),
                    "salesforce_id" => $item->getSalesforceItemId(),
                    "sku" => $item->getSku(),
                    "order_id" => $item->getOrderId()
                );

                $options = $item->getProductOptions()["options"];
                $postLength = "";
                foreach ($options as $option) {
                    if ($option["label"] == "Post Length") {
                        $postLength = $option["value"];
                        break;
                    }
                }

                $unitPrice = $item->getBasePrice() * $currencyRate;

                $reasonText = "";
                if ($item->getTeamworkReason()) {
                    $reasonText = $item->getTeamworkReason();
                } elseif ($item->getOtherSysQty() < 0) {
                    $reasonText = "Return";
                }
                $salesforceItemId = $item->getSalesforceItemId();
                $itemArray = array(
                    "attributes" => array("type" => "OrderItem"),
                    "Id" => $salesforceItemId,
                    "PricebookEntryId" => $salesforcePricebkEntryId,//"01u290000037WAR",
                    "quantity" => ($isTeamworkOrder) ? ($item->getOtherSysQty() ? $item->getOtherSysQty() : 1) : $item->getQtyOrdered(),
                    "UnitPrice" => $unitPrice,
                    "Post_Length__c" => $postLength,
                    "Magento_Order_Item_Id__c" => $item->getItemId(),
                    "SKU__c" => $item->getSku(),
                    "reason__c" => $reasonText
                );
                array_push($orderItem["records"], $itemArray);
            }

            $salesforceOrderId = $order->getSalesforceOrderId();
//            $objectType = $helper::ORDER_OBJECT;
            $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
            $oldStoreArr = array();
            foreach ($ostores as $storeO) {
                $oldStoreArr[$storeO->getId()] = $storeO->getName();
            }
            $oldStoreArr[0] = "Admin";
            $request = array();
            $request["order"] = array(
                array(
                    "attributes" => array("type" => "order"),
                    "Id" => $salesforceOrderId,
                    "EffectiveDate" => date("Y-m-d", strtotime($createdAt)),
                    "Created_At__c" => date("Y-m-d", strtotime($createdAt)) . "T" . date("H:i:s", strtotime($createdAt)) . "+00:00",//date("Y-m-d H:i:s",strtotime($createdAt)),
                    "Status" => $status,
                    "accountId" => $salesforceAccountId,    //"0012900000Ls44hAAB",
                    "Pricebook2Id" => $pricebookId,    //"01s290000001ivyAAA",//$pricebookId,
                    "BillingCity" => ($billingAddr) ? $billingAddr["city"] : "",
                    "BillingCountry" => $countryName,
                    "BillingPostalCode" => ($billingAddr) ? $billingAddr["postcode"] : "",
                    "BillingState" => $state,
                    "BillingStreet" => ($billingAddr) ? $billingAddr["street"] : "",
                    "ShippingCity" => ($shippingAddr) ? $shippingAddr["city"] : "",
                    "ShippingCountry" => $countryNameShip,
                    "ShippingPostalCode" => ($shippingAddr) ? $shippingAddr["postcode"] : "",
                    "ShippingState" => $stateShip,
                    "ShippingStreet" => ($shippingAddr) ? $shippingAddr["street"] : "",

                    "Shipping_Method__c" => $shippingDescription,
                    "Quantity__c" => $totalQty,
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
                    "Store__c" => $oldStoreArr[$order->getStoreId()],
                    "Old_Store__c" => $oldStoreArr[$order->getOldStoreId()],
                    "Order_Id__c" => $order->getId(),
                    "Increment_Id__c" => $incrementId,
                    "Customer_Group__c" => $customerGroup,
                    "Customer_Email__c" => $customerEmail,
                    "Counterpoint_Order_ID__c" => $counterpointOrderId,
                    "Customer_Note__c" => ($customerNote) ? $customerNote : "",
                    "Signature__c" => ($order->getNoSignatureDelivery()) ? "Yes" : "No",
                    "OrderItems" => $orderItem
                )
            );

            $payment = $order->getPayment();
            $code = $payment->getData('cc_type');
            $aType = Mage::getSingleton('payment/config')->getCcTypes();
            if (isset($aType[$code])) {
                $sName = $aType[$code];
                $request["order"][0]["Card_Type__c"] = $sName;
            }

            $last4Digits = $payment->getCcLast4();
            if ($last4Digits) {
                $request["order"][0]["Card_Number__c"] = "XXXX-" . $last4Digits;
            }

            $transactionId = $payment->getData("last_trans_id");
            if ($transactionId) {
                $request["order"][0]["Transaction_Id__c"] = $transactionId;
            }

            if ($order->getCreateOrderMethod() == 2) {
                $tmOrginalOrderId = $order->getTeamworkOrigReceiptId();
                if ($tmOrginalOrderId) {
                    $tmOrginalOrderId = "TW-" . $tmOrginalOrderId;
                    $orderObj = Mage::getModel('sales/order')->loadByIncrementId($tmOrginalOrderId);
                    if ($orderObj) {
                        if ($orderObj->getSalesforceOrderId()) {
                            $request["order"][0]["Reference_Order__c"] = $orderObj->getSalesforceOrderId();
                        }
                        $request["order"][0]["Magento_Reference_Order__c"] = $orderObj->getIncrementId();
                    }
                }
                $tmData = json_decode($order->getOtherSysExtraInfo(), true);
                $request["order"][0]["Teamwork_Receipt_Id__c"] = $tmData["ReceiptNum"];
                $request["order"][0]["Teamwork_Universal_Id__c"] = $tmData["DeviceTransactionNumber"];
                $request["order"][0]["Teamwork_Cashier__c"] = $tmData["EMPNAME"];
                array_push($orderList, $request["order"]);
            }
        }
        return $orderList;
    }

    public function getCustomersUpdateData(DateTime $lastRunTime)
    {
        $helper = $this->getHelper();
        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();

        foreach ($ostores as $storeO) {
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }

        $oldStoreArr[0] = "Admin";

        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToFilter('created_at', array('from' => $lastRunTime));

        $customerList = array();

        if ($customers) {
            foreach ($customers as $customer) {
                $salesforceId = $customer->getSalesforceCustomerId();
                $salesforceContactId = $customer->getSalesforceContactId();
                if(!$salesforceId && !$salesforceContactId)
                    return;
                $prefix = $customer->getPrefix();
                $fName = $customer->getFirstname();
                $mName = $customer->getMiddlename();
                $lName = $customer->getLastname();
                $fullName = "";

                if ($prefix) {
                    $fullName .= $prefix . " ";
                }

                $fullName .= $fName . " ";

                if ($mName) {
                    $fullName .= $mName;
                }

                $fullName .= $lName;


                $defaultBillingAddr = $customer->getDefaultBillingAddress();
                $state = "";
                $countryName = "";

                if ($defaultBillingAddr) {
                    if (!empty($defaultBillingAddr['region_id'])) {
                        $region = Mage::getModel('directory/region')
                            ->load($defaultBillingAddr['region_id']);
                        $state = $region->getName();
                    } else {
                        $state = $defaultBillingAddr['region'];
                    }

                    $bcountryNm = $defaultBillingAddr['country_id'];

                    if (!empty($bcountryNm)) {
                        if (strlen($bcountryNm) > 3) {
                            $countryName = $bcountryNm;
                        } else {
                            $country = Mage::getModel('directory/country')
                                ->loadByCode($defaultBillingAddr['country_id']);
                            if ($country->getId()) {
                                $countryName = $country->getName();
                            }
                        }
                    }
                }

                $stateShip = "";
                $countryNameShip = "";
                $defaultShippingAddr = $customer->getDefaultShippingAddress();

                if ($defaultShippingAddr) {
                    if (!empty($defaultBillingAddr['region_id'])) {
                        $region = Mage::getModel('directory/region')
                            ->load($defaultShippingAddr['region_id']);
                        $stateShip = $region->getName();
                    } else {
                        $stateShip = $defaultShippingAddr['region'];
                    }

                    $scountryNm = $defaultShippingAddr['country_id'];

                    if (!empty($scountryNm)) {
                        if (strlen($scountryNm) > 3) {
                            $countryNameShip = $scountryNm;
                        } else {
                            $country = Mage::getModel('directory/country')
                                ->loadByCode($defaultShippingAddr['country_id']);
                            if ($country->getId()) {
                                $countryNameShip = $country->getName();
                            }
                        }
                    }
                }

                $request = array(
                    "attributes" => array("type" => "Account"),
                    "Id" => $salesforceId,
                    "Name" => $fullName,
                    //"AccountNumber"       => "",
                    //"Site"                => "",
                    //"AccountSource"       => "",
                    //"Birth_Date_c"        => ($customer->getDob()) ? date("Y-m-d",strtotime($customer->getDob())) : null,//"YYYY-MM-DD",
                    "Company__c" => $customer->getCompany(),
                    "Counterpoint_No__c" => $customer->getCounterpointCustNo(),
                    "Created_In__c" => $customer->getCreatedIn(),
                    "Customer_ID__c" => $customer->getId(),
                    "Customer_Note__c" => $customer->getCustomerNote(),
                    "Default_Billing__c" => $customer->getDefaultBilling(),
                    "Default_Shipping__c" => $customer->getDefaultShipping(),
                    //"Description"         => "",
                    "Email__c" => $customer->getEmail(),
                    //"Fax"                 => "",
                    "Gender__c" => ($customer->getGender()) ? $customer->getGender() : 4,
                    "Phone" => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                    "Store__c" => $oldStoreArr[$customer->getStoreId()],
                    "Old_Store__c" => $oldStoreArr[$customer->getOldStoreId()],
                    "Teamwork_Customer_ID__c" => $customer->getTeamworkCustomerId(),
                    "TW_UC_GUID__c" => $customer->getTwUcGuid(),
                    "Group__c" => $customer->getGroupId(),
                    "BillingStreet" => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                    "BillingCity" => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                    "BillingState" => ($defaultBillingAddr) ? $state : "",
                    "BillingPostalCode" => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                    "BillingCountry" => ($defaultBillingAddr) ? $countryName : "",
                    "ShippingStreet" => ($defaultShippingAddr) ? implode(", ", $defaultShippingAddr->getStreet()) : "",
                    "ShippingCity" => ($defaultShippingAddr) ? $defaultShippingAddr->getCity() : "",
                    "ShippingState" => ($defaultShippingAddr) ? $stateShip : "",
                    "ShippingPostalCode" => ($defaultShippingAddr) ? $defaultShippingAddr->getPostcode() : "",
                    "ShippingCountry" => ($defaultShippingAddr) ? $countryNameShip : ""
                );

                if ($customer->getDob()) {
                    $request["Birth_Date__c"] = date("Y-m-d", strtotime($customer->getDob()));
                }

                $contactRequest = array(
                    "attributes" => array("type" => "Contact"),
                    "Id" => $salesforceContactId,
                    "FirstName" => $fName,
                    "MiddleName" => $mName,
                    "LastName" => $lName,
                    "Email" => $customer->getEmail(),
                    "Phone" => ($defaultBillingAddr) ? $defaultBillingAddr->getTelephone() : "",
                    "MailingStreet" => ($defaultBillingAddr) ? implode(", ", $defaultBillingAddr->getStreet()) : "",
                    "MailingCity" => ($defaultBillingAddr) ? $defaultBillingAddr->getCity() : "",
                    "MailingState" => ($defaultBillingAddr) ? $state : "",
                    "MailingPostalCode" => ($defaultBillingAddr) ? $defaultBillingAddr->getPostcode() : "",
                    "MailingCountry" => ($defaultBillingAddr) ? $countryName : "",
                    "Contact_Id__c" => $customer->getId(),
                    "AccountID" => $salesforceId
                );

                //tmwork fields accept marketing
                if ($customer->getTwAcceptMarketing()) {
                    $request["Accept_Marketing__c"] = $customer->getTwAcceptMarketing();
                }
                //tmwork fields accept transactional
                if ($customer->getTwAcceptTransactional()) {
                    $request["Accept_Transactional__c"] = $customer->getTwAcceptTransactional();
                }
                array_push($customerList, $request);
                array_push($customerList, $contactRequest);
                $helper->salesforceUpdateLog("----- customer data -----");
            }
//            print_r(json_encode($customerList));
//            die;
            return $customerList;
        }
    }

    public function getInvoicesUpdateData($lastRunTime)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("addInvoiceToSalesforce request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $invoiceCollection = Mage::getModel("sales/order_invoice")->getCollection()
            ->addFieldToFilter('updated_at', array('from' => $lastRunTime));
        $invoiceList = array();
        foreach ($invoiceCollection as $invoice) {

            $order = $invoice->getOrder();
            if ($order->getCreateOrderMethod() == 2) {
                return;
            }

            $order = Mage::getModel("sales/order")->load($order->getId());
            $helper->salesforceUpdateLog("order id :" . $order->getId());
            $salesforceOrderId = $order->getSalesforceOrderId();
            $salesforceInvoiceId = $invoice->getSalesforceInvoiceId();
            $helper->salesforceUpdateLog("salesforce order id :" . $salesforceOrderId);
            if ($salesforceOrderId && $salesforceInvoiceId) {
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

                //$totalQty = $invoice->getTotalQty();
                $totalQty = 0;
                foreach ($invoice->getAllItems() as $item) {
                    if ($item->getOrderItem()->getParentItem()) {
                        continue;
                    }
                    $qty = $item->getQty();
                    $totalQty += $qty;
                }

                $salesforceInvoiceId = $invoice->getSalesforceInvoiceId();

                $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
                $oldStoreArr = array();
                foreach ($ostores as $storeO) {
                    $oldStoreArr[$storeO->getId()] = $storeO->getName();
                }
                $oldStoreArr[0] = "Admin";

                $orderDate = date("Y-m-d", strtotime($orderDate)) . "T" . date("H:i:s", strtotime($orderDate)) . "+00:00";
                $createdAt = date("Y-m-d", strtotime($createdAt)) . "T" . date("H:i:s", strtotime($createdAt)) . "+00:00";

                $request = array(
                    "attributes" => array("type" => "Invoice__c"),
                    "Id" => $salesforceInvoiceId,
                    "Discount_Amount__c" => $baseDiscountAmount,
                    "Discount_Descrition__c" => "for advertisment",
                    "Grand_Total__c" => $baseGrandTotal,
                    "Invoice_Date__c" => $createdAt,//date("Y-m-d",strtotime($createdAt)),
                    "Invoice_Id__c" => $invoiceIncrementId,
                    "Order_Date__c" => $orderDate,//date("Y-m-d",strtotime($orderDate)),
                    "Order_Id__c" => $orderIncrementId,
                    "Shipping_Amount__c" => $baseShippingAmount,
                    "Status__c" => $status,
                    "Subtotal__c" => $baseSubtotal,
                    "Tax_Amount__c" => $basTaxAmount,
                    "Total_Quantity__c" => $totalQty,
                    "Store__c" => $oldStoreArr[$storeId],
                    "Order__c" => $salesforceOrderId,
                    "Name" => "Invoice for Order #" . $orderIncrementId
                );
//                print_r($request);
//                die;
                array_push($invoiceList, $request);
            } else {
                $helper->salesforceUpdateLog("salesforce salesforce order ID not found.");
            }
//            }
        }
//        print_r(json_encode($invoiceList));
//        die;
        return $invoiceList;
    }

    /**
     * add creditmemo order data into salesforce
     */
    public function getCreditMemoUpdateData(DateTime $lastRunTime)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("addCreditmemoToSalesforce request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $creditMemoCollection = Mage::getResourceModel('sales/order_creditmemo_collection')
            ->addFieldToFilter('updated_at', array('from' => $lastRunTime));

        //$creditMemo = $observer->getEvent()->getCreditmemo();
        $creditMemoList = array();
        foreach ($creditMemoCollection as $creditMemo) {
            $items = $creditMemo->getAllItems();

            $order = $creditMemo->getOrder();

            if ($order->getCreateOrderMethod() == 2) {
                return;
            }

            $salesforceOrderId = $order->getSalesforceOrderId();
            $helper->salesforceUpdateLog("salesforc order id :" . $salesforceOrderId);
            if (!$salesforceOrderId && !$salesforceCreditmemoId) {
                return;
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

            $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
            $oldStoreArr = array();
            foreach ($ostores as $storeO) {
                $oldStoreArr[$storeO->getId()] = $storeO->getName();
            }
            $oldStoreArr[0] = "Admin";

            $createdAt = date("Y-m-d", strtotime($createdAt)) . "T" . date("H:i:s", strtotime($createdAt)) . "+00:00";
            $orderDate = date("Y-m-d", strtotime($orderDate)) . "T" . date("H:i:s", strtotime($orderDate)) . "+00:00";

            $request = array(
                "attributes" => array("type" => "Credit_Memo__c"),
                "Id" => $salesforceCreditmemoId,
                "Adjustment__c" => $baseAdjustment,
                "Created_At__c" => $createdAt,//date("Y-m-d",strtotime($createdAt)),
                "Credit_Memo_Id__c" => $incrementId,
                "Stauts__c" => $status,
                "Discount_Amount__c" => $discountAmount,
                "Grand_Total__c" => $grandTotal,
                "Order_Date__c" => $orderDate,//date("Y-m-d",strtotime($orderDate)),
                "Order_Id__c" => $orderIncrementId,
                "Shipping_Amount__c" => $shippingAmount,
                "Store__c" => $oldStoreArr[$storeId],
                "Subtotal__c" => $subtotal,
                "Tax_Amount__c" => $taxAmount,
                "Order__c" => $salesforceOrderId,
                "Name" => "Credit Memo for Order #" . $orderIncrementId
            );
            array_push($creditMemoList, $request);
        }
        return $creditMemoList;
    }

    /**
     * add shipment information into salesforce
     */
    public function getShipmentUpdateData(DateTime $lastRunTime)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("addShipmentToSalesforce request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        //$shipment = $observer->getEvent()->getShipment();
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addFieldToFilter('updated_at', array('from' => $lastRunTime));

        $shipmentList = array();
        foreach ($shipmentCollection as $shipment) {
            $salesforceShipmentId = $shipment->getSalesforceShipmentId();

            $order = $shipment->getOrder();

            if ($order->getCreateOrderMethod() == 2) {
                return;
            }

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
            /* foreach ($tracksNumCollection as $track){
                $helper->salesforceUpdateLog($track->getData());
                $trackNumberArr[]   = $track->getData("track_number");
                $titlesArr[]        = $track->getData("title");
            } */
            /* $carrierTitles = implode(",", $titlesArr);
            $trackNums = implode(",", $trackNumberArr); */

            if (!$salesforceOrderId && !$salesforceShipmentId) {
                return;
            }

            $request = array(
                "attributes" => array("type" => "Shipment__c"),
                "Id" => $salesforceShipmentId,
                "Customer_Id__c" => $customerId,
                "Increment_ID__c" => $incrementId,
                "Order__c" => $salesforceOrderId,
                "Order_Id__c" => $orderIncrementId,
                "Quantity__c" => $totalQty,
                "Shipping_Label__c" => "",
                "Weight__c" => $weight,
                //"Carrier__c"        => $carrierTitles,
                //"Track_Number__c"   => $trackNums,
                "Name" => "Shipment for Order #" . $orderIncrementId
            );
            array_push($shipmentList, $request);
        }
//        print_r(json_encode($shipmentList));
//        die;
        return $shipmentList;
    }

    /**
     * add tracking info into salesforce
     */
    public function getTrackingInfoUpdateData(DateTime $lastRunTime)
    {
        $helper = $this->getHelper();
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }


        $shipmentTrackCollection = Mage::getResourceModel('sales/order_shipment_track_collection')
            ->addFieldToFilter('updated_at', array('from' => $lastRunTime));

        $shipmentTrackList = array();
        foreach ($shipmentTrackCollection as $track) {
            $salesforceShipmentTrackId = $track->getData('salesforce_shipment_track_id');
            $trackingId = $track->getNumber();
            $shipment = $track->getShipment();
            $shipmentModel = Mage::getModel('sales/order_shipment')->load($shipment->getParentId());

            $request = array(
                "attributes" => array("type" => "Tracking_Information__c"),
                "Id" => $salesforceShipmentTrackId,
                "Magento_Tracker_Id__c" => $track->getData("entity_id"),
                "Name" => $track->getData("title"),
                "Shipment__c" => $shipmentModel->getSalesforceShipmentId(),
                "Tracking_Number__c" => $track->getData("track_number"),
                "Carrier__c" => $track->getData("carrier_code")
            );
            if (!empty($salesforceShipmentTrackId))
                array_push($shipmentTrackList, $request);
        }
//        print_r(json_encode($shipmentTrackList));
//        die;
        return $shipmentTrackList;
    }

    //using salesforce contentversion & document
    private function uploadInvoicePdf($order)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("uploadInvoicePdf request.");
        try {
            $orderIncrementId = $order->getIncrementId();
            $fileName = "Order_Invoice.pdf";

            $salesforceOrderId = $order->getSalesforceOrderId();

            if (!$salesforceOrderId) {
                $helper->salesforceUpdateLog("saleforce order id empty.");
                return;
            }

            $objectType = $helper::UPLOAD_DOC_OBJECT;

            $invoices = $order->getInvoiceCollection();
            if (Mage::helper("core")->isModuleEnabled("Allure_Pdf")) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices, true);
            } else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }


            $body[] = implode("\r\n", array(
                "Content-Type: application/json; charset=utf-8",
                "Content-Disposition: form-data; name=\"entity_content\";",
                "",
                '{
                    "PathOnClient" : "Order-#' . $orderIncrementId . '_Invoice.pdf"
                 }'
            ));


            $filedata = $pdf->render();

            $body[] = implode("\r\n", array(
                "Content-Type: application/octet-stream",
                "Content-Disposition: form-data; name=\"VersionData\"; filename=\"Order-#{$orderIncrementId}_Invoice.pdf\"",
                "",
                $filedata,
            ));

            // generate safe boundary
            do {
                $boundary = "---------------------" . md5(mt_rand() . microtime());
            } while (preg_grep("/{$boundary}/", $body));

            // add boundary for each parameters
            array_walk($body, function (&$part) use ($boundary) {
                $part = "--{$boundary}\r\n{$part}";
            });

            // add final boundary
            $body[] = "--{$boundary}--";
            $body[] = "";


            $url = $helper::CONTENTVERSION_URL;
            $requestMethod = "POST";
            $response = $helper->sendRequest($url, $requestMethod, $body, true, $boundary);
            //$helper->salesforceUpdateLog($response);
            $responseArr = json_decode($response, true);
            if ($responseArr["success"]) {
                //get documentLink id
                $helper->salesforceUpdateLog("call document api ");
                $salesforceContentVersionId = $responseArr["id"];
                $url1 = $helper::CONTENTVERSION_URL . "/{$salesforceContentVersionId}";
                $response1 = $helper->sendRequest($url1, "GET", null);
                $responseArr1 = json_decode($response1, true);
                $documentId = $responseArr1["ContentDocumentId"];
                if ($documentId) {
                    $helper->salesforceUpdateLog("link invoice pdf document id - " . $documentId);
                    $url2 = $helper::DOCUMENTLINK_URL;


                    $request1 = array(
                        "ContentDocumentId" => $documentId,
                        "LinkedEntityId" => $salesforceOrderId,
                        "ShareType" => "V"
                    );
                    $response2 = $helper->sendRequest($url2, "POST", $request1);
                    $responseArr2 = json_decode($response2, true);
                    if ($responseArr2["success"]) {
                        $coreResource = Mage::getSingleton('core/resource');
                        $write = $coreResource->getConnection('core_write');
                        $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='" . $documentId . "' WHERE entity_id ='" . $order->getId() . "'";
                        $write->query($sql_order);
                        $helper->salesforceUpdateLog("salesforce uploaded doc id updated into order table.");
                        $helper->salesforceUpdateLog("Invoice pdf uploaded.");
                    }
                }
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
            } else {
                $helper->addSalesforcelogRecord($objectType, $requestMethod, $order->getId(), $response);
            }

        } catch (Exception $e) {
            $helper->salesforceUpdateLog("Exception in uploadInvoicePdf - " . $e->getMessage());
        }
    }


    //using salesforce contentversion & document
    private function uploadInvoicePdfTeamwork($order)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("uploadInvoicePdf teamwork request.");
        try {
            $orderIncrementId = $order->getIncrementId();
            $fileName = "Order_Invoice.pdf";

            $salesforceOrderId = $order->getSalesforceOrderId();

            if (!$salesforceOrderId) {
                $helper->salesforceUpdateLog("saleforce order id empty.");
                return;
            }

            $objectType = $helper::UPLOAD_DOC_OBJECT;

            $invoices = $order->getInvoiceCollection();
            if (Mage::helper("core")->isModuleEnabled("Allure_Pdf")) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices, true);
            } else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }


            $body[] = implode("\r\n", array(
                "Content-Type: application/json; charset=utf-8",
                "Content-Disposition: form-data; name=\"entity_content\";",
                "",
                '{
                    "PathOnClient" : "Order-#' . $orderIncrementId . '_Invoice.pdf"
                 }'
            ));


            $filedata = $pdf->render();

            $body[] = implode("\r\n", array(
                "Content-Type: application/octet-stream",
                "Content-Disposition: form-data; name=\"VersionData\"; filename=\"Order-#{$orderIncrementId}_Invoice.pdf\"",
                "",
                $filedata,
            ));

            // generate safe boundary
            do {
                $boundary = "---------------------" . md5(mt_rand() . microtime());
            } while (preg_grep("/{$boundary}/", $body));

            // add boundary for each parameters
            array_walk($body, function (&$part) use ($boundary) {
                $part = "--{$boundary}\r\n{$part}";
            });

            // add final boundary
            $body[] = "--{$boundary}--";
            $body[] = "";


            $url = $helper::CONTENTVERSION_URL;
            $requestMethod = "POST";
            $response = $helper->sendRequest($url, $requestMethod, $body, true, $boundary);
            //$helper->salesforceUpdateLog($response);
            $responseArr = json_decode($response, true);
            if ($responseArr["success"]) {
                //get documentLink id
                $helper->salesforceUpdateLog("call document api ");
                $salesforceContentVersionId = $responseArr["id"];
                $url1 = $helper::CONTENTVERSION_URL . "/{$salesforceContentVersionId}";
                $response1 = $helper->sendRequest($url1, "GET", null);
                $responseArr1 = json_decode($response1, true);
                $documentId = $responseArr1["ContentDocumentId"];
                if ($documentId) {
                    $helper->salesforceUpdateLog("link invoice pdf document id - " . $documentId);
                    $url2 = $helper::DOCUMENTLINK_URL;


                    $request1 = array(
                        "ContentDocumentId" => $documentId,
                        "LinkedEntityId" => $salesforceOrderId,
                        "ShareType" => "V"
                    );
                    $response2 = $helper->sendRequest($url2, "POST", $request1);
                    $responseArr2 = json_decode($response2, true);
                    if ($responseArr2["success"]) {
                        $coreResource = Mage::getSingleton('core/resource');
                        $write = $coreResource->getConnection('core_write');
                        $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='" . $documentId . "' WHERE entity_id ='" . $order->getId() . "'";
                        $write->query($sql_order);
                        $helper->salesforceUpdateLog("salesforce uploaded doc id updated into order table.");
                        $helper->salesforceUpdateLog("Invoice pdf uploaded.");
                    }
                }
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
            } else {
                $helper->addSalesforcelogRecord($objectType, $requestMethod, $order->getId(), $response);
            }

        } catch (Exception $e) {
            $helper->salesforceUpdateLog("Exception in uploadInvoicePdf - " . $e->getMessage());
        }
    }


    public function uploadInvoicePdf1($order)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("uploadInvoicePdf request.");
        try {
            $orderIncrementId = $order->getIncrementId();
            $fileName = "Order_Invoice.pdf";

            if ($order->getSalesforceUploadedDocId()) {
                $helper->salesforceUpdateLog("salesforce uploaded doc id updated into order table already.");
                return;
            }

            $objectType = $helper::UPLOAD_DOC_OBJECT;

            $invoices = $order->getInvoiceCollection();
            if (Mage::helper("core")->isModuleEnabled("Allure_Pdf")) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices, true);
            } else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }

            $filedata = base64_encode($pdf->render());

            $request = array(
                "ParentId" => $order->getSalesforceOrderId(),
                "Name" => $fileName,
                "body" => "$filedata",
                "IsPrivate" => "false"
            );

            //$helper->salesforceUpdateLog(json_encode($request));

            $url = $helper::INVOICE_PDF_URL_UPLOAD;
            $requestMethod = "POST";
            $response = $helper->sendRequest($url, $requestMethod, $request);

            $responseArr = json_decode($response, true);
            if ($responseArr["success"]) {
                $coreResource = Mage::getSingleton('core/resource');
                $write = $coreResource->getConnection('core_write');
                $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='" . $responseArr["id"] . "' WHERE entity_id ='" . $order->getId() . "'";
                $write->query($sql_order);
                $helper->salesforceUpdateLog("salesforce uploaded doc id updated into order table.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
            } else {
                $helper->addSalesforcelogRecord($objectType, $requestMethod, $order->getId(), $response);
            }

        } catch (Exception $e) {
            $helper->salesforceUpdateLog("Exception in uploadInvoicePdf - " . $e->getMessage());
        }
    }

    /**
     * add shipment information into salesforce
     */
    public function addTeamworkShipmentToSalesforce($shipmentId)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("addShipmentToSalesforce teamwork request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);;
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

        if (!$salesforceOrderId) {
            return;
        }

        $objectType = $helper::SHIPMENT_OBJECT;
        $requestMethod = "GET";
        $urlPath = $helper::SHIPMENT_URL;
        if ($salesforceShipmentId) {
            $requestMethod = "PATCH";
            $urlPath .= "/" . $salesforceShipmentId;
        } else {
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
            //"Carrier__c"        => $carrierTitles,
            //"Track_Number__c"   => $trackNums,
            "Name" => "Shipment for Order #" . $orderIncrementId
        );
        //$helper->salesforceUpdateLog($urlPath);
        $helper->salesforceUpdateLog($request);

        $response = $helper->sendRequest($urlPath, $requestMethod, $request);
        $responseArr = json_decode($response, true);
        if ($responseArr["success"]) {
            $salesforceId = $responseArr["id"];
            $salesforceShipmentId = $salesforceId;
            $helper->salesforceUpdateLog("order_id :" . $order->getId() . " shipment_id :" . $shipment->getId() . " salesforce_Id :" . $salesforceId);
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='" . $salesforceId . "' WHERE entity_id ='" . $shipment->getId() . "'";
            $write->query($sql_order);
            $helper->salesforceUpdateLog("salesforce id updated into shipment.");
            $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $shipment->getId());

            //$this->updateOrderData($order);
        } else {
            if ($responseArr == "") {
                $helper->salesforceUpdateLog("salesforce id not updated into shipment.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $shipment->getId());
            } else {
                $helper->addSalesforcelogRecord($objectType, $requestMethod, $shipment->getId(), $response);
            }
        }

        if ($salesforceShipmentId) {
            $helper->salesforceUpdateLog("In Track Info");
            $isTrack = false;
            $requestR["records"] = array();
            foreach ($tracksNumCollection as $track) {
                if (!$track->getData("salesforce_shipment_track_id")) {
                    $isTrack = true;
                    $tArr = array(
                        "attributes" => array("type" => "Tracking_Information__c", "referenceId" => $track->getData("entity_id")),
                        "Magento_Tracker_Id__c" => $track->getData("entity_id"),
                        "Name" => $track->getData("title"),
                        "Shipment__c" => $salesforceShipmentId,
                        "Tracking_Number__c" => $track->getData("track_number"),
                        "Carrier__c" => $track->getData("carrier_code")
                    );
                    array_push($requestR["records"], $tArr);
                }
            }
            if ($isTrack) {
                $helper->salesforceUpdateLog("In Track Info request");
                $requestMethod = "POST";
                $urlPath = $helper::SHIPMENT_TRACK_URL;
                $responseT = $helper->sendRequest($urlPath, $requestMethod, $requestR);
                $tResponseArr = json_decode($responseT, true);
                if ($tResponseArr["hasErrors"] == false) {
                    $results = $tResponseArr["results"];
                    $sql_order = "";
                    foreach ($results as $res) {
                        $sql_order .= "UPDATE sales_flat_shipment_track SET salesforce_shipment_track_id='" . $res["id"] . "' WHERE entity_id ='" . $res["referenceId"] . "';";
                    }
                    $coreResource = Mage::getSingleton('core/resource');
                    $write1 = $coreResource->getConnection('core_write');
                    //$sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
                    $write1->query($sql_order);
                    //$helper->salesforceUpdateLog($sql_order);
                }
            }

        }
    }

    /**
     * add creditmemo order data into salesforce
     */
    public function addTeamworkCreditmemoToSalesforce($creditmemoId)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("addCreditmemoToSalesforce teamwork request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $creditMemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        $items = $creditMemo->getAllItems();

        $order = $creditMemo->getOrder();
        $salesforceOrderId = $order->getSalesforceOrderId();
        $helper->salesforceUpdateLog("salesforc order id :" . $salesforceOrderId);
        if (!$salesforceOrderId) {
            return;
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

        $currencyRate = $order->getStoreToBaseRate();
        if (!$currencyRate) {
            $currencyRate = 1;
        }

        $objectType = $helper::CREDITMEMO_OBJECT;

        $requestMethod = "GET";
        $urlPath = $helper::CREDIT_MEMO_URL;
        if ($salesforceCreditmemoId) {
            $requestMethod = "PATCH";
            $urlPath .= "/" . $salesforceCreditmemoId;
        } else {
            $requestMethod = "POST";
        }

        $ostores = Mage::helper("allure_virtualstore")->getVirtualStores();
        $oldStoreArr = array();
        foreach ($ostores as $storeO) {
            $oldStoreArr[$storeO->getId()] = $storeO->getName();
        }
        $oldStoreArr[0] = "Admin";

        $orderDate = date("Y-m-d", strtotime($orderDate)) . "T" . date("H:i:s", strtotime($orderDate)) . "+00:00";

        $request = array(
            "Adjustment__c" => $baseAdjustment,
            "Created_At__c" => $orderDate,//date("Y-m-d",strtotime($createdAt)),
            "Credit_Memo_Id__c" => $incrementId,
            "Stauts__c" => $status,
            "Discount_Amount__c" => ($discountAmount) ? ($discountAmount * $currencyRate) : 0,
            "Grand_Total__c" => $grandTotal * $currencyRate,
            "Order_Date__c" => $orderDate,//date("Y-m-d",strtotime($orderDate)),
            "Order_Id__c" => $orderIncrementId,
            "Shipping_Amount__c" => $shippingAmount * $currencyRate,
            "Store__c" => $oldStoreArr[$storeId],
            "Subtotal__c" => $subtotal * $currencyRate,
            "Tax_Amount__c" => $taxAmount * $currencyRate,
            "Order__c" => $salesforceOrderId,
            "Name" => "Credit Memo for Order #" . $orderIncrementId
        );

        $response = $helper->sendRequest($urlPath, $requestMethod, $request);
        $responseArr = json_decode($response, true);
        if ($responseArr["success"]) {
            $salesforceId = $responseArr["id"];
            $helper->salesforceUpdateLog("Salesforce Id :" . $salesforceId);
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');
            $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='" . $salesforceId . "' WHERE entity_id ='" . $creditMemo->getId() . "'";
            $write->query($sql_order);
            $helper->salesforceUpdateLog("salesforce id updated into creditmemo.");
            $helper->salesforceUpdateLog("order_id :" . $order->getId() . " creditmemo_id:" . $creditMemo->getId() . " $$ salesforce_id" . $salesforceId);;

            $cRequest = array("allOrNone" => false);
            $cRequest["records"] = array();
            $requestMethod = "PATCH";
            $urlPath = $helper::UPDATE_COMPOSITE_OBJECT_URL;
            foreach ($items as $item) {
                $orderItemId = $item->getOrderItemId();
                $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
                if (!$orderItem) {
                    continue;
                }
                $salesforceItemId = $orderItem->getSalesforceItemId();
                if (!$salesforceItemId) {
                    continue;
                }
                $tempArr = array(
                    "attributes" => array("type" => "OrderItem"),
                    "id" => $salesforceItemId,
                    "Credit_Memo__c" => $salesforceId
                );
                array_push($cRequest["records"], $tempArr);
            }

            $response = $helper->sendRequest($urlPath, $requestMethod, $cRequest);
            $responseArr1 = json_decode($response, true);
            if ($responseArr1[0]["success"]) {
                $helper->salesforceUpdateLog("creditmemo items updated into salesforce.");
                //$this->updateOrderData($order);
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $creditMemo->getId());
            } else {
                if ($responseArr == "") {
                    $helper->salesforceUpdateLog("creditmemo items not updated into salesforce.");
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $creditMemo->getId());
                } else {
                    $helper->addSalesforcelogRecord($objectType, $requestMethod, $creditMemo->getId(), $response);
                }
            }
        }
    }

    public function updateOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("In updateOrder request");
        $helper->salesforceUpdateLog("order status - " . $order->getStatus());
        $this->updateOrderData($order);
    }

    public function updateOrderData($order)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("In updateOrderData request");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        if ($order) {
            $order = Mage::getModel("sales/order")->load($order->getId());
            $salesforceOrderId = $order->getSalesforceOrderId();
            $helper->salesforceUpdateLog("salesforce order id :" . $salesforceOrderId);
            if (!$salesforceOrderId) {
                return;
            }

            //for teamwork currency rate
            $currencyRate = 1;
            if ($order->getCreateOrderMethod() == 2) {
                $currencyRate = $order->getStoreToBaseRate();
                if (!$currencyRate) {
                    $currencyRate = 1;
                }
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

            $status = $order->getStatus();

            $requestMethod = "PATCH";
            $urlPath = $helper::ORDER_URL . "/" . $salesforceOrderId;

            $request = array(
                "Shipping_Amount__c" => $baseShippingAmount * $currencyRate,

                "Total_Refunded_Amount__c" => $baseTotalRefunded * $currencyRate,
                "Tax_Amount__c" => $baseTaxAmount * $currencyRate,

                "Sub_Total__c" => $baseSubtotal * $currencyRate,
                "Discount__c" => $discountAmount * $currencyRate,
                "Discount_Base__c" => $baseDiscountAmount * $currencyRate,
                "Grant_Total__c" => $grandTotal * $currencyRate,
                "Grand_Total_Base__c" => $baseGrandTotal * $currencyRate,

                "Total_Paid__c" => $baseTotalPaid * $currencyRate,
                "Total_Due__c" => $baseTotalDue * $currencyRate,
                "Status" => $status,
            );
            $helper->salesforceUpdateLog("made order update api call to salesforce");
            $response = $helper->sendRequest($urlPath, $requestMethod, $request);
        }
    }

    public function deleteShipmentToSalesforce(Varien_Event_Observer $observer)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("deleteShipmentToSalesforce request.");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $shipment = $observer->getEvent()->getShipment();
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();
        if (!$salesforceShipmentId) {
            $helper->salesforceUpdateLog("No delete operation perform on shipment #" . $shipment->getIncrementId());
            return;
        }
        $requestMethod = "DELETE";
        $urlPath = $helper::SHIPMENT_URL . "/" . $salesforceShipmentId;
        $response = $helper->sendRequest($urlPath, $requestMethod, null);
    }

    /**
     * delete tracking info from salesforce
     */
    public function deleteTrackInfoSalesforce(Varien_Event_Observer $observer)
    {
        $helper = $this->getHelper();
        $helper->salesforceUpdateLog("deleteTrackInfoSalesforce request");

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceUpdateLog("Salesforce Plugin Disabled.");
            return;
        }

        $event = $observer->getEvent();
        $track = $event->getTrack();
        $shipment = $track->getShipment();
        $helper = $this->getHelper();
        $trackSalesforceId = $track->getData("salesforce_shipment_track_id");
        if (!$trackSalesforceId) {
            $helper->salesforceUpdateLog("No delete operation perform on track number shipment #" . $shipment->getIncrementId());
            return;
        }
        $requestMethod = "DELETE";
        $urlPath = $helper::SHIPMENT_TRACK_URL_1 . "/" . $trackSalesforceId;
        $response = $helper->sendRequest($urlPath, $requestMethod, null);
    }
}
