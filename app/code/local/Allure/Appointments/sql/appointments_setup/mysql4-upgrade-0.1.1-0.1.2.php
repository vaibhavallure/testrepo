<?php
$installer = $this;
$installer->startSetup();
/* Piercers table */
$installer->run("
			alter table {$this->getTable('appointments/appointments')} 
			add column `sms_status` text 
		");

$installer->endSetup();