<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('sugarcrm/config_data')} (
	`name` varchar(50) NOT NULL ,
	`value` text NOT NULL DEFAULT '',
	PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT IGNORE INTO {$this->getTable('sugarcrm/config_data')} (`name`, `value`) VALUES
('server', ''),
('wsdl', ''),
('namespace', ''),
('use', '1'),
('style', '1'),
('username', ''),
('password', ''),
('user_order_to_sugarcrm',''),
('sugarcrm_account_id',''),
('license','')
;

CREATE TABLE IF NOT EXISTS {$this->getTable('sugarcrm/user_operations')} (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`bean` varchar(20) NOT NULL,
	`operation` varchar(10) NOT NULL,
	`enable` tinyint(1) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('sugarcrm/fields_map')} (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`module_name` varchar(50) NOT NULL,
	`sugarcrm_field` varchar(255) NOT NULL,
	`fields_mapping_type` enum('magefield','evalcode') NOT NULL,
	`mage_customer_field` varchar(100) NOT NULL,
	`eval_code` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('sugarcrm/synch_map')} (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`cid` int(10) UNSIGNED NOT NULL,
	`bean` varchar( 50 ) NOT NULL,
	`sid` varchar(36) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`sid`),
	UNIQUE KEY `IDX_SUGARCRM_CUSTOMER` (`cid`,`bean`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();