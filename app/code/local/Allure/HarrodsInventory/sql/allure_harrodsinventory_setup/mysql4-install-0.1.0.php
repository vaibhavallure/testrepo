<?php


$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$installer->run("        
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_harrodsinventory_price')} (
                `row_id` int(10) UNSIGNED NOT NULL auto_increment,
                `productid` int(10) UNSIGNED NOT NULL,
                `price` DECIMAL (12,4) DEFAULT NULL,
                `updated_date` TIMESTAMP,
                 PRIMARY KEY  (`row_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");

$installer->endSetup();

