<?php
/*
 * script to assign store id to all pages and static blocks
 *
 * */

require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);

ob_start();

if ($argv[1]) {
    setStore($argv[1], "page");
    setStore($argv[1], "block");
} else {
    addLogStream("Plz specify store id");
    die();
}
function addLogStream($message)
{
    echo $message . "\n";
    flush();
    ob_end_flush();
}

function setStore($store_id, $type)
{
    $resource = Mage::getSingleton('core/resource');

    $id_key = "{$type}_id";
    $table = "cms_{$type}_store";

    $readConnection = $resource->getConnection('core_read');
    $writeConnection = $resource->getConnection('core_write');

    $query = "SELECT DISTINCT({$id_key}) FROM {$table}";
    $results = $readConnection->fetchAll($query);

    foreach ($results as $page) {
        $query = "SELECT * FROM `$table` WHERE {$id_key}={$page[$id_key]} AND store_id={$store_id}";
        $record = $readConnection->fetchAll($query);
        if (!count($record)) {
            $q = "INSERT INTO `$table`(`{$id_key}`, `store_id`) VALUES ({$page[$id_key]},{$store_id})";
            $writeConnection->query($q);

            addLogStream("INSERTED=> {$type}:{$page[$id_key]} , STORE_ID:{$store_id}");
        } else {
            addLogStream("PRESENT=> {$type}:{$page[$id_key]} , STORE_ID:{$store_id}");
        }
    }

}