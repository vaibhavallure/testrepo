<?php
$installer = $this;
$installer->startSetup();
/* Piercers table */
$installer->run("
			alter table {$this->getTable('appointments/timing')} 
			add column `store_id` int  DEFAULT 1
		");

$installer->endSetup();