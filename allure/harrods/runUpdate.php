<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();


Mage::helper("harrodsinventory")->add_log("Manual call to update inventory function");
Mage::getModel('harrodsinventory/cron')->updateHarrodsInventory();
Mage::helper("harrodsinventory")->add_log("value Updated");
Mage::helper("harrodsinventory")->generateReport();
Mage::helper("harrodsinventory")->add_log("report generated");

echo "Done";
die;
