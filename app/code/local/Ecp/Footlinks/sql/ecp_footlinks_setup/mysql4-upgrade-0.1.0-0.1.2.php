<?php

$installer = $this;

$installer->startSetup();

$fieldsTable = $installer->getConnection()->describeTable($installer->getTable('ecp_footlinks'));

if(!isset($fieldsTable['use_for_seo_text'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_footlinks'), 'use_for_seo_text', 'smallint(6) NOT NULL default 0');
}

if(!isset($fieldsTable['block_for_seo_default'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_footlinks'), 'block_for_seo_default', 'varchar(255) NOT NULL default ""');
}

if(!isset($fieldsTable['block_for_home_seo'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_footlinks'), 'block_for_home_seo', 'varchar(255) NOT NULL default ""');
}

$installer->endSetup();
