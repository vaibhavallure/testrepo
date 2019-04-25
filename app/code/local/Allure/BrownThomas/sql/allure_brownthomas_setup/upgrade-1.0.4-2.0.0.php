<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('brownthomas/product'))
    ->addColumn('row_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Row Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Product Id')
    ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => false,
    ),'Updated Date')
    ->addColumn('updated_date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => false,
    ),'Updated Date')
    ->addColumn('last_sent_date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => true,
    ),'Last Sent Date');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}


$installer->endSetup();