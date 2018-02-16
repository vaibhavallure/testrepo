<?php
$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('allure_teamwork_customer')} ADD COLUMN counterpoint_cust_no VARCHAR(255) default null;");
$this->run("ALTER TABLE {$this->getTable('allure_teamwork_customer')} ADD COLUMN is_counterpoint_cust INT default 0;");
$this->run("alter table {$this->getTable('allure_teamwork_customer')} add COLUMN customer_note text default null;");
$this->endSetup();

?>
