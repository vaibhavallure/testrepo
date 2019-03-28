<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service')}`
    CHANGE COLUMN `status` `status` ENUM('new','processing','done','error') NOT NULL DEFAULT 'new' AFTER `channel_id`;
");
$installer->endSetup();