<?php
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('service_style')}`
        ADD COLUMN `order_cost` DECIMAL(38,20) NOT NULL DEFAULT '0' AFTER `url_key`;
        
    ALTER TABLE `{$this->getTable('service_items')}`
        ADD COLUMN `order_cost` DECIMAL(38,20) NOT NULL DEFAULT '0' AFTER `url_key`;
");
$installer->endSetup();