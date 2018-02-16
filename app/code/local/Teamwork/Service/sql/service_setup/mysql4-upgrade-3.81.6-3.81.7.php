<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_dcss')}`
    ADD COLUMN `code` VARCHAR(10) NULL AFTER `dcss_id`;
");
$installer->endSetup();