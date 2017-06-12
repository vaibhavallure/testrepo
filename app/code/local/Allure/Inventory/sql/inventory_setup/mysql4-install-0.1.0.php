<?php


$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
 
if (Mage::helper('core')->isModuleEnabled('Allure_Inventory')){

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_inventory_receive_log')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `product_id` int(11) NOT NULL,
             	`previous_qty` int(11) NOT NULL,
             	`added_qty` int(11) NOT NULL,
                `stock_id` int(11) NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `user_id` int(11) NOT NULL,
                PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
  ");  
}

$installer->endSetup();

