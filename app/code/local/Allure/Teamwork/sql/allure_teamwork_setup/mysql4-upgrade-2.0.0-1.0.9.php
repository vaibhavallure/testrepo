<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_teamwork_product_data')} (
                `entity_id` int  NOT NULL auto_increment,
                `tm_item_id` varchar(255) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `sku` varchar(255) DEFAULT NULL,
                `price` decimal(12,4) DEFAULT NULL,
                `salesforce_product_id` varchar(255) DEFAULT NULL,
                `salesforce_standard_pricebk` varchar(255) DEFAULT NULL, 
                `salesforce_wholesale_pricebk` varchar(255) DEFAULT NULL, 
                 PRIMARY KEY  (`entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


        
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_teamwork_sync_data')} (
                `entity_id` int  NOT NULL auto_increment,
                `tm_receipt_id` varchar(255) unique,
                `customer_status` varchar(255) DEFAULT NULL,
                `order_status` varchar(255) DEFAULT NULL,
                `invoice_status` varchar(255) DEFAULT NULL,
                `shipment_status` varchar(255) DEFAULT NULL,
                `creditmemo_status` varchar(255) DEFAULT NULL, 
                `tmdata` text DEFAULT NULL, 
                 PRIMARY KEY  (`entity_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");

$installer->endSetup();
