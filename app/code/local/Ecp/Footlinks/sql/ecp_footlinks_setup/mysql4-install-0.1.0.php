<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    
CREATE TABLE IF NOT EXISTS {$this->getTable('ecp_footlinks')} (
  footlink_id int(11) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  link varchar(255) NULL ,
  status smallint(6) NOT NULL default '0',
  type varchar(255) NOT NULL default '',
  block_value varchar(255) NOT NULL default '',
  url_value varchar(255) NOT NULL default '',
  created_time datetime NULL,
  update_time datetime NULL,
  PRIMARY KEY (footlink_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
