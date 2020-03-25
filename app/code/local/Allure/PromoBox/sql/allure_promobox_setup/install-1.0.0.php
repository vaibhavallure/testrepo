<?php

$installer = $this;

$installer->startSetup();

/** creating allure_promobox_banner Table */

$table = $installer->getConnection()
    ->newTable($installer->getTable('promobox/banner'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Name')
    ->addColumn('html_block', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ), 'html_block')
    ->addColumn('size', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Size')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'image');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}


/** creating allure_promobox_category Table */

$table = $installer->getConnection()
    ->newTable($installer->getTable('promobox/category'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Category_id')
    ->addColumn('starting_row', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Starting row')
    ->addColumn('row_gap', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Row Gap')
    ->addColumn('size', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Size')
    ->addColumn('start_date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => true,
    ),'Start date')
    ->addColumn('end_date', Varien_Db_Ddl_Table::TYPE_DATETIME,null,array(
        'nullable'  => true,
    ),'End date')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable'  => false,
    ), 'Status');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}

/** creating allure_promoBox_box Table */

$table = $installer->getConnection()
    ->newTable($installer->getTable('promobox/box'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('promobox_category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Promo Box Category Id')
    ->addColumn('promobox_banner_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Promo Box Banner Id')
    ->addColumn('row_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Row number')
    ->addColumn('side', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'Side');

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();