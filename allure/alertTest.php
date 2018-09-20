<?php
require_once '../app/Mage.php';
umask(0);

/*$function = $_GET['function'];
$password = $_GET['pass'];*/
Mage::app('admin');

Mage::getModel('alertservices/alerts')->alertProductPrice();

Mage::getModel('alertservices/alerts')->alertSalesOfFour(true);

Mage::getModel('alertservices/alerts')->alertSalesOfSix(true);

Mage::getModel('alertservices/alerts')->alertCheckoutIssue();

echo "done";
