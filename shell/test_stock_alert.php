<?php

echo "Started " . date("d/m/y h:i:s") . "<br/>";

define('TEST_MODE', 1);

require_once '../app/Mage.php';
umask(0);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

Mage::getModel('ecp_alertstock/observer')->process();