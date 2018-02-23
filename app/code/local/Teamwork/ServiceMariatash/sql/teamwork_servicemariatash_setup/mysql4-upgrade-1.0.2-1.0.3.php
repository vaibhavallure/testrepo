<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_items')}`
	ADD COLUMN `c_vlu` VARCHAR(30) NULL DEFAULT NULL AFTER `customtext6`;
");
$installer->endSetup();