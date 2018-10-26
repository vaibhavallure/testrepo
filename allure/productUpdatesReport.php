<?php
require_once '../app/Mage.php';
umask(0);
Mage::app('admin');


$dt = array();
//Mage::getModel('productupdatereport/cron')->getProductUpdatesCollection(false);
Mage::getModel('productupdatereport/cron')->getProductUpdatesReport();

echo "done";