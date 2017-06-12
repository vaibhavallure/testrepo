<?php

$installer = $this;

$installer->startSetup();

$fieldsTable = $installer->getConnection()->describeTable($installer->getTable('ecp_familycolors'));
if(!isset($fieldsTable['color_apparel'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_familycolors'), 'color_apparel', 'text NOT NULL default ""');
}
if(!isset($fieldsTable['diamond_color'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_familycolors'), 'diamond_color', 'text NOT NULL default ""');
}
if(!isset($fieldsTable['metal_color'])){
    $installer->getConnection()->addColumn($installer->getTable('ecp_familycolors'), 'metal_color', 'text NOT NULL default ""');
}

$installer->endSetup();
