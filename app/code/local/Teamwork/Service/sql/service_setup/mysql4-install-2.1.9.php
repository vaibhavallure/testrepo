<?php
$installer = $this;
$installer->startSetup();

$configObject = Mage::getConfig();

$user = Mage::getModel('api/user');
$user->setWebsiteId(1);

$user->loadByUsername((string)$configObject->getNode('teamwork_service/api_user/username'));

if(!$user->getData('user_id'))
{
    $roleCollection = Mage::getModel('api/roles')->getCollection();
    $roleName = (string)$configObject->getNode('teamwork_service/api_user/role');
    $roleCollection->addFieldToFilter('role_name', $roleName);
    $roleCollection->addFieldToFilter('role_type', 'G');
    if ($roleCollection->count()) {
        $role = $roleCollection->getFirstItem();
    } else {
        $role = Mage::getModel('api/roles')
            ->setName((string)$configObject->getNode('teamwork_service/api_user/role'))
            ->setPid(false)
            ->setRoleType('G')->save();
    }

    Mage::getModel("api/rules")
        ->setRoleId($role->getId())
        ->setResources(array('all'))
    ->saveRel();

    $user->setData(array(
        'username'                => (string)$configObject->getNode('teamwork_service/api_user/username'),
        'firstname'                => (string)$configObject->getNode('default/trans_email/ident_general/name'),
        'lastname'                => '',
        'email'                    => (string)$configObject->getNode('default/trans_email/ident_general/email'),
        'api_key'                => (string)$configObject->getNode('teamwork_service/api_user/default_key'),
        'api_key_confirmation'    => (string)$configObject->getNode('teamwork_service/api_user/default_key'),
        'is_active'                => 1,
        'user_roles'            => '',
        'assigned_user_role'    => '',
        'role_name'                => $roleName,
        'roles'                    => array($role->getId())
    ));
    $user->save()->load($user->getId());

    $user->setRoleIds(array($role->getId()))
       ->setRoleUserId($user->getUserId())
    ->saveRelations();
}

$installer->run("
-- Dumping structure for table service
DROP TABLE IF EXISTS `{$this->getTable('service')}`;
CREATE TABLE `{$this->getTable('service')}` (
  `request_id` CHAR(36) NOT NULL,
  `rec_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel_id` CHAR(36) NOT NULL,
  `status` VARCHAR(255) DEFAULT NULL,
  `type` VARCHAR(255) NOT NULL,
  `chunk_group_count` INT(10) NOT NULL DEFAULT '0',
  `chunk` INT(10) NOT NULL DEFAULT '1',
  `total_chunks` INT(10) NOT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_acss
DROP TABLE IF EXISTS `{$this->getTable('service_acss')}`;
CREATE TABLE `{$this->getTable('service_acss')}` (
  `acss_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `level1_id` CHAR(36) DEFAULT NULL,
  `level2_id` CHAR(36) DEFAULT NULL,
  `level3_id` CHAR(36) DEFAULT NULL,
  `level4_id` CHAR(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_acss_level1
DROP TABLE IF EXISTS `{$this->getTable('service_acss_level1')}`;
CREATE TABLE `{$this->getTable('service_acss_level1')}` (
  `level1_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_acss_level2
DROP TABLE IF EXISTS `{$this->getTable('service_acss_level2')}`;
CREATE TABLE `{$this->getTable('service_acss_level2')}` (
  `level2_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_acss_level3
DROP TABLE IF EXISTS `{$this->getTable('service_acss_level3')}`;
CREATE TABLE `{$this->getTable('service_acss_level3')}` (
  `level3_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_acss_level4
DROP TABLE IF EXISTS `{$this->getTable('service_acss_level4')}`;
CREATE TABLE `{$this->getTable('service_acss_level4')}` (
  `level4_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_attribute_set
DROP TABLE IF EXISTS `{$this->getTable('service_attribute_set')}`;
CREATE TABLE `{$this->getTable('service_attribute_set')}` (
  `attribute_set_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  `name` VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (`attribute_set_id`),
  INDEX `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_attribute_value
DROP TABLE IF EXISTS `{$this->getTable('service_attribute_value')}`;
CREATE TABLE `{$this->getTable('service_attribute_value')}` (
  `attribute_value_id` CHAR(36) NOT NULL,
  `attribute_set_id` CHAR(36) NOT NULL,
  `attribute_value` VARCHAR(100) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  PRIMARY KEY (`attribute_value_id`,`attribute_set_id`),
  KEY `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_brand
DROP TABLE IF EXISTS `{$this->getTable('service_brand')}`;
CREATE TABLE `{$this->getTable('service_brand')}` (
  `brand_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  PRIMARY KEY (`brand_id`),
  INDEX `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_category
DROP TABLE IF EXISTS `{$this->getTable('service_category')}`;
CREATE TABLE `{$this->getTable('service_category')}` (
  `category_id` CHAR(36) NOT NULL,
  `channel_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  `parent_id` CHAR(36) NOT NULL,
  `category_name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `changed` DECIMAL(14,4) NOT NULL,
  `display_order` INT(10) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`,`channel_id`),
  KEY `request_id_channel_id` (`request_id`,`channel_id`),
  INDEX `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_channel
DROP TABLE IF EXISTS `{$this->getTable('service_channel')}`;
CREATE TABLE `{$this->getTable('service_channel')}` (
  `channel_id` CHAR(36) NOT NULL,
  `channel_name` VARCHAR(100) NOT NULL,
  `qty_location` CHAR(36) DEFAULT NULL,
  PRIMARY KEY (`channel_id`),
  INDEX `channel_name` (`channel_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_collection
DROP TABLE IF EXISTS `{$this->getTable('service_collection')}`;
CREATE TABLE `{$this->getTable('service_collection')}` (
  `collection_id` CHAR(36) NOT NULL,
  `name` VARCHAR(128) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  PRIMARY KEY (`collection_id`),
  INDEX `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_collection_category
DROP TABLE IF EXISTS `{$this->getTable('service_collection_category')}`;
CREATE TABLE `{$this->getTable('service_collection_category')}` (
  `collection_id` CHAR(36) NOT NULL,
  `category_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`collection_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_dcss
DROP TABLE IF EXISTS `{$this->getTable('service_dcss')}`;
CREATE TABLE `{$this->getTable('service_dcss')}` (
  `dcss_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `department_id` CHAR(36) DEFAULT NULL,
  `class_id` CHAR(36) DEFAULT NULL,
  `subclass1_id` CHAR(36) DEFAULT NULL,
  `subclass2_id` CHAR(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_dcss_department
DROP TABLE IF EXISTS `{$this->getTable('service_dcss_department')}`;
CREATE TABLE `{$this->getTable('service_dcss_department')}` (
  `department_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_dcss_class
DROP TABLE IF EXISTS `{$this->getTable('service_dcss_class')}`;
CREATE TABLE `{$this->getTable('service_dcss_class')}` (
  `class_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_dcss_subclass1
DROP TABLE IF EXISTS `{$this->getTable('service_dcss_subclass1')}`;
CREATE TABLE `{$this->getTable('service_dcss_subclass1')}` (
  `subclass1_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_dcss_subclass2
DROP TABLE IF EXISTS `{$this->getTable('service_dcss_subclass2')}`;
CREATE TABLE `{$this->getTable('service_dcss_subclass2')}` (
  `subclass2_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_discount
DROP TABLE IF EXISTS `{$this->getTable('service_discount')}`;
CREATE TABLE `{$this->getTable('service_discount')}` (
  `discount_id` CHAR(36) NOT NULL,
  `code` VARCHAR(30) NOT NULL,
  `description` VARCHAR(100) DEFAULT NULL,
  `type` TINYINT(1) NOT NULL DEFAULT '0',
  `default_perc` DECIMAL(38,20) DEFAULT NULL,
  PRIMARY KEY (`discount_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_fee
DROP TABLE IF EXISTS `{$this->getTable('service_fee')}`;
CREATE TABLE `{$this->getTable('service_fee')}` (
  `fee_id` CHAR(36) NOT NULL DEFAULT '',
  `code` CHAR(255) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `alias` VARCHAR(128) DEFAULT NULL,
  `item_level` TINYINT(1) NOT NULL,
  `global_level` TINYINT(1) NOT NULL,
  `default_perc` DECIMAL(38,20) DEFAULT NULL,
  `default_amount` DECIMAL(38,20) DEFAULT NULL,
  PRIMARY KEY (`fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_identifier
DROP TABLE IF EXISTS `{$this->getTable('service_identifier')}`;
CREATE TABLE `{$this->getTable('service_identifier')}` (
  `identifier_id` VARCHAR(100) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `item_id` CHAR(36) NOT NULL,
  `idclass` TINYINT(1) DEFAULT NULL,
  `value` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_inventory
DROP TABLE IF EXISTS `{$this->getTable('service_inventory')}`;
CREATE TABLE `{$this->getTable('service_inventory')}` (
  `item_id` CHAR(36) NOT NULL,
  `location_id` CHAR(36) NOT NULL DEFAULT '',
  `channel_id` CHAR(36) NOT NULL DEFAULT '',
  `request_id` CHAR(36) NOT NULL DEFAULT '',
  `quantity` INT(10) DEFAULT NULL,
  PRIMARY KEY (`item_id`,`location_id`,`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_items
DROP TABLE IF EXISTS `{$this->getTable('service_items')}`;
CREATE TABLE `{$this->getTable('service_items')}` (
  `item_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  `style_id` CHAR(36) NOT NULL,
  `plu` VARCHAR(255) DEFAULT NULL,
  `ecomerce` VARCHAR(255) DEFAULT NULL,
  `attribute1_id` CHAR(36) DEFAULT NULL,
  `attribute2_id` CHAR(36) DEFAULT NULL,
  `attribute3_id` CHAR(36) DEFAULT NULL,
  `customdate1` DATETIME DEFAULT NULL,
  `customdate2` DATETIME DEFAULT NULL,
  `customdate3` DATETIME DEFAULT NULL,
  `customdate4` DATETIME DEFAULT NULL,
  `customdate5` DATETIME DEFAULT NULL,
  `customdate6` DATETIME DEFAULT NULL,
  `customflag1` TINYINT(1) DEFAULT NULL,
  `customflag2` TINYINT(1) DEFAULT NULL,
  `customflag3` TINYINT(1) DEFAULT NULL,
  `customflag4` TINYINT(1) DEFAULT NULL,
  `customflag5` TINYINT(1) DEFAULT NULL,
  `customflag6` TINYINT(1) DEFAULT NULL,
  `customlookup1` VARCHAR(255) DEFAULT NULL,
  `customlookup2` VARCHAR(255) DEFAULT NULL,
  `customlookup3` VARCHAR(255) DEFAULT NULL,
  `customlookup4` VARCHAR(255) DEFAULT NULL,
  `customlookup5` VARCHAR(255) DEFAULT NULL,
  `customlookup6` VARCHAR(255) DEFAULT NULL,
  `customlookup7` VARCHAR(255) DEFAULT NULL,
  `customlookup8` VARCHAR(255) DEFAULT NULL,
  `customlookup9` VARCHAR(255) DEFAULT NULL,
  `customlookup10` VARCHAR(255) DEFAULT NULL,
  `customlookup11` VARCHAR(255) DEFAULT NULL,
  `customlookup12` VARCHAR(255) DEFAULT NULL,
  `customnumber1` DECIMAL(38,20) DEFAULT NULL,
  `customnumber2` DECIMAL(38,20) DEFAULT NULL,
  `customnumber3` DECIMAL(38,20) DEFAULT NULL,
  `customnumber4` DECIMAL(38,20) DEFAULT NULL,
  `customnumber5` DECIMAL(38,20) DEFAULT NULL,
  `customnumber6` DECIMAL(38,20) DEFAULT NULL,
  `custominteger1` INT(10) DEFAULT NULL,
  `custominteger2` INT(10) DEFAULT NULL,
  `custominteger3` INT(10) DEFAULT NULL,
  `custominteger4` INT(10) DEFAULT NULL,
  `custominteger5` INT(10) DEFAULT NULL,
  `custominteger6` INT(10) DEFAULT NULL,
  `customtext1` VARCHAR(30) DEFAULT NULL,
  `customtext2` VARCHAR(30) DEFAULT NULL,
  `customtext3` VARCHAR(30) DEFAULT NULL,
  `customtext4` VARCHAR(30) DEFAULT NULL,
  `customtext5` VARCHAR(30) DEFAULT NULL,
  `customtext6` VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_item_category
DROP TABLE IF EXISTS `{$this->getTable('service_item_category')}`;
CREATE TABLE `{$this->getTable('service_item_category')}` (
  `item_id` CHAR(36) NOT NULL,
  `category_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`item_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_item_channel
DROP TABLE IF EXISTS `{$this->getTable('service_item_channel')}`;
CREATE TABLE `{$this->getTable('service_item_channel')}` (
  `item_id` CHAR(36) NOT NULL,
  `channel_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`item_id`,`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_item_collection
DROP TABLE IF EXISTS `{$this->getTable('service_item_collection')}`;
CREATE TABLE `{$this->getTable('service_item_collection')}` (
  `item_id` CHAR(36) NOT NULL,
  `collection_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`item_id`,`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_location
DROP TABLE IF EXISTS `{$this->getTable('service_location')}`;
CREATE TABLE `{$this->getTable('service_location')}` (
  `location_id` CHAR(36) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1-true; 0-false',
  `name` VARCHAR(255) DEFAULT NULL,
  `contact` VARCHAR(255) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `address2` VARCHAR(255) DEFAULT NULL,
  `address3` VARCHAR(255) DEFAULT NULL,
  `address4` VARCHAR(255) DEFAULT NULL,
  `postal_code` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(255) DEFAULT NULL,
  `state` VARCHAR(255) DEFAULT NULL,
  `country` VARCHAR(255) DEFAULT NULL,
  `longitude` VARCHAR(255) DEFAULT NULL,
  `latitude` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(255) DEFAULT NULL,
  `fax` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `home_page` VARCHAR(255) DEFAULT NULL,
  `alias` VARCHAR(255) DEFAULT NULL,
  `is_open` VARCHAR(255) DEFAULT NULL,
  `location_price_group` VARCHAR(255) DEFAULT NULL,
  `custom_date1` DATETIME DEFAULT NULL,
  `custom_date2` DATETIME DEFAULT NULL,
  `custom_date3` DATETIME DEFAULT NULL,
  `custom_date4` DATETIME DEFAULT NULL,
  `custom_date5` DATETIME DEFAULT NULL,
  `custom_date6` DATETIME DEFAULT NULL,
  `custom_flag1` TINYINT(1) DEFAULT NULL,
  `custom_flag2` TINYINT(1) DEFAULT NULL,
  `custom_flag3` TINYINT(1) DEFAULT NULL,
  `custom_flag4` TINYINT(1) DEFAULT NULL,
  `custom_flag5` TINYINT(1) DEFAULT NULL,
  `custom_flag6` TINYINT(1) DEFAULT NULL,
  `custom_flag7` TINYINT(1) DEFAULT NULL,
  `custom_flag8` TINYINT(1) DEFAULT NULL,
  `custom_flag9` TINYINT(1) DEFAULT NULL,
  `custom_flag10` TINYINT(1) DEFAULT NULL,
  `custom_flag11` TINYINT(1) DEFAULT NULL,
  `custom_flag12` TINYINT(1) DEFAULT NULL,
  `custom_lookup1` VARCHAR(255) DEFAULT NULL,
  `custom_lookup3` VARCHAR(255) DEFAULT NULL,
  `custom_lookup4` VARCHAR(255) DEFAULT NULL,
  `custom_lookup5` VARCHAR(255) DEFAULT NULL,
  `custom_lookup6` VARCHAR(255) DEFAULT NULL,
  `custom_lookup7` VARCHAR(255) DEFAULT NULL,
  `custom_lookup8` VARCHAR(255) DEFAULT NULL,
  `custom_lookup9` VARCHAR(255) DEFAULT NULL,
  `custom_lookup10` VARCHAR(255) DEFAULT NULL,
  `custom_lookup11` VARCHAR(255) DEFAULT NULL,
  `custom_lookup12` VARCHAR(255) DEFAULT NULL,
  `custom_number1` DECIMAL(38,20) DEFAULT NULL,
  `custom_number2` DECIMAL(38,20) DEFAULT NULL,
  `custom_number3` DECIMAL(38,20) DEFAULT NULL,
  `custom_number4` DECIMAL(38,20) DEFAULT NULL,
  `custom_number5` DECIMAL(38,20) DEFAULT NULL,
  `custom_number6` DECIMAL(38,20) DEFAULT NULL,
  `custom_integer1` INT(10) DEFAULT NULL,
  `custom_integer2` INT(10) DEFAULT NULL,
  `custom_integer3` INT(10) DEFAULT NULL,
  `custom_integer4` INT(10) DEFAULT NULL,
  `custom_integer5` INT(10) DEFAULT NULL,
  `custom_integer6` INT(10) DEFAULT NULL,
  `custom_text1` VARCHAR(30) NOT NULL,
  `custom_text2` VARCHAR(30) NOT NULL,
  `custom_text3` VARCHAR(30) NOT NULL,
  `custom_text4` VARCHAR(30) NOT NULL,
  `custom_text5` VARCHAR(30) NOT NULL,
  `custom_text6` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_location_schedule
DROP TABLE IF EXISTS `{$this->getTable('service_location_schedule')}`;
CREATE TABLE `{$this->getTable('service_location_schedule')}` (
  `location_id` CHAR(36) NOT NULL,
  `open_time` DATETIME DEFAULT NULL,
  `close_time` DATETIME DEFAULT NULL,
  `day` TINYINT(1) NOT NULL,
  `closed` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`location_id`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_manufacturer
DROP TABLE IF EXISTS `{$this->getTable('service_manufacturer')}`;
CREATE TABLE `{$this->getTable('service_manufacturer')}` (
  `manufacturer_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`manufacturer_id`),
  INDEX `internal_id` (`internal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_media
DROP TABLE IF EXISTS `{$this->getTable('service_media')}`;
CREATE TABLE `{$this->getTable('service_media')}` (
    `media_uri` CHAR(36) NOT NULL,
    `host_id` CHAR(36) NOT NULL,
    `media_index` INT(10) NOT NULL,
    `host_type` ENUM('style','location','item','category','collection','package') NOT NULL,
    `media_type` VARCHAR(100) NOT NULL,
    `media_name` VARCHAR(100) NULL DEFAULT NULL,
    `media_sub_type` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`media_uri`, `host_id`, `media_index`),
    INDEX `host_type` (`host_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_package
DROP TABLE IF EXISTS `{$this->getTable('service_package')}`;
CREATE TABLE `{$this->getTable('service_package')}` (
  `package_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  `description` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT,
  PRIMARY KEY (`package_id`),
  KEY `package_id_request_id` (`package_id`,`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_package_category
DROP TABLE IF EXISTS `{$this->getTable('service_package_category')}`;
CREATE TABLE `{$this->getTable('service_package_category')}` (
  `package_id` CHAR(36) NOT NULL,
  `category_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`package_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_package_channel
DROP TABLE IF EXISTS `{$this->getTable('service_package_channel')}`;
CREATE TABLE `{$this->getTable('service_package_channel')}` (
  `package_id` CHAR(36) NOT NULL,
  `channel_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`package_id`,`channel_id`),
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_package_collection
DROP TABLE IF EXISTS `{$this->getTable('service_package_collection')}`;
CREATE TABLE `{$this->getTable('service_package_collection')}` (
  `package_id` CHAR(36) NOT NULL,
  `collection_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`package_id`,`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_package_component
DROP TABLE IF EXISTS `{$this->getTable('service_package_component')}`;
CREATE TABLE `{$this->getTable('service_package_component')}` (
  `package_id` CHAR(36) NOT NULL,
  `comp_no` INT(10) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `description` TEXT,
  `allow_none` TINYINT(1) NOT NULL DEFAULT '1',
  `allow_multiple` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`package_id`,`comp_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_package_component_element
DROP TABLE IF EXISTS `{$this->getTable('service_package_component_element')}`;
CREATE TABLE `{$this->getTable('service_package_component_element')}` (
  `package_id` CHAR(36) NOT NULL,
  `no` INT(10) NOT NULL,
  `item_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `price` DECIMAL(38,20) NOT NULL,
  `is_component_default` TINYINT(1) NOT NULL DEFAULT '0',
  `quantity` INT(10) NOT NULL,
  PRIMARY KEY (`package_id`,`no`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_price
DROP TABLE IF EXISTS `{$this->getTable('service_price')}`;
CREATE TABLE `{$this->getTable('service_price')}` (
  `item_id` CHAR(36) NOT NULL,
  `price_level` TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT '1',
  `request_id` CHAR(36) NOT NULL,
  `price` DECIMAL(38,20) DEFAULT NULL,
  INDEX `service_price_service` (`request_id`),
  PRIMARY KEY (`item_id`, `price_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_settings
DROP TABLE IF EXISTS `{$this->getTable('service_settings')}`;
CREATE TABLE `{$this->getTable('service_settings')}` (
  `setting_name` VARCHAR(100) NOT NULL,
  `setting_value` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_setting_payment
DROP TABLE IF EXISTS `{$this->getTable('service_setting_payment')}`;
CREATE TABLE `{$this->getTable('service_setting_payment')}` (
    `channel_id` CHAR(36) NOT NULL,
    `name` VARCHAR(128) NOT NULL,
    `description` TEXT NOT NULL,
    `active` TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
    PRIMARY KEY (`channel_id`, `name`),
    INDEX `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_setting_shipping
DROP TABLE IF EXISTS `{$this->getTable('service_setting_shipping')}`;
CREATE TABLE `{$this->getTable('service_setting_shipping')}` (
    `channel_id` CHAR(36) NOT NULL,
    `name` VARCHAR(128) NOT NULL,
    `description` TEXT NOT NULL,
    `active` TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT '0',
    PRIMARY KEY (`channel_id`, `name`),
    INDEX `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_status
DROP TABLE IF EXISTS `{$this->getTable('service_status')}`;
CREATE TABLE `{$this->getTable('service_status')}` (
  `PackageId` CHAR(36) NOT NULL,
  `WebOrderId` CHAR(36) NOT NULL,
  `Status` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`PackageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_status_items
DROP TABLE IF EXISTS `{$this->getTable('service_status_items')}`;
CREATE TABLE `{$this->getTable('service_status_items')}` (
  `ItemId` CHAR(36) NOT NULL,
  `PackageId` CHAR(36) NOT NULL,
  `Qty` INT(10) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  PRIMARY KEY (`PackageId`,`ItemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_status_shipping
DROP TABLE IF EXISTS `{$this->getTable('service_status_shipping')}`;
CREATE TABLE `{$this->getTable('service_status_shipping')}` (
  `ShippingInformationId` CHAR(36) NOT NULL,
  `PackageId` CHAR(36) NOT NULL,
  `Carrier` VARCHAR(50) DEFAULT NULL,
  `ShippingMethod` VARCHAR(50) DEFAULT NULL,
  `TrackingNo` VARCHAR(100) DEFAULT NULL,
  `Estimate` VARCHAR(100) DEFAULT NULL,
  `Description` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`ShippingInformationId`),
  KEY `PackageId` (`PackageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_style
DROP TABLE IF EXISTS `{$this->getTable('service_style')}`;
CREATE TABLE `{$this->getTable('service_style')}` (
  `style_id` CHAR(36) NOT NULL,
  `request_id` CHAR(36) NOT NULL,
  `internal_id` INT(10) DEFAULT NULL,
  `no` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `description2` TEXT,
  `description3` TEXT,
  `description4` TEXT,
  `ecommdescription` TEXT,
  `ecomerce` VARCHAR(100) DEFAULT NULL,
  `dcss` CHAR(36) DEFAULT NULL,
  `acss` CHAR(36) DEFAULT NULL,
  `attributeset1` CHAR(36) DEFAULT NULL,
  `attributeset2` CHAR(36) DEFAULT NULL,
  `attributeset3` CHAR(36) DEFAULT NULL,
  `brand` CHAR(36) DEFAULT NULL,
  `manufacturer` CHAR(36) DEFAULT NULL,
  `customdate1` DATETIME DEFAULT NULL,
  `customdate2` DATETIME DEFAULT NULL,
  `customdate3` DATETIME DEFAULT NULL,
  `customdate4` DATETIME DEFAULT NULL,
  `customdate5` DATETIME DEFAULT NULL,
  `customdate6` DATETIME DEFAULT NULL,
  `customflag1` TINYINT(1) DEFAULT NULL,
  `customflag2` TINYINT(1) DEFAULT NULL,
  `customflag3` TINYINT(1) DEFAULT NULL,
  `customflag4` TINYINT(1) DEFAULT NULL,
  `customflag5` TINYINT(1) DEFAULT NULL,
  `customflag6` TINYINT(1) DEFAULT NULL,
  `customlookup1` VARCHAR(255) DEFAULT NULL,
  `customlookup2` VARCHAR(255) DEFAULT NULL,
  `customlookup3` VARCHAR(255) DEFAULT NULL,
  `customlookup4` VARCHAR(255) DEFAULT NULL,
  `customlookup5` VARCHAR(255) DEFAULT NULL,
  `customlookup6` VARCHAR(255) DEFAULT NULL,
  `customlookup7` VARCHAR(255) DEFAULT NULL,
  `customlookup8` VARCHAR(255) DEFAULT NULL,
  `customlookup9` VARCHAR(255) DEFAULT NULL,
  `customlookup10` VARCHAR(255) DEFAULT NULL,
  `customlookup11` VARCHAR(255) DEFAULT NULL,
  `customlookup12` VARCHAR(255) DEFAULT NULL,
  `customnumber1` DECIMAL(38,20) DEFAULT NULL,
  `customnumber2` DECIMAL(38,20) DEFAULT NULL,
  `customnumber3` DECIMAL(38,20) DEFAULT NULL,
  `customnumber4` DECIMAL(38,20) DEFAULT NULL,
  `customnumber5` DECIMAL(38,20) DEFAULT NULL,
  `customnumber6` DECIMAL(38,20) DEFAULT NULL,
  `custominteger1` INT(10) DEFAULT NULL,
  `custominteger2` INT(10) DEFAULT NULL,
  `custominteger3` INT(10) DEFAULT NULL,
  `custominteger4` INT(10) DEFAULT NULL,
  `custominteger5` INT(10) DEFAULT NULL,
  `custominteger6` INT(10) DEFAULT NULL,
  `customtext1` VARCHAR(30) DEFAULT NULL,
  `customtext2` VARCHAR(30) DEFAULT NULL,
  `customtext3` VARCHAR(30) DEFAULT NULL,
  `customtext4` VARCHAR(30) DEFAULT NULL,
  `customtext5` VARCHAR(30) DEFAULT NULL,
  `customtext6` VARCHAR(30) DEFAULT NULL,
  `date_available` DATETIME DEFAULT NULL,
  `inactive` TINYINT(1) DEFAULT NULL,
  `date_inserted` DATETIME NOT NULL,
  `date_updated` DATETIME NOT NULL,
  PRIMARY KEY (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_style_category
DROP TABLE IF EXISTS `{$this->getTable('service_style_category')}`;
CREATE TABLE `{$this->getTable('service_style_category')}` (
  `style_id` CHAR(36) NOT NULL,
  `category_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`style_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_style_channel
DROP TABLE IF EXISTS `{$this->getTable('service_style_channel')}`;
CREATE TABLE `{$this->getTable('service_style_channel')}` (
  `style_id` CHAR(36) NOT NULL,
  `channel_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`style_id`,`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_style_collection
DROP TABLE IF EXISTS `{$this->getTable('service_style_collection')}`;
CREATE TABLE `{$this->getTable('service_style_collection')}` (
  `style_id` CHAR(36) NOT NULL,
  `collection_id` CHAR(36) NOT NULL,
  PRIMARY KEY (`style_id`,`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_style_related
DROP TABLE IF EXISTS `{$this->getTable('service_style_related')}`;
CREATE TABLE `{$this->getTable('service_style_related')}` (
  `style_id` CHAR(36) NOT NULL,
  `related_style_id` CHAR(36) NOT NULL,
  `related_style_type` VARCHAR(50) NOT NULL,
  `request_id` CHAR(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_weborder
DROP TABLE IF EXISTS `{$this->getTable('service_weborder')}`;
CREATE TABLE `{$this->getTable('service_weborder')}` (
  `WebOrderId` CHAR(36) NOT NULL,
  `EComChannelId` CHAR(36) NOT NULL,
  `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DefaultLocationId` CHAR(36) DEFAULT NULL,
  `EComShippingMethod` VARCHAR(50) DEFAULT NULL,
  `OrderNo` VARCHAR(50) NOT NULL,
  `OrderDate` DATETIME NOT NULL,
  `EComCustomerId` VARCHAR(128) NOT NULL,
  `BillFirstName` VARCHAR(50) DEFAULT NULL,
  `BillLastName` VARCHAR(50) DEFAULT NULL,
  `BillMiddleName` VARCHAR(50) DEFAULT NULL,
  `BillGender` TINYINT(1) DEFAULT NULL,
  `BillBirthday` DATETIME DEFAULT NULL,
  `BillEmail` VARCHAR(50) DEFAULT NULL,
  `BillPhone` VARCHAR(50) DEFAULT NULL,
  `BillMobilePhone` VARCHAR(50) DEFAULT NULL,
  `BillCompany` VARCHAR(50) DEFAULT NULL,
  `BillAddress1` VARCHAR(128) DEFAULT NULL,
  `BillAddress2` VARCHAR(128) DEFAULT NULL,
  `BillCity` VARCHAR(128) DEFAULT NULL,
  `BillCountry` VARCHAR(50) DEFAULT NULL,
  `BillPostalCode` VARCHAR(50) DEFAULT NULL,
  `BillState` VARCHAR(50) DEFAULT NULL,
  `ShipFirstName` VARCHAR(50) DEFAULT NULL,
  `ShipLastName` VARCHAR(50) DEFAULT NULL,
  `ShipMiddleName` VARCHAR(50) DEFAULT NULL,
  `ShipGender` TINYINT(1) DEFAULT NULL,
  `ShipBirthday` DATETIME DEFAULT NULL,
  `ShipEmail` VARCHAR(50) DEFAULT NULL,
  `ShipPhone` VARCHAR(50) DEFAULT NULL,
  `ShipMobilePhone` VARCHAR(50) DEFAULT NULL,
  `ShipCompany` VARCHAR(128) DEFAULT NULL,
  `ShipAddress1` VARCHAR(128) DEFAULT NULL,
  `ShipAddress2` VARCHAR(128) DEFAULT NULL,
  `ShipCity` VARCHAR(50) DEFAULT NULL,
  `ShipCountry` VARCHAR(50) DEFAULT NULL,
  `ShipPostalCode` VARCHAR(50) DEFAULT NULL,
  `ShipState` VARCHAR(50) DEFAULT NULL,
  `CustomDate1` DATETIME DEFAULT NULL,
  `CustomDate2` DATETIME DEFAULT NULL,
  `CustomDate3` DATETIME DEFAULT NULL,
  `CustomDate4` DATETIME DEFAULT NULL,
  `CustomDate5` DATETIME DEFAULT NULL,
  `CustomDate6` DATETIME DEFAULT NULL,
  `CustomFlag1` TINYINT(1) DEFAULT NULL,
  `CustomFlag2` TINYINT(1) DEFAULT NULL,
  `CustomFlag3` TINYINT(1) DEFAULT NULL,
  `CustomFlag4` TINYINT(1) DEFAULT NULL,
  `CustomFlag5` TINYINT(1) DEFAULT NULL,
  `CustomFlag6` TINYINT(1) DEFAULT NULL,
  `CustomLookupValue1` CHAR(36) DEFAULT NULL,
  `CustomLookupValue2` CHAR(36) DEFAULT NULL,
  `CustomLookupValue3` CHAR(36) DEFAULT NULL,
  `CustomLookupValue4` CHAR(36) DEFAULT NULL,
  `CustomLookupValue5` CHAR(36) DEFAULT NULL,
  `CustomLookupValue6` CHAR(36) DEFAULT NULL,
  `CustomNumber1` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber2` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber3` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber4` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber5` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber6` DECIMAL(38,20) DEFAULT NULL,
  `CustomInteger1` INT(10) DEFAULT NULL,
  `CustomInteger2` INT(10) DEFAULT NULL,
  `CustomInteger3` INT(10) DEFAULT NULL,
  `CustomInteger4` INT(10) DEFAULT NULL,
  `CustomInteger5` INT(10) DEFAULT NULL,
  `CustomInteger6` INT(10) DEFAULT NULL,
  `CustomText1` VARCHAR(30) DEFAULT NULL,
  `CustomText2` VARCHAR(30) DEFAULT NULL,
  `CustomText3` VARCHAR(30) DEFAULT NULL,
  `CustomText4` VARCHAR(30) DEFAULT NULL,
  `CustomText5` VARCHAR(30) DEFAULT NULL,
  `CustomText6` VARCHAR(30) DEFAULT NULL,
  `Instruction` TEXT,
  PRIMARY KEY (`WebOrderId`),
  INDEX `EComChannelId_OrderDate` (`EComChannelId`, `OrderDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_weborder_discount_reason
DROP TABLE IF EXISTS `{$this->getTable('service_weborder_discount_reason')}`;
CREATE TABLE `{$this->getTable('service_weborder_discount_reason')}` (
  `WebOrderId` CHAR(36) NOT NULL,
  `GlobalDiscountReasonId` CHAR(36) NOT NULL,
  `GlobalDiscountAmount` DECIMAL(38,20) NOT NULL,
  `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`WebOrderId`,`GlobalDiscountReasonId`),
  CONSTRAINT `FK_service_weborder_discount_reason_service_weborder` FOREIGN KEY (`WebOrderId`) REFERENCES `{$this->getTable('service_weborder')}` (`WebOrderId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_weborder_fee
DROP TABLE IF EXISTS `{$this->getTable('service_weborder_fee')}`;
CREATE TABLE `{$this->getTable('service_weborder_fee')}` (
  `FeeId` CHAR(36) NOT NULL,
  `WebOrderId` CHAR(36) NOT NULL,
  `TaxAmount` DECIMAL(38,20) NOT NULL,
  `UnitPrice` DECIMAL(38,20) NOT NULL,
  `Qty` INT(10) NOT NULL DEFAULT '0',
  `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`FeeId`,`WebOrderId`),
  KEY `weborder_id` (`WebOrderId`),
  CONSTRAINT `FK_service_weborder_fee_service_weborder` FOREIGN KEY (`WebOrderId`) REFERENCES `{$this->getTable('service_weborder')}` (`WebOrderId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_weborder_item
DROP TABLE IF EXISTS `{$this->getTable('service_weborder_item')}`;
CREATE TABLE `{$this->getTable('service_weborder_item')}` (
  `WebOrderItemId` CHAR(36) NOT NULL,
  `WebOrderItemsGroupId` CHAR(36) NOT NULL,
  `WebOrderId` CHAR(36) NOT NULL,
  `ItemId` CHAR(36) NOT NULL,
  `OrderQty` DECIMAL(38,20) NOT NULL,
  `UnitPrice` DECIMAL(38,20) NOT NULL,
  `LineTaxAmount` DECIMAL(38,20) NOT NULL,
  `TrackingNo` VARCHAR(50) DEFAULT NULL,
  `LineNo` INT(10) NOT NULL DEFAULT '0',
  `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CustomDate1` DATETIME DEFAULT NULL,
  `CustomDate2` DATETIME DEFAULT NULL,
  `CustomDate3` DATETIME DEFAULT NULL,
  `CustomDate4` DATETIME DEFAULT NULL,
  `CustomDate5` DATETIME DEFAULT NULL,
  `CustomDate6` DATETIME DEFAULT NULL,
  `CustomFlag1` TINYINT(1) DEFAULT NULL,
  `CustomFlag2` TINYINT(1) DEFAULT NULL,
  `CustomFlag3` TINYINT(1) DEFAULT NULL,
  `CustomFlag4` TINYINT(1) DEFAULT NULL,
  `CustomFlag5` TINYINT(1) DEFAULT NULL,
  `CustomFlag6` TINYINT(1) DEFAULT NULL,
  `CustomLookupValue1` CHAR(36) DEFAULT NULL,
  `CustomLookupValue2` CHAR(36) DEFAULT NULL,
  `CustomLookupValue3` CHAR(36) DEFAULT NULL,
  `CustomLookupValue4` CHAR(36) DEFAULT NULL,
  `CustomLookupValue5` CHAR(36) DEFAULT NULL,
  `CustomLookupValue6` CHAR(36) DEFAULT NULL,
  `CustomNumber1` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber2` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber3` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber4` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber5` DECIMAL(38,20) DEFAULT NULL,
  `CustomNumber6` DECIMAL(38,20) DEFAULT NULL,
  `CustomInteger1` INT(10) DEFAULT NULL,
  `CustomInteger2` INT(10) DEFAULT NULL,
  `CustomInteger3` INT(10) DEFAULT NULL,
  `CustomInteger4` INT(10) DEFAULT NULL,
  `CustomInteger5` INT(10) DEFAULT NULL,
  `CustomInteger6` INT(10) DEFAULT NULL,
  `CustomText1` VARCHAR(30) DEFAULT NULL,
  `CustomText2` VARCHAR(30) DEFAULT NULL,
  `CustomText3` VARCHAR(30) DEFAULT NULL,
  `CustomText4` VARCHAR(30) DEFAULT NULL,
  `CustomText5` VARCHAR(30) DEFAULT NULL,
  `CustomText6` VARCHAR(30) DEFAULT NULL,
  `Notes` TEXT,
  PRIMARY KEY (`WebOrderItemId`),
  KEY `WebOrderItemsGroupId` (`WebOrderItemsGroupId`),
  KEY `FK_service_weborder_item_service_weborder` (`WebOrderId`),
  CONSTRAINT `FK_service_weborder_item_service_weborder` FOREIGN KEY (`WebOrderId`) REFERENCES `{$this->getTable('service_weborder')}` (`WebOrderId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_weborder_item_fee
DROP TABLE IF EXISTS `{$this->getTable('service_weborder_item_fee')}`;
CREATE TABLE `{$this->getTable('service_weborder_item_fee')}` (
    `WebOrderItemId` CHAR(36) NOT NULL,
    `FeeId` CHAR(36) NOT NULL,
    `UnitPrice` DECIMAL(38,20) NOT NULL,
    `TaxAmount` DECIMAL(38,20) NOT NULL,
    `Qty` INT(10) NOT NULL DEFAULT '0',
    `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`WebOrderItemId`, `FeeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Dumping structure for table service_weborder_item_line_discount
DROP TABLE IF EXISTS `{$this->getTable('service_weborder_item_line_discount')}`;
CREATE TABLE `{$this->getTable('service_weborder_item_line_discount')}` (
  `WebOrderItemId` CHAR(36) NOT NULL,
  `LineDiscountReasonId` CHAR(36) NOT NULL,
  `LineDiscountAmount` DECIMAL(38,20) NOT NULL,
  `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`WebOrderItemId`,`LineDiscountReasonId`),
  CONSTRAINT `FK_service_weborder_item_line_discount_service_weborder_item` FOREIGN KEY (`WebOrderItemId`) REFERENCES `{$this->getTable('service_weborder_item')}` (`WebOrderItemId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table service_weborder_payment
DROP TABLE IF EXISTS `{$this->getTable('service_weborder_payment')}`;
CREATE TABLE `{$this->getTable('service_weborder_payment')}` (
  `WebOrderPaymentId` CHAR(36) NOT NULL,
  `WebOrderId` CHAR(36) NOT NULL,
  `CardType` VARCHAR(50) NOT NULL,
  `EComPaymentMethod` VARCHAR(50) NOT NULL,
  `AccountNumber` VARCHAR(38) NOT NULL,
  `PaymentAmount` DECIMAL(38,20) NOT NULL,
  `CardExpMonth` TINYINT(2) DEFAULT NULL,
  `CardExpYear` SMALLINT(4) DEFAULT NULL,
  `MerchantId` VARCHAR(128) DEFAULT NULL,
  `CardOrderId` VARCHAR(255) DEFAULT NULL,
  `ReferenceNum` VARCHAR(255) DEFAULT NULL,
  `TransactionId` VARCHAR(255) DEFAULT NULL,
  `ListOrder` INT(10) DEFAULT NULL,
  `CardholderFirstName` VARCHAR(128) DEFAULT NULL,
  `CardholderLastName` VARCHAR(128) DEFAULT NULL,
  `CardholderAddress1` VARCHAR(128) DEFAULT NULL,
  `CardholderAddress2` VARCHAR(128) DEFAULT NULL,
  `CardholderCity` VARCHAR(128) DEFAULT NULL,
  `CardholderState` VARCHAR(128) DEFAULT NULL,
  `CardholderCountryCode` VARCHAR(128) DEFAULT NULL,
  `CardholderPostalCode` VARCHAR(128) DEFAULT NULL,
  `LoyaltyRewardPointAmount` INT(10) DEFAULT NULL,
  `RecCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`WebOrderPaymentId`),
  KEY `WebOrderId` (`WebOrderId`),
  CONSTRAINT `FK_service_weborder_payment_service_weborder` FOREIGN KEY (`WebOrderId`) REFERENCES `{$this->getTable('service_weborder')}` (`WebOrderId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();