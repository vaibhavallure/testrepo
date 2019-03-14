<?php


$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('allure_harrodsinventory_product'),'ppc', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => false,
        'length'    => 10,
        'comment'   => 'ppc'
    ));
$installer->endSetup();

