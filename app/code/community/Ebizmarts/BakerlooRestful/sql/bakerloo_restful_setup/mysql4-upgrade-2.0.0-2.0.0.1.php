<?php

$installer = $this;
$installer->startSetup();

$shifts = $installer->getTable('bakerloo_restful/shift');
$installer->getConnection()->addColumn($shifts, 'json_payload', "TEXT NULL DEFAULT NULL");

$installer->endSetup();
