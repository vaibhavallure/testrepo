<?php

$installer = $this;

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$adminRolesTableRoles = $installer->getTable('admin/role');
$installer->getConnection()->addColumn($adminRolesTableRoles, 'restrict_by_store', "tinyint(1) NOT NULL DEFAULT 0");

$adminUserTableRoles = $installer->getTable('admin/user');
$installer->getConnection()->addColumn($adminUserTableRoles, 'store_restrictions', "VARCHAR(255)");

$installer->endSetup();
