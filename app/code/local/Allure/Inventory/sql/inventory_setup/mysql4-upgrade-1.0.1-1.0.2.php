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
    $this->getTable('allure_purchase_order_item'),//table name
    'is_custom',      //column name
    'int(11)  DEFAULT 0'  //datatype definition
);

$installer->endSetup();

