<?php
$this->startSetup();
$this->addAttribute('quote_item', 'conversion_rate', array(
    'type'          => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'label'         => 'Conversion Rate',
    'visible'       => true,
    'required'      => false,
    'default'		=> null
));

$this->endSetup();

