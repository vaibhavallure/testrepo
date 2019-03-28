<?php


$installer = $this;

$installer->startSetup();


$connection = $installer->getConnection();

if (Mage::helper('core')->isModuleEnabled('Allure_Teamwork')){
    
    $installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_teamwork_duplicate_customer')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `customer_id` int(11) NOT NULL,
                `email` varchar(255) DEFAULT NULL,
                `temp_email` varchar(255) DEFAULT NULL,
                `cust_no` varchar(255) DEFAULT NULL,
                `cust_note` text DEFAULT NULL,
                `is_non_mag_cust` int default 0,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");  
}

$installer->endSetup();

