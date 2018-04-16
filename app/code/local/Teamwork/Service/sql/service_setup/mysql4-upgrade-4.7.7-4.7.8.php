<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
	ADD COLUMN `customlongtext1` TEXT NULL DEFAULT NULL AFTER `customtext6`,
	ADD COLUMN `customlongtext2` TEXT NULL DEFAULT NULL AFTER `customlongtext1`,
	ADD COLUMN `customlongtext3` TEXT NULL DEFAULT NULL AFTER `customlongtext2`,
	ADD COLUMN `customlongtext4` TEXT NULL DEFAULT NULL AFTER `customlongtext3`,
	ADD COLUMN `customlongtext5` TEXT NULL DEFAULT NULL AFTER `customlongtext4`,
	ADD COLUMN `customlongtext6` TEXT NULL DEFAULT NULL AFTER `customlongtext5`,
	ADD COLUMN `customlongtext7` TEXT NULL DEFAULT NULL AFTER `customlongtext6`,
	ADD COLUMN `customlongtext8` TEXT NULL DEFAULT NULL AFTER `customlongtext7`,
	ADD COLUMN `customlongtext9` TEXT NULL DEFAULT NULL AFTER `customlongtext8`,
	ADD COLUMN `customlongtext10` TEXT NULL DEFAULT NULL AFTER `customlongtext9`;
");
$installer->endSetup();