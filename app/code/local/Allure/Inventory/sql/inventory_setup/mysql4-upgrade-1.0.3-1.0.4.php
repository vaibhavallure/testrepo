<?php

$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
$installer->run("
			CREATE TABLE IF NOT EXISTS {$this->getTable('allure_inventory_purchase_draft')} (
			`id` int(11) unsigned NOT NULL auto_increment,
            `po_id` int(11) DEFAULT NULL,
			`item_id` int(11) DEFAULT NULL,
			`qty` int(11) DEFAULT NULL,
			`cost`  DOUBLE(16,2) DEFAULT NULL,
			`comment` varchar(255) DEFAULT NULL,
			`user_id` int(11) DEFAULT NULL,
			`store_id` int(11) DEFAULT NULL,
            `is_custom` int(11)  DEFAULT 0,
            `vendor_sku` varchar(255) DEFAULT NULL,
		   	PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
$installer->endSetup();

