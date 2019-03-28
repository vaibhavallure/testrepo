<?php


$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
 
if (Mage::helper('core')->isModuleEnabled('Allure_Teamwork')){

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_teamwork_customer')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `customer_id` int(11) NOT NULL,
                `email` varchar(255) DEFAULT NULL,
                `teamwork_customer_id` varchar(255) DEFAULT NULL,
                `auto_gen_bill_id` varchar(255) DEFAULT NULL,
                `auto_gen_ship_id` varchar(255) DEFAULT NULL,
                `is_error` int(11) DEFAULT 0,
                `response` text,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
  ");  
}

$installer->endSetup();

