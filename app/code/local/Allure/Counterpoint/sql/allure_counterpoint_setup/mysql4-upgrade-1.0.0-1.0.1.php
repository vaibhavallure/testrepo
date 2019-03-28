<?php
$this->startSetup();
$this->addAttribute('order', 'counterpoint_str_id', array(
    'type'          => 'int',
    'label'         => 'Counterpoint STR Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->addAttribute('quote', 'counterpoint_str_id', array(
    'type'          => 'int',
    'label'         => 'Counterpoint STR Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));


$this->addAttribute('order', 'counterpoint_sta_id', array(
    'type'          => 'int',
    'label'         => 'Counterpoint STA Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->addAttribute('quote', 'counterpoint_sta_id', array(
    'type'          => 'int',
    'label'         => 'Counterpoint STA Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->addAttribute('order', 'counterpoint_drw_id', array(
    'type'          => 'int',
    'label'         => 'Counterpoint DRW Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->addAttribute('quote', 'counterpoint_drw_id', array(
    'type'          => 'int',
    'label'         => 'Counterpoint DRW Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> 0
));

$this->addAttribute('order', 'counterpoint_doc_id', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint DOC Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote', 'counterpoint_doc_id', array(
    'type'          => 'varchar',
    'label'         => 'Counterpoint DOC Id',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('order', 'counterpoint_extra_info', array(
    'type'          => 'text',
    'label'         => 'Counterpoint Extra Info',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->addAttribute('quote', 'counterpoint_extra_info', array(
    'type'          => 'text',
    'label'         => 'Counterpoint Extra Info',
    'visible'       => true,
    'required'      => false,
    'default'		=> ''
));

$this->endSetup();

?>
