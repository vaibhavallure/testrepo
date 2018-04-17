<?php


$installer = $this;

$installer->startSetup();


$connection = $installer->getConnection();

    
$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_meta_information')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `term` varchar(255) DEFAULT NULL,
             	`title` varchar(255) DEFAULT NULL,
             	`description` text DEFAULT NULL,
                `status` int(11) DEFAULT 1,
                PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");

$installer->endSetup();

