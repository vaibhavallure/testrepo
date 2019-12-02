<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('ecp_slideshow')} ADD `switch` VARCHAR(10) NOT NULL AFTER `background`;
");

$installer->endSetup();
?>
