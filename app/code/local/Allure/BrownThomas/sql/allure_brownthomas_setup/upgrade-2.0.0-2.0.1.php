<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('brownthomas/filetransfer'))
    ->addColumn('row_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Row Id')
    ->addColumn('file', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'File')
    ->addColumn('transfer_date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => false,
    ),'Transfer Date');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}


$installer->endSetup();