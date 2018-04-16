<?php
$installer = $this;

$installer->startSetup();

$date = Mage::getModel('core/date')->gmtDate(null,'-2 days');
$guid = Mage::helper('teamwork_service')->getGuidFromString($date);

$installer->run("

INSERT INTO `{$this->getTable('service_chq')}` (`document_id`, `api_type`, `status`, `last_updated_time`, `created_at`, `updated_at`)
SELECT *
    FROM (SELECT '{$guid}' document_id, 'inventory-export' api_type, 'Successful' status, '{$date}' last_updated_time, '{$date}' created_at, '{$date}' updated_at) tbl
    WHERE EXISTS(
        SELECT channel_id FROM service_channel
    );
");
$installer->endSetup();