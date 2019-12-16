<?php
require_once 'app/Mage.php';
umask(0);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$xml_path = Mage::getBaseDir('app') . DS . 'etc' . DS . 'local.xml';
$doc = new DOMDocument();
$doc->load($xml_path);

$mosConfig['host'] = $doc->getElementsByTagName("host")->item(0)->nodeValue;
$mosConfig['user'] = $doc->getElementsByTagName("username")->item(0)->nodeValue;
$mosConfig['pass'] = $doc->getElementsByTagName("password")->item(0)->nodeValue;
$mosConfig['dbna'] = $doc->getElementsByTagName("dbname")->item(0)->nodeValue;
echo '<pre>';
print_r($mosConfig);

$systemPath = $xml_path = Mage::getBaseDir('var') . DS . 'log' . DS . 'system.log';
$systemData = file($systemPath);
$sys_count = count($systemData);
echo "\n\n<h1>System Log</h1>\n\n";
if ($sys_count < 500) {
    for ($i = 0; $i < $sys_count; $i ++) {
        echo $systemData[$i] . "\n";
    }
} else {
    for ($i = $sys_count - 500; $i < $sys_count; $i ++) {
        echo $systemData[$i] . "\n";
    }
}

$exceptionPath = $xml_path = Mage::getBaseDir('var') . DS . 'log' . DS . 'exception.log';
$exceptionData = file($exceptionPath);
$ex_count = count($exceptionData);
echo "\n\n<h1>Exception Log</h1>\n\n";
if ($ex_count < 500) {
    for ($i = 0; $i < $ex_count; $i ++) {
        echo $exceptionData[$i] . "\n";
    }
} else {
    for ($i = $ex_count - 500; $i < $ex_count; $i ++) {
        echo $exceptionData[$i] . "\n";
    }
}

die();

$exceptionPath = $xml_path = Mage::getBaseDir('var') . DS . 'log' . DS . 'shipping_usps.log';
$exceptionData = file($exceptionPath);
$ex_count = count($exceptionData);
echo "\n\n<h1>USPS Log</h1>\n\n";
if ($ex_count < 500) {
    for ($i = 0; $i < $ex_count; $i ++) {
        echo $exceptionData[$i] . "\n";
    }
} else {
    for ($i = $ex_count - 500; $i < $ex_count; $i ++) {
        echo $exceptionData[$i] . "\n";
    }
}

$exceptionPath = $xml_path = Mage::getBaseDir('var') . DS . 'log' . DS . 'shipping_fedex.log';
$exceptionData = file($exceptionPath);
$ex_count = count($exceptionData);
echo "\n\n<h1>SHIPPING_FEDEX LOG</h1>\n\n";
if ($ex_count < 500) {
    for ($i = 0; $i < $ex_count; $i ++) {
        echo $exceptionData[$i] . "\n";
    }
} else {
    for ($i = $ex_count - 500; $i < $ex_count; $i ++) {
        echo $exceptionData[$i] . "\n";
    }
}