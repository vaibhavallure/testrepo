<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_collection')}` DROP COLUMN `internal_id`;
ALTER TABLE `{$this->getTable('service_brand')}` DROP COLUMN `internal_id`;
ALTER TABLE `{$this->getTable('service_manufacturer')}` DROP COLUMN `internal_id`;
");
$installer->endSetup();