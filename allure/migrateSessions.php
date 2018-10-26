<?php
require_once '../app/Mage.php';
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app();
/**
* Set up the redis client
*/
/**
* Set up the Session Model to help with compression and other misc items.
*/
$redisSession = Mage::getSingleton('core_mysql4/session');
/**
* Get the resource model
*/
$resource = Mage::getSingleton('core/resource');
/**
* Retrieve the read connection
*/
$readConnection = $resource->getConnection('core_read');
// 1. query to order by session_expires, limit N, save last expire time, and session_id
// 2. modify query with where session_expires >= last expire time, and session_id != session_id
$expiresAt = 0;
$lastId = 'NONE';

if (isset($_REQUEST['lastId'])) {
	$lastId = $_REQUEST['lastId'];
}


if (isset($_REQUEST['expiresAt'])) {
	$expiresAt = strtotime($_REQUEST['expiresAt']);
}

$batchlimit = 1000;
do {
    //$query = 'SELECT session_id, session_expires, session_data FROM ' . $resource->getTableName('core/session').
    //         ' WHERE session_expires >= ? AND session_id != ? ORDER BY session_expires ASC LIMIT '.$batchlimit;
    //$results = $readConnection->fetchAll($query, array($exptime, $lastid));
    $query = $readConnection->select()
                        ->from(array('cs'=>$resource->getTableName('core/session')),
                               array('session_id', 'session_expires', 'session_data'))
                        ->having("session_expires > ?", $expiresAt)
                        ->having("session_id != ?", $lastId)
                        ->limit($batchlimit)
                        ->order('session_expires ' . Varien_Data_Collection::SORT_ORDER_DESC);
    $results = $readConnection->fetchAll($query);
    //var_dump($results);
    foreach($results as $row) {
        $lastid = $row['session_id'];
        $exptime = $row['session_expires'];
        $sesskey = $lastid;
        $redisSession->write($sesskey, $row['session_data']);
        echo $lastid . " " . date('Y-m-d H:i:s', $exptime) . "\n";
    }
    echo "----------------------------------\n";
} while( !empty($results) );
