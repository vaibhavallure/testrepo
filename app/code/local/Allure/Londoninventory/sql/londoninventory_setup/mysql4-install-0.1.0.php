<?php


$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
 
if (Mage::helper('core')->isModuleEnabled('Allure_Londoninventory')){

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_inventory_purchased')} (
          		 `id` int(11) unsigned NOT NULL auto_increment,
                `sku` varchar(255) default null,
                `qty` int(11) default 0,
                 PRIMARY KEY  (`id`)
             	
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
  ");  
}

$installer->endSetup();

