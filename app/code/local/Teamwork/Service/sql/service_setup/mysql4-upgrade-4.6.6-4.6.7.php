<?php
$installer = $this;

$installer->startSetup();
$installer->run("

    DROP TABLE IF EXISTS `{$this->getTable('service_setting_attribute_mapping')}`;
    CREATE TABLE `{$this->getTable('service_setting_attribute_mapping')}` (
    `entity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `attribute_id` SMALLINT(5) NOT NULL,
    `field_id` INT(11) NOT NULL,
    UNIQUE INDEX (`entity_id`),
    INDEX `attribute_id` (`attribute_id`),
    INDEX `field_id` (`field_id`),
    PRIMARY KEY (`entity_id`)
    ) 
    ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS `{$this->getTable('service_chq_mappingfields')}`;
    CREATE TABLE `{$this->getTable('service_chq_mappingfields')}` (
    `entity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(100) NOT NULL,
    `value` VARCHAR(100) NOT NULL,
    `type` VARCHAR(100) NOT NULL,
    `type_id` VARCHAR(32) NOT NULL DEFAULT 'simple',
    `channel_id` CHAR(36) NOT NULL,
    UNIQUE INDEX (`entity_id`),
    PRIMARY KEY (`entity_id`,`channel_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
");
$installer->endSetup();