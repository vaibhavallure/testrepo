<?php


$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();

if (Mage::helper('core')->isModuleEnabled('Allure_Inventory')){
    
    $installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_inventory_po_vendor_work')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `po_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `vendor_sku` varchar(255) NOT NULL,
             	`shipped_qty` int(11) NOT NULL,
             	`ship_date`  datetime DEFAULT NULL,
              	`vendor_comment`  text DEFAULT NULL,
                `is_custom` int(11) DEFAULT 0,
                PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");
}

$installer->endSetup();

