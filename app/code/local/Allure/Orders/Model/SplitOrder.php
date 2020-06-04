<?php

class Allure_Orders_Model_SplitOrder{
    const LOG_FILE_NAME = "split_orders.log";
    
    /** Order item status */
    const ITEM_ADD = "ITEM_ADD";
    const ITEM_MODIFY = "ITEM_MODIFY";
    
    /** Order address types  */
    const BILLING_ADDR_TYPE = "billing";
    const SHIPPING_ADDR_TYPE = "shipping";
    
    const GUEST = 0;
    const GENERAL = 1;
    const WHOLESALE = 2;
    
    /** @var Mage_Core_Model_Resource $_resource */
    protected $_resource;
    
    /** Varien_Db_Adapter_Interface $_readConnection */
    protected $_readConnection;
    
    /** Varien_Db_Adapter_Interface $_writeConnection */
    protected $_writeConnection;
    
    public function __construct(){
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_readConnection = $this->_resource->getConnection("core_read");
        $this->_writeConnection = $this->_resource->getConnection("core_write");
    }
    
    /**
     * Keep track of split order data
     * @param mixed $message
     */
    private function addLog($message = null){
        if(!$message) return ;
        Mage::log($message, Zend_Log::DEBUG, self::LOG_FILE_NAME, true);
    }
    
    
    private function loadOrder($incrementId){
        $this->addLog("Order Id = {$incrementId} in loadOrder.");
        $orderData = array();
        $orderResults = $this->_readConnection->select()
        ->from($this->_resource->getTableName("sales/order"))
        ->where("increment_id = ?", $incrementId);
        $orderResults = $this->_readConnection->fetchAll($orderResults);
        if(count($orderResults) > 0){
            $orderData = $orderResults[0];
        }
        return $orderData;
    }
    
    private function loadEntity($incrementId, $tableName, $cond){
        $this->addLog("Order Id = {$incrementId} in loadEntity.");
        $this->addLog("Order Id = {$incrementId} in loadEntity {$tableName} {$cond}.");
        $entityResult = $this->_readConnection->select()
        ->from($tableName)
        ->where($cond);
        return $this->_readConnection->fetchAll($entityResult);
    }
    
    private function prepareNewIncrementId($incrementId, $code){
        return $incrementId . "-" .strtoupper(substr($code, 0, 1));
    }
    
    private function orderGrid($orderId, $origIncrementId, $internalOrderId,$incrementId, $updateOrder){
        //create or update order grid
        //check order grid present or not
        $orderGridTable = $this->_resource->getTableName("sales/order_grid");
        $orderGridCond = "increment_id = '{$incrementId}'";
        $orderGridData = $this->loadEntity($incrementId, $orderGridTable, $orderGridCond);
        if(!count($orderGridData)){
            //load orig order grid data
            $orderGridCond = "increment_id = '{$origIncrementId}'";
            $origOrdGridData = $this->loadEntity($origIncrementId, $orderGridTable, $orderGridCond);
            if(count($origOrdGridData)){
                $origOrdGridData = $origOrdGridData[0];
                $origOrdGridDataCopy = $origOrdGridData;
                $origOrdGridDataCopy["entity_id"] = $orderId;
                $origOrdGridDataCopy["increment_id"] = $incrementId;
                $origOrdGridDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
                $origOrdGridDataCopy["grand_total"] = $updateOrder["grand_total"];
                $origOrdGridDataCopy["base_total_paid"] = $updateOrder["base_total_paid"];
                $origOrdGridDataCopy["total_paid"] = $updateOrder["total_paid"];
                $this->_writeConnection->insert($orderGridTable, $origOrdGridDataCopy);
            }else{
                $this->addLog("order grid {$origIncrementId} not found.");
            }
        }else {
            $orderGridData = $orderGridData[0];
            $ordGridEntityId = $orderGridData["entity_id"];
            $orderGridDataCopy = array();//$orderGridData;
            $orderGridDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
            $orderGridDataCopy["grand_total"] = $updateOrder["grand_total"];
            $orderGridDataCopy["base_total_paid"] = $updateOrder["base_total_paid"];
            $orderGridDataCopy["total_paid"] = $updateOrder["total_paid"];
            $this->_writeConnection->update($orderGridTable, $orderGridDataCopy,"entity_id = {$ordGridEntityId}");
            $this->addLog("Update order grid");
        }
    }
    
    private function orderPayment($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder){
        $orderPaymentTable = $this->_resource->getTableName("sales/order_payment");
        $ordPaymentCond = "parent_id = {$orderId}";
        $ordPaymentData = $this->loadEntity($incrementId, $orderPaymentTable, $ordPaymentCond);
        if(count($ordPaymentData)){
            $ordPaymentDataCopy = array();//$ordPaymentData[0];
            $ordPaymentData = $ordPaymentData[0];
            $entityId = $ordPaymentData["entity_id"];
            $ordPaymentDataCopy["parent_id"] = $orderId;
            $ordPaymentDataCopy["shipping_captured"] = $updateOrder["shipping_amount"];
            $ordPaymentDataCopy["base_shipping_captured"] = $updateOrder["base_shipping_amount"];
            $ordPaymentDataCopy["shipping_amount"] = $updateOrder["shipping_amount"];
            $ordPaymentDataCopy["base_shipping_amount"] = $updateOrder["base_shipping_amount"];
            $ordPaymentDataCopy["amount_paid"] = $updateOrder["total_paid"];
            $ordPaymentDataCopy["base_amount_paid"] = $updateOrder["base_total_paid"];
            $ordPaymentDataCopy["amount_authorized"] = $updateOrder["total_paid"];
            $ordPaymentDataCopy["base_amount_authorized"] = $updateOrder["base_total_paid"];
            $ordPaymentDataCopy["base_amount_paid_online"] = $updateOrder["base_total_paid"];
            $ordPaymentDataCopy["amount_ordered"] = $updateOrder["total_paid"];
            $ordPaymentDataCopy["base_amount_ordered"] = $updateOrder["base_total_paid"];
            $this->_writeConnection->update($orderPaymentTable, $ordPaymentDataCopy, "entity_id = {$entityId}");
            $this->addLog("Update order payment");
        }else {
            //load original order payment
            $ordPaymentCond = "parent_id = {$internalOrderId}";
            $origOrdPayments = $this->loadEntity($origIncrementId, $orderPaymentTable, $ordPaymentCond);
            foreach ($origOrdPayments as $origOrdPayment){
                $ordPaymentDataCopy = $origOrdPayment;
                unset($ordPaymentDataCopy["entity_id"]);
                $ordPaymentDataCopy["parent_id"] = $orderId;
                $ordPaymentDataCopy["shipping_captured"] = $updateOrder["shipping_amount"];
                $ordPaymentDataCopy["base_shipping_captured"] = $updateOrder["base_shipping_amount"];
                $ordPaymentDataCopy["shipping_amount"] = $updateOrder["shipping_amount"];
                $ordPaymentDataCopy["base_shipping_amount"] = $updateOrder["base_shipping_amount"];
                $ordPaymentDataCopy["amount_paid"] = $updateOrder["total_paid"];
                $ordPaymentDataCopy["base_amount_paid"] = $updateOrder["base_total_paid"];
                $ordPaymentDataCopy["amount_authorized"] = $updateOrder["total_paid"];
                $ordPaymentDataCopy["base_amount_authorized"] = $updateOrder["base_total_paid"];
                $ordPaymentDataCopy["base_amount_paid_online"] = $updateOrder["base_total_paid"];
                $ordPaymentDataCopy["amount_ordered"] = $updateOrder["total_paid"];
                $ordPaymentDataCopy["base_amount_ordered"] = $updateOrder["base_total_paid"];
                $affRows = $this->_writeConnection->insert($orderPaymentTable, $ordPaymentDataCopy);
            }
                
            $this->addLog("order payment {$incrementId} created.");
        }
    }
    
    private function orderStatusHistory($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder)
    {
        $orderHistoryTable = $this->_resource->getTableName("sales/order_status_history");
        $statusHistCond = "parent_id = {$orderId}";
        $ordStatusHistoryData = $this->loadEntity($incrementId, $orderHistoryTable, $statusHistCond);
        if(!count($ordStatusHistoryData)){
            //load original order status history
            $statusHistCond = "parent_id = {$internalOrderId}";
            $ordStatusHistoryData = $this->loadEntity($origIncrementId, $orderHistoryTable, $statusHistCond);
            foreach ($ordStatusHistoryData as $orderHistory){
                $orderHistoryCopy = $orderHistory;
                unset($orderHistoryCopy["entity_id"]);
                $orderHistoryCopy["parent_id"] = $orderId;
                $this->_writeConnection->insert($orderHistoryTable, $orderHistoryCopy);
            }
        }
    }
    
    private function orderInvoice($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder){
        $orderInvoiceTable = $this->_resource->getTableName("sales/invoice");
        $orderInvoiceGridTable = $this->_resource->getTableName("sales/invoice_grid");
        $ordInvoiceCond = "order_id = {$orderId}";
        $ordInvoiceData = $this->loadEntity($incrementId, $orderInvoiceTable, $ordInvoiceCond);
        if(count($ordInvoiceData)){
            foreach ($ordInvoiceData as $ordInvoice){
                $orderInvoiceDataCopy = $ordInvoice;
                $orderInvoiceDataCopy["grand_total"] = $updateOrder["grand_total"];
                $orderInvoiceDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
                $orderInvoiceDataCopy["shipping_tax_amount"] = $updateOrder["shipping_tax_amount"];
                $orderInvoiceDataCopy["base_shipping_tax_amount"] = $updateOrder["base_shipping_tax_amount"];
                $orderInvoiceDataCopy["tax_amount"] = $updateOrder["tax_amount"];
                $orderInvoiceDataCopy["base_tax_amount"] = $updateOrder["base_tax_amount"];
                $orderInvoiceDataCopy["discount_amount"] = $updateOrder["discount_amount"];
                $orderInvoiceDataCopy["base_discount_amount"] = $updateOrder["base_discount_amount"];
                $orderInvoiceDataCopy["shipping_amount"] = $updateOrder["shipping_amount"];
                $orderInvoiceDataCopy["base_shipping_amount"] = $updateOrder["base_shipping_amount"];
                $orderInvoiceDataCopy["shipping_incl_tax"] = $updateOrder["shipping_incl_tax"];
                $orderInvoiceDataCopy["base_shipping_incl_tax"] = $updateOrder["base_shipping_incl_tax"];
                $orderInvoiceDataCopy["subtotal_incl_tax"] = $updateOrder["subtotal_incl_tax"];
                $orderInvoiceDataCopy["base_subtotal_incl_tax"] = $updateOrder["base_subtotal_incl_tax"];
                $orderInvoiceDataCopy["subtotal"] = $updateOrder["subtotal"];
                $orderInvoiceDataCopy["base_subtotal"] = $updateOrder["base_subtotal"];
                $this->_writeConnection->update($orderInvoiceTable, $orderInvoiceDataCopy, "entity_id = {$ordInvoice["entity_id"]}");
                
                $invGridId = $ordInvoice["entity_id"];
                $invoiceGridDataCopy = array();
                $invoiceGridDataCopy["grand_total"] = $updateOrder["grand_total"];
                $invoiceGridDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
                $this->_writeConnection->update($orderInvoiceGridTable, $invoiceGridDataCopy,"entity_id = {$invGridId}");
                $this->addLog("Invoice grid {$incrementId} updated.");
            }
        }else{
            //load original order invoice data
            $ordInvoiceCond = "order_id = {$internalOrderId}";
            $origOrderInvoiceData = $this->loadEntity($origIncrementId, $orderInvoiceTable, $ordInvoiceCond);
            foreach ($origOrderInvoiceData as $orderInvoiceData){
                $orderInvoiceDataCopy = $orderInvoiceData;
                $invIncrementId = $orderInvoiceDataCopy["increment_id"];
                $wrseCode = "B";
                $invIncrementId = $this->prepareNewIncrementId($invIncrementId, $wrseCode);
                $orderInvoiceDataCopy["grand_total"] = $updateOrder["grand_total"];
                $orderInvoiceDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
                $orderInvoiceDataCopy["shipping_tax_amount"] = $updateOrder["shipping_tax_amount"];
                $orderInvoiceDataCopy["base_shipping_tax_amount"] = $updateOrder["base_shipping_tax_amount"];
                $orderInvoiceDataCopy["tax_amount"] = $updateOrder["tax_amount"];
                $orderInvoiceDataCopy["base_tax_amount"] = $updateOrder["base_tax_amount"];
                $orderInvoiceDataCopy["discount_amount"] = $updateOrder["discount_amount"];
                $orderInvoiceDataCopy["base_discount_amount"] = $updateOrder["base_discount_amount"];
                $orderInvoiceDataCopy["shipping_amount"] = $updateOrder["shipping_amount"];
                $orderInvoiceDataCopy["base_shipping_amount"] = $updateOrder["base_shipping_amount"];
                $orderInvoiceDataCopy["shipping_incl_tax"] = $updateOrder["shipping_incl_tax"];
                $orderInvoiceDataCopy["base_shipping_incl_tax"] = $updateOrder["base_shipping_incl_tax"];
                $orderInvoiceDataCopy["subtotal_incl_tax"] = $updateOrder["subtotal_incl_tax"];
                $orderInvoiceDataCopy["base_subtotal_incl_tax"] = $updateOrder["base_subtotal_incl_tax"];
                $orderInvoiceDataCopy["subtotal"] = $updateOrder["subtotal"];
                $orderInvoiceDataCopy["base_subtotal"] = $updateOrder["base_subtotal"];
                
                //load order address
                $ordAddressTable = $this->_resource->getTableName("sales/order_address");
                $ordAddressData = $this->loadEntity($incrementId, $ordAddressTable, "parent_id = {$orderId}");
                foreach ($ordAddressData as $ordAddress){
                    if($ordAddress["address_type"] == self::BILLING_ADDR_TYPE){
                        $orderInvoiceDataCopy["billing_address_id"] = $ordAddress["entity_id"];
                    }else{
                        $orderInvoiceDataCopy["shipping_address_id"] = $ordAddress["entity_id"];
                    }
                }
                
                $orderInvoiceDataCopy["order_id"] = $orderId;
                $orderInvoiceDataCopy["increment_id"] = $invIncrementId;
                
                unset($orderInvoiceDataCopy["entity_id"]);
                
                $affInvRows = $this->_writeConnection->insert($orderInvoiceTable, $orderInvoiceDataCopy);
                if($affInvRows){
                    $this->addLog("Invoice created...");
                    $newInvoiceId = $this->_writeConnection->lastInsertId($this->_resource->getTableName("sales/order_item"));
                    //load invoice grid
                    $invoiceGridCond = "increment_id = '{$invIncrementId}'";
                    $invoiceGridData = $this->loadEntity($incrementId, $orderInvoiceGridTable, $invoiceGridCond);
                    if(count($invoiceGridData)){
                        $invoiceGridData = $invoiceGridData[0];
                        $invGridId = $invoiceGridData["entity_id"];
                        $invoiceGridDataCopy = array();
                        $invoiceGridDataCopy["grand_total"] = $updateOrder["grand_total"];
                        $invoiceGridDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
                        $this->_writeConnection->update($orderInvoiceGridTable, $invoiceGridDataCopy,"entity_id = {$invGridId}");
                        $this->addLog("Invoice grid updated.");
                    }else{
                        //load original invoice grid data
                        $invoiceGridCond = "order_increment_id = '{$origIncrementId}'";
                        $invoiceGridData = $this->loadEntity($incrementId, $orderInvoiceGridTable, $invoiceGridCond);
                        if(count($invoiceGridData)){
                            $invoiceGridDataCopy = $invoiceGridData[0];
                            $invoiceGridDataCopy["entity_id"] = $newInvoiceId;
                            $invoiceGridDataCopy["grand_total"] = $updateOrder["grand_total"];
                            $invoiceGridDataCopy["base_grand_total"] = $updateOrder["base_grand_total"];
                            $invoiceGridDataCopy["order_id"] = $orderId;
                            $invoiceGridDataCopy["increment_id"] = $invIncrementId;
                            $invoiceGridDataCopy["order_increment_id"] = $incrementId;
                            $this->_writeConnection->insert($orderInvoiceGridTable, $invoiceGridDataCopy);
                            $this->addLog("Invoice grid created.");
                        }else{
                            $this->addLog("Invoice {$incrementId} grid not created.");
                        }
                    }
                }
            }
        }
    }
    
    private function orderInvoiceItem($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder){
        $orderInvoiceTable = $this->_resource->getTableName("sales/invoice");
        $ordInvoiceCond = "order_id = {$orderId}";
        $orderItemsTable = $this->_resource->getTableName("sales/order_item");
        $orderInvoiceItemTable = $this->_resource->getTableName("sales/invoice_item");
        $orderInvoiceData = $this->loadEntity($incrementId, $orderInvoiceTable, $ordInvoiceCond);
        foreach ($orderInvoiceData as $orderInvoice){
            $intenalInvoiceId = $orderInvoice["entity_id"];
            $ordInvoiceItemCond = "parent_id = {$intenalInvoiceId}";
            $ordInvoiceItems = $this->loadEntity($incrementId, $orderInvoiceItemTable, $ordInvoiceItemCond);
            if(count($ordInvoiceItems)){
                //delete invoice items
                $this->_writeConnection->delete($orderInvoiceItemTable,$ordInvoiceItemCond);
                $this->addLog("Invoice items deleted");
            }
            //create invoice items
            //load order items
            $ordItemsData = $this->loadEntity($incrementId,$orderItemsTable , "order_id = {$orderId}");
            foreach ($ordItemsData as $orderItem){
                $invoiceItem = array(
                    "price" => $orderItem["price"],
                    "base_price" => $orderItem["base_price"],
                    "tax_amount" => $orderItem["tax_amount"],
                    "base_tax_amount" => $orderItem["base_tax_amount"],
                    "row_total" => $orderItem["row_total"],
                    "base_row_total" => $orderItem["base_row_total"],
                    "discount_amount" => $orderItem["discount_amount"],
                    "base_discount_amount" => $orderItem["base_discount_amount"],
                    "price_incl_tax" => $orderItem["price_incl_tax"],
                    "base_price_incl_tax" => $orderItem["base_price_incl_tax"],
                    "qty" => $orderItem["qty_ordered"],
                    "row_total_incl_tax" => $orderItem["row_total_incl_tax"],
                    "base_row_total_incl_tax" => $orderItem["base_row_total_incl_tax"],
                    "product_id" => $orderItem["product_id"],
                    "order_item_id" => $orderItem["item_id"],
                    "sku" => $orderItem["sku"],
                    "name" => $orderItem["name"],
                    "parent_id" => $intenalInvoiceId
                    //"cost" => $orderItem["cost"],
                    //"base_cost" => $orderItem["base_cost"]
                );
                
                $this->_writeConnection->insert($orderInvoiceItemTable, $invoiceItem);
                $this->addLog("Invoice items created");
            }
        }
    }
    
    
    private function startOrderSpliting($incrementId, $internalOrderId){
        $this->addLog("Order Id = {$incrementId} in startOrderSpliting method.");
        
        //load original order data by using internal order id
        $origOrderData = $this->loadOrder($incrementId);
        if(!count($origOrderData)){
            throw new Exception("Order Id = {$incrementId} does not exist.") ;
        }
        
        $this->processOrderItems($internalOrderId, $incrementId);
    }
    
    private function getPairedItems($orderItems){
        $parentChildeArray = array();
        foreach ($orderItems as $orderItem){
            if(!($orderItem["warehouse_id"])) continue;
            
            $nonPlSku = $orderItem["sku"];
            $nonPlSku = strtoupper(trim($nonPlSku));
            
            foreach ($orderItems as $orderItem1){
                $plParentItem = $orderItem1["pl_parent_item"];
                if(!$plParentItem) continue;
                
                $buyRequest = unserialize($orderItem1["product_options"]);
                $pLenSku = $buyRequest["info_buyRequest"]["options"]["parent_sku"];
                $pLenSku = strtoupper(trim($pLenSku));
                $nonPlItemId = $orderItem["item_id"];
                $pLItemId = $orderItem1["item_id"];
                $pLProductId = $orderItem1["product_id"];
                $nonPlProductId = $orderItem["product_id"];
                
                if($pLenSku == $nonPlSku){
                    $pLQty = $orderItem1["warehouse_qty"];
                    $nonPlQty = $orderItem["warehouse_qty"];
                    $pLenWrId = $orderItem1["warehouse_id"];
                    $nonPlWrId = $orderItem["warehouse_id"];
                    
                    $parentChildeArray[$pLItemId] = array(
                        "pl_item_id" => $pLItemId,
                        "non_pl_item_id" => $nonPlItemId,
                        "warehouse_id" => $pLenWrId,
                        "nonpl_product_id" => $nonPlProductId,
                        "plproduct_id" => $pLProductId,
                        "qty" => $pLQty
                    );
                    
                    
                }
            }
            
        }
        return $parentChildeArray;
    }
    
    private function getParentItem($items,$plItemOptions)
    {
        
        foreach ($items as $item) {
            if($item->getProductType()!="configurable" || $item->getSku()!=$plItemOptions['parent_sku'])
                continue;
                
                $options=$item->getProductOptions();
                foreach ($options['options'] as $option)
                {
                    
                    if(strtolower($option['label'])=="post length")
                    {
                        //echo  strtolower($option['value']);
                        if(strtolower($option['value'])==strtolower($plItemOptions['post_length']))
                        {
                            return $item->getId();
                        }
                    }
                }
        }
        
        return "";
    }
    
    private function processOrderItems($internalOrderId, $origIncrementId){
        $this->addLog("In processOrderItems method");
        try {
            $order = Mage::getModel('sales/order')->load($internalOrderId);
            $origIncrementId = $order->getIncrementId();
            $backorderItems = array();
            $instockorderItems = array();

            $orderItems = array();
            $orderItemData = array();

            /* convert all items into array with minimum information */
            foreach ($order->getAllItems() as $item) {

                $orderItems[$item->getId()]['item_id'] = $item->getId();
                $orderItems[$item->getId()]['parent_item_id'] = $item->getParentItemId();
                $orderItems[$item->getId()]['quote_item_id'] = $item->getQuoteItemId();
                $orderItems[$item->getId()]['product_type'] = $item->getProductType();
                $orderItems[$item->getId()]['qty_backordered'] = $item->getQtyBackordered();
                $orderItems[$item->getId()]['pl_parent_item'] = $item->getPlParentItem();

                if ($item->getPlParentItem()) {
                    $plItemOptions = $item->getProductOptions();
                    $orderItems[$item->getId()]['pl_parent_item'] = $this->getParentItem($order->getAllItems(), $plItemOptions['info_buyRequest']['options']);
                }
                
            }
            
            
            $salesOrderTable = $this->_resource->getTableName("sales/order");
            $salesOrderItemTable = $this->_resource->getTableName("sales/order_item");
            $salesOrderAddressTable = $this->_resource->getTableName("sales/order_address");
            
            $orderItemCond = "order_id = ".$internalOrderId;
            $orderItemsData = $this->loadEntity($origIncrementId, $salesOrderItemTable, $orderItemCond);
            foreach ($orderItemsData as $orderItem){
                $orderItemData[$orderItem['item_id']] = $orderItem;
            }
            
            /* get only keys in new array */
            $orderItemsKeys = array_keys($orderItems);
            
            foreach ($orderItems as $item) {
                /* check qty_backorder for backordered item */
                if ($item['qty_backordered'] > 0 && ! in_array($item['item_id'], $backorderItems)) {

                    /* if item is simple product */
                    if ($item['parent_item_id'] != null) {
                        /* add parent(configurable) item id into $backorderItems array */
                        array_push($backorderItems, $item['parent_item_id']);

                        /* add current(simple) item id into $backorderItems array */
                        array_push($backorderItems, $item['item_id']);

                        /* search for postlength product item */
                        $postLengthProductIndex = array_search($item['parent_item_id'], array_column($orderItems, 'pl_parent_item'));

                        /* add postlength product item id into $backorderItems array */
                        if ($postLengthProductIndex)
                            array_push($backorderItems, $orderItemsKeys[$postLengthProductIndex]);
                    } /* if item is post length product */
                    else if ($item['pl_parent_item'] != null) {

                        /* add parent(configurable) item id into $backorderItems array */
                        array_push($backorderItems, $item['pl_parent_item']);

                        /* search for simple product item from parent(configurable) item */
                        $childProductIndex = array_search($item['pl_parent_item'], array_column($orderItems, 'parent_item_id'));

                        /* add simple product item id into $backorderItems array */
                        if ($childProductIndex)
                            array_push($backorderItems, $orderItemsKeys[$childProductIndex]);

                        /* add current(post length) item id into $backorderItems array */
                        array_push($backorderItems, $item['item_id']);
                    }
                }
            }
            
            /*get all backordered items*/
            $this->addLog("Out of stock items");
            $this->addLog($backorderItems);
            
            /*get all in stock items*/
            $instockorderItems = array_diff($orderItemsKeys, $backorderItems);
            
            $this->addLog("instock items");
            $this->addLog($instockorderItems);
            
            
            if(count($instockorderItems) > 0 && count($backorderItems) > 0){
                $this->addLog("Order spliting required.");
                
                $instockOrderItemsData = array();
                $backOrderItemsData = array();
                
                $instockOrderItemParentChilds = array();
                $backOrderItemParentChilds = array();
                
                //backorder items data
                foreach ($backorderItems as $bItemId){
                    $bItem = $orderItemData[$bItemId];
                    $backOrderItemsData[$bItem['item_id']] = $bItem;
                    if(isset($bItem['parent_item_id']) && !empty($bItem['parent_item_id'])){
                        $backOrderItemParentChilds[$bItem['parent_item_id']] = $bItem['item_id'];
                    }
                }
                
                //instock order items data
                foreach ($instockorderItems as $iItemId){
                    $iItem = $orderItemData[$iItemId];
                    $instockOrderItemsData[$iItem['item_id']] = $iItem;
                    if(isset($iItem['parent_item_id']) && !empty($iItem['parent_item_id'])){
                        $instockOrderItemParentChilds[$iItem['parent_item_id']] = $iItem['item_id'];
                    }
                }
                
                
                //create back order
                $newInternalOrderId = 0;
                $code = "B";
                $bIncrementId = $this->prepareNewIncrementId($origIncrementId, $code);
                $bCond = "increment_id = '{$bIncrementId}'";
                $bOrderDataArr = $this->loadEntity($bIncrementId, $salesOrderTable, $bCond);
                if(!count($bOrderDataArr)){
                    $origCond = "increment_id = '{$origIncrementId}'";
                    $origOrderDataArr = $this->loadEntity($origIncrementId, $salesOrderTable, $origCond);
                    foreach ($origOrderDataArr as $origOrderData){
                        $origOrderDataCopy = $origOrderData;
                        //create back order
                        unset($origOrderDataCopy["entity_id"]);
                        $origOrderDataCopy["increment_id"] = $bIncrementId;
                        $affectedRows = $this->_writeConnection->insert($salesOrderTable, $origOrderDataCopy);
                        if(!$affectedRows){
                            throw new Exception("Order Id = {$bIncrementId} spliting order not created.") ;
                        }
                        $newInternalOrderId = $this->_writeConnection->lastInsertId($salesOrderTable);
                    }
                }else{
                    //existing new order
                    foreach ($bOrderDataArr as $bOrderData){
                        $newInternalOrderId = $bOrderData["entity_id"];
                    }
                }
                
                //order address table
                $newOrdAddrCond = "parent_id = ".$newInternalOrderId;
                $newOrderAddressData = $this->loadEntity($bIncrementId, $salesOrderAddressTable, $newOrdAddrCond);
                if(!count($newOrderAddressData)){
                    //load orig order address
                    $origOrdAddrCond = "parent_id = ".$internalOrderId;
                    $origOrderAddrResult = $this->loadEntity($origIncrementId, $salesOrderAddressTable, $origOrdAddrCond);
                    if(!count($origOrderAddrResult)){
                        throw new Exception("Order Id = {$origIncrementId} address entries not found.") ;
                    }
                    
                    $updateNewOrdAddr = array();
                    foreach ($origOrderAddrResult as $origOrdAddr){
                        $addrType = $origOrdAddr["address_type"];
                        $origOrdAddrCopy = $origOrdAddr;
                        unset($origOrdAddrCopy["entity_id"]);
                        $origOrdAddrCopy["parent_id"] = $newInternalOrderId;
                        $addrAffectedRows = $this->_writeConnection->insert($salesOrderAddressTable, $origOrdAddrCopy);
                        if($addrAffectedRows){
                            $newOrdAddrId = $this->_writeConnection->lastInsertId($salesOrderAddressTable);
                            if($addrType == self::BILLING_ADDR_TYPE){
                                $updateNewOrdAddr["billing_address_id"] = $newOrdAddrId;
                            }else{
                                $updateNewOrdAddr["shipping_address_id"] = $newOrdAddrId;
                            }
                        }
                    }
                    
                    $this->addLog("update order 1");
                    //update order table billing and shipping address id
                    $this->_writeConnection->update($salesOrderTable, $updateNewOrdAddr, "entity_id = {$newInternalOrderId}");
                }
                
                //create back orders items
                $this->addLog("back order start");
                $this->updateOrderItems($newInternalOrderId, $origIncrementId, $internalOrderId, $bIncrementId, $backOrderItemsData);
                $this->addLog("back order finish");
                
                //create instock orders items
                //delete back order items from original order
                $bOrderItemIds = implode(",", $backorderItems);
                $origOrdItemCond = "order_id = {$internalOrderId} AND item_id IN({$bOrderItemIds})";
                $this->_writeConnection->delete($salesOrderItemTable,$origOrdItemCond);
                $this->addLog("deleted items = ".$bOrderItemIds);
                $this->addLog("instock order start");
                $this->updateOrderItems($internalOrderId, $origIncrementId, $internalOrderId, $origIncrementId, $instockOrderItemsData);
                $this->addLog("instock order end");
                
                //load backorder & instock order again
                $instockOrder = Mage::getModel("sales/order")->load($internalOrderId);
                $instockOrder->save();
                Mage::dispatchEvent('sales_order_save_after', array('order'=>$instockOrder));
                $this->addLog("order {$internalOrderId} saved.");
                $backOrder = Mage::getModel("sales/order")->load($newInternalOrderId);
                $backOrder->save();
                Mage::dispatchEvent('sales_order_save_after', array('order'=>$backOrder));
                $this->addLog("order {$backOrder->getId()} saved.");
                
            }else {
                $this->addLog("No order spliting required.");
            }
        } catch (Exception $e) {
            $this->addLog("Exc - in processOrderItems. Message: {$e->getMessage()}");
            throw $e;
        }
    }
    
    
    
    private function updateOrderItems($orderId, $origIncrementId, $internalOrderId, $incrementId, $specificOrderItems){
        try{
            $grandTotal = $baseGrandTotal = 0.0;
            $discAmount = $baseDiscAmount = 0.0;
            $discInvoiced = $baseDiscInvoiced = 0.0;
            $shippingAmt = $baseShippingAmt = 0.0;
            $shippingInvoiced = $baseShippingInvoiced = 0.0;
            $shippingTaxAmt = $baseShippingTaxAmt = 0.0;
            $subtotal = $baseSubtotal = 0.0;
            $subtotalInvoiced = $baseSubtotalInvoiced = 0.0;
            $taxAmt = $baseTaxAmt = 0.0;
            $taxInvoiced = $baseTaxInvoiced = 0.0;
            $totalInvoiced = $baseTotalInvoiced = 0.0;
            $totalPaid = $baseTotalPaid = 0.0;
            $shippingDiscountAmt = $baseShippingDiscountAmt = 0.0;
            $subtotalInclTax = $baseSubtotalInclTax = 0.0;
            $totalDue = $baseTotalDue = 0.0;
            $shippingInclTax = $baseShippingInclTax = 0.0;
            
            $orderItemTable = $this->_resource->getTableName("sales/order_item");
            
            $totalQty = 0;
            
            $isOrderInvoiced = false;
            
            $newParentItems = array();
            foreach ($specificOrderItems as $orderItemId => $orderItem){
                //check order item id present or not
                $sku = $orderItem["sku"];
                $typeId = $orderItem["product_type"];
                
                if($orderItem["qty_invoiced"]){
                    $isOrderInvoiced = true;
                }
                
                $qtys = $orderItem["qty_ordered"];
                $oldQtys = $orderItem["qty_ordered"];
                if($qtys != $oldQtys){
                    //price & order item total calculation
                    $price = $orderItem["price"];
                    $basePrice = $orderItem["base_price"];
                    
                    // discount calculation
                    //$discountPer = $orderItem["discount_percent"];
                    $itmDiscountAmt = $orderItem["discount_amount"];
                    $baseItmDiscountAmt = $orderItem["base_discount_amount"];
                    
                    $singleProductDisc = 0.0;
                    $singleProductBaseDisc = 0.0;
                    if($itmDiscountAmt > 0 && $baseItmDiscountAmt > 0){
                        $singleProductDisc = ($itmDiscountAmt / $oldQtys);
                        $singleProductBaseDisc = ($baseItmDiscountAmt / $oldQtys);
                        $itmDiscountAmt = ($singleProductDisc * $qtys);
                        $baseItmDiscountAmt = ($singleProductBaseDisc * $qtys);
                        $orderItem["discount_amount"] = $itmDiscountAmt;
                        $orderItem["base_discount_amount"] = $baseItmDiscountAmt;
                    }
                    
                    // tax calculation
                    //$taxPer = $orderItem["tax_percent"];
                    $itmTaxAmt = $orderItem["tax_amount"];
                    $baseItmTaxAmt = $orderItem["base_tax_amount"];
                    
                    $singleProductTax = 0.0;
                    $singleProductBaseTax = 0.0;
                    if($itmTaxAmt > 0 && $baseItmTaxAmt > 0){
                        $singleProductTax = $orderItem["price_incl_tax"] - $price; //($taxAmt / $oldQtys);
                        $singleProductBaseTax = $orderItem["base_price_incl_tax"] - $basePrice; //($baseTaxAmt / $oldQtys);
                        $itmTaxAmt = ($singleProductTax * $qtys);
                        $baseItmTaxAmt = ($singleProductBaseTax * $qtys);
                        $orderItem["tax_amount"] = $itmTaxAmt;
                        $orderItem["base_tax_amount"] = $baseItmTaxAmt;
                    }
                    
                    $rowTotal = ($price * $qtys) - $itmDiscountAmt;
                    $baseRowTotal = ($basePrice * $qtys) - $baseItmDiscountAmt;
                    $rowTotalInclTax = $rowTotal + $itmTaxAmt;
                    $baseRowTotalInclTax = $baseRowTotal + $baseItmTaxAmt;
                    
                    $qtyInvoiced = $orderItem["qty_invoiced"];
                    if($qtyInvoiced){
                        $orderItem["qty_invoiced"] = $qtys;
                        $orderItem["tax_invoiced"] = $itmTaxAmt;
                        $orderItem["base_tax_invoiced"] = $baseItmTaxAmt;
                        $orderItem["discount_invoiced"] = $itmDiscountAmt;
                        $orderItem["base_discount_invoiced"] = $baseItmDiscountAmt;
                        $orderItem["row_invoiced"] = $rowTotal;
                        $orderItem["base_row_invoiced"] = $baseRowTotal;
                    }
                    
                    $orderItem["row_total"] = $rowTotal;
                    $orderItem["base_row_total"] = $baseRowTotal;
                    $orderItem["row_total_incl_tax"] = $rowTotalInclTax;
                    $orderItem["base_row_total_incl_tax"] = $baseRowTotalInclTax;
                    
                }else{
                    $rowTotal = ($orderItem["row_total"]) ? $orderItem["row_total"] : 0.0;
                    $baseRowTotal = ($orderItem["base_row_total"]) ? $orderItem["base_row_total"] : 0.0;
                    $rowTotalInclTax = ($orderItem["row_total_incl_tax"]) ? $orderItem["row_total_incl_tax"] : 0.0;
                    $baseRowTotalInclTax = ($orderItem["base_row_total_incl_tax"]) ? $orderItem["base_row_total_incl_tax"] : 0.0;
                    $itmTaxAmt = ($orderItem["tax_amount"]) ? $orderItem["tax_amount"] : 0.0;
                    $baseItmTaxAmt = ($orderItem["base_tax_amount"]) ? $orderItem["base_tax_amount"] : 0.0;
                    $itmDiscountAmt = ($orderItem["discount_amount"]) ? $orderItem["discount_amount"] : 0.0;
                    $baseItmDiscountAmt = ($orderItem["base_discount_amount"]) ? $orderItem["base_discount_amount"] : 0.0;
                }
                
                //order item total calculation
                $subtotal += $rowTotal;
                $baseSubtotal += $baseRowTotal;
                $subtotalInclTax += $rowTotalInclTax;
                $baseSubtotalInclTax += $baseRowTotalInclTax;
                $taxAmt += $itmTaxAmt;
                $baseTaxAmt += $baseItmTaxAmt;
                $discAmount += $itmDiscountAmt;
                $baseDiscAmount += $baseItmDiscountAmt;
                
                $orderItemResult = $this->_readConnection->select()
                ->from($orderItemTable)
                ->where("sku = '{$sku}' AND product_type = '{$typeId}' AND order_id = {$orderId} AND quote_item_id = {$orderItem["quote_item_id"]}");
                $selectOrderItemData = $this->_readConnection->fetchAll($orderItemResult);
                
                
                if(count($selectOrderItemData) > 0){
                    $copyOrderItem = $orderItem;
                    $copyOrderItem["qty_ordered"] = $qtys;
                    $copyOrderItem["qty_invoiced"] = $qtys;
                    if($copyOrderItem["qty_backordered"]){
                        $copyOrderItem["qty_backordered"] = $qtys;
                    }
                    
                    if($orderId == $internalOrderId){
                        $copyOrderItem["qty_backordered"] = 0;
                        $copyOrderItem["backorder_time"] = null;
                    }
                    
                    if(isset($copyOrderItem["parent_item_id"]) && !empty($copyOrderItem["parent_item_id"])){
                        $qtys = 0;
                    }
                    $totalQty += $qtys;
                    
                    $this->_writeConnection->update($orderItemTable, $copyOrderItem, "item_id = {$orderItemId}");
                    $this->addLog("Order Item updated");
                    
                    
                }else{
                    $copyOrderItem = $orderItem;
                    
                    $oldItemId = $copyOrderItem["item_id"];
                    
                    if(isset($copyOrderItem["parent_item_id"]) && !empty($copyOrderItem["parent_item_id"])){
                        /* if(isset($newParentItems[$sku]) && !empty($newParentItems[$sku])){
                            $copyOrderItem["parent_item_id"] = $newParentItems[$sku];
                        } */
                        
                        if(isset($newParentItems[$oldItemId]) && !empty($newParentItems[$oldItemId])){
                            $copyOrderItem["parent_item_id"] = $newParentItems[$oldItemId];
                        }
                    }
                    
                    $copyOrderItem["qty_ordered"] = $qtys;
                    $copyOrderItem["qty_invoiced"] = $qtys;
                    if($copyOrderItem["qty_backordered"]){
                        $copyOrderItem["qty_backordered"] = $qtys;
                    }
                    
                    if($orderId == $internalOrderId){
                        $copyOrderItem["qty_backordered"] = 0;
                        $copyOrderItem["backorder_time"] = null;
                    }
                    
                    $copyOrderItem["order_id"] = $orderId;
                    
                    unset($copyOrderItem["item_id"]);
                    
                    if(isset($copyOrderItem["parent_item_id"]) && !empty($copyOrderItem["parent_item_id"])){
                        $qtys = 0;
                    }
                    $totalQty += $qtys;
                    
                    $insertedItemCount = $this->_writeConnection->insert($orderItemTable, $copyOrderItem);
                    if($insertedItemCount){
                        $newOrdeItemId = $this->_writeConnection->lastInsertId($orderItemTable);
                        if(empty($copyOrderItem["parent_item_id"])){
                            //$newParentItems[$sku] = $newOrdeItemId;
                            $newParentItems[$oldItemId] = $newOrdeItemId;
                        }
                    }
                }
            }
            
            //update order totals
            
            if($orderId == $internalOrderId){
                $orderData = $this->loadOrder($origIncrementId);
                if(count($orderData)){
                    $shippingAmt = $orderData["shipping_amount"];
                    $baseShippingAmt = $orderData["base_shipping_amount"];
                    $shippingInclTax = $orderData["shipping_incl_tax"];
                    $baseShippingInclTax = $orderData["base_shipping_incl_tax"];
                    $shippingTaxAmt = $orderData["shipping_tax_amount"];
                    $baseShippingTaxAmt = $orderData["base_shipping_tax_amount"];
                    $shippingDiscountAmt = isset($orderData["shipping_discount_amount"]) ? $orderData["shipping_discount_amount"] : 0.0;
                    $baseShippingDiscountAmt = isset($orderData["base_shipping_discount_amount"]) ? $orderData["base_shipping_discount_amount"] : 0.0;
                    $discAmount += $shippingDiscountAmt;
                    $baseDiscAmount += $baseShippingDiscountAmt;
                }
            }
            
            $grandTotal = $subtotalInclTax + $shippingInclTax  - $discAmount;
            $baseGrandTotal = $baseSubtotalInclTax + $baseShippingInclTax - $baseDiscAmount;
            
            $discAmount *= (-1);
            $baseDiscAmount *= (-1);
            
            if($isOrderInvoiced){
                $discInvoiced = $discAmount;
                $baseDiscInvoiced = $baseDiscAmount;
                $shippingInvoiced = $shippingAmt;
                $baseShippingInvoiced = $baseShippingAmt ;
                $subtotalInvoiced = $subtotal;
                $baseSubtotalInvoiced = $baseSubtotal;
                $taxInvoiced = $taxAmt;
                $baseTaxInvoiced = $baseTaxAmt;
                $totalInvoiced = $grandTotal;
                $baseTotalInvoiced = $baseGrandTotal;
                $totalPaid = $grandTotal;
                $baseTotalPaid = $baseGrandTotal;
                $totalDue =  $baseTotalDue = 0.0;
            }else {
                $totalDue =  $grandTotal;
                $baseTotalDue = $baseGrandTotal;
                $totalPaid = $baseTotalPaid = 0.0;
            }
            
            $updateOrder = array(
                "subtotal" => $subtotal,
                "base_subtotal" => $baseSubtotal,
                "subtotal_incl_tax" => $subtotalInclTax,
                "base_subtotal_incl_tax" => $baseSubtotalInclTax,
                "grand_total" => $grandTotal,
                "base_grand_total" => $baseGrandTotal,
                "shipping_amount" => $shippingAmt,
                "base_shipping_amount" => $baseShippingAmt,
                "shipping_tax_amount" => $shippingTaxAmt,
                "base_shipping_tax_amount" => $baseShippingTaxAmt,
                "shipping_incl_tax" => $shippingInclTax,
                "base_shipping_incl_tax" => $baseShippingInclTax,
                "discount_amount" => $discAmount,
                "base_discount_amount" => $baseDiscAmount,
                "shipping_discount_amount" => $shippingDiscountAmt,
                "base_shipping_discount_amount" => $shippingDiscountAmt,
                "tax_amount" => $taxAmt,
                "base_tax_amount" => $baseTaxAmt,
                "total_paid" => $totalPaid,
                "base_total_paid" => $baseTotalPaid,
                "total_due" => $totalDue,
                "base_total_due" => $baseTotalDue,
                
                "discount_invoiced" => $discInvoiced,
                "base_discount_invoiced" => $baseDiscInvoiced,
                "shipping_invoiced" => $shippingInvoiced,
                "base_shipping_invoiced" => $baseShippingInvoiced,
                "subtotal_invoiced" => $subtotalInvoiced,
                "base_subtotal_invoiced" => $baseSubtotalInvoiced,
                "tax_invoiced" => $taxInvoiced,
                "base_tax_invoiced" => $baseTaxInvoiced,
                "total_invoiced" => $totalInvoiced,
                "base_total_invoiced" => $baseTotalInvoiced,
                
                "total_qty_ordered" => $totalQty,
            );
            
            //update order table
            $this->_writeConnection->update($this->_resource->getTableName("sales/order"), $updateOrder, "entity_id = {$orderId}");
            
            //order grid
            $this->orderGrid($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder);
            
            //create or update order payment transaction
            $this->orderPayment($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder);
            
            //create order history
            $this->orderStatusHistory($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder);
            
            if($isOrderInvoiced){
                //create or update order invoice
                $this->orderInvoice($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder);
                
                //create invoice item
                $this->orderInvoiceItem($orderId, $origIncrementId, $internalOrderId, $incrementId, $updateOrder);
            }
        }catch (Exception $e){
            $this->addLog("Exc - in updateOrderItems() method. Message : {$e->getMessage()}");
        }
    }
    
    private function backup($orderId = 0, $incrementId = 0, $tableName, $bkTableName, $cond){
        //$cond = $condColumn . " = " . $orderId;
        $backupData = $this->loadEntity($incrementId, $bkTableName, $cond);
        if(!count($backupData)){
            $header = array_keys($this->_readConnection->describeTable($bkTableName));
            $headers = implode(",", $header);
            $query = "INSERT INTO {$bkTableName}({$headers}) SELECT {$headers} FROM {$tableName} WHERE {$cond} ";
            $this->_writeConnection->query($query);
        }
    }
    
    private function backupOrder($orderId = 0, $incrementId = 0){
        $this->addLog("Backup order id = {$incrementId} start");
        try {
            //Table names
            $orderTable = $this->_resource->getTableName("sales/order");
            $orderItemTable = $this->_resource->getTableName("sales/order_item");
            $orderPaymentTable = $this->_resource->getTableName("sales/order_payment");
            $invoiceTable = $this->_resource->getTableName("sales/invoice");
            $invoiceItemTable = $this->_resource->getTableName("sales/invoice_item");
            $bkOrderTable = $this->_resource->getTableName("before_split_sales_flat_order");
            $bkOrderItemTable = $this->_resource->getTableName("before_split_sales_flat_order_item");
            $bkOrderPaymentTable = $this->_resource->getTableName("before_split_sales_flat_order_payment");
            $bkInvoiceTable = $this->_resource->getTableName("before_split_sales_flat_invoice");
            $bkInvoiceItemtable = $this->_resource->getTableName("before_split_sales_flat_invoice_item");
            
            $this->backup($orderId, $incrementId, $orderTable, $bkOrderTable, "entity_id = {$orderId}");
            $this->backup($orderId, $incrementId, $orderItemTable, $bkOrderItemTable, "order_id = {$orderId}");
            $this->backup($orderId, $incrementId, $orderPaymentTable, $bkOrderPaymentTable, "parent_id = {$orderId}");
            $this->backup($orderId, $incrementId, $invoiceTable, $bkInvoiceTable, "order_id = {$orderId}");
            
            //load invoice data
            $invoiceData = $this->loadEntity($incrementId, $invoiceTable, "order_id = {$orderId}");
            foreach ($invoiceData as $invoice){
                $invoiceId = $invoice["entity_id"];
                $this->backup($invoiceId, $incrementId, $invoiceItemTable, $bkInvoiceItemtable, "parent_id = {$invoiceId}");
            }
            
            
        }catch (Exception $e){
            $this->addLog("Exception - in backupOrder() method. Msg : {$e->getMessage()}");
        }
        $this->addLog("Backup order id = {$incrementId} end");
    }
    
    public function spliteOrders($internalOrderId = 0, $incrementId = 0){
        $this->addLog("--- In spliteOrders method ---");
        $this->addLog("Order Id = {$incrementId}");
        
        if(!$internalOrderId){
            $this->addLog("This order id = {$incrementId} does not exist.");
            return ;
        }
        
        try {
            
            //$this->_writeConnection->beginTransaction();
            $this->backupOrder($internalOrderId, $incrementId);
            
            $this->addLog("Order Id = {$incrementId} spliting process start");
            
            $this->startOrderSpliting($incrementId, $internalOrderId);
            //$this->_writeConnection->commit();
        } catch (Exception $e) {
            //$this->_writeConnection->rollBack();
            $this->addLog("Exc - in process({$incrementId}). Message: {$e->getMessage()}");
        }
        $this->addLog("Order Id = {$incrementId} spliting process end");
    }
    
    public function orderSaveAfterForPaypal($observer)
    {
        $this->addLog("orderSaveAfterForPaypal method");
        try {
            /* $invoice = $observer->getEvent()->getDataObject();
             $order = $invoice->getOrder(); */
            //$order = $observer->getEvent()->getOrder();
            
            $order = $observer->getEvent()->getOrder();
            
            
                
                $isSent = $order->getEmailSent();
                $storeId = $order->getStoreId();
                $isSendOrderEmail = Mage::helper("allure_orders")
                ->canSendConfirmationEmail($storeId);
                
                $customerGroupId = $order->getCustomerGroupId();
                
                $paymentMethod = $order->getPayment()->getMethod();
                Mage::log("order_id = {$order->getId()} payment method = {$paymentMethod}",Zend_Log::DEBUG, 'split_orders.log',true);
                
                if($isSendOrderEmail && !$isSent && $paymentMethod != "paypal_express"){
                    if($customerGroupId == self::GUEST){
                        $order->queueNewOrderEmail();
                    }elseif ($customerGroupId == self::GENERAL){
                        $orderArray = array($order->getId() => $order);
                        $order->queueMultiAddressNewOrderEmail($orderArray);
                    }else {
                        $order->queueNewOrderEmail();
                    }
                }
                
                if($paymentMethod != "paypal_express"){
                    return ;
                }
                
                $status = $order->getStatus();
                $this->addLog("order id = {$order->getIncrementId()}");
                $this->addLog("order id = {$order->getIncrementId()} status = {$status}");
                $this->addLog("order id = {$order->getIncrementId()} has invoice = {$order->hasInvoices()}");
                if ($order->hasInvoices() && $status == "processing") {
                    $this->spliteOrders($order->getId(), $order->getIncrementId());
                }
        } catch (Exception $e) {
            $this->addLog("Exc - in orderSaveAfterForPaypal . Message: {$e->getMessage()}");
        }
        
    }
    
    public function orderSaveAfter($observer)
    {
        $this->addLog("orderSaveAfter method");
        try {
            /* $invoice = $observer->getEvent()->getDataObject();
            $order = $invoice->getOrder(); */
            //$order = $observer->getEvent()->getOrder();
            
            $orders = $observer->getEvent()->getOrder();
            if ($orders) { //if (!is_array($orders)) {
                $orders = array($orders);
            }else{ //handle multiaddress order
                $orders = $observer->getEvent()->getOrders();
            }
            
            foreach ($orders as $order){
                
                $isSent = $order->getEmailSent();
                $storeId = $order->getStoreId();
                $isSendOrderEmail = Mage::helper("allure_orders")
                ->canSendConfirmationEmail($storeId);
                
                $customerGroupId = $order->getCustomerGroupId();
                
                $paymentMethod = $order->getPayment()->getMethod();
                Mage::log("order_id = {$order->getId()} payment method = {$paymentMethod}",Zend_Log::DEBUG, 'split_orders.log',true);
                
                if($isSendOrderEmail && !$isSent && $paymentMethod != "paypal_express"){
                    if($customerGroupId == self::GUEST){
                        $order->queueNewOrderEmail();
                    }elseif ($customerGroupId == self::GENERAL){
                        $orderArray = array($order->getId() => $order);
                        $order->queueMultiAddressNewOrderEmail($orderArray);
                    }else {
                        $order->queueNewOrderEmail();
                    }
                }
                
                
                $status = $order->getStatus();
                $this->addLog("order id = {$order->getIncrementId()}");
                $this->addLog("order id = {$order->getIncrementId()} status = {$status}");
                $this->addLog("order id = {$order->getIncrementId()} has invoice = {$order->hasInvoices()}");
                if ($order->hasInvoices() && $status == "processing") {
                    $this->spliteOrders($order->getId(), $order->getIncrementId());
                }
            }
        } catch (Exception $e) {
            $this->addLog("Exc - in orderSaveAfter order id = {$order->getId()}. Message: {$e->getMessage()}");
        }
        
    }
    
    public function orderSplitProcess($ordersIds){
        $this->addLog("in orderSplitProcess method");
        try {
            $orderObj = null;
            $orderArray = array();
            if(count($ordersIds) == 1){
                foreach ($ordersIds as $orderId){
                    $orderObj = Mage::getModel("sales/order")->load($orderId);
                    $orderArray[$orderObj->getId()] = $orderObj;
                }
            }elseif (count($ordersIds) > 1){
                $cnt = 1;
                foreach ($ordersIds as $orderId){
                    $order = Mage::getModel("sales/order")->load($orderId);
                    if($cnt == 1){
                        $orderObj = $order;
                    }
                    
                    if($order->hasInvoices()){
                        $orderArray[$order->getId()] = $order;
                    }
                    $cnt++;
                }
            }
            
            if($orderObj && $orderObj->hasInvoices()){
                $isSent = $orderObj->getEmailSent();
                $storeId = $orderObj->getStoreId();
                $isSendOrderEmail = Mage::helper("allure_orders")
                ->canSendConfirmationEmail($storeId);
                
                $customerGroupId = $orderObj->getCustomerGroupId();
                
                $paymentMethod = $orderObj->getPayment()->getMethod();
                Mage::log("order_id = {$orderObj->getId()} payment method = {$paymentMethod}",Zend_Log::DEBUG, 'split_orders.log',true);
                
                if($isSendOrderEmail && !$isSent && $paymentMethod != "paypal_express"){
                    if($customerGroupId == self::GUEST){
                        $orderObj->queueNewOrderEmail();
                    }elseif ($customerGroupId == self::GENERAL){
                        if(count($orderArray) == 1){
                            $orderObj->queueNewOrderEmail();
                        }else{
                            $orderObj->queueMultiAddressNewOrderEmail($orderArray);
                        }
                    }else {
                        $orderObj->queueNewOrderEmail();
                    }
                }
            }
            
            foreach ($orderArray as $order){
                //$order = Mage::getModel("sales/order")->load($orderId);
                $status = $order->getStatus();
                $this->addLog("order id = {$order->getIncrementId()}");
                $this->addLog("order id = {$order->getIncrementId()} status = {$status}");
                $this->addLog("order id = {$order->getIncrementId()} has invoice = {$order->hasInvoices()}");
                if ($order->hasInvoices()) {
                    $this->spliteOrders($order->getId(), $order->getIncrementId());
                }
            }
        } catch (Exception $e) {
            $this->addLog("Exc - in orderSplitProcess. Message: {$e->getMessage()}");
        }
    }
    
    
    public function orderSplit($observer)
    {
        $this->addLog("in orderSplit method");
        try {
            $ordersIds = $observer->getEvent()->getOrderIds();
            $this->orderSplitProcess($ordersIds);
        } catch (Exception $e) {
            $this->addLog("Exc - in orderSplit . Message: {$e->getMessage()}");
        }
        
    }
}