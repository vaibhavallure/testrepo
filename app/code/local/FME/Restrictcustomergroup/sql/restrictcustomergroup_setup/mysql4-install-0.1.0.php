<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('fme_restrictcustomergroup')};
CREATE TABLE `{$this->getTable('fme_restrictcustomergroup')}` (
  `rule_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) DEFAULT NULL,
  `priority` int(250) DEFAULT NULL,
  `customer_groups` text,
  `description` text,
  `cms_pages` text,
  `other_pages` text,
  `static_block_ids` text,
  `catalog_category_ids` text,
  `condition_serialized` mediumtext,
  `manual_url_redirect` mediumtext,
  `form_type` varchar(150) DEFAULT NULL,
  `status` smallint(6) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('fme_restrictcustomergroup_blocks')};
CREATE TABLE `{$this->getTable('fme_restrictcustomergroup_blocks')}` (
  `block_id` mediumint(9) DEFAULT NULL,
  `rule_id` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('fme_restrictcustomergroup_store')};
CREATE TABLE `{$this->getTable('fme_restrictcustomergroup_store')}` (
  `rule_id` mediumint(9) DEFAULT NULL COMMENT 'foreign key',
  `store_id` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->setConfigData('restrictcustomergroup/basic/restriction_type','basic');
$installer->endSetup(); 
