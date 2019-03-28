<?php
/**
 * Created by PhpStorm.
 * User: ajay
 * Date: 2/8/17
 * Time: 4:22 PM
 */

$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();



$connection->addColumn(
    $this->getTable('allure_inventory_receive_log'),//table name
    'cost',      //column name
    'DOUBLE(16,2) DEFAULT 0'  //datatype definition
);

$installer->endSetup();

