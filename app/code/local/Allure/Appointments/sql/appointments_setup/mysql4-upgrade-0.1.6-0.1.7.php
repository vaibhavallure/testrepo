<?php

$installer = $this;

$installer->startSetup();

/**
 * create inventory table
 */

$connection = $installer->getConnection();



$connection->addColumn(
    $this->getTable('allure_piercing_appointments'),//table name
    'ip',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$connection->addColumn(
    $this->getTable('allure_piercing_appointments'),//table name
    'ip_contry',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );
$connection->addColumn(
    $this->getTable('allure_piercing_appointments'),//table name
    'ip_region',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );

$connection->addColumn(
    $this->getTable('allure_piercing_appointments'),//table name
    'ip_city',      //column name
    'varchar(255)  DEFAULT null'  //datatype definition
    );
$connection->addColumn(
    $this->getTable('allure_piercing_appointments'),//table name
    'is_ip_processed',      //column name
    'varchar(255)  DEFAULT 0'  //datatype definition
    );



$installer->endSetup();

