<?php
$installer = $this;
$installer->startSetup();
/* Piercers table */
$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('appointments/appointments')};
		CREATE TABLE {$this->getTable('appointments/appointments')} (
		`id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
		`firstname` varchar( 255 ) NOT NULL default '',
		`lastname` varchar( 255 ) NOT NULL default '',
		`email` varchar( 255 ) NOT NULL default '',
		`phone` varchar( 255 ) NOT NULL default '',
		`street` varchar( 255 ) NOT NULL default '',
		`city` varchar( 255 ) NOT NULL default '',
		`state` varchar( 255 ) NOT NULL default '',
		`country` varchar( 255 ) NOT NULL default '',
		`postal_code` varchar( 255 ) NOT NULL default '',
		`notification_pref` tinyint  NOT NULL ,
		`piercing_qty` int  NOT NULL ,
		`piercing_loc` varchar( 255 ) NOT NULL default '',
		`appointment_start` timestamp,
		`appointment_end` timestamp,
		`booking_time` timestamp,
		`customer_id` varchar( 255 ) NOT NULL default '',
		`piercer_id` varchar( 255 ) NOT NULL default '',
		`store_id` int  NOT NULL ,
		`last_notified` timestamp,
		`app_status` int  NOT NULL ,
		`special_notes` text NOT NULL default '',
		PRIMARY KEY ( `id` )
		) ENGINE = InnoDB DEFAULT CHARSET = utf8;
		");

$installer->endSetup();