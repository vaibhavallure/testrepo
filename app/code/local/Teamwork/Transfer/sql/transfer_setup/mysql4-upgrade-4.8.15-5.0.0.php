<?php
$installer = $this;
$installer->startSetup();

$select = $installer->getConnection()->select()->from( $this->getTable('service_channel'), array('channel_id') );
if( $installer->getConnection()->fetchCol($select) )
{
    $configClass = new Mage_Core_Model_Config();
    $configClass->saveConfig(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_PRODUCTS, 1, 'default', 0);
}

$installer->endSetup();