<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service')}`
    DROP COLUMN `chunk_group_count`;

ALTER TABLE `{$this->getTable('service_setting_payment')}`
    CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `description`;

ALTER TABLE `{$this->getTable('service_setting_shipping')}`
    CHANGE COLUMN `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `description`;

");
$installer->endSetup();