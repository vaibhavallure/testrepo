<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_location')}`
    ADD COLUMN `custom_lookup2` VARCHAR(255) NULL DEFAULT NULL AFTER `custom_lookup1`;
");
$installer->endSetup();