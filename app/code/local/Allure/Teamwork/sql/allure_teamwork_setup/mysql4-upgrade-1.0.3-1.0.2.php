<?php


$installer = $this;

$installer->startSetup();


$connection = $installer->getConnection();

if (Mage::helper('core')->isModuleEnabled('Allure_Teamwork')){
    
    $installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_teamwork_ar_cust_cp')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `cust_no` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `optional_email` varchar(255) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `fst_name` varchar(255) DEFAULT NULL,
                `lst_name` varchar(255) DEFAULT NULL,
                `addr1` varchar(255) DEFAULT NULL,
                `addr2` varchar(255) DEFAULT NULL,
                `city` varchar(255) DEFAULT NULL,
                `state` varchar(255) DEFAULT NULL,
                `zip_code` varchar(255) DEFAULT NULL,
                `phone` varchar(255) DEFAULT NULL,
                `country` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `str_id` varchar(255) DEFAULT NULL,
                `cust_note` text DEFAULT NULL,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");
    
    $installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_teamwork_log_table')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `operation` varchar(255) DEFAULT NULL,
                `page` int DEFAULT 0,
                `size` int DEFAULT 0,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");
}

$installer->endSetup();

