<?php
$this->startSetup();


$setup = Mage::getModel ( 'customer/entity_setup' , 'core_setup' );

$setup->addAttribute('customer', 'is_duplicate', array(
    'type' => 'int',
    'input' => 'text',
    'label' => 'Is Duplicate',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => 0,
    'visible_on_front' => 1,
    'source' =>   NULL
));

$this->endSetup();

?>
