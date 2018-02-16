<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_weborder')}`
 CHANGE COLUMN `RecCreated` `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `EComChannelId`;
");
$installer->endSetup();