<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
	ADD COLUMN `custommultiselect1` MEDIUMTEXT NULL DEFAULT NULL AFTER `customlongtext17`,
	ADD COLUMN `custommultiselect2` MEDIUMTEXT NULL DEFAULT NULL AFTER `custommultiselect1`;
");
$installer->endSetup();