<?php

$installer = $this;
$installer->startSetup();

$installer->run(
    "

    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_location/store')} (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Store ID',
      `title` varchar(255) NOT NULL COMMENT 'Title',
      `address` text NOT NULL COMMENT 'Address',
      `latitude` decimal(15,10) NOT NULL COMMENT 'Latitude',
      `longitude` decimal(15,10) NOT NULL COMMENT 'Longitude',
      `address_display` text NOT NULL COMMENT 'Address To Display',
      `website_url` varchar(255) NOT NULL COMMENT 'Website Url',
      `telephone` varchar(50) NOT NULL COMMENT 'Phone',
      `mon_hours` varchar(100) NOT NULL COMMENT 'Monday Hours',
      `tues_hours` varchar(100) NOT NULL COMMENT 'Tuesday Hours',
      `wed_hours` varchar(100) NOT NULL COMMENT 'Wednesday Hours',
      `thurs_hours` varchar(100) NOT NULL COMMENT 'Thursday Hours',
      `fri_hours` varchar(100) NOT NULL COMMENT 'Friday Hours',
      `sat_hours` varchar(100) NOT NULL COMMENT 'Saturday Hours',
      `sun_hours` varchar(100) NOT NULL COMMENT 'Sunday Hours',
      `active` tinyint(1) NOT NULL DEFAULT '0',
      `notes` text NOT NULL COMMENT 'Notes',
      `created_at` datetime NOT NULL default '0001-01-01 00:00:00',
      `updated_at` datetime NOT NULL default '0001-01-01 00:00:00',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


"
);

$installer->endSetup();
