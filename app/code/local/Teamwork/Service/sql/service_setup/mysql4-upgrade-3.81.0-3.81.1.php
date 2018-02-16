<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_style')}`
    CHANGE COLUMN `taxcategory_id` `taxcategory` INT(1) NOT NULL DEFAULT '0' AFTER `ecomerce`;
    DROP TABLE `{$this->getTable('sevice_tax_category')}`;
");
$installer->endSetup();