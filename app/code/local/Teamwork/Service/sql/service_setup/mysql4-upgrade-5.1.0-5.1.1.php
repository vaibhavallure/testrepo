<?php
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('service_chq')}`
        ADD COLUMN `additional_data` TEXT NULL DEFAULT NULL AFTER `updated_at`;
");
$installer->endSetup();