<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */

$entities = array(
    "quote",
    "order"
);


$installer->addAttribute('quote_address', 'no_signature_delivery',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'visible' => true,
        'required' => false,
        'default' => 0
    )
);

$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'default'  => 0,
    'visible'  => true,
    'required' => false
);

foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'has_gift_item', $options);
}

$itemEntities = array(
    'quote_item',
    'order_item',
    'quote_address_item'
);
$itemOptions = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'default'  => 0,
    'visible'  => true,
    'required' => false
);

foreach ($itemEntities as $entity) {
    $installer->addAttribute($entity, 'is_gift_item', $itemOptions);
    $installer->addAttribute($entity, 'gift_item_qty', $itemOptions);
    $installer->addAttribute($entity, 'is_gift_wrap', $itemOptions);
    $installer->addAttribute($entity, 'gift_wrap_qty', $itemOptions);
}

$installer->addAttribute("shipment", "prefered_shipping_code", array(
    'type'          => 'varchar',
    'label'         => 'Shipping Code',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$installer->addAttribute("shipment", "prefered_shipping_description", array(
    'type'          => 'varchar',
    'label'         => 'Shipping Description',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$installer->addAttribute("shipment", "prefered_shipping_price", array(
    'type'          => 'decimal',
    'label'         => 'Shipping Price',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0.0
));

$installer->endSetup();
