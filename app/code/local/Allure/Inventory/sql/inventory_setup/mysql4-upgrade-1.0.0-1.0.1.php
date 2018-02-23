<?php
/**
 * Created by PhpStorm.
 * User: ajay
 * Date: 2/8/17
 * Time: 4:09 PM
 */
$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();
$connection->addColumn(
    $this->getTable('allure_inventory_purchase_tmp'),//table name
    'is_custom',      //column name
    'int(11)  DEFAULT 0'  //datatype definition
);

$installer->run("
			CREATE TABLE IF NOT EXISTS {$this->getTable('allure_inventory_custom_item')} (
			`id` int(11) unsigned NOT NULL auto_increment,
			`sku` varchar(255)  DEFAULT NULL,
			`name` varchar(255) DEFAULT NULL,
			`cost`  DOUBLE(16,2) DEFAULT NULL,
		   	PRIMARY KEY  (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");

$installer->endSetup();

