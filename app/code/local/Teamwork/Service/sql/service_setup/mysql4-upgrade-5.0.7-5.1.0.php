<?php
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('service_chq')}`
        ADD COLUMN `host_document_id` CHAR(36) NULL DEFAULT NULL AFTER `try`,
        ADD INDEX `host_document_id` (`host_document_id`);
");
$installer->endSetup();