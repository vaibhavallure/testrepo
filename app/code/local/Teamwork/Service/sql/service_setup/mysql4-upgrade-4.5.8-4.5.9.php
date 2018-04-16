<?php

$installer = $this;
$installer->startSetup();
$weborderItemTableName = $this->getTable('service_weborder_item');
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
	ADD COLUMN `WebOrderProcessingArea` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 - WebOrders; 1 - SalesOrders' AFTER `Status`;

");
$installer->endSetup();
