<?php

$installer = $this;
$installer->startSetup();

$styleTable = $this->getTable('service_media_dam_style');
$styleImageTable = $this->getTable('service_media_dam_image');

$installer->run("
DROP TABLE IF EXISTS `{$styleTable}`;
CREATE TABLE `{$styleTable}` (
    `entity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `style_id` CHAR(36) NOT NULL,
    `style_no` VARCHAR(255) DEFAULT NULL,
    `attributeset1` CHAR(36) DEFAULT NULL,
    `attributeset1_name` VARCHAR(50) NULL DEFAULT NULL,
    `dam_marker` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`entity_id`),
    UNIQUE KEY (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$styleImageTable}`;
CREATE TABLE `{$styleImageTable}` (
    `entity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `style_id` CHAR(36) NOT NULL,
    `media_id` CHAR(36) NOT NULL,
    `url` VARCHAR(255) NULL DEFAULT NULL,
    `file_name` VARCHAR(255) NULL DEFAULT NULL,
    `sort` INT(11) NOT NULL DEFAULT '0',
    `excluded` TINYINT(1) NULL DEFAULT NULL,
    `base` TINYINT(1) NULL DEFAULT NULL,
    `thumbnail` TINYINT(1) NULL DEFAULT NULL,
    `small` TINYINT(1) NULL DEFAULT NULL,
    `attributevalue1` CHAR(36) DEFAULT NULL,
    `attributevalue1_name` CHAR(36) DEFAULT NULL,
    PRIMARY KEY (`entity_id`),
    FOREIGN KEY (`style_id`) REFERENCES `{$styleTable}` (`style_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();
