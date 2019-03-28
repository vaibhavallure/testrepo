<?php 
$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();



$connection->addColumn(
    $this->getTable('allure_meta_information'),//table name
    'type',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
);
$connection->addColumn(
    $this->getTable('allure_meta_information'),//table name
    'image',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$connection->addColumn(
    $this->getTable('allure_meta_information'),//table name
    'site_name',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$connection->addColumn(
    $this->getTable('allure_meta_information'),//table name
    'url',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$installer->endSetup();

