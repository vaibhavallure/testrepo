<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_email/queue'), 'delete_attachment', 'TINYINT(1) NOT NULL DEFAULT 0');
$installer->getConnection()->update($installer->getTable('bakerloo_email/queue'), array('delete_attachment' => 1), array('email_result' => array('eq' => 1)));

$installer->endSetup();
