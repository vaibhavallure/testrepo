<?php
$installer = $this;

$installer->startSetup();

$tableName = $this->getTable('service_weborder');
if (!$installer->getConnection()->tableColumnExists($tableName, 'GuestCheckout')) {
    $installer->run("
    ALTER TABLE `{$tableName}`
        ADD COLUMN `GuestCheckout` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `Status`;   
    ");
}

$installer->endSetup();
