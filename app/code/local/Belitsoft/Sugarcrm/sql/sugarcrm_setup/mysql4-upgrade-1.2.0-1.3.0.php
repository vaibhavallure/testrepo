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
INSERT IGNORE INTO {$this->getTable('sugarcrm/config_data')}
	(`name`, `value`)
VALUES
	('enable_user_order_to_sugarcrm', '0')
;

CREATE TABLE IF NOT EXISTS {$this->getTable('sugarcrm/stages')} (
	`stage_id` int(11) NOT NULL AUTO_INCREMENT,
	`bean` varchar(20) NOT NULL,
	`mage_status` varchar(255) NOT NULL,
	`sugar_stage` varchar(255) NOT NULL,
	PRIMARY KEY (`stage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('sugarcrm/synch_map')} ADD `model` VARCHAR(20) NOT NULL AFTER `id`;
ALTER TABLE {$this->getTable('sugarcrm/synch_map')} ADD `accountsid` VARCHAR(36) NOT NULL;
ALTER TABLE {$this->getTable('sugarcrm/synch_map')} ADD `contactsid` VARCHAR(36) NOT NULL;
ALTER TABLE {$this->getTable('sugarcrm/synch_map')} ADD `leadsid` VARCHAR(36) NOT NULL;

UPDATE {$this->getTable('sugarcrm/synch_map')} SET `model` = 'order' WHERE (`bean` = 'Opportunities' OR `bean` = 'Cases') AND `model` = '';

ALTER TABLE {$this->getTable('sugarcrm/synch_map')} DROP INDEX `IDX_SUGARCRM_CUSTOMER`,
	ADD UNIQUE `IDX_SUGARCRM_CUSTOMER` ( `model` , `cid` , `bean` );
");

$installer->endSetup();