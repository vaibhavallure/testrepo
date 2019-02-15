<?php


$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$installer->run("        
       CREATE TABLE IF NOT EXISTS allure_harrodsinventory_zero_stock (
                `row_id` int(10) UNSIGNED NOT NULL auto_increment,
                `productid` int(10) UNSIGNED NOT NULL,
                `stock` int(10) NOT NULL,
                `updated_date` TIMESTAMP,
                 PRIMARY KEY  (`row_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");

$installer->endSetup();

