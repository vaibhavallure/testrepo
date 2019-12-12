<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_appointment_customers')} ADD special_notes text default ''");

$installer->endSetup();



