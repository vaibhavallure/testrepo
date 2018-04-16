<?php
$installer = $this;
$installer->startSetup();
$installer->run("

-- Dumping structure for table service_setting_mapping
DROP TABLE IF EXISTS `{$this->getTable('service_setting_mapping')}`;
CREATE TABLE `{$this->getTable('service_setting_mapping')}` (
    `name` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `required` TINYINT(1) NOT NULL DEFAULT '0',
    `type` VARCHAR(50) NOT NULL,
    `node` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('service_settings')}`
    CHANGE COLUMN `setting_value` `setting_value` TEXT NULL DEFAULT NULL AFTER `setting_name`;

ALTER TABLE `{$this->getTable('service_media')}`
    CHANGE COLUMN `media_name` `media_name` VARCHAR(255) NULL DEFAULT NULL AFTER `media_type`,
    ADD COLUMN `attribute1` CHAR(36) NULL DEFAULT NULL AFTER `media_name`,
    ADD COLUMN `attribute2` CHAR(36) NULL DEFAULT NULL AFTER `attribute1`,
    ADD COLUMN `attribute3` CHAR(36) NULL DEFAULT NULL AFTER `attribute2`;

ALTER TABLE `{$this->getTable('service_style')}`
    CHANGE COLUMN `date_available` `dateavailable` DATETIME NULL DEFAULT NULL AFTER `customtext6`;

ALTER TABLE `{$this->getTable('service_dcss')}`
    ADD PRIMARY KEY (`dcss_id`);
ALTER TABLE `{$this->getTable('service_acss')}`
    ADD PRIMARY KEY (`acss_id`);
ALTER TABLE `{$this->getTable('service_acss_level1')}`
    ADD PRIMARY KEY (`level1_id`);
ALTER TABLE `{$this->getTable('service_acss_level2')}`
    ADD PRIMARY KEY (`level2_id`);
ALTER TABLE `{$this->getTable('service_acss_level3')}`
    ADD PRIMARY KEY (`level3_id`);
ALTER TABLE `{$this->getTable('service_acss_level4')}`
    ADD PRIMARY KEY (`level4_id`);
ALTER TABLE `{$this->getTable('service_dcss_class')}`
    ADD PRIMARY KEY (`class_id`);
ALTER TABLE `{$this->getTable('service_dcss_department')}`
    ADD PRIMARY KEY (`department_id`);
ALTER TABLE `{$this->getTable('service_dcss_subclass1')}`
    ADD PRIMARY KEY (`subclass1_id`);
ALTER TABLE `{$this->getTable('service_dcss_subclass2')}`
    ADD PRIMARY KEY (`subclass2_id`);

ALTER TABLE `{$this->getTable('service_weborder_payment')}`
    CHANGE COLUMN `AccountNumber` `AccountNumber` VARCHAR(38) NULL DEFAULT NULL AFTER `EComPaymentMethod`;


-- Dumping structure for table sevice_tax_category
DROP TABLE IF EXISTS `{$this->getTable('sevice_tax_category')}`;
CREATE TABLE `{$this->getTable('sevice_tax_category')}` (
    `tax_category_id` CHAR(36) NOT NULL,
    `request_id` CHAR(36) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`tax_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('service_media')}`
    ADD INDEX `host_id_media_index_media_type` (`host_id`, `media_type`, `media_index`);

ALTER TABLE `{$this->getTable('service_media')}`
    DROP PRIMARY KEY,
    ADD INDEX `host_id_media_uri_media_index` (`host_id`, `media_uri`, `media_index`);

");
$installer->endSetup();