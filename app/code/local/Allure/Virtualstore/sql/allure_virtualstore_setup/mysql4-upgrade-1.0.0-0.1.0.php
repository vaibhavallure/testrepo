<?php
$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('allure_virtual_store')} ADD COLUMN IF NOT EXISTS tm_location_code VARCHAR(255) default null;");
$this->endSetup();

?>
