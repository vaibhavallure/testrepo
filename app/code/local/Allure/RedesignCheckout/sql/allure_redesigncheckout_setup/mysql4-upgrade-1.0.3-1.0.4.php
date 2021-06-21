<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('salesrule')} ADD custom_error_message text default NULL");

$installer->endSetup();
