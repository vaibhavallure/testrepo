<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('ecp_slideshow')} ADD `position` int(2) NOT NULL DEFAULT 0;
");

$installer->endSetup();
?>
