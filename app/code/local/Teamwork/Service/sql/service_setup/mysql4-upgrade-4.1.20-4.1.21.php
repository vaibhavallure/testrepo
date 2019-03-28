<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('service_media')}`
    ADD COLUMN `request_id` CHAR(36) NOT NULL AFTER `channel_id`,
    ADD INDEX  `request_id` (`request_id`);

DROP TABLE IF EXISTS `{$this->getTable('service_media_value')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('service_media_value')}` (
    `media_uri` CHAR(36) NOT NULL,
    `internal_id` INT(10) UNSIGNED NOT NULL,
    `save_params` TEXT NOT NULL,
    INDEX `media_uri` (`media_uri`),
    INDEX `fk_service_media_value_catalog_product_entity_media_gallery` (`internal_id`),
    CONSTRAINT `fk_service_media_value_catalog_product_entity_media_gallery` FOREIGN KEY (`internal_id`) REFERENCES `{$this->getTable('catalog_product_entity_media_gallery')}` (`value_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
");



$installer->endSetup();