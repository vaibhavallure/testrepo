<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_attribute_value')}`
	ADD COLUMN `order` INT NOT NULL DEFAULT '0' AFTER `attribute_alias2`;
");
$installer->endSetup();