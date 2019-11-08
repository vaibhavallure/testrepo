<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_appointment_customers')} ADD language_pref VARCHAR (10) default 'en'");

$installer->endSetup();



