<?php

$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
$installer->run("
			CREATE TABLE IF NOT EXISTS {$this->getTable('allure_purchase_order')} (
			`po_id` int(11) unsigned NOT NULL auto_increment,
			`ref_no` varchar(255) DEFAULT NULL,
			`vendor_id` int(11) DEFAULT NULL,
			`vendor_name` varchar(255) DEFAULT NULL,
			`status` varchar(255) DEFAULT NULL,
			`admin_comment` varchar(255) DEFAULT NULL,
			`vendor_comment` varchar(255) DEFAULT NULL,
			`total_amount` DOUBLE(16,2) DEFAULT NULL,
			`paid_amount` DOUBLE(16,2) DEFAULT NULL,
			`pending_amount` DOUBLE(16,2) DEFAULT NULL,
			`stock_id` int(11) DEFAULT NULL,
			`created_date` datetime default NULL,
			`updated_date` datetime default NULL,
		   	PRIMARY KEY  (`po_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			
			
			
			CREATE TABLE IF NOT EXISTS {$this->getTable('allure_purchase_order_item')} (
			`id` int(11) unsigned NOT NULL auto_increment,
			`po_id` int(11) unsigned NOT NULL,
			`ref_no` varchar(255) DEFAULT NULL,
			`product_id` int(11) unsigned NOT NULL,
			`requested_qty` int(11) DEFAULT NULL,
			`proposed_qty` int(11) DEFAULT NULL,
			`requested_delivery_date` datetime default NULL,
			`proposed_delivery_date` datetime default NULL,
			`status` varchar(255) DEFAULT NULL,
			`admin_comment` varchar(255) DEFAULT NULL,
			`vendor_comment` varchar(255) DEFAULT NULL,
			`total_amount` DOUBLE(16,2) DEFAULT NULL,
			`stock_id` int(11) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			 
			
			CREATE TABLE IF NOT EXISTS {$this->getTable('allure_purchase_order_log')} (
			`id` int(11) unsigned NOT NULL auto_increment,
			`po_id` int(11) unsigned NOT NULL,
			`ref_no` varchar(255) DEFAULT NULL,
			`date` datetime default NULL,
			`user_id` int(11) unsigned NOT NULL,
			`total_amount` DOUBLE(16,2)  NOT NULL,
			`vendor_id` varchar(255) DEFAULT NULL,
			`stock_id` int(11) DEFAULT NULL,
			PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			
			");

$installer->endSetup();

