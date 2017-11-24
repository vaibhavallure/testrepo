<?php
$installer = $this;
$installer->startSetup();
/* Piercers table */
$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('appointments/dates')};
		CREATE TABLE {$this->getTable('appointments/dates')} (
		`id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
		`date` date NOT NULL,
		`store_id` int  NOT NULL ,
		`is_available` tinyint  NOT NULL  default 1,
		`exclude` tinyint  NOT NULL default 0,
		PRIMARY KEY ( `id` )
		) ENGINE = InnoDB DEFAULT CHARSET = utf8;
		");
$installer->endSetup();