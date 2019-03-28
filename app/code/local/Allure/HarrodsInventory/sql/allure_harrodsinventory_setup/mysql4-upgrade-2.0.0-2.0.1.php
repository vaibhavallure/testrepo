<?php

$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$installer->run("        
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_harrodsinventory_product')} (
                `row_id` int(10) UNSIGNED NOT NULL auto_increment,
                `productid` int(10) UNSIGNED NOT NULL,
                `updated_date` TIMESTAMP,
                 PRIMARY KEY  (`row_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");

$installer->endSetup();

