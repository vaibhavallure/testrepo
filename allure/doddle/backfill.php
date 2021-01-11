<?php

require_once('../../app/Mage.php');
Mage::app();

$write = Mage::getSingleton("core/resource")->getConnection("core_write");
$sql = "INSERT IGNORE INTO doddle_returns_order_sync_queue (order_id, status, fail_count, created_at)
		SELECT entity_id, 'pending', '0', NOW()
		FROM sales_flat_order
		WHERE created_at > NOW() - INTERVAL 60 DAY";	
    
    

$write->query($sql);    

?>