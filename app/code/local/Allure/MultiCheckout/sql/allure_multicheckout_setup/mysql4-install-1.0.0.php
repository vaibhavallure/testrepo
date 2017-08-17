<?php
$this->startSetup();
$this->addAttribute('order', 'delivery_method',
        array(
                'type' => 'varchar',
                'label' => 'Delivery Method',
                'visible' => true,
                'required' => false,
                'default' => ''
        ));

$this->addAttribute('quote', 'delivery_method',
        array(
                'type' => 'varchar',
                'label' => 'Delivery Method',
                'visible' => true,
                'required' => false,
                'default' => ''
        ));

$this->endSetup();
?>
