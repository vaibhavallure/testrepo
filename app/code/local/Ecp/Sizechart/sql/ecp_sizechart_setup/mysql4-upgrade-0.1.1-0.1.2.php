<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE  `".$this->getTable('ecp_sizechart')."` ADD  `filename` text;
");

$installer->endSetup();