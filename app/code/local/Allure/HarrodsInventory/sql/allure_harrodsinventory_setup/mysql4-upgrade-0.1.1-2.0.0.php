<?php


$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('allure_harrodsinventory_price'),'file_generated', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => false,
        'length'    => 10,
        'comment'   => 'File Generated'
    ));
$installer->endSetup();

