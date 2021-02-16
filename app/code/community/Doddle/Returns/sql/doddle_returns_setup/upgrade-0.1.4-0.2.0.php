<?php
/** @var $installer Doddle_Returns_Model_Resource_Setup */
$setup = $this;
$setup->startSetup();

/**
 * Update the Doddle order sync queue schema for string based Doddle order ID
 */
$setup->getConnection()->changeColumn(
    $setup->getTable($setup->getTable('doddle_returns/order_sync_queue')),
    'doddle_order_id',
    'doddle_order_id',
    array(
        'type'   => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'comment'=> 'Doddle order ID reference from response'
    )
);

$setup->endSetup();
