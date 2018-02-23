<?php
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_media_dam_image')}`
	ADD COLUMN `label` VARCHAR(50) NULL;
");
$installer->endSetup();