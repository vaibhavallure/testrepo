<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('teamworkdam/process'))
    ->addColumn('process_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Row Id')
    ->addColumn('process_info', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Process Information')
    ->addColumn('process_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Process Information')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => false,
    ),'started at');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}


$installer->endSetup();