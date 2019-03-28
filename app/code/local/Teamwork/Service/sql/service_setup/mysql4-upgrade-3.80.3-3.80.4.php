<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
    ADD COLUMN `taxcategory_id` CHAR(36) NULL DEFAULT NULL AFTER `ecomerce`;
");
$installer->endSetup();