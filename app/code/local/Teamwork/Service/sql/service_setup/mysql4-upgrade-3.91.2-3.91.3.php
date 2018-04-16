<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_media')}`
    ADD COLUMN `order` INT NOT NULL DEFAULT '0' AFTER `media_sub_type`;
");
$installer->endSetup();