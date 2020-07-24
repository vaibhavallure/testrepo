<?php
$installer = $this;
$installer->startSetup();

$installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_waitwhile_booking')} (
            `booking_id` int(11) UNSIGNED NOT NULL auto_increment,
            `appointment_id` int(11) UNSIGNED NOT NULL,
            `waitwhile_booking_id` varchar(255) NOT NULL,
            PRIMARY KEY(`booking_id`),
            FOREIGN KEY(`appointment_id`) REFERENCES `allure_piercing_appointments`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_waitwhile_services')} (
            `service_id` smallint(5) UNSIGNED NOT NULL auto_increment,
            `waitwhile_service_id` varchar(255) NOT NULL,
            `code` varchar(100) NOT NULL,
            `name` varchar(200) NOT NULL,
            `store_id` smallint(5) NOT NULL,
            PRIMARY KEY  (`service_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_waitwhile_localization')} (
            `locale_id` smallint(5) UNSIGNED NOT NULL auto_increment,
            `waitwhile_locale_id` varchar(255) NOT NULL,
            `store_id` smallint(5) NOT NULL,
            PRIMARY KEY  (`locale_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  "); 
 
$installer->endSetup();

