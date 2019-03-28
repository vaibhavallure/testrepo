<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
	ADD COLUMN `inventype` VARCHAR(100) NULL DEFAULT NULL AFTER `no`;
");
$installer->endSetup();