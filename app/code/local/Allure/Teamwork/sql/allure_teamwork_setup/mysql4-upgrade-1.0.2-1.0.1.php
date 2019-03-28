<?php
$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('allure_customer_counterpoint')} ADD COLUMN is_non_mag_cust int default 0;");
$this->endSetup();

?>
