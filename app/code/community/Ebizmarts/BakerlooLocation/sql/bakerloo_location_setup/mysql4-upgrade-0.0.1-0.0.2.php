<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `street` varchar(255) DEFAULT NULL COMMENT 'Street';");
$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `city` varchar(255) DEFAULT NULL COMMENT 'City';");
$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `region` varchar(255) DEFAULT NULL COMMENT 'Region';");
$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `region_id` int(10) unsigned DEFAULT NULL COMMENT 'Region Id';");
$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `postcode` varchar(255) DEFAULT NULL COMMENT 'Postcode';");
$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `country_id` varchar(2) DEFAULT NULL COMMENT 'Country Id';");
$installer->run("ALTER TABLE `{$installer->getTable('bakerloo_location/store')}` ADD COLUMN `fax` varchar(255) DEFAULT NULL COMMENT 'Fax';");


$installer->endSetup();
