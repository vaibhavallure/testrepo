<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_location')}`
	ALTER `custom_text1` DROP DEFAULT,
	ALTER `custom_text2` DROP DEFAULT,
	ALTER `custom_text3` DROP DEFAULT,
	ALTER `custom_text4` DROP DEFAULT,
	ALTER `custom_text5` DROP DEFAULT,
	ALTER `custom_text6` DROP DEFAULT;
ALTER TABLE `{$this->getTable('service_location')}`
	CHANGE COLUMN `custom_text1` `custom_text1` VARCHAR(30) NULL AFTER `custom_integer6`,
	CHANGE COLUMN `custom_text2` `custom_text2` VARCHAR(30) NULL AFTER `custom_text1`,
	CHANGE COLUMN `custom_text3` `custom_text3` VARCHAR(30) NULL AFTER `custom_text2`,
	CHANGE COLUMN `custom_text4` `custom_text4` VARCHAR(30) NULL AFTER `custom_text3`,
	CHANGE COLUMN `custom_text5` `custom_text5` VARCHAR(30) NULL AFTER `custom_text4`,
	CHANGE COLUMN `custom_text6` `custom_text6` VARCHAR(30) NULL AFTER `custom_text5`;

");
$installer->endSetup();