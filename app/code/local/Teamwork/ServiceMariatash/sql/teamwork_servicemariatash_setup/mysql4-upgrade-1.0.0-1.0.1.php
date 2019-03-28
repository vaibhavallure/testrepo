<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
	ADD COLUMN `customlongtext16` TEXT NULL DEFAULT NULL AFTER `customlongtext10`,
	ADD COLUMN `customlongtext17` TEXT NULL DEFAULT NULL AFTER `customlongtext16`;
");
$installer->endSetup();