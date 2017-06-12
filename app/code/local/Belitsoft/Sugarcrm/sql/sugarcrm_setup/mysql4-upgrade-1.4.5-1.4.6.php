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
CREATE TABLE IF NOT EXISTS {$this->getTable('sugarcrm/error')} (
	`error_id`		int(10) NOT NULL AUTO_INCREMENT,
	`type`			varchar(10) NOT NULL,
	`operation`		varchar(30) NOT NULL,
	`entity_id`		int(10) NOT NULL,
	`error`			varchar(255) NOT NULL,
	`params`		text NOT NULL,
	`status`		tinyint(1) NOT NULL DEFAULT '0',
	`creation_date`	datetime NOT NULL, 
	`update_date`	datetime NOT NULL, 
	PRIMARY KEY (`error_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();