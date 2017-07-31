<?php
$installer = $this;
$installer->startSetup();
/* Piercers table */

$installer->run("
		alter table {$this->getTable('appointments/piercers')}
			add column `color` text 
		");

$installer->endSetup();