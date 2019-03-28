<?php
$installer = $this;
$installer->startSetup();
$installer->run("

ALTER TABLE `{$this->getTable('service')}`
    CHANGE COLUMN `status` `status` ENUM('new', 'processing', 'done') NOT NULL DEFAULT 'new' AFTER `channel_id`,
    ADD COLUMN `start` DATETIME NULL DEFAULT NULL AFTER `total_chunks`,
    ADD COLUMN `end` DATETIME NULL DEFAULT NULL AFTER `start`,
    ADD COLUMN `response` TEXT NULL DEFAULT NULL AFTER `end`,
    DROP COLUMN `type`;
");
$installer->endSetup();