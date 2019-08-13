<?php
$installer = $this;

$installer->startSetup();

$installer->run("
         CREATE TABLE IF NOT EXISTS {$this->getTable('allure_appointment_customers')} (
                `id` int(11) unsigned NOT NULL auto_increment,
                `appointment_id` int(11) unsigned NOT NULL,
                `firstname` varchar(255) DEFAULT NULL,
                `lastname` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `phone` varchar(255) DEFAULT NULL,
                `install` int(11) default 0,
                `piercing` int(11) default 0,
                `sms_notification` int(1) default 0,
                 PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
  ");

$installer->endSetup();



