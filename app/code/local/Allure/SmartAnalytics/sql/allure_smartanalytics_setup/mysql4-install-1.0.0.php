<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Core_Model_Config();

$setup->saveConfig('google/analytics/active', '0', 'default', 0);

$installer->endSetup();
