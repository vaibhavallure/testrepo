<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('sales_flat_quote')} ADD `no_signature_delivery` TINYINT(1) NULL DEFAULT '0';
");
$installer->run("
    ALTER TABLE {$this->getTable('sales_flat_order')} ADD `no_signature_delivery` TINYINT(1) NULL DEFAULT '0';
");
$installer->endSetup();