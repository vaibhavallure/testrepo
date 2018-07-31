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
    $this->getTable('allure_virtual_store'),//table name
    'currency',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$connection->addColumn(
    $this->getTable('allure_virtual_store'),//table name
    'timezone',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$installer->endSetup();
