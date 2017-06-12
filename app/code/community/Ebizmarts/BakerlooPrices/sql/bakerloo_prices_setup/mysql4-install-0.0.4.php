<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'pos_applied_taxes', 'text');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'pos_applied_taxes', 'text');

$installer->endSetup();