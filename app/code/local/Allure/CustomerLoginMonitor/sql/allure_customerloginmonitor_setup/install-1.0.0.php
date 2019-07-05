<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('customerloginmonitor/login'))
    ->addColumn('row_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Row Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Customer Id')
    ->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Customer Name')
    ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Customer Email')
    ->addColumn('remote_ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'IP')
    ->addColumn('browser', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'browser')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'status')
    ->addColumn('additional_info', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'additional_info')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => false,
    ),'Date');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}


$installer->endSetup();