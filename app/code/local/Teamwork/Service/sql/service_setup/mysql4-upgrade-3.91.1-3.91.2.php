<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service')}`
    CHANGE COLUMN `status` `status` ENUM('loading','new','processing','done','error','reindex') NOT NULL DEFAULT 'loading' AFTER `channel_id`;
");
$installer->endSetup();