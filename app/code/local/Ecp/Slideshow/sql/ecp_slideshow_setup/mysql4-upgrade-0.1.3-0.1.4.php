<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('ecp_slideshow')} ADD `slide_mobile_background` VARCHAR(200) NOT NULL default '';
");

$installer->endSetup();
?>
