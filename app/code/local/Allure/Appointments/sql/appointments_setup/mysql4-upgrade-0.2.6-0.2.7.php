<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_piercing_appointments')} ADD language_pref VARCHAR (10) default 'en'");

$installer->endSetup();



