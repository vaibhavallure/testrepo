<?php

$installer = $this;
$installer->startSetup();

$itemTables = $installer->getTable('sales/quote_item');
$installer->getConnection()->addColumn($itemTables, 'pos_item_guid', "VARCHAR(255) NULL DEFAULT NULL");

$debugTable = $installer->getTable('bakerloo_restful/debug');
$installer->getConnection()->addColumn($debugTable, 'request_url', "VARCHAR(255) NULL DEFAULT NULL");

$installer->endSetup();
