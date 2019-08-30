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
        Mage::log("addOrderToSalesforce",Zend_Log::DEBUG,"my.log",true);
        $helper = $this->getHelper();
        $helper->salesforceLog("----------- Magento Event START: addOrderToSalesforce request. -----------");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();

        if(Mage::registry('sales_order_save_after_'.$orderId)){
            return $this;
        }
        Mage::register('sales_order_save_after_'.$orderId,true);


        $createOrderMethod = $order->getCreateOrderMethod();
        if($createOrderMethod == 2) {
            $helper->salesforceLog("Return from Order Event - Teamwork order -".$orderId);
            return;
        }
        
        $requestData = $helper->getOrderRequestData($order,true);
        if(empty($requestData)){
            $helper->salesforceLog("Return from Order Event - Empty request data-".$orderId);
            return;
        }

        $request = array("order" => array());
        array_push($request["order"],$requestData["request"]);
        $magOrderItemArr = $requestData["orderItem"];

        $requestMethod = "POST";
        $objectType = $helper::ORDER_OBJECT;

        $urlPath = $helper::ORDER_PLACE_URL;
        $response = $helper->sendRequest($urlPath, $requestMethod, $request);

        $responseArr = json_decode($response, true);
        $helper->salesforceLog($responseArr);
        $sql = "";
        if ($responseArr["done"]) {
            $recordes = $responseArr["records"][0];
            $salesforceId = $recordes["Id"];

            $sql .= "UPDATE sales_flat_order SET salesforce_order_id='" . $salesforceId . "' WHERE entity_id ='" . $orderId . "';";

            $orderItemsArr = $recordes["OrderItems"];
            $count = 0;
            if ($orderItemsArr["done"]) {
                $orderItemsList = $orderItemsArr["records"];
                foreach ($orderItemsList as $ordItem) {
                    $salesforceItemId = $ordItem["Id"];
                    $mOrderItem = $magOrderItemArr[$count];
                    $mItemId = $mOrderItem["item_id"];
                    $mOrderId = $mOrderItem["order_id"];
                    $mSku = $mOrderItem["sku"];
                    if ($mOrderId && $mSku) {
                        if ($salesforceItemId) {
                            $sql .= "UPDATE sales_flat_order_item SET salesforce_item_id='" . $salesforceItemId .
                                "' WHERE item_id ='" . $mItemId."';";
                        }
                    }
                    $count++;
                }
            }
            $helper->executeQuery($sql);
            $helper->salesforceLog("Salesforce Order/OrderItem id updated into table.");
            $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
        } else {
            $helper->addSalesforcelogRecord($objectType, $requestMethod, $order->getId(), $response);
        }
        $helper->salesforceLog("----------- Magento Event END: addOrderToSalesforce request. -----------");
    }
    
    public function addInvoiceIntoSalesforce($orderId){
        $helper = $this->getHelper();
        $helper->salesforceLog("addInvoiceToSalesforce teamwork request.");
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if (!$isEnable) {
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }

        $order = Mage::getModel("sales/order")->load($orderId);

        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $salesforceInvoiceId = $invoice->getSalesforceInvoiceId();
            $objectType = $helper::INVOICE_OBJECT;
            $urlPath = $helper::INVOICE_URL;

            $requestMethod = "POST";

            $request = $helper->getInvoiceRequestData($invoice, true, true);

            $response = $helper->sendRequest($urlPath, $requestMethod, $request);
            $responseArr = json_decode($response, true);
            if ($responseArr["success"]) {
                $salesforceId = $responseArr["id"];
                $helper->salesforceLog("INVOICE : Order_id :" . $order->getId() . " Invoice_id :" . $invoice->getId() . " $$ InvoiceSalesforceId :" . $salesforceId);

                $sql_order = "UPDATE sales_flat_invoice SET salesforce_invoice_id='" . $salesforceId . "' WHERE entity_id ='" . $invoice->getId() . "'";
                $helper->executeQuery($sql_order);
                $helper->salesforceLog("INVOICE: Salesforce id updated into invoice.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $invoice->getId());
            } else {
                if ($responseArr == "") {
                    $helper->salesforceLog("INVOICE: Salesforce id not updated into invoice.");
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $invoice->getId());
                } else {
                    $helper->addSalesforcelogRecord($objectType, $requestMethod, $invoice->getId(), $response);
                }
            }
        }
        //upload invoice
        $this->uploadInvoicePdfTeamwork($order);
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
        if($order->getCreateOrderMethod() == 2){
            $helper->salesforceLog("Return from Invoice Event - Teamwork Invoice -".$invoice->getId());
            return ;
        }

        $order = Mage::getModel("sales/order")->load($order->getId());
        $helper->salesforceLog("order id :".$order->getId());
        $salesforceOrderId = $order->getSalesforceOrderId();
        $helper->salesforceLog("salesforce order id :".$salesforceOrderId);
        if($salesforceOrderId){
            $salesforceInvoiceId = $invoice->getSalesforceInvoiceId();

            $objectType = $helper::INVOICE_OBJECT;

            $urlPath = $helper::INVOICE_URL;
            $requestMethod = "POST";

            $request = $helper->getInvoiceRequestData($invoice,true,true);
            
            $response       = $helper->sendRequest($urlPath, $requestMethod, $request);
            $responseArr    = json_decode($response,true);
            if($responseArr["success"]){
                $salesforceId = $responseArr["id"];
                $helper->salesforceLog("INVOICE: Order_id :".$order->getId()." Invoice_id :".$invoice->getId()." $$  SalesforceInvoiceId :".$salesforceId);

                $sql_order = "UPDATE sales_flat_invoice SET salesforce_invoice_id='".$salesforceId."' WHERE entity_id ='".$invoice->getId()."'";
                $helper->executeQuery($sql_order);
                $helper->salesforceLog("INVOICE : SalesforceId updated into invoice.");
                $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $invoice->getId());

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
    
    
    //using salesforce contentversion & document
    private function uploadInvoicePdf($order){
        $helper = $this->getHelper();
        $helper->salesforceLog("uploadInvoicePdf request.");
        try{
            $orderIncrementId = $order->getIncrementId();
            $fileName = "Order_Invoice.pdf";
            
            $salesforceOrderId = $order->getSalesforceOrderId();
            
            if(!$salesforceOrderId){
                $helper->salesforceLog("saleforce order id empty.");
                return;
            }
            
            $objectType = $helper::UPLOAD_DOC_OBJECT;
            
            $invoices = $order->getInvoiceCollection();
            if(Mage::helper("core")->isModuleEnabled("Allure_Pdf")){
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices,true);
            }else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }
            
            
            $body[] = implode("\r\n", array(
                "Content-Type: application/json; charset=utf-8",
                "Content-Disposition: form-data; name=\"entity_content\";",
                "",
                '{
                    "PathOnClient" : "Order-#'.$orderIncrementId.'_Invoice.pdf"
                 }'
            ));
            
            
            $filedata =  $pdf->render();
            
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
                //$helper->salesforceLog($response);
                $responseArr = json_decode($response,true);
                if($responseArr["success"]){
                    //get documentLink id
                    $helper->salesforceLog("call document api ");
                    $salesforceContentVersionId = $responseArr["id"];
                    $url1 = $helper::CONTENTVERSION_URL."/{$salesforceContentVersionId}";
                    $response1 = $helper->sendRequest($url1 , "GET" , null);
                    $responseArr1 = json_decode($response1,true);
                    $documentId = $responseArr1["ContentDocumentId"];
                    if($documentId){
                        $helper->salesforceLog("link invoice pdf document id - ".$documentId);
                        $url2 = $helper::DOCUMENTLINK_URL;
                        
                        
                        
                        $request1 = array(
                            "ContentDocumentId"     =>  $documentId,
                            "LinkedEntityId"        =>  $salesforceOrderId,
                            "ShareType"             =>  "V"
                        );
                        $response2 = $helper->sendRequest($url2 , "POST" , $request1);
                        $responseArr2 = json_decode($response2,true);
                        if($responseArr2["success"]){
                            $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='".$documentId."' WHERE entity_id ='".$order->getId()."'";
                            $helper->executeQuery($sql_order);
                            $helper->salesforceLog("salesforce uploaded doc id updated into order table.");
                            $helper->salesforceLog("Invoice pdf uploaded.");
                        }
                    }
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$order->getId(),$response);
                }
                
        }catch(Exception $e){
            $helper->salesforceLog("Exception in uploadInvoicePdf - ".$e->getMessage());
        }
    }
    
    
    //using salesforce contentversion & document
    private function uploadInvoicePdfTeamwork($order){
        $helper = $this->getHelper();
        $helper->salesforceLog("uploadInvoicePdf teamwork request.");
        try{
            $orderIncrementId = $order->getIncrementId();
            $fileName = "Order_Invoice.pdf";
            
            $salesforceOrderId = $order->getSalesforceOrderId();
            
            if(!$salesforceOrderId){
                $helper->salesforceLog("saleforce order id empty.");
                return;  
            }
            
            $objectType = $helper::UPLOAD_DOC_OBJECT;
            
            $invoices = $order->getInvoiceCollection();
            if(Mage::helper("core")->isModuleEnabled("Allure_Pdf")){
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getCompressPdf($invoices,true);
            }else {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            }
            
            
            $body[] = implode("\r\n", array(
                "Content-Type: application/json; charset=utf-8",
                "Content-Disposition: form-data; name=\"entity_content\";",
                "",
                '{
                    "PathOnClient" : "Order-#'.$orderIncrementId.'_Invoice.pdf"
                 }'
            ));
            
            
            $filedata =  $pdf->render();
            
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
                //$helper->salesforceLog($response);
                $responseArr = json_decode($response,true);
                if($responseArr["success"]){
                    //get documentLink id
                    $helper->salesforceLog("call document api ");
                    $salesforceContentVersionId = $responseArr["id"];
                    $url1 = $helper::CONTENTVERSION_URL."/{$salesforceContentVersionId}";
                    $response1 = $helper->sendRequest($url1 , "GET" , null);
                    $responseArr1 = json_decode($response1,true);
                    $documentId = $responseArr1["ContentDocumentId"];
                    if($documentId){
                        $helper->salesforceLog("link invoice pdf document id - ".$documentId);
                        $url2 = $helper::DOCUMENTLINK_URL;
                        
                        
                        
                        $request1 = array(
                            "ContentDocumentId"     =>  $documentId,
                            "LinkedEntityId"        =>  $salesforceOrderId,
                            "ShareType"             =>  "V"
                        );
                        $response2 = $helper->sendRequest($url2 , "POST" , $request1);
                        $responseArr2 = json_decode($response2,true);
                        if($responseArr2["success"]){
                            $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='".$documentId."' WHERE entity_id ='".$order->getId()."'";
                            $helper->executeQuery($sql_order);
                            $helper->salesforceLog("salesforce uploaded doc id updated into order table.");
                            $helper->salesforceLog("Invoice pdf uploaded.");
                        }
                    }
                    $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $order->getId());
                }else{
                    $helper->addSalesforcelogRecord($objectType,$requestMethod,$order->getId(),$response);
                }
                
        }catch(Exception $e){
            $helper->salesforceLog("Exception in uploadInvoicePdf - ".$e->getMessage());
        }
    }
    
    
    public function uploadInvoicePdf1($order){
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
                $sql_order = "UPDATE sales_flat_order SET salesforce_uploaded_doc_id='".$responseArr["id"]."' WHERE entity_id ='".$order->getId()."'";
                $helper->executeQuery($sql_order);
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

        if($order->getCreateOrderMethod() == 2){
            $helper->salesforceLog("Return from Shipment Event - Order -".$order->getId());
            return ;
        }
        $tracksNumCollection = $shipment->getAllTracks();

        $objectType = $helper::SHIPMENT_OBJECT;
        $requestMethod = "GET";
        $urlPath = $helper::SHIPMENT_URL;

        $requestMethod = "POST";

        $request = $helper->getShipmentRequestData($shipment,true,true);
        $helper->salesforceLog($request);
        
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr    = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $salesforceShipmentId = $salesforceId;
            $helper->salesforceLog("order_id :".$order->getId()." shipment_id :".$shipment->getId()." salesforce_Id :".$salesforceId);

            $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
            $helper->executeQuery($sql_order);

            $helper->salesforceLog("salesforce id updated into shipment.");
            $helper->deleteSalesforcelogRecord($objectType, $requestMethod, $shipment->getId());
            
            //$this->updateOrderData($order);
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
                    $helper->executeQuery($sql_order);
                }
            }
            
        }
        
        
    }

    /**
     * add shipment information into salesforce
     */
    public function addTeamworkShipmentToSalesforce($shipmentId){
        $helper = $this->getHelper();
        $helper->salesforceLog("addShipmentToSalesforce teamwork request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $shipment =  Mage::getModel('sales/order_shipment')->load($shipmentId);;
        $salesforceShipmentId = $shipment->getSalesforceShipmentId();
        
        $order = $shipment->getOrder();
        

        $tracksNumCollection = $shipment->getAllTracks();
        
        $objectType = $helper::SHIPMENT_OBJECT;
        $requestMethod = "GET";
        $urlPath = $helper::SHIPMENT_URL;

        $requestMethod = "POST";

        $request = $helper->getShipmentRequestData($shipment,true,true);
        $helper->salesforceLog($request);
        
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr    = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $salesforceShipmentId = $salesforceId;
            $helper->salesforceLog("order_id :".$order->getId()." shipment_id :".$shipment->getId()." salesforce_Id :".$salesforceId);

            $sql_order = "UPDATE sales_flat_shipment SET salesforce_shipment_id='".$salesforceId."' WHERE entity_id ='".$shipment->getId()."'";
            $helper->executeQuery($sql_order);
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
                    $helper->executeQuery($sql_order);
                }
            }
            
        }
    }

    
    /**
     * add creditmemo order data into salesforce
     */
    public function addCreditmemoToSalesforce(Varien_Event_Observer $observer){
        $helper = $this->getHelper();
        $helper->salesforceLog("CreditMemo: addCreditmemoToSalesforce request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("CreditMemo: Salesforce Plugin Disabled.");
            return;
        }
        
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $items      = $creditMemo->getAllItems();
        
        $order = $creditMemo->getOrder();
        
        if ($order->getCreateOrderMethod() == 2){
            $helper->salesforceLog("CreditMemo: Return from CreditMemo event Teamwork Order -".$order->getId());
            return;
        }
        
        $salesforceOrderId = $order->getSalesforceOrderId();

        if(!$salesforceOrderId){
            $helper->salesforceLog("CreditMemo: SalesfroceOrderId not found:");
            return ;
        }else{
            $helper->salesforceLog("CreditMemo: SalesfroceOrderId found:".$salesforceOrderId);
        }
        $salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();

        $objectType = $helper::CREDITMEMO_OBJECT;
        
        $requestMethod  = "GET";
        $urlPath        = $helper::CREDIT_MEMO_URL;

        $requestMethod = "POST";

        $request = $helper->getCreditMemoRequestData($creditMemo,true,true);
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $helper->salesforceLog("Salesforce Id :".$salesforceId);
            $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='".$salesforceId."' WHERE entity_id ='".$creditMemo->getId()."'";
            $helper->executeQuery($sql_order);
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
                //$this->updateOrderData($order);
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
    
    
    /**
     * add creditmemo order data into salesforce
     */
    public function addTeamworkCreditmemoToSalesforce($creditmemoId){
        $helper = $this->getHelper();
        $helper->salesforceLog("addCreditmemoToSalesforce teamwork request.");
        
        $isEnable = Mage::helper("allure_salesforce")->isEnabled();
        if(!$isEnable){
            $helper->salesforceLog("Salesforce Plugin Disabled.");
            return;
        }
        
        $creditMemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        $items      = $creditMemo->getAllItems();
        
        $order = $creditMemo->getOrder();
        $salesforceOrderId = $order->getSalesforceOrderId();
        $helper->salesforceLog("salesforc order id :".$salesforceOrderId);
        if(!$salesforceOrderId){
            return ;
        }
        $salesforceCreditmemoId = $creditMemo->getSalesforceCreditmemoId();

        $objectType = $helper::CREDITMEMO_OBJECT;
        
        $requestMethod  = "GET";
        $urlPath        = $helper::CREDIT_MEMO_URL;

        $requestMethod = "POST";

        $request = $helper->getCreditMemoRequestData($creditMemo, true, true);
        
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr = json_decode($response,true);
        if($responseArr["success"]){
            $salesforceId = $responseArr["id"];
            $helper->salesforceLog("Salesforce Id :".$salesforceId);
            
            $sql_order = "UPDATE sales_flat_creditmemo SET salesforce_creditmemo_id='".$salesforceId."' WHERE entity_id ='".$creditMemo->getId()."'";
            $helper->executeQuery($sql_order);
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
                //$this->updateOrderData($order);
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

        $requestMethod = "POST";
        $urlPath = $helper::SHIPMENT_TRACK_URL_1 ;
        $request = $helper->getTrackingInformationData($track,true,true);
        $response = $helper->sendRequest($urlPath,$requestMethod,$request);
        $responseArr = json_decode($response,true);
        if($responseArr["success"]){
            $sql_order = "UPDATE sales_flat_shipment_track SET salesforce_shipment_track_id='".$responseArr["id"]."' WHERE entity_id ='".$track->getData("entity_id")."';";
            $helper->executeQuery($sql_order);
        }
    }
    
    public function updateOrder(Varien_Event_Observer $observer){
        $order = $observer->getEvent()->getOrder();
        $helper = $this->getHelper();
        $helper->salesforceLog("In updateOrder request");
        $helper->salesforceLog("order status - ".$order->getStatus());
        $this->updateOrderData($order);
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
            
            //for teamwork currency rate
            $currencyRate = 1;
            if($order->getCreateOrderMethod() == 2){
                $currencyRate = $order->getStoreToBaseRate();
                if(!$currencyRate){
                    $currencyRate = 1;
                }
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
            
            $status = $order->getStatus();
            
            $requestMethod  = "PATCH";
            $urlPath        = $helper::ORDER_URL . "/" .$salesforceOrderId;
            
            $request = array(
                "Shipping_Amount__c"            => $baseShippingAmount * $currencyRate,
                
                "Total_Refunded_Amount__c"      => $baseTotalRefunded * $currencyRate,
                "Tax_Amount__c"                 => $baseTaxAmount * $currencyRate,
                
                "Sub_Total__c"                  => $baseSubtotal * $currencyRate,
                "Discount__c"                   => $discountAmount * $currencyRate,
                "Discount_Base__c"              => $baseDiscountAmount * $currencyRate,
                //"Grant_Total__c"                => $grandTotal * $currencyRate,
                "Grant_Total__c"                => $grandTotal,
                "Grand_Total_Base__c"           => $baseGrandTotal * $currencyRate,
                
                "Total_Paid__c"                 => $baseTotalPaid * $currencyRate,
                "Total_Due__c"                  => $baseTotalDue * $currencyRate,
                "Status"                        => $status,
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
