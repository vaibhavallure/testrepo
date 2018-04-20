<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_category')}`
    ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_active`;
");
$installer->endSetup();