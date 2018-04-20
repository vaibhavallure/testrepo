<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('service_items')}`
    ADD COLUMN `IsChargeItem` TINYINT(1) NULL DEFAULT NULL,
    ADD COLUMN `ChargeItemType` VARCHAR(255) NULL DEFAULT NULL,
    ADD COLUMN `EligibleForDiscount` TINYINT(1) NULL DEFAULT NULL,
    ADD COLUMN `NeverChargeShipping` TINYINT(1) NULL DEFAULT NULL
");

$installer->endSetup();
