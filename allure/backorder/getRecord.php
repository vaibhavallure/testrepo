<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

Mage::getModel('backorderrecord/cron')->getBackorderRecord();


echo "Done";
die;
