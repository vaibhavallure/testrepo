<?php
$installer = $this;
$installer->startSetup();

$fieldsOrder = $installer->getConnection()->describeTable($installer->getTable('ecp_reviews'));

if(!isset($fieldsOrder['sort_order'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_reviews'), 'sort_order', 'int NOT NULL default 0');
}

$installer->endSetup();