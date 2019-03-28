<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();


if(Mage::helper("backorderrecord/config")->getDebugStatus())
    Mage::log('Manual call to send mail function',Zend_Log::DEBUG, 'backorder_data.log', true);


Mage::getModel('backorderrecord/cron')->getBackorderRecord();


echo "Done";
die;
