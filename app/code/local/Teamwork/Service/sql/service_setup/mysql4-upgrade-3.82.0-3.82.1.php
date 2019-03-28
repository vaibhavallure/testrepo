<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_category')}`
    ALTER `changed` DROP DEFAULT;
ALTER TABLE `{$this->getTable('service_category')}`
    CHANGE COLUMN `changed` `changed` INT(10) NOT NULL AFTER `description`;
");
$installer->endSetup();