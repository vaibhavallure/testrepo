<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_setting_attribute_mapping')}`
	ADD COLUMN `push_once` TINYINT(1) NOT NULL DEFAULT 0 AFTER `field_id`;
");
$installer->endSetup();