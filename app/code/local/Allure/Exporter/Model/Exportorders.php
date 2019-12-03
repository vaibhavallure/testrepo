<?php

class Allure_Exporter_Model_Exportorders extends Allure_Exporter_Model_Exporter
{

    const ENCLOSURE = '"';
    const DELIMITER = ',';
    public $io;
    
    public $connection, $resource;
    
    
    const SALES_FLAT_ORDER                  = "sales_flat_order";
    const SALES_FLAT_ORDER_GRID             = "sales_flat_order_grid";
    const SALES_FLAT_ORDER_ADDRESS          = "sales_flat_order_address";
    const SALES_FLAT_ORDER_ITEM             = "sales_flat_order_item";
    const SALES_FLAT_ORDER_PAYMENT          = "sales_flat_order_payment";
    const SALES_PAYMENT_TRANSACTION         = "sales_payment_transaction";
    const SALES_FLAT_ORDER_STATUS_HISTORY   = "sales_flat_order_status_history";
    
    const SALES_FLAT_INVOICE                = "sales_flat_invoice";
    const SALES_FLAT_INVOICE_GRID           = "sales_flat_invoice_grid";
    const SALES_FLAT_INVOICE_ITEM           = "sales_flat_invoice_item";
    const SALES_FLAT_INVOICE_COMMENT        = "sales_flat_invoice_comment";
    
    const SALES_FLAT_SHIPMENT               = "sales_flat_shipment";
    const SALES_FLAT_SHIPMENT_GRID          = "sales_flat_shipment_grid";
    const SALES_FLAT_SHIPMENT_ITEM          = "sales_flat_shipment_item";
    const SALES_FLAT_SHIPMENT_COMMENT       = "sales_flat_shipment_comment";
    const SALES_FLAT_SHIPMENT_TRACK         = "sales_flat_shipment_track";
    
    const SALES_FLAT_CREDITMEMO             = "sales_flat_creditmemo";
    const SALES_FLAT_CREDITMEMO_GRID        = "sales_flat_creditmemo_grid";
    const SALES_FLAT_CREDITMEMO_ITEM        = "sales_flat_creditmemo_item";
    const SALES_FLAT_CREDITMEMO_COMMENT     = "sales_flat_creditmemo_comment";
    
    const ORDER                 = "order";
    const ORDER_ITEM            = "order_item";
    const ORDER_ADDRESS         = "order_address";
    const ORDER_PAYMENT         = "order_payment";
    const ORDER_GRID            = "order_grid";
    const ORDER_TRANSACTION     = "order_transaction";
    const ORDER_STATUS_HISTORY  = "order_status_history";
    const INVOICE               = "invoice";
    const INVOICE_GRID          = "invoice_grid";
    const INVOICE_ITEM          = "invoice_item";
    const INVOICE_COMMENT       = "invoice_comment";
    const SHIPMENT              = "shipment";
    const SHIPMENT_GRID         = "shipment_grid";
    const SHIPMENT_ITEM         = "shipment_item";
    const SHIPMENT_COMMENT      = "shipment_comment";
    const SHIPMENT_TRACK        = "shipment_track";
    const CREDITMEMO            = "creditmemo";
    const CREDITMEMO_GRID       = "creditmemo_grid";
    const CREDITMEMO_ITEM       = "creditmemo_item";
    const CREDITMEMO_COMMENT    = "creditmemo_comment";
    
    const CUSTOMER_ENTITY           = "customer_entity";
    const CUSTOMER_ENTITY_DATETIME  = "customer_entity_datetime";
    const CUSTOMER_ENTITY_DECIMAL   = "customer_entity_decimal";
    const CUSTOMER_ENTITY_INT       = "customer_entity_int";
    const CUSTOMER_ENTITY_TEXT      = "customer_entity_text";
    const CUSTOMER_ENTITY_VARCHAR   = "customer_entity_varchar";
    
    const CUSTOMER_ADDRESS_ENTITY           = "customer_address_entity";
    const CUSTOMER_ADDRESS_ENTITY_DATETIME  = "customer_address_entity_datetime";
    const CUSTOMER_ADDRESS_ENTITY_DECIMAL   = "customer_address_entity_decimal";
    const CUSTOMER_ADDRESS_ENTITY_INT       = "customer_address_entity_int";
    const CUSTOMER_ADDRESS_ENTITY_TEXT      = "customer_address_entity_text";
    const CUSTOMER_ADDRESS_ENTITY_VARCHAR   = "customer_address_entity_varchar";
    
    protected  $tableStructureArray = array(
        self::ORDER                 => self::SALES_FLAT_ORDER,
        self::ORDER_ITEM            => self::SALES_FLAT_ORDER_ITEM,
        self::ORDER_ADDRESS         => self::SALES_FLAT_ORDER_ADDRESS,
        self::ORDER_PAYMENT         => self::SALES_FLAT_ORDER_PAYMENT,
        self::ORDER_GRID            => self::SALES_FLAT_ORDER_GRID,
        self::ORDER_TRANSACTION     => self::SALES_PAYMENT_TRANSACTION,
        self::ORDER_STATUS_HISTORY  => self::SALES_FLAT_ORDER_STATUS_HISTORY,
        self::INVOICE               => self::SALES_FLAT_INVOICE,
        self::INVOICE_GRID          => self::SALES_FLAT_INVOICE_GRID,
        self::INVOICE_ITEM          => self::SALES_FLAT_INVOICE_ITEM,
        self::INVOICE_COMMENT       => self::SALES_FLAT_INVOICE_COMMENT,
        self::SHIPMENT              => self::SALES_FLAT_SHIPMENT,
        self::SHIPMENT_GRID         => self::SALES_FLAT_SHIPMENT_GRID,
        self::SHIPMENT_ITEM         => self::SALES_FLAT_SHIPMENT_ITEM,
        self::SHIPMENT_COMMENT      => self::SALES_FLAT_SHIPMENT_COMMENT,
        self::SHIPMENT_TRACK        => self::SALES_FLAT_SHIPMENT_TRACK,
        self::CREDITMEMO            => self::SALES_FLAT_CREDITMEMO,
        self::CREDITMEMO_GRID       => self::SALES_FLAT_CREDITMEMO_GRID,
        self::CREDITMEMO_ITEM       => self::SALES_FLAT_CREDITMEMO_ITEM,
        self::CREDITMEMO_COMMENT    => self::SALES_FLAT_CREDITMEMO_COMMENT,
    );
    
    protected $loadedTableStructureArray = array();
    
    protected $customerArray = array(
        self::CUSTOMER_ENTITY,
        self::CUSTOMER_ENTITY_DATETIME,
        self::CUSTOMER_ENTITY_DECIMAL,
        self::CUSTOMER_ENTITY_INT,
        self::CUSTOMER_ENTITY_VARCHAR,
        self::CUSTOMER_ENTITY_TEXT
    );
    
    protected $customerAddressArray = array(
        self::CUSTOMER_ADDRESS_ENTITY_DATETIME,
        self::CUSTOMER_ADDRESS_ENTITY_DECIMAL,
        self::CUSTOMER_ADDRESS_ENTITY_INT,
        self::CUSTOMER_ADDRESS_ENTITY_VARCHAR,
        self::CUSTOMER_ADDRESS_ENTITY_TEXT
    );
    
    public function loadTableStructure(){
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = $resource->getConnection('core_read');

        $this->resource = $resource;
        $this->connection = $connection;
        
        foreach ($this->tableStructureArray as $tableAlis => $tableName){
            $this->loadedTableStructureArray[$tableAlis] = array_keys($connection->describeTable($resource->getTableName($tableName)));
        }
    }

    public function exportOrders($orders)
    {
        $this->loadTableStructure();
        $this->io = new Varien_Io_File();
        $path = Mage::getBaseDir('var') . DS . 'export';
        $fileName = 'order_export_' . date("Ymd_His") . '.txt';
        $file = $path . DS . $fileName;
        $this->io->setAllowCreateFolders(true);
        $this->io->open(array('path' => $path));
        $this->io->streamOpen($file, 'w+');
        $this->io->streamLock(true);
        //$this->writeHeadRow();
        foreach ($orders as $order) {
            $order = Mage::getModel('sales/order')->load($order);
            $this->writeOrder($order, $fp);
        }
        return $fileName;
    }
    
    public function exportOrdersAbove($orders){
        $this->loadTableStructure();
        $this->io = new Varien_Io_File();
        $path = Mage::getBaseDir('var') . DS . 'export';
        $fileName = 'order_export_' . date("Ymd_His") . '.sql';
        $file = $path . DS . $fileName;
        $this->io->setAllowCreateFolders(true);
        $this->io->open(array('path' => $path));
        $this->io->streamOpen($file, 'w+');
        $this->io->streamLock(true);
        $orderId = $orders[0];
        
        $liveCustomerId = 211228;
        $this->prepareCustomerData($liveCustomerId);
        
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $ordersCollection->addFieldToFilter("entity_id", array("gteq" => $orderId));
        foreach ($ordersCollection as $order) {
            $_order = Mage::getModel('sales/order')->load($order->getId());
            $this->writeOrder($_order, $fp);
        }
        return $fileName;
    }

    protected function writeHeadRow()
    {
        $this->io->streamWriteCsv($this->getHeadRowValues());
    }
    
    private function prepareQuery($data, $tableName, $structureName){
        $orderKeys = $this->loadedTableStructureArray[$structureName];
        $orderKeys = array_combine($orderKeys, $orderKeys);
        $data = array_intersect_key( $data, array_flip($orderKeys) );
        $columns = "";
        $values = "";
        foreach ($data as $key => $value){
            if(empty($value)) continue ;
            $columns .= "`". $key . "`" . ",";
            if(is_numeric($value)){
                $values .= $value . ",";
            }else{
                $values .= "'".$value ."'". ",";
            }
        }
        
        if($tableName == self::SALES_FLAT_ORDER_ADDRESS){
            $columns .= "`email_shipping`" . ","; 
            $values .= "''". ",";
        }
        
        if($tableName == self::SALES_FLAT_ORDER_PAYMENT){
            $columns .= "`ccforpos_ref_no`,`cp1forpos_ref_no`,`cp2forpos_ref_no`,`codforpos_ref_no`,`cashforpos_ref_no`". ",";
            $values .= "'','','','',''". ",";
        }
        
        $columns = trim($columns, ",");
        $values = trim($values, ",");
        $query = "INSERT INTO `{$tableName}`({$columns}) values($values);";
        $this->writeRow($query);
        //return $query;
    }
    
    private function prepareInsertQuery($results, $tableName){
        foreach ($results as $result){
            $columns = "";
            $values = "";
            foreach ($result as $key => $value){
                if(empty($value)) continue ;
                if(unserialize($value)){
                    //$value = str_replace('"','\"',$value);
                }else{
                    $value = str_replace("'", "\'", $value);
                }
                
                $columns .= "`". $key . "`" . ",";
                if(is_numeric($value) && $key != "protect_code"){
                    $values .= $value . ",";
                }else{
                    $values .= "'".$value ."'". ",";
                }
            }
            if($tableName == self::SALES_FLAT_ORDER_PAYMENT){
                $columns .= "`ccforpos_ref_no`,`cp1forpos_ref_no`,`cp2forpos_ref_no`,`codforpos_ref_no`,`cashforpos_ref_no`". ",";
                $values .= "'','','','',''". ",";
            }
            $columns = trim($columns, ",");
            $values = trim($values, ",");
            $query = "INSERT IGNORE INTO `{$tableName}`({$columns}) values($values);";
            $this->writeRow($query);
        }
    }
    
    private function writeRow($query){
        $this->io->streamWrite($query);
        $this->io->streamWrite("\n");
    }
    
    private function prepareCustomerData($startCustomerId){
        $customerTable = self::CUSTOMER_ENTITY;
        $customers = $this->connection->fetchAll("SELECT * FROM {$customerTable} WHERE entity_id > {$startCustomerId}");
        foreach ($customers as $customer){
            $customerId = $customer["entity_id"];
            foreach ($this->customerArray as $tableCustomerName){
                $resultsCustomer = $this->connection->fetchAll("SELECT * FROM {$tableCustomerName} WHERE entity_id = {$customerId}");
                $this->prepareInsertQuery($resultsCustomer, $tableCustomerName);
            }
            
            $customerAddressTable = self::CUSTOMER_ADDRESS_ENTITY;
            $resultsAddress = $this->connection->fetchAll("SELECT * FROM {$customerAddressTable} WHERE parent_id = {$customerId}");
            $this->prepareInsertQuery($resultsAddress, $customerAddressTable);
            
            foreach ($resultsAddress as $result){
                $entityId = $result["entity_id"];
                foreach ($this->customerAddressArray as $tableName){
                    if($tableName != self::CUSTOMER_ADDRESS_ENTITY){
                        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$entityId}");
                        $this->prepareInsertQuery($results, $tableName);
                    }
                }
            }
            
        }
        
    }
    
    private function prepareCustomerAddressData($startCustomerId){
        $customerId = $order["customer_id"];
        $customerAddressTable = self::CUSTOMER_ADDRESS_ENTITY;
        $results = $this->connection->fetchAll("SELECT * FROM {$customerAddressTable} WHERE parent_id = {$customerId}");
        $this->prepareInsertQuery($results, $customerAddressTable);
        
        foreach ($results as $result){
            $entityId = $result["entity_id"];
            foreach ($this->customerAddressArray as $tableName){
                if($tableName != self::CUSTOMER_ADDRESS_ENTITY){
                    $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$entityId}");
                    $this->prepareInsertQuery($results, $tableName);
                }
            }
        }
    }
    
    
    private function prepareCustomer($order){
        $customerId = $order["customer_id"];
        foreach ($this->customerArray as $tableName){
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$customerId}");
            $this->prepareInsertQuery($results, $tableName);
        }
    }
    
    private function prepareCustomerAddress($order){
        $customerId = $order["customer_id"];
        $customerAddressTable = self::CUSTOMER_ADDRESS_ENTITY;
        $results = $this->connection->fetchAll("SELECT * FROM {$customerAddressTable} WHERE parent_id = {$customerId}");
        $this->prepareInsertQuery($results, $customerAddressTable);
        
        foreach ($results as $result){
            $entityId = $result["entity_id"];
            foreach ($this->customerAddressArray as $tableName){
                if($tableName != self::CUSTOMER_ADDRESS_ENTITY){
                    $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$entityId}");
                    $this->prepareInsertQuery($results, $tableName);
                }
            }
        }
        
    }
    
    private function prepareCustomerJson($order){
        $customerId = $order["customer_id"];
        $results = array();
        foreach ($this->customerArray as $tableName){
            $results[$tableName] = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$customerId}");
        }
        return $results;
    }
    
    private function prepareCustomerAddressJson($order){
        $customerId = $order["customer_id"];
        $customerAddressTable = self::CUSTOMER_ADDRESS_ENTITY;
        $results = $this->connection->fetchAll("SELECT * FROM {$customerAddressTable} WHERE parent_id = {$customerId}");
        $resultsArray = array();
        $resultsArray[$customerAddressTable] = $results;
        foreach ($results as $result){
            $entityId = $result["entity_id"];
            foreach ($this->customerAddressArray as $tableName){
                if($tableName != self::CUSTOMER_ADDRESS_ENTITY){
                    $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$entityId}");
                    $resultsArray[$tableName] = $results;
                }
            }
        }
        return $resultsArray;
    }
    
    protected function writeOrder($order,$fp){
        //$this->prepareCustomer($order);
        //$this->prepareCustomerAddress($order);
        //order information
        $tableName = self::SALES_FLAT_ORDER;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //order item information
        $tableName = self::SALES_FLAT_ORDER_ITEM;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //order address information
        $tableName = self::SALES_FLAT_ORDER_ADDRESS;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //order grid information
        $tableName = self::SALES_FLAT_ORDER_GRID;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //order payment information
        $tableName = self::SALES_FLAT_ORDER_PAYMENT;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //order status hoistory information
        $tableName = self::SALES_FLAT_ORDER_STATUS_HISTORY;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //payment transaction
        $tableName = self::SALES_PAYMENT_TRANSACTION;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //invoice
        $tableName = self::SALES_FLAT_INVOICE;
        $resultsInvoice = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($resultsInvoice, $tableName);
        
        //invoice item
        foreach ($resultsInvoice as $result){
            $invoiceId = $result["entity_id"];
            $tableName = self::SALES_FLAT_INVOICE_ITEM;
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$invoiceId}");
            $this->prepareInsertQuery($results, $tableName);
            
            $tableName = self::SALES_FLAT_INVOICE_COMMENT;
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$invoiceId}");
            $this->prepareInsertQuery($results, $tableName);
        }
        
        //invoice grid
        $tableName = self::SALES_FLAT_INVOICE_GRID;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        
        // shipment
        $tableName = self::SALES_FLAT_SHIPMENT;
        $resultsShipment = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($resultsShipment, $tableName);
        
        //shipment item
        foreach ($resultsShipment as $result){
            $shipmentId = $result["entity_id"];
            $tableName = self::SALES_FLAT_SHIPMENT_ITEM;
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$shipmentId}");
            $this->prepareInsertQuery($results, $tableName);
            
            $tableName = self::SALES_FLAT_SHIPMENT_COMMENT;
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$shipmentId}");
            $this->prepareInsertQuery($results, $tableName);
        }
        
        //shipment grid
        $tableName = self::SALES_FLAT_SHIPMENT_GRID;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        //shipment tracks
        $tableName = self::SALES_FLAT_SHIPMENT_TRACK;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        
        //creditmemo
        $tableName = self::SALES_FLAT_CREDITMEMO;
        $resultsCreditmemo = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($resultsCreditmemo, $tableName);
        
        //creditmemo item
        foreach ($resultsCreditmemo as $result){
            $creditmemoId = $result["entity_id"];
            $tableName = self::SALES_FLAT_CREDITMEMO_ITEM;
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$creditmemoId}");
            $this->prepareInsertQuery($results, $tableName);
            
            $tableName = self::SALES_FLAT_CREDITMEMO_COMMENT;
            $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$creditmemoId}");
            $this->prepareInsertQuery($results, $tableName);
        }
        
        //creditmemo grid
        $tableName = self::SALES_FLAT_CREDITMEMO;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
    }
    
    
    protected function writeOrderJson($order,$fp){
        $customer = $this->prepareCustomerJson($order);
        $customerAddress = $this->prepareCustomerAddressJson($order);
        
        $orderArray = array();
        $orderArray["customer"] = $customer;
        $orderArray["customer_address"] = $customerAddress;
        
        //order information
        $tableName = self::SALES_FLAT_ORDER;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE entity_id = {$order->getId()}");
        $orderArray["order"] = $results;
        
        //order item information
        $tableName = self::SALES_FLAT_ORDER_ITEM;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["order_items"] = $results;
        
        //order address information
        $tableName = self::SALES_FLAT_ORDER_ADDRESS;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$order->getId()}");
        $orderArray["order_addresses"] = $results;
        
        //order payment information
        $tableName = self::SALES_FLAT_ORDER_PAYMENT;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$order->getId()}");
        $orderArray["order_payments"] = $results;
        
        //order status hoistory information
        $tableName = self::SALES_FLAT_ORDER_STATUS_HISTORY;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$order->getId()}");
        $orderArray["order_status_history"] = $results;
        
        //payment transaction
        $tableName = self::SALES_PAYMENT_TRANSACTION;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["order_transaction"] = $results;
        
        //shipment grid
        $tableName = self::SALES_FLAT_SHIPMENT_GRID;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["order_grid"] = $results;
        
        
        //invoice
        $tableName = self::SALES_FLAT_INVOICE;
        $resultsInvoice = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        
        $invoiceList = array();
        //invoice item
        foreach ($resultsInvoice as $result){
            $invoiceId = $result["entity_id"];
            $tableName = self::SALES_FLAT_INVOICE_ITEM;
            $resultsItem = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$invoiceId}");
            
            $tableName = self::SALES_FLAT_INVOICE_COMMENT;
            $resultsComment = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$invoiceId}");
        
            $invoiceList[] = array(
                "invoice" => $result,
                "invoice_items" => $resultsItem,
                "comments" => $resultsComment
            );
        }
        
        $orderArray["invoice_list"] = $invoiceList;
        
        //invoice grid
        $tableName = self::SALES_FLAT_INVOICE_GRID;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["invoice_list"]["invoice_grid"] = $results;
        
        // shipment
        $tableName = self::SALES_FLAT_SHIPMENT;
        $resultsShipment = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        
        $shipmentList = array();
        //shipment item
        foreach ($resultsShipment as $result){
            $shipmentId = $result["entity_id"];
            $tableName = self::SALES_FLAT_SHIPMENT_ITEM;
            $resultsItems = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$shipmentId}");
            
            $tableName = self::SALES_FLAT_SHIPMENT_COMMENT;
            $resultsComments = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$shipmentId}");
            $shipmentList[] = array(
                "shipment" => $result,
                "shipment_items" => $resultsItems,
                "comments" => $resultsComments
            );
        }
        $orderArray["shipment_list"] = $shipmentList;
        
        
        //shipment grid
        $tableName = self::SALES_FLAT_SHIPMENT_GRID;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["shipment_list"]["shipment_grid"] = $results;
        
        //shipment tracks
        $tableName = self::SALES_FLAT_SHIPMENT_TRACK;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["shipment_list"]["shipment_tracks"] = $results;
        
        
        //creditmemo
        $tableName = self::SALES_FLAT_CREDITMEMO;
        $resultsCreditmemo = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        
        $creditMemoList = array();
        //creditmemo item
        foreach ($resultsCreditmemo as $result){
            $creditmemoId = $result["entity_id"];
            $tableName = self::SALES_FLAT_CREDITMEMO_ITEM;
            $resultsItems = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$creditmemoId}");
            
            $tableName = self::SALES_FLAT_CREDITMEMO_COMMENT;
            $resultsComments = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE parent_id = {$creditmemoId}");
            
            $creditMemoList[] = array(
                "creditmemo" => $result,
                "creditmemo_items" => $resultsItems,
                "comments" => $resultsComments
            );
        }
        $orderArray["creditmemo_list"] = $creditMemoList;
        
        //creditmemo grid
        $tableName = self::SALES_FLAT_CREDITMEMO;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $orderArray["creditmemo_list"]["creditmemo_grid"] = $results;
        
        $this->io->streamWrite(json_encode($orderArray));
        $this->io->streamWrite("\n");
    }
    
    
    
    
    protected function writeOrderUUUU($order,$fp){
        $this->prepareCustomer($order);
        $this->prepareCustomerAddress($order);
        $orderData = $order->getData();
        $this->prepareQuery($orderData, self::SALES_FLAT_ORDER, self::ORDER);
        $orderItemsData = $order->getItemsCollection()->getData();
        foreach ($orderItemsData as $items){
            $this->prepareQuery($items, self::SALES_FLAT_ORDER_ITEM, self::ORDER_ITEM);
        }
        
        //order grid
        $orderGridKeys = $this->loadedTableStructureArray[self::ORDER_GRID];
        $orderGridKeys = array_combine($orderGridKeys, $orderGridKeys);
        $orderGridData = array_intersect_key( $orderData, array_flip($orderGridKeys) );
        
        $orderAddressData = $order->getAddressesCollection()->getData();
        $billingName = "";
        $shippingName = "";
        foreach ($orderAddressData as $address){
            if($address["address_type"] == "billing"){
                $orderGridData["billing_name"] = $address["firstname"] . $address["lastname"];
                $billingName = $orderGridData["billing_name"];
            }else{
                $orderGridData["shipping_name"] = $address["firstname"] . $address["lastname"];
                $shippingName = $orderGridData["shipping_name"];
            }
            $this->prepareQuery($address, self::SALES_FLAT_ORDER_ADDRESS, self::ORDER_ADDRESS);
            
        }
        $this->prepareQuery($orderGridData, self::SALES_FLAT_ORDER_GRID, self::ORDER_GRID);
        
        
        
        $paymentData = $order->getPaymentsCollection()->getData();
        foreach ($paymentData as $payment){
            $this->prepareQuery($payment, self::SALES_FLAT_ORDER_PAYMENT, self::ORDER_PAYMENT);
        }
        //payment transaction
        $tableName = self::SALES_PAYMENT_TRANSACTION;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        $orderStatusHistory = $order->getStatusHistoryCollection()->getData();
        foreach ($orderStatusHistory as $statusHistory){
            $this->prepareQuery($statusHistory, self::SALES_FLAT_ORDER_STATUS_HISTORY, self::ORDER_STATUS_HISTORY);
        }
        
        
        $invoiceCollection = $order->getInvoiceCollection();
        foreach ($invoiceCollection as $invoice){
            $this->prepareQuery($invoice->getData(), self::SALES_FLAT_INVOICE, self::INVOICE);
            
            //invoice grid collection
            $invoiceGridKeys = $this->loadedTableStructureArray[self::INVOICE_GRID];
            $invoiceGridKeys = array_combine($invoiceGridKeys, $invoiceGridKeys);
            $invoiceGridData = array_intersect_key( $invoice->getData(), array_flip($invoiceGridKeys) );
            $invoiceGridData["order_increment_id"] = $orderData["increment_id"];
            $invoiceGridData["order_created_at"] = $orderData["created_at"];
            $invoiceGridData["billing_name"] = $billingName;
            
            $this->prepareQuery($invoiceGridData, self::SALES_FLAT_INVOICE_GRID, self::INVOICE_GRID);
            
            $invoiceItems = $invoice->getItemsCollection()->getData();
            foreach ($invoiceItems as $invoiceItem){
                $this->prepareQuery($invoiceItem, self::SALES_FLAT_INVOICE_ITEM, self::INVOICE_ITEM);
            }
            $invoiceComments = $invoice->getCommentsCollection()->getData();
            foreach ($invoiceComments as $invoiceComment){
                $this->prepareQuery($invoiceComment, self::SALES_FLAT_INVOICE_COMMENT, self::INVOICE_COMMENT);
            }
            
        }
        
        $shipmentCollection = $order->getShipmentsCollection();
        foreach ($shipmentCollection as $shipment){
            $this->prepareQuery($shipment->getData(), self::SALES_FLAT_SHIPMENT, self::SHIPMENT);
            
            //shipment grid collection
            $shipmentGridKeys = $this->loadedTableStructureArray[self::SHIPMENT_GRID];
            $shipmentGridKeys = array_combine($shipmentGridKeys, $shipmentGridKeys);
            $shipmentGridData = array_intersect_key( $shipment->getData(), array_flip($shipmentGridKeys) );
            $shipmentGridData["order_increment_id"] = $orderData["increment_id"];
            $shipmentGridData["order_created_at"] = $orderData["created_at"];
            $shipmentGridData["shipping_name"] = $shippingName;
            
            $this->prepareQuery($shipmentGridData, self::SALES_FLAT_SHIPMENT_GRID, self::SHIPMENT_GRID);
            
            
            $shipmentItems = $shipment->getItemsCollection()->getData();
            foreach ($shipmentItems as $shipmentItem){
                $this->prepareQuery($shipmentItem, self::SALES_FLAT_SHIPMENT_ITEM, self::SHIPMENT_ITEM);
            }
            $shipmentComments = $shipment->getCommentsCollection()->getData();
            foreach ($shipmentComments as $shipmentComment){
                $this->prepareQuery($shipmentComment, self::SALES_FLAT_SHIPMENT_COMMENT, self::SHIPMENT_COMMENT);
            }
        }
        
        //shipment track 
        $tableName = self::SALES_FLAT_SHIPMENT_TRACK;
        $results = $this->connection->fetchAll("SELECT * FROM {$tableName} WHERE order_id = {$order->getId()}");
        $this->prepareInsertQuery($results, $tableName);
        
        $creditmemoCollection = $order->getCreditmemosCollection();
        foreach ($creditmemoCollection as $creditmemo){
            $this->prepareQuery($creditmemo->getData(), self::SALES_FLAT_CREDITMEMO, self::CREDITMEMO);
            
            //creditmemo grid collection
            $creditmemoGridKeys = $this->loadedTableStructureArray[self::CREDITMEMO_GRID];
            $creditmemoGridKeys = array_combine($creditmemoGridKeys, $creditmemoGridKeys);
            $creditmemoGridData = array_intersect_key( $creditmemo->getData(), array_flip($creditmemoGridKeys) );
            $creditmemoGridData["order_increment_id"] = $orderData["increment_id"];
            $creditmemoGridData["order_created_at"] = $orderData["created_at"];
            $creditmemoGridData["billing_name"] = $billingName;
            
            $this->prepareQuery($creditmemoGridData, self::SALES_FLAT_CREDITMEMO_GRID, self::CREDITMEMO_GRID);
            
            
            $creditmemoItems = $creditmemo->getItemsCollection()->getData();
            foreach ($creditmemoItems as $creditmemoItem){
                $this->prepareQuery($creditmemoItem, self::SALES_FLAT_CREDITMEMO_ITEM, self::CREDITMEMO_ITEM);
            }
            $creditmemoComments = $creditmemo->getCommentsCollection()->getData();
            foreach ($creditmemoComments as $creditmemoComment){
                $this->prepareQuery($creditmemoComment, self::SALES_FLAT_CREDITMEMO_COMMENT, self::CREDITMEMO_COMMENT);
            }
        }
    }
    
    protected function writeOrdersss($order, $fp)
    {
        $orderArray = array();
        $orderArray["order"] = $order->getData();
        $orderArray["order_items"] = $order->getItemsCollection()->getData();
        $orderArray["order_payments"] = $order->getPaymentsCollection()->getData();
        $orderArray["order_addresses"] = $order->getAddressesCollection()->getData();
        $orderArray["order_status_history"] = $order->getStatusHistoryCollection()->getData();
        
        $invoiceList = array();
        $invoiceCollection = $order->getInvoiceCollection();
        foreach ($invoiceCollection as $invoice){
            $invoiceArr = array(
                "invoice" => $invoice->getData(),
                "invoice_items" => $invoice->getItemsCollection()->getData(),
                "comments" => $invoice->getCommentsCollection()->getData()
            );
            $invoiceList[] = $invoiceArr;
        }
        $orderArray["invoice_list"] = $invoiceList;
        
        $shipmentList = array();
        $shipmentCollection = $order->getShipmentsCollection();
        foreach ($shipmentCollection as $shipment){
            $shipmentArr = array(
                "shipment" => $shipment->getData(),
                "shipment_items" => $shipment->getItemsCollection()->getData(),
                "comments" => $shipment->getCommentsCollection()->getData()
            );
            $shipmentList[] = $shipmentArr;
        }
        $orderArray["shipment_list"] = $shipmentList;
        
        $creditmemoList = array();
        $creditmemoCollection = $order->getCreditmemosCollection();
        foreach ($creditmemoCollection as $creditmemo){
            $creditmemoArr = array(
                "creditmemo" => $creditmemo->getData(),
                "creditmemo_items" => $creditmemo->getItemsCollection()->getData(),
                "comments" => $creditmemo->getCommentsCollection()->getData()
            );
            $creditmemoList[] = $creditmemoArr;
        }
        $orderArray["creditmemo_list"] = $creditmemoList;
        $orderArray["shipment_tracks"] = $order->getTracksCollection()->getData();
        
        $this->io->streamWrite(json_encode($orderArray));
        $this->io->streamWrite("\n");
    }
    

    protected function writeOrder1($order, $fp)
    {
        $common = $this->getCommonOrderValues($order);
        $blank = $this->getBlankOrderValues($order);
        $orderItems = $order->getItemsCollection();
        $itemInc = 0;
        $data = array();
        $count = 0;
        foreach ($orderItems as $item) {
            if ($count == 0) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                $this->io->streamWriteCsv($record);
            } else {
                $record = array_merge($blank, $this->getOrderItemValues($item, $order, ++$itemInc));
                $this->io->streamWriteCsv($record);
            }

            $count++;
        }
    }

    protected function getHeadRowValues()
    {
        return array(
            "order_id",
            "email",
            "firstname",
            "lastname",
            "prefix",
            "middlename",
            "suffix",
            "taxvat",
            "created_at",
            "updated_at",
            "invoice_created_at",
            "shipment_created_at",
            "creditmemo_created_at",
            "tax_amount",
            "base_tax_amount",
            "discount_amount",
            "base_discount_amount",
            "shipping_tax_amount",
            "base_shipping_tax_amount",
            "base_to_global_rate",
            "base_to_order_rate",
            "store_to_base_rate",
            "store_to_order_rate",
            "subtotal_incl_tax",
            "base_subtotal_incl_tax",
            "coupon_code",
            "shipping_incl_tax",
            "base_shipping_incl_tax",
            "shipping_method",
            "shipping_amount",
            "subtotal",
            "base_subtotal",
            "grand_total",
            "base_grand_total",
            "base_shipping_amount",
            "adjustment_positive",
            "adjustment_negative",
            "refunded_shipping_amount",
            "base_refunded_shipping_amount",
            "refunded_subtotal",
            "base_refunded_subtotal",
            "refunded_tax_amount",
            "base_refunded_tax_amount",
            "refunded_discount_amount",
            "base_refunded_discount_amount",
            "store_id",
            "order_status",
            "order_state",
            "hold_before_state",
            "hold_before_status",
            "store_currency_code",
            "base_currency_code",
            "order_currency_code",
            "total_paid",
            "base_total_paid",
            "is_virtual",
            "total_qty_ordered",
            "remote_ip",
            "total_refunded",
            "base_total_refunded",
            "total_canceled",
            "total_invoiced",
            "customer_id",
            "billing_prefix",
            "billing_firstname",
            "billing_middlename",
            "billing_lastname",
            "billing_suffix",
            "billing_street_full",
            "billing_city",
            "billing_region",
            "billing_country",
            "billing_postcode",
            "billing_telephone",
            "billing_company",
            "billing_fax",
            "customer_id",
            "shipping_prefix",
            "shipping_firstname",
            "shipping_middlename",
            "shipping_lastname",
            "shipping_suffix",
            "shipping_street_full",
            "shipping_city",
            "shipping_region",
            "shipping_country",
            "shipping_postcode",
            "shipping_telephone",
            "shipping_company",
            "shipping_fax",
            "payment_method",
            "product_sku",
            "product_name",
            "qty_ordered",
            "qty_invoiced",
            "qty_shipped",
            "qty_refunded",
            "qty_canceled",
            "product_type",
            "original_price",
            "base_original_price",
            "row_total",
            "base_row_total",
            "row_weight",
            "price_incl_tax",
            "base_price_incl_tax",
            "product_tax_amount",
            "product_base_tax_amount",
            "product_tax_percent",
            "product_discount",
            "product_base_discount",
            "product_discount_percent",
            "is_child",
            "product_option"
        );
    }

    //Common orders value

    protected function getCommonOrderValues($order)
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();
        if (!$shippingAddress)
            $shippingAddress = $billingAddress;

        $credit_detail = $this->getCreditMemoDetail($order);
        return array(
            $order->getIncrementId(),
            $order->getData('customer_email'),
            $this->formatText($order->getData('customer_firstname')),
            $this->formatText($order->getData('customer_lastname')),
            $this->formatText($order->getData('customer_prefix')),
            $this->formatText($order->getData('customer_middlename')),
            $this->formatText($order->getData('customer_suffix')),
            $order->getData('customer_taxvat'),
            $order->getData('created_at'),
            $order->getData('updated_at'),
            $this->getInvoiceDate($order),
            $this->getShipmentDate($order),
            $this->getCreditmemoDate($order),
            $order->getData('tax_amount'),
            $order->getData('base_tax_amount'),
            $order->getData('discount_amount'),
            $order->getData('base_discount_amount'),
            $order->getData('shipping_tax_amount'),
            $order->getData('base_shipping_tax_amount'),
            $order->getData('base_to_global_rate'),
            $order->getData('base_to_order_rate'),
            $order->getData('store_to_base_rate'),
            $order->getData('store_to_order_rate'),
            $order->getData('subtotal_incl_tax'),
            $order->getData('base_subtotal_incl_tax'),
            $order->getData('coupon_code'),
            $order->getData('shipping_incl_tax'),
            $order->getData('base_shipping_incl_tax'),
            $this->getShippingMethod($order),
            $order->getData('shipping_amount'),
            $order->getData('subtotal'),
            $order->getData('base_subtotal'),
            $order->getData('grand_total'),
            $order->getData('base_grand_total'),
            $order->getData('base_shipping_amount'),
            $credit_detail['adjustment_positive'],
            $credit_detail['adjustment_negative'],
            $credit_detail['shipping_amount'],
            $credit_detail['base_shipping_amount'],
            $credit_detail['subtotal'],
            $credit_detail['base_subtotal'],
            $credit_detail['tax_amount'],
            $credit_detail['base_tax_amount'],
            $credit_detail['discount_amount'],
            $credit_detail['base_discount_amount'],
            $order->getData('store_id'),
            $order->getStatus(),
            $order->getState(),
            $order->getHoldBeforeState(),
            $order->getHoldBeforeStatus(),
            $order->getData('store_currency_code'),
            $order->getData('base_currency_code'),
            $order->getData('order_currency_code'),
            $order->getData('total_paid'),
            $order->getData('base_total_paid'),
            $order->getData('is_virtual'),
            $order->getData('total_qty_ordered'),
            $order->getData('remote_ip'),
            $order->getData('total_refunded'),
            $order->getData('base_total_refunded'),
            $order->getData('total_canceled'),
            $order->getData('total_invoiced'),
            $order->getData('customer_id'),
            $this->formatText($order->getBillingAddress()->getData('prefix')),
            $this->formatText($order->getBillingAddress()->getData('firstname')),
            $this->formatText($order->getBillingAddress()->getData('middlename')),
            $this->formatText($order->getBillingAddress()->getData('lastname')),
            $this->formatText($order->getBillingAddress()->getData('suffix')),
            $this->formatText($order->getBillingAddress()->getData('street')),
            $this->formatText($order->getBillingAddress()->getData('city')),
            $this->formatText($order->getBillingAddress()->getData('region')),
            $this->formatText($order->getBillingAddress()->getData('country_id')),
            $order->getBillingAddress()->getData('postcode'),
            $order->getBillingAddress()->getData('telephone'),
            $this->formatText($order->getBillingAddress()->getData('company')),
            $order->getBillingAddress()->getData('fax'),
            $order->getData('customer_id'),
            $shippingAddress->getData('prefix'),
            $this->formatText($shippingAddress->getData('firstname')),
            $this->formatText($shippingAddress->getData('middlename')),
            $this->formatText($shippingAddress->getData('lastname')),
            $this->formatText($shippingAddress->getData('suffix')),
            $this->formatText($shippingAddress->getData('street')),
            $this->formatText($shippingAddress->getData('city')),
            $this->formatText($shippingAddress->getData('region')),
            $shippingAddress->getData('country_id'),
            $shippingAddress->getData('postcode'),
            $shippingAddress->getData('telephone'),
            $this->formatText($shippingAddress->getData('company')),
            $shippingAddress->getData('fax'),
            $this->getPaymentMethod($order)
        );
    }

    protected function getBlankOrderValues($order)
    {
        return array(
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    }

    //To return the array of ordered items
    protected function getOrderItemValues($item, $order, $itemInc = 1)
    {
        return array(
            $this->getItemSku($item),
            $this->formatText($item->getName()),
            (int) $item->getQtyOrdered(),
            (int) $item->getQtyInvoiced(),
            (int) $item->getQtyShipped(),
            (int) $item->getQtyRefunded(),
            (int) $item->getQtyCanceled(),
            $item->getProductType(),
            $item->getOriginalPrice(),
            $item->getBaseOriginalPrice(),
            $item->getRowTotal(),
            $item->getBaseRowTotal(),
            $item->getRowWeight(),
            $item->getPriceInclTax(),
            $item->getBasePriceInclTax(),
            $item->getTaxAmount(),
            $item->getBaseTaxAmount(),
            $item->getTaxPercent(),
            $item->getDiscountAmount(),
            $item->getBaseDiscountAmount(),
            $item->getDiscountPercent(),
            $this->getChildInfo($item),
            $item->getdata('product_options')
        );
    }

}
