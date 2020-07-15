<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  {$this->getTable('allure_appointment_customers')} ADD ans_covid_section SMALLINT default 0");

$installer->endSetup();



