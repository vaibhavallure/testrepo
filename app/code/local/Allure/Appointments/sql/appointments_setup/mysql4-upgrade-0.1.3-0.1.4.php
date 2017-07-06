<?php
$installer = $this;
$installer->startSetup();
/* Piercers table */
$installer->run("
			alter table {$this->getTable('appointments/piercers')} 
			MODIFY 	working_days text;
		");
$installer->run("
		alter table {$this->getTable('appointments/piercers')}
		MODIFY 	working_hours text;
		");

$installer->endSetup();