<?php

$installer = $this;

$installer->startSetup();

$fieldsTable = $installer->getConnection()->describeTable($installer->getTable('ecp_footlinks'));
if(!isset($fieldsTable['sort_order'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_footlinks'), 'sort_order', 'int(10) NULL default 0');
}

$installer->endSetup();
