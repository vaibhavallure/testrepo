<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service')}`
    ADD COLUMN `last_update` DATETIME NULL DEFAULT NULL AFTER `end`;
");
$installer->endSetup();