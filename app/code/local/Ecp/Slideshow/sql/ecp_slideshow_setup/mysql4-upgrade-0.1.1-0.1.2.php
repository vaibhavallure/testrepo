<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('ecp_slideshow')} ADD `slide_content` VARCHAR(100) NOT NULL AFTER `position`, ADD `background` VARCHAR(10) NOT NULL AFTER `slide_content`;
");

$installer->endSetup();
?>
