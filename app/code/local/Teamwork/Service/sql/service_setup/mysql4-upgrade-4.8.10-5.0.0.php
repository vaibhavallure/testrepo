<?php
$installer = $this;

$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('service_chq')}`;
CREATE TABLE `{$this->getTable('service_chq')}` (
	`entity_id` INT(11) NOT NULL AUTO_INCREMENT,
	`document_id` CHAR(36) NOT NULL,
    `parent_document_id` CHAR(36) NULL DEFAULT NULL,
	`api_type` VARCHAR(100) NOT NULL,
	`status` ENUM(
        'InQueue',
        'Validation',
        'InProcess',
        'Successful',
        'Error',
        'Pending'
    ) NOT NULL,
	`processed` INT(11) NOT NULL DEFAULT '0',
	`try` TINYINT(4) NOT NULL DEFAULT '1',
	`last_updated_time` DATETIME NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`document_id`),
	UNIQUE INDEX `entity_id` (`entity_id`),
	INDEX `status` (`status`),
	INDEX `api_type` (`api_type`),
	INDEX `last_updated_time` (`last_updated_time`),
    INDEX `parent_document_id` (`parent_document_id`)
)
COLLATE='utf8_general_ci' ENGINE=InnoDB;
");
$installer->endSetup();