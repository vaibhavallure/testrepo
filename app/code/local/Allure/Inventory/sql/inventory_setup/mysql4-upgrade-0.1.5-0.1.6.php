<?php

$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
$installer->run("
			CREATE TABLE IF NOT EXISTS {$this->getTable('allure_lowstock_reports_log')} (
			`id` int(11) unsigned NOT NULL auto_increment,
			`sent_to` varchar(255) DEFAULT NULL,
			`store_id` int(11) DEFAULT NULL,
			`created_date` datetime default NULL,
			`path` varchar(255) DEFAULT NULL,
		   	PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");

$installer->endSetup();

