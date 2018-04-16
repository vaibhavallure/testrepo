<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_location')}`
	ADD COLUMN `status_changed` BOOLEAN NOT NULL default TRUE;
");
$installer->endSetup();