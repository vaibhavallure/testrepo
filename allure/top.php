<?php
require_once '../app/Mage.php';
umask(0);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$type = (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) ? $_REQUEST['type'] : 'log';
$name = (isset($_REQUEST['name']) && !empty($_REQUEST['name'])) ? $_REQUEST['name'].'.log' : 'exception.log';

$log_name = str_replace("_", ' ',basename($name, '.log'));

$log_label = ucwords($log_name);

$systemPath = $xml_path = Mage::getBaseDir('var') . DS . $type . DS . $name;
$systemData = file($systemPath);
$sys_count = count($systemData);
echo "\n\n<h2>{$log_label} Log</h2>\n\n<pre>";
if ($sys_count < 500) {
    for ($i = 0; $i < $sys_count; $i ++) {
        echo $systemData[$i] . "\n";
    }
} else {
    for ($i = $sys_count - 500; $i < $sys_count; $i ++) {
        echo $systemData[$i] . "\n";
    }
}