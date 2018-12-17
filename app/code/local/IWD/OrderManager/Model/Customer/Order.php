<?php
class IWD_OrderManager_Model_Customer_Order extends Mage_Sales_Model_Order
{
    const XML_PATH_CUSTOMER_ORDERS_RESENT_ORDER_GRID_COLUMN = 'iwd_ordermanager/customer_orders/resent_orders_grid_columns';
    const XML_PATH_CUSTOMER_ORDERS_ORDER_GRID_COLUMN = 'iwd_ordermanager/customer_orders/orders_grid_columns';
    
    public function getSelectedColumnsForRecentOrderGrid()
    {
        $selected_columns = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_ORDERS_RESENT_ORDER_GRID_COLUMN);
        return explode(",", $selected_columns);
    }
    
    public function getSelectedColumnsForOrderGrid()
    {
        $selected_columns = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_ORDERS_ORDER_GRID_COLUMN);
        return explode(",", $selected_columns);
    }
    
    public function getRecentOrdersCollectionForCurrentCustomer()
    {
        $selected_columns = $this->getSelectedColumnsForRecentOrderGrid();
        
        $collection = Mage::getResourceModel('sales/order_grid_collection')
        ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        
        $collection->addFieldToSelect(array('status', 'store_id', 'store_name', 'customer_id',
            'base_grand_total', 'base_total_paid', 'grand_total', 'total_paid', 'increment_id', 'base_currency_code',
            'order_currency_code', 'shipping_name', 'billing_name', 'created_at', 'updated_at')
            );
        
        return Mage::getModel('iwd_ordermanager/order_grid')->getOrdersCollection($selected_columns, $collection);
    }
    
    public function getOrdersCollectionForCurrentCustomer1()
    {
        $selected_columns = $this->getSelectedColumnsForOrderGrid();
        
        $collection = Mage::getResourceModel('sales/order_grid_collection')
        ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        
        //aws02 - changes add column old_store_id
        $gridColArr = array('status', 'store_id', 'store_name', 'customer_id',
            'base_grand_total', 'base_total_paid', 'grand_total', 'total_paid', 'increment_id', 'base_currency_code',
            'order_currency_code', 'shipping_name', 'billing_name', 'created_at', 'updated_at');
        
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $gridColArr[] = 'old_store_id';
        }
        
        $collection->addFieldToSelect(
            $gridColArr
        );
        
        return Mage::getModel('iwd_ordermanager/order_grid')->getOrdersCollection($selected_columns, $collection);
    }
    
    /**
     * show all order to customer 
     */
    public function getOrdersCollectionForCurrentCustomer()
    {
        $selected_columns = $this->getSelectedColumnsForOrderGrid();
        
        $collection = Mage::getResourceModel('sales/order_collection')
        ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        
        $gridColArr = array('status', 'store_id', 'store_name', 'customer_id',
            'base_grand_total', 'base_total_paid', 'grand_total', 'total_paid', 'increment_id', 'base_currency_code',
            'order_currency_code','created_at', 'updated_at');
        
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $gridColArr[] = 'old_store_id';
        }
        
        $collection->addFieldToSelect(
            $gridColArr
            );
        
        $tableName_sales_flat_order_address = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address');
        $collection->getSelect()->joinLeft(array('bill' => $tableName_sales_flat_order_address),
            'main_table.entity_id = bill.parent_id AND bill.address_type="billing"',
            array('concat(bill.firstname," ",bill.lastname) as billing_name')
            );
        $collection->getSelect()->joinLeft(array('ship' => $tableName_sales_flat_order_address),
            'main_table.entity_id = ship.parent_id AND ship.address_type="shipping"',
            array('concat(ship.firstname," ",ship.lastname) as shipping_name'));
        
        $tableName_sales_flat_order_item = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
        $collection->getSelect()->joinLeft(array('order_items' => $tableName_sales_flat_order_item),
            "main_table.entity_id = order_items.order_id",
            array('product_sku' => new Zend_Db_Expr('group_concat(DISTINCT order_items.sku SEPARATOR ", ")'),
                'ordered_products' => new Zend_Db_Expr('group_concat(DISTINCT order_items.name SEPARATOR ", ")'),
                'product_options' => new Zend_Db_Expr('group_concat(DISTINCT order_items.product_options SEPARATOR "|| ")'),
                'qty_ordered', 'qty_invoiced', 'qty_shipped', 'qty_canceled', 'qty_refunded'
            ));
        
        //dont display teamwork order to other admin user
        if (Mage::helper('core')->isModuleEnabled('Allure_AdminPermissions')){
            $helper = Mage::helper("allure_adminpermissions");
            if(!$helper->isShowTeamworkOrders()){
                $collection ->addFieldToFilter('main_table.create_order_method', array('nin' => array(2)));
            }
        }
        
        $collection->getSelect()->group('main_table.entity_id');
       
        return $collection;
    }
}