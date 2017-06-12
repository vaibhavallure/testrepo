<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$fieldsOrder = $installer->getConnection()->describeTable($installer->getTable('newsletter_subscriber'));
if(!isset($fieldsOrder['first_name'])){
    $installer->getConnection()->addColumn($installer->getTable('newsletter_subscriber'), 'first_name', 'varchar(150) character set latin1 collate latin1_general_ci NOT NULL default ""');
}

if(!isset($fieldsOrder['last_name'])){
    $installer->getConnection()->addColumn($installer->getTable('newsletter_subscriber'), 'last_name', 'varchar(150) character set latin1 collate latin1_general_ci NOT NULL default ""');
}

if(!isset($fieldsOrder['country'])){
    $installer->getConnection()->addColumn($installer->getTable('newsletter_subscriber'), 'country', 'varchar(100) character set latin1 collate latin1_general_ci NOT NULL default ""');
}

$installer->endSetup();
