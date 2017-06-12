<?php


$installer = $this;

$installer->startSetup();


$connection = $installer->getConnection();



$connection->addColumn(
		$this->getTable('allure_purchase_order_item'),//table name
		'remaining_qty',      //column name
		'int(11)  DEFAULT 0'  //datatype definition
		);

$installer->endSetup();


