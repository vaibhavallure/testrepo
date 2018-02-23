<?php
$installer = $this;
$installer->startSetup();
$installer->run("

ALTER TABLE `{$this->getTable('service_items')}`
    ADD COLUMN `weight` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `attribute3_id`,
    ADD COLUMN `width` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `weight`,
    ADD COLUMN `height` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `width`,
    ADD COLUMN `length` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `height`,
    CHANGE COLUMN `customnumber1` `customnumber1` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `customlookup12`,
    CHANGE COLUMN `customnumber2` `customnumber2` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `customnumber1`,
    CHANGE COLUMN `customnumber3` `customnumber3` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `customnumber2`,
    CHANGE COLUMN `customnumber4` `customnumber4` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `customnumber3`,
    CHANGE COLUMN `customnumber5` `customnumber5` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `customnumber4`,
    CHANGE COLUMN `customnumber6` `customnumber6` DECIMAL(38,20) NOT NULL DEFAULT '0.00000000000000000000' AFTER `customnumber5`;

ALTER TABLE `{$this->getTable('service_status_items')}`
    DROP COLUMN `internal_id`;
");
$installer->endSetup();