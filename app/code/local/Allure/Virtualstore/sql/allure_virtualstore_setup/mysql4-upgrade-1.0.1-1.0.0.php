<?php
$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('allure_virtual_store')} ADD COLUMN utc_offset VARCHAR(255) default null;");
$this->endSetup();

?>
