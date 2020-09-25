<?php
$installer = $this;

$installer->startSetup();

$installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_waitwhile_resources')} (
            `resource_id` smallint(5) UNSIGNED NOT NULL auto_increment,
            `waitwhile_resource_id` varchar(255) NOT NULL,
            `code` varchar(100) DEFAULT NULL,
            `name` varchar(200) NOT NULL,
            `store_id` smallint(5) NOT NULL,
            PRIMARY KEY  (`resource_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  "); 
$installer->endSetup();



