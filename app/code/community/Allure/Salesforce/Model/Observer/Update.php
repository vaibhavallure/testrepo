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
    public function getDataHelper()
    {
        return Mage::helper('allure_salesforce');
    }

    /**
     * for bulk updating records for Salesforce
     */
    public function syncDataToSalesforce()
    {
        $helper = $this->getHelper();
        $dataHelper = $this->getDataHelper();
        $helper->salesforceLog("------------ BULK Update: request started ------------",true);

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("Salesforce Plugin Disabled.",true);
            return;
        }

        $formattedLastRunTime = $dataHelper->getLastRunTime();
        if (empty($formattedLastRunTime)) {
            $formattedLastRunTime = new DateTime($dataHelper->getBulkUpdateInitialTime());//static right now only for test purpose
            $formattedLastRunTime = $formattedLastRunTime->format("Y-m-d H:m:s");
        }

        //$formattedLastRunTime = $lastRunTime->format("Y-m-d H:m:s");
        $helper->salesforceLog("BULK Update: lastRunTime = ".$formattedLastRunTime,true);

        $this->getRequestData($formattedLastRunTime, null);
    }

    /**
     * @param null $lastRunTime for updating records it must be present
     * @param null $requestData for creating record
     */
    public function getRequestData($formattedLastRunTime = null ,$requestData = null) {
        $helper = $this->getHelper();
        $dataHelper = $this->getDataHelper();

        //save time when request for bulk started for future use
        $requestStartTime = new DateTime();
        $dataHelper->setLastRunTime($requestStartTime->format("Y-m-d H:m:s"));

        if (!empty($formattedLastRunTime)) {
            $helper->salesforceLog("BULK Update: in getRequestData for UPDATE",true);
        }else {
            $helper->salesforceLog("BULK Create: in getRequestData for CREATE",true);
        }

        $products = $this->getProductUpdateData($formattedLastRunTime,(!empty($requestData) && $requestData['products'])?$requestData['products']:null);

        if(empty($formattedLastRunTime)){
            $orders = $this->getUpdatedOrdersData(null,(!empty($requestData) && $requestData['orders'])?$requestData['orders']:null);
        }else{
            $updatedOrdersData = $this->getUpdatedOrdersData($formattedLastRunTime,(!empty($requestData) && $requestData['orders'])?$requestData['orders']:null);
            $orders = $updatedOrdersData["orders"];
            $orderItems = $updatedOrdersData["order_items"];
        }

        $customers = $this->getCustomersUpdateData($formattedLastRunTime,(!empty($requestData) && $requestData['customers'])?$requestData['customers']:null);

        $invoices = $this->getInvoicesUpdateData($formattedLastRunTime,(!empty($requestData) && $requestData['invoice'])?$requestData['invoice']:null);

        $creditMemos = $this->getCreditMemoUpdateData($formattedLastRunTime,(!empty($requestData) && $requestData['credit_memo'])?$requestData['credit_memo']:null);

        $shipments = $this->getShipmentUpdateData($formattedLastRunTime,(!empty($requestData) && $requestData['shipment'])?$requestData['shipment']:null);

        if(!empty($formattedLastRunTime))
            $shipmentTrackings = $this->getTrackingInfoUpdateData($formattedLastRunTime);


        if(empty($formattedLastRunTime)){
            $combinedData = array("products" => $products,"customers" => $customers['customer'], "contact" => $customers['contact'],  "orders" => $orders, "invoice" => $invoices, "credit_memo" => $creditMemos, "shipment" => $shipments);
        }else{
            $combinedData = array("products" => $products, "customers" => $customers['customer'], "contact" => $customers['contact'], "orders" => $orders, "order_items" => $orderItems, "invoice" => $invoices, "credit_memo" => $creditMemos, "shipment" => $shipments, "shipment_track" => $shipmentTrackings);
        }

        $this->sendCompositeRequest($combinedData, $formattedLastRunTime);
    }


    public function sendCompositeRequest($requestData, $lastRunTime)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("BULK Update: sendCompositeRequest START ",true);
        $dataHelper = $this->getDataHelper();

        $objectMappings = array(
            "products" => "Product2",
            "orders" => "Order",
            "order_items" => "OrderItem",
            "customers" => "Account",
            "contact" => "Contact",
            "invoice" => "Invoice__c",
            "credit_memo" => "Credit_Memo__c",
            "shipment" => "Shipment__c",
            "shipment_track" => "Tracking_Information__c"
        );

        foreach ($requestData as $modelName => $reqArr) {
            if (!empty($reqArr)) {
                $chunkedReqArray = array_chunk($reqArr, 200);
                foreach ($chunkedReqArray as $reqArray) {
                    $helper->salesforceLog("------------------BULK Update:(" . $modelName . ") START send in chunk size =".sizeof($reqArray),true);
                    $request["records"] = $reqArray;

                    if (empty($lastRunTime)) {
                        $urlPath = "/services/data/v42.0/composite/tree/" . $objectMappings[$modelName];
                        $requestMethod = "POST";
                    } else {
                        $urlPath = $helper::UPDATE_COMPOSITE_OBJECT_URL;
                        $request["allOrNone"] = false;
                        $requestMethod = "PATCH";
                    }
                    //print_r(json_encode($request,true));die;
                    $response = $helper->sendRequest($urlPath, $requestMethod, $request);
                    $responseArr = json_decode($response, true);

                    if (!$responseArr["hasErrors"]) {
                        $helper->salesforceLog("bulk operation was succesfull");
                        $helper->addSalesforcelogRecord("BULK operation ", $requestMethod, "BULKOP-" . $lastRunTime, $response);
                        if (empty($lastRunTime))
                            $helper->bulkProcessResponse($responseArr, $modelName);
                    } else {
                        if ($responseArr == "") {
                            $helper->salesforceLog("bulk updation failed");
                            $helper->addSalesforcelogRecord("BULK operation ", $requestMethod, "BULKOP-" . $lastRunTime, $response);
                        } else {
                            $helper->addSalesforcelogRecord("BULK operation ", $requestMethod, "BULKOP-" . $lastRunTime, $response);
                        }
                    }
                }
                $helper->salesforceLog("------------------BULK Update: END send in chunk size",true);
            }
        }
        $helper->salesforceLog("BULK Update: sendCompositeRequest END ",true);
    }
    

    /**
     * add order data into salesforce when order placed into magento
     * @param $lastRunTime, Array $list
     * @return Array
     */
    public function getUpdatedOrdersData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: START getUpdatedOrders request.",true);

        //var_dump($lastRunTime->format("Y-m-d H:m:s"));die;

        $create = !empty($list) || $list !== null;

        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getUpdatedOrders list null",true);
            return;
        }

        //create if we need to update the records
        if(!$create){
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_order_id', array('neq' => null));
        }else if(!empty($list)) {                                                       //crate records from Tmobserver data
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter('entity_id', array('in' => $list));
        }

        $helper->salesforceLog("Order collection size - ".$orders->getSize()." For".$create?"BULK Create":"BULK Update",true);

        $orderList = array();
        $orderItemList = array();

        foreach ($orders as $order) {

            //if create and already in SF then don't create
            if($create && !empty($salesforceOrderId))
                continue;

            $request = $helper->getOrderRequestData($order,$create);


            if($create){
                if(!empty($request))
                    array_push($orderList, $request["request"]);
            }
            else{
                if(!empty($request) && !empty($request["order"]))
                    array_push($orderList, $request["order"]);
                if(!empty($request) && !empty($request["orderItem"]))
                    array_push($orderItemList, $request["orderItem"]);
            }
        }

        $helper->salesforceLog("------------BULK UPDATE: END getUpdatedOrders request.",true);
        if(empty($orderItemList)){
            //print_r(json_encode($orderList));die;
            return $orderList;
        }
        else{
            $combinedArray = array("orders" => $orderList,"order_items" => $orderItemList);
            return $combinedArray;
        }
    }

    public function getCustomersUpdateData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: Start getCustomersUpdateData request.",true);
        $create = !empty($list) || $list !== null;

        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getCustomersUpdateData list null",true);
            return;
        }

        if(!$create){
            $customers = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_customer_id', array('neq' => null));
        }else if(!empty($list)){
            $customers = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToFilter('entity_id', array('in' => $list));
        }

        $helper->salesforceLog("Customer collection size - ".$customers->getSize()." For".$create?"BULK Create":"BULK Update",true);
        $customerList = array("customer" => array(), "contact" => array());

        if ($customers) {
            foreach ($customers as $customer) {
                $salesforceId = $customer->getSalesforceCustomerId();
                $salesforceContactId = $customer->getSalesforceContactId();
                $requestData = $helper->getCustomerRequestData($customer,false,false);

                if($create){
                    //don't push to Salesforce if the ID's are present for Account or Contact
                    if(!$salesforceId && !empty($customerList) && !empty($customerList["customer"]))
                        array_push($customerList['customer'], $requestData["customer"]);
                    if(!$salesforceContactId && !empty($customerList) && !empty($customerList["contact"]))
                        array_push($customerList['contact'], $requestData["contact"]);
                }else{
                    if(!empty($requestData) && !empty($requestData["customer"]))
                        array_push($customerList['customer'], $requestData["customer"]);
                    if(!empty($requestData) && !empty($requestData["contact"]))
                        array_push($customerList['contact'], $requestData["contact"]);
                }
            }
            $helper->salesforceLog("------------BULK UPDATE: end getCustomersUpdateData request.",true);
            return $customerList;
        }
    }

    public function getInvoicesUpdateData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: Start getInvoicesUpdateData request.",true);

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("Salesforce Plugin Disabled.",true);
            return;
        }

        $create = !empty($list) || $list !== null;

        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getInvoicesUpdateData list null",true);
            return;
        }

        if(!$create){
            $invoiceCollection = Mage::getModel("sales/order_invoice")->getCollection()
                ->addFieldToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_invoice_id', array('neq' => null));
        }else if(!empty($list)){
            $invoiceCollection = Mage::getModel("sales/order_invoice")->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $list));
        }
//var_dump($invoiceCollection->getData());die;
        $helper->salesforceLog("Invoice collection size - ".$invoiceCollection->getSize()." For".$create?"BULK Create":"BULK Update",true);

        $invoiceList = array();
        foreach ($invoiceCollection as $invoice) {
            $requestData = $helper->getInvoiceRequestData($invoice,$create,false);
            if(!empty($requestData))
                array_push($invoiceList,$requestData);
        }
        //print_r(json_encode($invoiceList));die;
        $helper->salesforceLog("------------BULK UPDATE: End getInvoicesUpdateData request.",true);
        return $invoiceList;
    }

    /**
     * add creditmemo order data into salesforce
     */
    public function getCreditMemoUpdateData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: Start getCreditMemoUpdateData request.",true);

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("CreditMemo: Salesforce Plugin Disabled.",true);
            return;
        }

        $create = !empty($list) || $list !== null;

        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getCreditMemoUpdateData list null",true);
            return;
        }

        if(!$create){
            $creditMemoCollection = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addFieldToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_creditmemo_id', array('neq' => null));
        }else if(!empty($list)){
            $creditMemoCollection = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addFieldToFilter('entity_id', array('in' => $list));
        }

        $helper->salesforceLog("CreditMemo collection size - ".$creditMemoCollection->getSize()." For".$create?"BULK Create":"BULK Update",true);

        $creditMemoList = array();
        foreach ($creditMemoCollection as $creditMemo) {
            $request = $helper->getCreditMemoRequestData($creditMemo,$create,false);
            if(!empty($request))
                array_push($creditMemoList, $request);
        }
        $helper->salesforceLog("------------BULK UPDATE: End getCreditMemoUpdateData request.",true);
        return $creditMemoList;
    }

    /**
     * add shipment information into salesforce
     */
    public function getShipmentUpdateData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: Start getShipmentUpdateData request.",true);

        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("Salesforce Plugin Disabled.",true);
            return;
        }

        $create = !empty($list) || $list !== null;

        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getShipmentUpdateData list null",true);
            return;
        }

        if(!$create){
            $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                ->addFieldToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_shipment_id', array('neq' => null));
        }else if(!empty($list)) {
            $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                ->addFieldToFilter('entity_id', array('in' => $list));
        }

        //var_dump($shipmentCollection->getData());die;
        $helper->salesforceLog("Shipment collection size - ".$shipmentCollection->getSize()." For".$create?"BULK Create":"BULK Update",true);

        $shipmentList = array();
        foreach ($shipmentCollection as $shipment) {
            $request = $helper->getShipmentRequestData($shipment,$create,false);
            if(!empty($request))
                array_push($shipmentList, $request);
        }
        //var_dump($shipmentList);die;
        $helper->salesforceLog("------------BULK UPDATE: End getShipmentUpdateData request.",true);
        return $shipmentList;
    }

    /**
     * add tracking info into salesforce
     */
    public function getTrackingInfoUpdateData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: Start getTrackingInfoUpdateData request.",true);
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("Salesforce Plugin Disabled.",true);
            return;
        }
//        var_dump($lastRunTime);die;

        $create = !empty($list) || $list !== null;
        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getTrackingInfoUpdateData list null",true);
            return;
        }

        if (!$create) {
            $shipmentTrackCollection = Mage::getResourceModel('sales/order_shipment_track_collection')
                ->addFieldToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_shipment_track_id', array('neq' => null));
        } else if (!empty($list)) {
            $shipmentTrackCollection = Mage::getResourceModel('sales/order_shipment_track_collection')
                ->addFieldToFilter('entity_id', array('in' => $list));
        }

        $helper->salesforceLog("ShipmentTrack collection size - ".$shipmentTrackCollection->getSize()." For".$create?"BULK Create":"BULK Update",true);
        //var_dump($shipmentTrackCollection->getData());die;

        $shipmentTrackList = array();
        foreach ($shipmentTrackCollection as $track) {
            $request = $helper->getTrackingInformationData($track,$create,false);
            if(!empty($request))
                array_push($shipmentTrackList, $request);
        }
        $helper->salesforceLog("------------BULK UPDATE: End getTrackingInfoUpdateData request.",true);
        return $shipmentTrackList;
    }

    private function getProductUpdateData($lastRunTime = null, $list = null)
    {
        $helper = $this->getHelper();
        $helper->salesforceLog("------------BULK UPDATE: start getProductUpdateData request.",true);
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("Salesforce Plugin Disabled.",true);
            return;
        }

        $create = (!empty($list) || $list !== null) && ($lastRunTime === null);
        if($list === null && $lastRunTime === null){
            $helper->salesforceLog("BULK create: in getProductUpdateData list null",true);
            return;
        }
        if (!$create) {
            $products = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('updated_at', array('from' => $lastRunTime))
                ->addAttributeToFilter('salesforce_shipment_track_id', array('neq' => null));;
                //var_dump($products->getData());die;
            $helper->salesforceLog("BULK Update: in getProductUpdateData collection size = ".$products->getSize(),true);
        } else if (!empty($list)) {
            $products = Mage::getModel('catalog/product')->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $list));
            $helper->salesforceLog("BULK Create: in getProductUpdateData collection size = ".$products->getSize(),true);
        }

        $helper->salesforceLog("Product collection size - ".$products->getSize()." For".$create?"BULK Create":"BULK Update",true);
        //var_dump($products->getData());die;

        $productList = array();
        foreach ($products as $productOb) {
            $product = Mage::getModel('catalog/product')->load($productOb->getId());
            try {
                if ($product) {
                    $requestData = $helper->getProductData($product,$create,false);

                    if(!empty($requestData) && $create)
                        array_push($productList,$requestData);
                    else{
                        if(!empty($requestData) && !empty($requestData["product"]))
                            array_push($productList,$requestData["product"]);
                        if(!empty($requestData) && !empty($requestData["pricebookEntries"]))
                            array_push($productList,$requestData["pricebookEntries"]);
                    }
                }
            } catch (Exception $e) {
                $helper->salesforceLog("BULK Update: Exception in ADD/UPDATE into salesforce, message ". $e->getMessage(),true);
            }
        }
        $helper->salesforceLog("------------BULK UPDATE: End getProductUpdateData request.",true);
        return $productList;
    }
}
