<pre><?php
require_once('../app/Mage.php');
Mage::app();

$orders = Mage::getModel('sales/order')->getCollection()
//->addFieldToFilter('status', array( 'in' => array('processing', 'in_production')))
->addAttributeToFilter('main_table.store_id', array('eq' => 1))
->addAttributeToSelect('entity_id')
->addAttributeToSelect('increment_id');

$orders->getSelect()->joinLeft(
		array('invoice' => Mage::getSingleton('core/resource')->getTableName('sales/invoice')),
		'main_table.entity_id=invoice.order_id',
		array('state')
);
$orders->addAttributeToFilter('invoice.state', array('eq' => 1));

$count = 0;

$processingOrders = array();

foreach ($orders as $order) {
	$id = $order->getId();
	$incrementId = $order->getIncrementId();
	$email = $order->getCustomerEmail();
	
	$count += 1;
	
	$processingOrders[] = $id;
	
	echo $incrementId." :: ".$id . "\n";
}

echo "COUNT :: ".$count."\n";

die;

$invoices = Mage::getModel('sales/order_invoice')->getCollection();
$invoices->addAttributeToSelect('state');
// 1 = "open", 2 = "paid", 3 = "cancelled"
$invoices->addAttributeToFilter('state', array('eq' => 1));
$invoices->addAttributeToFilter('store_id', array('eq' => 1))
->addAttributeToSelect('order_id')
->addAttributeToSelect('increment_id');


$count = 0;

foreach ($invoices as $invoice) {
	$id = $invoice->getIncrementId();
	$orderId = $invoice->getOrderId();
	
	$order = Mage::getModel('sales/order')->load($orderId);
	
	$isProcessing = "NO";
	
	if (in_array($orderId, $processingOrders)) {
		$isProcessing = "YES";
	}
	
	//var_dump($invoice->getData());
	
	$count += 1;
	
	
	echo $id." :: ".$orderId. " :: ".$order->getIncrementId(). " :: ".$isProcessing."\n";
}

echo "COUNT :: ".$count;