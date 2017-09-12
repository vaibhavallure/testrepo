<?php
$this->startSetup();
$this->addAttribute('quote', 'is_child_order',
        array(
                'type' => 'int',
                'label' => 'Is Child Order',
                'visible' => true,
                'required' => false,
                'default' => 0
        ));

$this->addAttribute('order', 'is_child_order',
        array(
                'type' => 'int',
                'label' => 'Is Child Order',
                'visible' => true,
                'required' => false,
                'default' => 0
        ));

$this->endSetup();
?>
