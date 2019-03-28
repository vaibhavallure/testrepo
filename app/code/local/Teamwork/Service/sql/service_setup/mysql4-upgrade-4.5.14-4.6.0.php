<?php
$installer = $this;

$installer->startSetup();
$installer->run("

    ALTER TABLE `{$this->getTable('service_category')}`
    ADD COLUMN `customtext1` TEXT DEFAULT NULL AFTER `keywords`,
    ADD COLUMN `customtext2` TEXT DEFAULT NULL AFTER `customtext1`,
    ADD COLUMN `customtext3` TEXT DEFAULT NULL AFTER `customtext2`,
    ADD COLUMN `customtext4` TEXT DEFAULT NULL AFTER `customtext3`,
    ADD COLUMN `customdate1` DATETIME DEFAULT NULL AFTER `customtext4`,
    ADD COLUMN `customdate2` DATETIME DEFAULT NULL AFTER `customdate1`,
    ADD COLUMN `customdate3` DATETIME DEFAULT NULL AFTER `customdate2`,
    ADD COLUMN `customdate4` DATETIME DEFAULT NULL AFTER `customdate3`,
    ADD COLUMN `customnumber1` INT(11) DEFAULT NULL AFTER `customdate4`,
    ADD COLUMN `customnumber2` INT(11) DEFAULT NULL AFTER `customnumber1`,
    ADD COLUMN `customnumber3` INT(11) DEFAULT NULL AFTER `customnumber2`,
    ADD COLUMN `customnumber4` INT(11) DEFAULT NULL AFTER `customnumber3`,
    ADD COLUMN `customdeсimal1` DECIMAL(38,20) DEFAULT NULL AFTER `customnumber4`,
    ADD COLUMN `customdeсimal2` DECIMAL(38,20) DEFAULT NULL AFTER `customdeсimal1`,
    ADD COLUMN `customdeсimal3` DECIMAL(38,20) DEFAULT NULL AFTER `customdeсimal2`,
    ADD COLUMN `customdeсimal4` DECIMAL(38,20) DEFAULT NULL AFTER `customdeсimal3`,
    ADD COLUMN `customflag1` TINYINT(1) DEFAULT NULL  AFTER `customdeсimal4`,
    ADD COLUMN `customflag2` TINYINT(1) DEFAULT NULL  AFTER `customflag1`,
    ADD COLUMN `customflag3` TINYINT(1) DEFAULT NULL  AFTER `customflag2`,
    ADD COLUMN `customflag4` TINYINT(1) DEFAULT NULL  AFTER `customflag3`,
    ADD COLUMN `customlookup1` VARCHAR(255) DEFAULT NULL  AFTER `customflag4`,
    ADD COLUMN `customlookup2` VARCHAR(255) DEFAULT NULL  AFTER `customlookup1`,
    ADD COLUMN `customlookup3` VARCHAR(255) DEFAULT NULL  AFTER `customlookup2`,
    ADD COLUMN `customlookup4` VARCHAR(255) DEFAULT NULL  AFTER `customlookup3`;
    
    ALTER TABLE `{$this->getTable('service')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_acss')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_acss_level1')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_acss_level2')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_acss_level3')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_acss_level4')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_attribute_set')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_attribute_value')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_brand')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_category')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_channel')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_collection')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_collection_category')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_dcss')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_dcss_class')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_dcss_department')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_dcss_subclass1')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_dcss_subclass2')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_discount')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_fee')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_identifier')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_inventory')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_items')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_item_category')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_item_channel')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_item_collection')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_location')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_location_schedule')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_manufacturer')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_media')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_media_value')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_package')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_package_category')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_package_channel')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_package_collection')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_package_component')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_package_component_element')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_price')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_settings')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_setting_mapping')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_setting_payment')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_setting_shipping')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_status')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_status_items')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_status_shipping')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_style')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_style_category')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_style_channel')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_style_collection')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_style_related')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder_discount_reason')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder_fee')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder_item')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder_item_fee')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder_item_line_discount')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    ALTER TABLE `{$this->getTable('service_weborder_payment')}`
    ADD COLUMN `entity_id` INT NOT NULL AUTO_INCREMENT FIRST,
    ADD UNIQUE INDEX `entity_id` (`entity_id`);
    
    DROP TABLE IF EXISTS `{$this->getTable('service_setting_rich_content')}`;
    CREATE TABLE `{$this->getTable('service_setting_rich_content')}` (
    `entity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `attribute_id` SMALLINT(5) NOT NULL,
    `media_index` INT(10) NOT NULL,
    `channel_id` CHAR(36) NOT NULL,
    PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   
");
$installer->endSetup();