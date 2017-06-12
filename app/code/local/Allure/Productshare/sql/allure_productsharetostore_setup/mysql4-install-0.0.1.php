<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	CREATE TABLE {$this->getTable('allure_productshare')}	
	(
		`ps_id` int NOT NULL auto_increment,
		`website_id` int default 0,
		`store_id` int default 0,
		`status` int default 0,
		`status_code` varchar(255) NOT NULL default 'none',
		`website_code` varchar(255) NOT NULL default '',
		`last_updated_product` int default 0,
		`execution` int default 1,
		
		PRIMARY KEY (`ps_id`)
	)
");

$installer->endSetup();

?>
