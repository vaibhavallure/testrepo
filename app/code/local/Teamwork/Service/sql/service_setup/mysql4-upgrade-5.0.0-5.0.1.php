<?php
$installer = $this;

$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('service_location_status')}`;
CREATE TABLE `{$this->getTable('service_location_status')}` (
	`entity_id` INT(11) NOT NULL AUTO_INCREMENT,
	`location_id` CHAR(36) NOT NULL,
	`channel_id` CHAR(36) NOT NULL,
	`enabled` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`location_id`, `channel_id`),
	UNIQUE INDEX `entity_id` (`entity_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

INSERT INTO `{$this->getTable('service_location_status')}` (location_id,channel_id,enabled)
SELECT location_id,channel_id,enabled FROM service_location;

DELETE loc1
    FROM `{$this->getTable('service_location')}` loc1
    JOIN `{$this->getTable('service_location')}` loc2 ON loc1.location_id=loc2.location_id AND loc1.channel_id != loc2.channel_id
WHERE loc1.entity_id>loc2.entity_id;

ALTER TABLE `{$this->getTable('service_location')}`
    DROP PRIMARY KEY,
	DROP COLUMN `channel_id`,
    DROP COLUMN `enabled`,
ADD PRIMARY KEY (`location_id`);

DELETE rel1
    FROM `{$this->getTable('service_style_related')}` rel1
    JOIN `{$this->getTable('service_style_related')}` rel2 ON rel1.style_id=rel2.style_id AND rel1.item_id=rel2.item_id AND rel1.related_style_id=rel2.related_style_id AND rel1.related_item_id=rel2.related_item_id AND rel1.related_style_type=rel2.related_style_type AND rel1.relation_kind=rel2.relation_kind AND rel1.channel_id!=rel2.channel_id
WHERE rel1.entity_id<rel2.entity_id;

ALTER TABLE `{$this->getTable('service_style_related')}`
	DROP COLUMN `channel_id`,
	DROP COLUMN `request_id`,
ADD INDEX `related_style_type` (`related_style_type`);

DROP TABLE IF EXISTS `{$this->getTable('service_discount_status')}`;
CREATE TABLE `{$this->getTable('service_discount_status')}` (
	`entity_id` INT(11) NOT NULL AUTO_INCREMENT,
	`discount_id` CHAR(36) NOT NULL,
	`channel_id` CHAR(36) NOT NULL,
	`enabled` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`discount_id`, `channel_id`),
	UNIQUE INDEX `entity_id` (`entity_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

INSERT INTO `{$this->getTable('service_discount_status')}` (discount_id,channel_id)
	SELECT dis.discount_id, ch.channel_id
	FROM `{$this->getTable('service_discount')}` dis
JOIN `{$this->getTable('service_channel')}` ch;

DROP TABLE IF EXISTS `{$this->getTable('service_fee_status')}`;
CREATE TABLE `{$this->getTable('service_fee_status')}` (
	`entity_id` INT(11) NOT NULL AUTO_INCREMENT,
	`fee_id` CHAR(36) NOT NULL,
	`channel_id` CHAR(36) NOT NULL,
	`enabled` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`fee_id`, `channel_id`),
	UNIQUE INDEX `entity_id` (`entity_id`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

INSERT INTO `{$this->getTable('service_fee_status')}` (fee_id,channel_id)
    SELECT fee.fee_id, ch.channel_id
    FROM `{$this->getTable('service_fee')}` fee
JOIN `{$this->getTable('service_channel')}` ch;

ALTER TABLE `{$this->getTable('service_dcss')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_dcss_class')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_dcss_department')}`
	DROP COLUMN `request_id`;
  
ALTER TABLE `{$this->getTable('service_dcss_subclass1')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_dcss_subclass2')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_acss')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_acss_level1')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_acss_level2')}`
	DROP COLUMN `request_id`;
  
ALTER TABLE `{$this->getTable('service_acss_level3')}`
	DROP COLUMN `request_id`;
    
ALTER TABLE `{$this->getTable('service_acss_level4')}`
	DROP COLUMN `request_id`;

ALTER TABLE `{$this->getTable('service_manufacturer')}`
	DROP COLUMN `request_id`;

ALTER TABLE `{$this->getTable('service_brand')}`
	DROP COLUMN `request_id`;

ALTER TABLE `{$this->getTable('service_identifier')}`
	DROP COLUMN `request_id`;

ALTER TABLE `{$this->getTable('service_discount')}`
	DROP INDEX `code`,
	ADD INDEX `code` (`code`);

ALTER TABLE `{$this->getTable('service_attribute_set')}`
	CHANGE COLUMN `code` `code` VARCHAR(30) NULL DEFAULT NULL AFTER `internal_id`;
    
ALTER TABLE `{$this->getTable('service_setting_attribute_mapping')}`
	ADD COLUMN `channel_id` CHAR(36) NOT NULL DEFAULT '' AFTER `attribute_id`,
	DROP PRIMARY KEY;

UPDATE {$this->getTable('service_setting_attribute_mapping')} map
    JOIN `{$this->getTable('service_chq_mappingfields')}` mapfld ON map.field_id=mapfld.entity_id
    SET map.channel_id=mapfld.channel_id;

UPDATE {$this->getTable('service_setting_attribute_mapping')} map
    JOIN `{$this->getTable('service_chq_mappingfields')}` mapfld1 ON map.field_id=mapfld1.entity_id
    JOIN `{$this->getTable('service_chq_mappingfields')}` mapfld2 ON mapfld1.value=mapfld2.value  and mapfld1.entity_id>mapfld2.entity_id
    SET map.field_id=mapfld2.entity_id;

DELETE map2
    FROM `{$this->getTable('service_chq_mappingfields')}` map1
    JOIN `{$this->getTable('service_chq_mappingfields')}` map2 ON map1.value=map2.value
    WHERE map1.entity_id<map2.entity_id;

ALTER TABLE `{$this->getTable('service_chq_mappingfields')}`
	ALTER `value` DROP DEFAULT;
    
ALTER TABLE `{$this->getTable('service_chq_mappingfields')}`
	CHANGE COLUMN `value` `value` VARCHAR(100) NOT NULL AFTER `entity_id`,
	DROP COLUMN `channel_id`,
	DROP PRIMARY KEY,
    ADD PRIMARY KEY (`value`);
");
$installer->endSetup();