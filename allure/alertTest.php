<?php
require_once '../app/Mage.php';
umask(0);

/*$function = $_GET['function'];
$password = $_GET['pass'];*/
Mage::app('admin');


Mage::getModel('alertservices/alerts')->alertAvgPageLoad();
Mage::getModel('alertservices/alerts')->alertPageNotFound();
Mage::getModel('alertservices/alerts')->alertNullUsers();
Mage::getModel('alertservices/alerts')->alertProductPrice();
Mage::getModel('alertservices/alerts')->alertSalesOfSix();
Mage::getModel('alertservices/alerts')->alertCheckoutIssue();
Mage::getModel('alertservices/alerts')->alertSalesOfFour(true);
//66.65.83.126 old ip
//203.109.124.232 sess
echo "done";
