<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_items')}`
    ADD COLUMN `skukey` VARCHAR(30) NULL DEFAULT NULL AFTER `length`;
");
$installer->endSetup();