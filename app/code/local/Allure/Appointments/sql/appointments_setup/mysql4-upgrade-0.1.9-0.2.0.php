<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_piercing_appointments')} ADD updated_at timestamp");

$installer->endSetup();



