<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
	CHANGE COLUMN `CustomText1` `CustomText1` TEXT NULL DEFAULT NULL AFTER `CustomInteger6`,
	CHANGE COLUMN `CustomText2` `CustomText2` TEXT NULL DEFAULT NULL AFTER `CustomText1`,
	CHANGE COLUMN `CustomText3` `CustomText3` TEXT NULL DEFAULT NULL AFTER `CustomText2`,
	CHANGE COLUMN `CustomText4` `CustomText4` TEXT NULL DEFAULT NULL AFTER `CustomText3`,
	CHANGE COLUMN `CustomText5` `CustomText5` TEXT NULL DEFAULT NULL AFTER `CustomText4`,
	CHANGE COLUMN `CustomText6` `CustomText6` TEXT NULL DEFAULT NULL AFTER `CustomText5`;

ALTER TABLE `{$this->getTable('service_weborder_item')}`
	CHANGE COLUMN `CustomText1` `CustomText1` TEXT NULL DEFAULT NULL AFTER `CustomInteger6`,
	CHANGE COLUMN `CustomText2` `CustomText2` TEXT NULL DEFAULT NULL AFTER `CustomText1`,
	CHANGE COLUMN `CustomText3` `CustomText3` TEXT NULL DEFAULT NULL AFTER `CustomText2`,
	CHANGE COLUMN `CustomText4` `CustomText4` TEXT NULL DEFAULT NULL AFTER `CustomText3`,
	CHANGE COLUMN `CustomText5` `CustomText5` TEXT NULL DEFAULT NULL AFTER `CustomText4`,
	CHANGE COLUMN `CustomText6` `CustomText6` TEXT NULL DEFAULT NULL AFTER `CustomText5`;
");
$installer->endSetup();