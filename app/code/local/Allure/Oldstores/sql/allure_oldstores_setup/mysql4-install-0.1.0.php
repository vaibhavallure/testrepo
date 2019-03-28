<?php


$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
 
if (Mage::helper('core')->isModuleEnabled('Allure_Oldstores')){

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_oldstores')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `old_store_id` int(11) NOT NULL,
                `old_store_name` varchar(255) DEFAULT NULL,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
  ");  
}

$installer->endSetup();

