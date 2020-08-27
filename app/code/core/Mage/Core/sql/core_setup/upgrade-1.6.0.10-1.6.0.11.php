<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'uniquesession/customer_session'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('core/uniquesession_customer_session'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
    ), 'Customer ID')
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Session')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addIndex(
        $installer->getIdxName(
            'core/uniquesession_customer_session',
            array('customer_id')
        ),
        array('customer_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    )
    ->addIndex(
        $installer->getIdxName(
            'core/uniquesession_customer_session',
            array('session_id')
        ),
        array('session_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    )
    ->addForeignKey($installer->getFkName('core/uniquesession_customer_session', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Customer Session');
$installer->getConnection()->createTable($table);

/**
 * Create table 'uniquesession/admin_user_session'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('core/uniquesession_admin_user_session'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('admin_user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
    ), 'Admin User ID')
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Session')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addIndex(
        $installer->getIdxName(
            'core/uniquesession_admin_user_session',
            array('admin_user_id')
        ),
        array('admin_user_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    )
    ->addIndex(
        $installer->getIdxName(
            'core/uniquesession_admin_user_session',
            array('session_id')
        ),
        array('session_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    )
    ->addForeignKey($installer->getFkName('core/uniquesession_admin_user_session', 'admin_user_id', 'admin/user', 'user_id'),
        'admin_user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Admin User Session');
$installer->getConnection()->createTable($table);

$installer->endSetup();