<?php


$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
 
if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){

$installer->run("
        
        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_virtual_website')} (
                `website_id` smallint(5) UNSIGNED NOT NULL auto_increment,
                `code` varchar(32) DEFAULT NULL,
                `name` varchar(64) DEFAULT NULL,
                `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `default_group_id` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `is_default` smallint(5) UNSIGNED DEFAULT '0',
                `stock_id` int(11) DEFAULT '0',
                `website_price_rule` decimal(12,4) DEFAULT '1.0000',
                 PRIMARY KEY  (`website_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_virtual_store_group')} (
                `group_id` smallint(5) UNSIGNED NOT NULL auto_increment,
                `website_id` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `name` varchar(255) NOT NULL,
                `root_category_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
                `default_store_id` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                 PRIMARY KEY  (`group_id`)
               --  FOREIGN KEY (`website_id`) REFERENCES `allure_virtual_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        CREATE TABLE IF NOT EXISTS {$this->getTable('allure_virtual_store')} (
                `store_id` smallint(5) UNSIGNED NOT NULL auto_increment,
                `code` varchar(32) DEFAULT NULL ,
                `website_id` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `group_id` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `name` varchar(255) NOT NULL COMMENT 'Store Name',
                `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `is_active` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                `is_copy_old_product` int(11) DEFAULT '0',
                 PRIMARY KEY  (`store_id`)
                -- FOREIGN KEY (`group_id`) REFERENCES `allure_virtual_store_group` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                -- FOREIGN KEY (`website_id`) REFERENCES `allure_virtual_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     
  ");  
}

$installer->endSetup();

