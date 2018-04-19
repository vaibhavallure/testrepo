<?php
$installer = $this;

$installer->startSetup();

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_piercing_price')} (
                `price_id` int(11) unsigned NOT NULL auto_increment,
                `type` varchar(255) DEFAULT NULL,
                `service_cost` varchar(255) DEFAULT NULL,
                `jewelry_start_at` varchar(255) DEFAULT NULL,
                `store_id` int(11) default 0,
                 PRIMARY KEY  (`price_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");

$installer->endSetup();



