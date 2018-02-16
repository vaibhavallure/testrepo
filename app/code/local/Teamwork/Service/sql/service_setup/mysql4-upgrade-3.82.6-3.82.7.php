<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_attribute_set')}`
    ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `name`;
ALTER TABLE `{$this->getTable('service_attribute_value')}`
    ADD COLUMN `attribute_alias` VARCHAR(100) NULL DEFAULT NULL AFTER `attribute_value`,
    ADD COLUMN `attribute_alias2` VARCHAR(100) NULL DEFAULT NULL AFTER `attribute_alias`;
");
$installer->endSetup();