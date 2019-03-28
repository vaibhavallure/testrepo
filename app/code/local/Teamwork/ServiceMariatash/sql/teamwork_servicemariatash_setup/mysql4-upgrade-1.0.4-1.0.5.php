<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_media_dam_image')}`
	ADD COLUMN `base_item` TINYINT(1) NULL DEFAULT NULL AFTER `small`,
	ADD COLUMN `thumbnail_item` TINYINT(1) NULL DEFAULT NULL AFTER `base_item`,
	ADD COLUMN `small_item` TINYINT(1) NULL DEFAULT NULL AFTER `thumbnail_item`;
");
$installer->endSetup();