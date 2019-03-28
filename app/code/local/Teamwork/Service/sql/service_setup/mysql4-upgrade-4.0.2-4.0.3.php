<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
	ADD COLUMN `Status` VARCHAR(255) NOT NULL AFTER `OrderDate`;
");
$installer->endSetup();