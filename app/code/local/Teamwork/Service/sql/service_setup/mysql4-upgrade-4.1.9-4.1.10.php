<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_media')}`
	ADD COLUMN `direct_uri` VARCHAR(255) default NULL;
");
$installer->endSetup();