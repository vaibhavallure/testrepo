<?php


$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();


$connection->addColumn(
		$this->getTable('cataloginventory/stock_item'),//table name
		'po_sent',      //column name
		'int(11)  DEFAULT 0'  //datatype definition
		);

$connection->addColumn(
		$this->getTable('allure_inventory_receive_log'),//table name
		'po_id',      //column name
		'int(11)  DEFAULT 0'  //datatype definition
		);

$installer->endSetup();


