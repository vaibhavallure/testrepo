<?php
$installer = $this;

$installer->startSetup();
$installer->run("

    ALTER TABLE `{$this->getTable('service_style_related')}`
    ADD COLUMN `relation_kind` VARCHAR(50) DEFAULT NULL AFTER `related_style_type`,
    ADD COLUMN `item_id` CHAR(36) DEFAULT NULL AFTER `style_id`,
    ADD COLUMN `related_item_id` CHAR(36) DEFAULT NULL AFTER `related_style_id`;
   
");
$installer->endSetup();