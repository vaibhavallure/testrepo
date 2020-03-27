<?php
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('ecp_sales_report_mail_log')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `sent_time` timestamp not null DEFAULT CURRENT_TIMESTAMP,
    `is_sent` smallint(5) DEFAULT 0,
    `error_message` text DEFAULT null,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup(); 