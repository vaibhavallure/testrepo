<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
/* Piercers table */
$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('appointments/piercers')};
		CREATE TABLE {$this->getTable('appointments/piercers')} (
		`id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
		`firstname` varchar( 255 ) NOT NULL default '',
		`lastname` varchar( 255 ) NOT NULL default '',
		`email` varchar( 255 ) NOT NULL default '',
		`phone` varchar( 255 ) NOT NULL default '',
		`working_days` varchar( 255 ) NOT NULL default '',
		`working_hours` varchar( 255 ) NOT NULL default '',
		`store_id` int  NOT NULL ,
		`is_active` tinyint  NOT NULL ,
		PRIMARY KEY ( `id` )
		) ENGINE = InnoDB DEFAULT CHARSET = utf8;
		");


/* Piercing timing */
$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('appointments/timing')};
		CREATE TABLE {$this->getTable('appointments/timing')} (
		`id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
		`qty` int( 11 ) NOT NULL ,
		`time` int( 11 ) NOT NULL ,
		PRIMARY KEY ( `id` )
		) ENGINE = InnoDB DEFAULT CHARSET = utf8;
		");
$installer->endSetup();