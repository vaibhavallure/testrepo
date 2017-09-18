<?php
$installer = $this;
$installer->startSetup();

$table = $this->getTable('tokenbase/card');

$installer->run("ALTER TABLE `{$table}` ADD COLUMN `name_on_card` varchar(255) default null");

$installer->endSetup();

?>
