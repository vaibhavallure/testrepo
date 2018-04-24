<?php
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('service_weborder')}`
        CHANGE COLUMN `BillAddressType` `BillAddressType` VARCHAR(255) NULL DEFAULT '' AFTER `BillAddressId`,
        CHANGE COLUMN `ShipAddressType` `ShipAddressType` VARCHAR(255) NULL DEFAULT '' AFTER `ShipAddressId`;
");
$installer->endSetup();