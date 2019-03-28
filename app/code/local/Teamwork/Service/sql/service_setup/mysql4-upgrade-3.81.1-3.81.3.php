<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_category')}`
    CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL AFTER `category_name`;
");
$installer->endSetup();