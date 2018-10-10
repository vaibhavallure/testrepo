<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

//create table - allure_salesforce_log
$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('allure_salesforce_log')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `object_type` varchar(255) DEFAULT null,
    `operation_state` int DEFAULT 0,
    `operation_name` varchar(255) DEFAULT null,
    `magento_id` int(11) DEFAULT 0,
    `response` text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


/**
 * add attribute to product i.e salesforce product id
 */
$catalogSetup = new Mage_Catalog_Model_Resource_Setup('core_setup');
$catalogSetup->addAttribute('catalog_product', 'salesforce_product_id', array(
    'group'                     => 'General',
    'label'                     => 'Salesforce Product Id',
    'note'                      => '',
    'default'                   => null,
    'type'                      => 'varchar',    //backend_type
    'input'                     => 'text',      //frontend_input
    'frontend_class'            => '',
    'backend'                   => '',
    'frontend'                  => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'                  => false,
    'apply_to'                  => '',
    'is_configurable'           => false,
    'visible_on_front'          => true,
    'used_in_product_listing'   => false
));

$catalogSetup->addAttribute('catalog_product', 'salesforce_standard_pricebk', array(
    'group'                     => 'General',
    'label'                     => 'Salesforce Standard Pricebook Id',
    'note'                      => '',
    'default'                   => null,
    'type'                      => 'varchar',    //backend_type
    'input'                     => 'text',      //frontend_input
    'frontend_class'            => '',
    'backend'                   => '',
    'frontend'                  => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'                  => false,
    'apply_to'                  => '',
    'is_configurable'           => false,
    'visible_on_front'          => true,
    'used_in_product_listing'   => false
));

$catalogSetup->addAttribute('catalog_product', 'salesforce_wholesale_pricebk', array(
    'group'                     => 'General',
    'label'                     => 'Salesforce Wholesale Pricebook Id',
    'note'                      => '',
    'default'                   => null,
    'type'                      => 'varchar',    //backend_type
    'input'                     => 'text',      //frontend_input
    'frontend_class'            => '',
    'backend'                   => '',
    'frontend'                  => '',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'                  => false,
    'apply_to'                  => '',
    'is_configurable'           => false,
    'visible_on_front'          => true,
    'used_in_product_listing'   => false
));

/**
 * add attribute to customer i.e salesforce customer id
 */
$customerSetup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );
$customerSetup->addAttribute('customer', 'salesforce_customer_id', array(
    'type'              => 'varchar',
    'input'             => 'text',
    'label'             => 'Salesforce Customer Id',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 0,
    'default'           => null,
    'visible_on_front'  => 1,
    'source'            =>   NULL,
    'comment'           => 'Salesforce Customer Id'
));

$customerSetup->addAttribute('customer', 'optional_email', array(
    'type'              => 'text',
    'input'             => 'text',
    'label'             => 'Optional Email',
    'global'            => 1,
    'visible'           => 1,
    'required'          => 0,
    'user_defined'      => 0,
    'default'           => null,
    'visible_on_front'  => 1,
    'source'            =>   NULL,
    'comment'           => 'Optional Email'
)); 

/**
 * add attribute to customer address i.e salesforce customer address id
 */
$customerSetup->addAttribute('customer_address', 'salesforce_address_id', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Salesforce Address Id',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));

/**
 * add field to order table i.e. salesforce_order_id
 */
$orderSetup = new Mage_Sales_Model_Resource_Setup('core_setup');
$orderSetup->addAttribute('order', 'salesforce_order_id', array(
    'type'          => 'varchar',
    'label'         => 'Salesforce Order Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute('order_item', 'salesforce_item_id', array(
    'type'          => 'varchar',
    'label'         => 'Salesforce Item Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute("invoice", "salesforce_invoice_id", array(
    'type'          => 'varchar',
    'label'         => 'Salesforce Invoice Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$orderSetup->addAttribute("shipment", "salesforce_shipment_id", array(
    'type'          => 'varchar',
    'label'         => 'Salesforce shipment Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$installer->run("ALTER TABLE sales_flat_creditmemo ADD COLUMN salesforce_creditmemo_id varchar(255) default null");

$installer->run("ALTER TABLE sales_flat_shipment_track ADD COLUMN salesforce_shipment_track_id varchar(255) default null");


$this->endSetup();




