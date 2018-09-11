<?php

$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
 
if (Mage::helper('core')->isModuleEnabled('Allure_AlertServices')){

$installer->run("
        
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_issues')} (
                `id` int(11) UNSIGNED NOT NULL auto_increment,
                `customer_email` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL,
                `type` varchar(255) DEFAULT NULL,
                `error_message` text DEFAULT NULL,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
  ");  
}

$installer->endSetup();