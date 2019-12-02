<?php

class Allure_Exporter_Model_Importorders extends Mage_Core_Model_Abstract
{

    public $order_info = array();
    public $order_item_info = array();
    public $order_item_flag = 0;
    public $store_id = 0;
    public $import_limit = 0;
    
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
    
    protected  $tableStructureArray = array(
        self::ORDER                 => self::SALES_FLAT_ORDER,
        self::ORDER_ITEM            => self::SALES_FLAT_ORDER_ITEM,
        self::ORDER_ADDRESS         => self::SALES_FLAT_ORDER_ADDRESS,
        self::ORDER_PAYMENT         => self::SALES_FLAT_ORDER_PAYMENT,
        self::ORDER_GRID            => self::SALES_FLAT_ORDER_GRID,
        self::ORDER_TRANSACTION     => self::SALES_PAYMENT_TRANSACTION,
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
    
    private $resource, $connection;
    
    
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
    
    private function runInsertQuery($data, $tableName){
        $this->connection->insert(
            $this->resource->getTableName($tableName),
            $data
        );
        return $this->connection->lastInsertId($tableName);
    }
    
    
    private function prepareQueryData($data, $tableName){
        $columns = $this->loadedTableStructureArray[$tableName];
        $columns = array_combine($columns, $columns);
        return array_intersect_key( $data, array_flip($columns) );
    }
    
    public function readCSV($csvFile, $data){
        Mage::log($csvFile,Zend_Log::DEBUG,'abc.log',true);
        try{
            
            $this->loadTableStructure();
            
            $ordersData = file($csvFile);
            
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            /** @var Varien_Db_Adapter_Interface $connection */
            $connection = $resource->getConnection('core_write');
            
            foreach ($ordersData as $line){
                //Mage::log(json_decode($line,true),Zend_Log::DEBUG,'abc.log',true);
                continue;
                
                $mappingArray = array();
                $parseData = json_decode($line,true);
                $orderData = $parseData["order"];
                if(isset($orderData["increment_id"]) && !empty($orderData["increment_id"])){
                    $incrementId = $orderData["increment_id"];
                    $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                    if(true || !$order->getId()){
                        //create order
                        $oldOrderId =  $orderData["entity_id"];
                        $orderData = $this->prepareQueryData($orderData, self::ORDER);
                        unset($orderData["entity_id"]);
                        $orderData["increment_id"] = $orderData["increment_id"] . "s2";
                        $newOrderId = $this->runInsertQuery($orderData, self::SALES_FLAT_ORDER);
                        $mappingArray["order"][$oldOrderId] = $newOrderId;
                        $orderData["entity_id"] = $newOrderId;
                        Mage::log("new order_id = ".$newOrderId,Zend_Log::DEBUG,'abc.log',true); 
                        
                        //create order item
                        $orderItemsData = $parseData["order_items"];
                        foreach ($orderItemsData as $itemData){
                            $orderItemData = $this->prepareQueryData($itemData, self::ORDER_ITEM);
                            $oldItemId = $orderItemData["item_id"];
                            unset($orderItemData["item_id"]);
                            $orderItemData["order_id"] = $newOrderId;
                            $oldParentItemId = $orderItemData["parent_item_id"];
                            $newItemId = $this->runInsertQuery($orderData, self::SALES_FLAT_ORDER_ITEM);
                            $mappingArray["order_item"][$oldItemId] = $newItemId;
                            //order item update
                            if($oldParentItemId){
                                $updateData = array("parent_item_id" => $newItemId);
                                $where = "item_id = {$newItemId}";
                                $this->connection->update(self::SALES_FLAT_ORDER_ITEM, $updateData, $where);
                            }
                        }
                        
                        //update order
                        $orderUpdateData = array();
                        
                        //create order address
                        $orderAddressesData = $parseData["order_addresses"];
                        $billingName    = "";
                        $shippingName   = "";
                        foreach ($orderAddressesData as $orderAddress){
                            $orderAddressData = $this->prepareQueryData($orderAddress, self::ORDER_ADDRESS);
                            $oldAddressId = $orderAddressData["entity_id"];
                            unset($orderAddressData["entity_id"]);
                            $orderAddressData["parent_id"] = $newOrderId;
                            $addressType = $orderAddressData["address_type"];
                            
                            if($addressType == "billing"){
                                $billingName = $orderAddressData["firstname"] . $orderAddressData["lastname"];
                            }else{
                                $shippingName = $orderAddressData["firstname"] . $orderAddressData["lastname"];
                            }
                            
                            $newAddressId = $this->runInsertQuery($orderAddressData, self::SALES_FLAT_ORDER_ADDRESS);
                            $mappingArray["order_address"][$oldAddressId] = $newAddressId;
                            $orderUpdateData[$addressType."_address_id"] = $orderData[$addressType."_address_id"];
                        }
                        
                        //update order
                        $whereOrder = "entity_id = {$newOrderId}";
                        $this->connection->update(self::SALES_FLAT_ORDER, $orderUpdateData, $whereOrder);
                        
                        //create order grid
                        $orderGridData = $this->prepareQueryData($orderData, self::ORDER_GRID);
                        $orderGridData["entity_id"] = $newOrderId;
                        $orderGridData["billing_name"] = $billingName;
                        $orderGridData["shipping_name"] = $shippingName;
                        $this->runInsertQuery($orderGridData, self::SALES_FLAT_ORDER_GRID);
                        
                        //create order payment
                        
                    }else{
                        Mage::log("order_id = ".$incrementId." already exist.",Zend_Log::DEBUG,'abc.log',true); 
                    }
                }
            }
        }catch (Exception $e){
            Mage::log($e->getMessage(),Zend_Log::DEBUG,'abc.log',true);
        }
        
    }
    

    public function readCSV1($csvFile, $data)
    {
        $this->import_limit = $data['import_limit'];
        $this->store_id = $data['store_id'];
        $file_handle = fopen($csvFile, 'r');
        $i = 0;
        $decline = array();
        $available = array();
        $success = 0;
        $parent_flag = 0;
        $invalid = 0;
        $line_number = 2;
        $total_order = 0;
        Mage::helper('exporter')->unlinkFile();
        Mage::helper('exporter')->header();
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle);

            if ($i != 0) {
                if ($line_of_text[$i][0] != '' && $parent_flag == 0) {
                    $this->insertOrderData($line_of_text[$i]);
                    $parent_flag = 1;
                    $total_order++;
                } else if ($line_of_text[$i][91] != '' && $parent_flag == 1 && $line_of_text[$i][0] == '') {
                    $this->insertOrderItem($line_of_text[$i]);
                } else if ($parent_flag == 1) {
                    try {
                        $message = Mage::getModel('exporter/createorder')->createOrder($this->order_info, $this->order_item_info, $this->store_id);
                        Mage::getModel('exporter/createorder')->removeOrderStatusHistory();
                    } catch (Exception $e) {
                        Mage::helper('exporter')->logException($e, $this->order_info['increment_id'], 'order', $line_number);
                        Mage::helper('exporter')->footer();
                        $decline[] = $this->order_info['increment_id'];
                        $message = 0;
                    }

                    if ($message == 1)
                        $success++;

                    if ($message == 2) {
                        Mage::helper('exporter')->logAvailable($this->order_info['increment_id'], 'order', $line_number);
                        Mage::helper('exporter')->footer();
                        $decline[] = $this->order_info['increment_id'];
                    }

                    $this->order_info = array();
                    $this->order_item_info = array();
                    $this->order_item_flag = 0;

                    if (is_array($line_of_text[$i])) {
                        $this->insertOrderData($line_of_text[$i]);
                        $parent_flag = 1;
                        $line_number = $i + 1;
                        $total_order++;
                    }
                }
            }

            $i++;

            if ($this->import_limit < $total_order)
                break;
        }

        $isPrintable = Mage::helper('exporter')->isPrintable();
        if ($success)
            Mage::getModel('core/session')->addSuccess(Mage::helper('exporter')->__('Total ' . $success . ' order(s) imported successfully!'));

        if ($decline || $isPrintable)
            Mage::getModel('core/session')->addError(Mage::helper('exporter')->__('Click <a href="' . Mage::helper("adminhtml")->getUrl("*/exporter/exportLog") . '">here</a> to view the error log'));

        fclose($file_handle);

        return array($success, $decline);
    }

    public function insertOrderData($orders_data)
    {
        $sales_order_arr = array();
        $sales_order_item_arr = array();
        $sales_order = $this->getSalesTable();
        $sales_payment = $this->getSalesPayment();
        $sales_shipping = $this->getSalesBilling();
        $sales_billing = $this->getSalesBilling();
        $sales_order_item = $this->getSalesItem();
        $model = Mage::getModel('sales/order');
        $i = 0;
        $j = 0;
        $k = 0;
        $l = 0;
        $m = 0;

        foreach ($orders_data as $order) {
            if (count($sales_order) > $i) {
                $sales_order_arr[$sales_order[$i]] = $order;
            } else if (count($sales_billing) > $j) {
                $sales_billing[$j] . $sales_order_arr['billing_address'][$sales_billing[$j]] = $order;
                $j++;
            } else if (count($sales_shipping) > $k) {
                $sales_order_arr['shipping_address'][$sales_shipping[$k]] = $order;
                $k++;
            } else if (count($sales_payment) > $l) {
                $sales_order_arr['payment'][$sales_payment[$l]] = $order;
                $l++;
            } else if (count($sales_order_item) > $m) {
                $sales_order_item_arr[$sales_order_item[$m]] = $order;
                $m++;
            }

            $i++;
        }

        $this->order_info = $sales_order_arr;
        $this->order_item_info[$this->order_item_flag] = $sales_order_item_arr;
        $this->order_item_flag++;
    }

    public function insertOrderItem($orders_data)
    {
        $sales_order_item_arr = array();
        $sales_order_item = $this->getSalesItem();
        $i = 0;
        for ($j = 91; $j < count($orders_data); $j++) {
            if (count($sales_order_item) > $i)
                $sales_order_item_arr[$sales_order_item[$i]] = $orders_data[$j];
            $i++;
        }

        $this->order_item_info[$this->order_item_flag] = $sales_order_item_arr;
        $this->order_item_flag++;
    }

    public function getSalesTable()
    {
        return array(
            'increment_id',
            'customer_email',
            'customer_firstname',
            'customer_lasttname',
            'customer_prefix',
            'customer_middlename',
            'customer_suffix',
            'taxvat',
            'created_at',
            'updated_at',
            'invoice_created_at',
            'shipment_created_at',
            'creditmemo_created_at',
            'tax_amount',
            'base_tax_amount',
            'discount_amount',
            'base_discount_amount',
            'shipping_tax_amount',
            'base_shipping_tax_amount',
            'base_to_global_rate',
            'base_to_order_rate',
            'store_to_base_rate',
            'store_to_order_rate',
            'subtotal_incl_tax',
            'base_subtotal_incl_tax',
            'coupon_code',
            'shipping_incl_tax',
            'base_shipping_incl_tax',
            'shipping_method',
            'shipping_amount',
            'subtotal',
            'base_subtotal',
            'grand_total',
            'base_grand_total',
            'base_shipping_amount',
            'adjustment_positive',
            'adjustment_negative',
            'refunded_shipping_amount',
            'base_refunded_shipping_amount',
            'refunded_subtotal',
            'base_refunded_subtotal',
            'refunded_tax_amount',
            'base_refunded_tax_amount',
            'refunded_discount_amount',
            'base_refunded_discount_amount',
            'store_id',
            'order_status',
            'order_state',
            'hold_before_state',
            'hold_before_status',
            'store_currency_code',
            'base_currency_code',
            'order_currency_code',
            'total_paid',
            'base_total_paid',
            'is_virtual',
            'total_qty_ordered',
            'remote_ip',
            'total_refunded',
            'base_total_refunded',
            'total_canceled',
            'total_invoiced');
    }

    public function getSalesBilling()
    {
        return array(
            'customer_address_id',
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'street',
            'city',
            'region',
            'country_id',
            'postcode',
            'telephone',
            'company',
            'fax');
    }

    public function getSalesPayment()
    {
        return array('method');
    }

    public function getSalesItem()
    {
        return array(
            'product_sku',
            'product_name',
            'qty_ordered',
            'qty_invoiced',
            'qty_shipped',
            'qty_refunded',
            'qty_canceled',
            'product_type',
            'original_price',
            'base_original_price',
            'row_total',
            'base_row_total',
            'row_weight',
            'price_incl_tax',
            'base_price_incl_tax',
            'product_tax_amount',
            'product_base_tax_amount',
            'product_tax_percent',
            'product_discount',
            'product_base_discount',
            'product_discount_percent',
            'is_child',
            'product_option');
    }

}
