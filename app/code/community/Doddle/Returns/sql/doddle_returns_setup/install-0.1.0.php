<?php
/** @var $installer Doddle_Returns_Model_Resource_Setup */
$setup = $this;
$setup->startSetup();

/**
 * Generate the Doddle order sync queue schema
 */
$orderSyncTable = $setup->getConnection()->newTable($setup->getTable('doddle_returns/order_sync_queue'))
    ->addColumn(
        'sync_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'primary' => true,
            'auto_increment' => true,
            'nullable' => false,
            'unsigned' => true,
        ),
        'Order sync ID'
    )
    ->addColumn(
        'order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false
        ),
        'Magento order enity ID'
    )
    ->addColumn(
        'status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
            'nullable' => true,
            'default' => Doddle_Returns_Model_Order_Sync_Queue::STATUS_PENDING
        ),
        'Sync status'
    )
    ->addColumn(
        'fail_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'default' => 0
        ),
        'Count of failed attempts to sync'
    )
    ->addColumn(
        'doddle_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true
        ),
        'Doddle order ID reference from response'
    )
    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => true
        ),
        'Created timestamp'
    )
    ->addColumn(
        'updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => true
        ),
        'Updated timestamp'
    )
    ->addIndex(
        $setup->getIdxName(
            'doddle_returns/order_sync_queue', array('status')
        ),
        array('status')
    )
    ->addForeignKey(
        $setup->getFkName(
            'doddle_returns/order_sync_queue', 'order_id', 'sales/order', 'entity_id'
        ),
        'order_id',
        $setup->getTable('sales/order'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Doddle Returns order sync');

$setup->getConnection()->createTable($orderSyncTable);

/**
 * Install new product attribute for Doddle return eligibility status (remove first if already installed)
 */
$setup->removeAttribute(
    'catalog_product',
    'doddle_returns_excluded'
);

$setup->addAttribute(
    'catalog_product',
    'doddle_returns_excluded',
    array(
        'group'           => 'Doddle Returns',
        'label'           => 'Exclude from Doddle Returns',
        'type'            => 'int',
        'input'           => 'select',
        'source'          => 'eav/entity_attribute_source_boolean',
        'default'         => 0,
        'required'        => false,
        'is_required'     => false,
        'user_defined'    => true,
        'is_configurable' => false,
        'note'            => 'Products flagged as excluded will not be available to return via Doddle Returns.'
    )
);

$setup->endSetup();
