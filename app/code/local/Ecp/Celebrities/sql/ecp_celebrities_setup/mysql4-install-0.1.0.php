<?php
/**
 * @category    Ecp
 * @package     Ecp_Celebrities
 */

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('ecp_celebrities')} (
  `celebrity_id` int(11) unsigned NOT NULL auto_increment,
  `celebrity_name` varchar(255) NOT NULL default '',
  `default_image` varchar(255) NOT NULL default '',  
  `description` text NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`celebrity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('ecp_celebrities_outfits')} (
  `celebrity_outfit_id` int(11) unsigned NOT NULL auto_increment,
  `celebrity_id` int(11) unsigned NOT NULL,
  `outfit_image` varchar(255) NOT NULL default '',
  `related_products` text default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`celebrity_outfit_id`),
  FOREIGN KEY (`celebrity_id`) REFERENCES ecp_celebrities(celebrity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 