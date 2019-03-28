<?php
$installer = $this;

$installer->startSetup();

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_appointments_log')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `user_id` int(11) default 0,
                `email` varchar(255) DEFAULT NULL,
                `user_type` varchar(255) DEFAULT NULL,
                `action` varchar(255) DEFAULT NULL,
                `date` timestamp,
                `input_data` text DEFAULT NULL,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");

$installer->endSetup();



