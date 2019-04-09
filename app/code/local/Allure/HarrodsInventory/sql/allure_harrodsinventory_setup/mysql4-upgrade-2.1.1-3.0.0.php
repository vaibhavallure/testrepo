<?php


$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$installer->run("        
       CREATE TABLE IF NOT EXISTS allure_harrodsinventory_file_transfer (
                `row_id` int(10) UNSIGNED NOT NULL auto_increment,
                `file` VARCHAR(200),
                `date` TIMESTAMP,
                 PRIMARY KEY  (`row_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");

$installer->endSetup();

