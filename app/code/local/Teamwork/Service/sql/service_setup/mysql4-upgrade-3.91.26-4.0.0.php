<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_status')}`
    ADD COLUMN `ShippingAmount` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `Status`;
");
$installer->endSetup();