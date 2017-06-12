<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'pos_product_line', 'text');

$installer->endSetup();