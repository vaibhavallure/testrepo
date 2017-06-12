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
	('enable_user_order_condition', ''),
	('disable_bridge', '0'),
	('show_errors_on_frontend', '0'),
	('show_errors_on_backend', '1')
;
");

$installer->getConnection()->addColumn(
	$installer->getTable('sugarcrm/user_operations'),
	'condition', 
	'text NOT NULL'
);

$installer->endSetup();