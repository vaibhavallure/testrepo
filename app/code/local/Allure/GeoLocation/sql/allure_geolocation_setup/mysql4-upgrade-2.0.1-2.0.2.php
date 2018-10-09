<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('tax/tax_calculation_rate'), 'is_min_tax_amount', 'tinyint(1) unsigned NOT NULL default \'0\'');
$installer->getConnection()->addColumn($installer->getTable('tax/tax_calculation_rate'), 'min_tax_amount', 'varchar(255) default NULL');
$installer->endSetup();
