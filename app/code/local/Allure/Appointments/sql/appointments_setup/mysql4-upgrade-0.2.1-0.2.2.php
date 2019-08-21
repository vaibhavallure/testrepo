<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_piercing_appointments')} ADD special_store INT(1) default 0");

$installer->endSetup();



