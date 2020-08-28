<?php
$installer = $this;
$installer->startSetup();

$installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_custom_url')} (
            `url_id` int(11) UNSIGNED NOT NULL auto_increment,
            `store_id` smallint(5) DEFAULT 0,
            `current_url` varchar(255) NOT NULL,
            `request_path` varchar(255) NOT NULL,
            `target_path` varchar(255) NOT NULL,
            `is_rewrite_url` smallint(5) DEFAULT 0,
            `rewrite_url_id` int(11) DEFAULT 0,
            `options` varchar(10) DEFAULT NULL,
            PRIMARY KEY(`url_id`),
            UNIQUE KEY UNQ_ALLURE_CUSTOM_URL_CURRENT_URL_STORE_ID (current_url , store_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
  "); 
 
$installer->endSetup();

