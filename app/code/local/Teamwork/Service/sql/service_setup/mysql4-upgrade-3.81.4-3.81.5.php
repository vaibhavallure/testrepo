<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_acss')}`
    ADD COLUMN `code` VARCHAR(10) NULL AFTER `acss_id`;
");
$installer->endSetup();