<?php
require_once '../app/Mage.php';
umask(0);

/*$function = $_GET['function'];
$password = $_GET['pass'];*/
Mage::app('admin');


Mage::getModel('alertservices/alerts')->alertAvgPageLoad();
/*Mage::getModel('alertservices/alerts')->alertPageNotFound();
Mage::getModel('alertservices/alerts')->alertNullUsers();
Mage::getModel('alertservices/alerts')->alertProductPrice();

Mage::getModel('alertservices/alerts')->alertSalesOfFour(true);

Mage::getModel('alertservices/alerts')->alertSalesOfSix();

Mage::getModel('alertservices/alerts')->alertCheckoutIssue();*/

echo "done";
