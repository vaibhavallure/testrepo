<?php

$installer = $this;
$installer->startSetup();

$installmentsTable = $installer->getTable('bakerloo_payment/installment');
$installer->getConnection()->addColumn($installmentsTable, 'payment_data', 'text');

$installer->endSetup();
