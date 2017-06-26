<pre><?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

$storeId 		= (int) $_GET['store'];
$incrementId 	= $_GET['id'];

$products = array();

if (empty($storeId) || !$storeId) {
	$storeId = 1;
}

$ordersCollection = Mage::getModel('sales/order')->getCollection()
//->addFieldToFilter('status', array( 'in' => array('processing', 'in_production')))
->addAttributeToFilter('main_table.store_id', array('eq' => $storeId))
->addAttributeToSelect('entity_id')
->addAttributeToSelect('customer_id')
->addAttributeToSelect('customer_group_id')
->addAttributeToSelect('increment_id');

if (isset ($incrementId) && $incrementId) {
	$ordersCollection->addAttributeToFilter('main_table.increment_id', array('eq' => $incrementId));
}

// Get All Pending Invoices
$ordersCollection->getSelect()->joinLeft(
		array('invoice' => Mage::getSingleton('core/resource')->getTableName('sales/invoice')),
		'main_table.entity_id=invoice.order_id',
		array('state')
);

$ordersCollection->addAttributeToFilter('invoice.state', array('eq' => 1));

$count = 0;

$processingOrders = array();

foreach ($ordersCollection as $order) {
	$id = $order->getId();
	$incrementId = $order->getIncrementId();
	$email = $order->getCustomerEmail();
	
	$customerGroupId = $order->getCustomerGroupId();
	
	$invoices = $order->getInvoiceCollection();
	
	$customerGroup = "Guest";
	
	switch ($customerGroupId) {
		case 1:
			$customerGroup = 'General';
			break;
		case 2:
			$customerGroup = 'Wholesale';
			break;
		case 3:
			$customerGroup = 'Retailer';
			break;
		case 4:
			$customerGroup = 'Press';
			break;
	}
	
	if ($invoices->count()) {
		
		$count += 1;
		
		echo $incrementId." :: ".$id . " :: $customerGroup \n";
		echo "INVOICES=>".$invoices->count()."\n";
		
		$orderInvoice = null;
		
		foreach ($invoices as $invoice){
			if (!$invoice->isCanceled()) {
				$orderInvoice = $invoice;
				echo "INVOICE:: ".$invoice->getId()." \n";
				var_dump(array(
						'amount_paid' => $invoice->getGrandTotal(),
						'base_amount_paid' => $invoice->getBaseGrandTotal(),
						'shipping_captured' => $invoice->getShippingAmount(),
						'base_shipping_captured' => $invoice->getBaseShippingAmount(),
				));
			}
		}
		
		$payment = $order->getPayment();
		
		echo "PAYMENT::".$payment->getId()."\n";
		var_dump($payment->getData());
// 		var_dump(array(
// 				'amount_paid' => $payment->getAmountOrdered(),
// 				'base_amount_paid' => $payment->getBaseAmountOrdered(),
// 				'shipping_captured' => $payment->getShippingAmount(),
// 				'base_shipping_captured' => $payment->getBaseShippingAmount(),
// 		));
		
		if ($payment->getAmountOrdered() == null && $orderInvoice) {
			//$payment->pay($orderInvoice);
		}
	}
}

echo "COUNT :: ".$count."\n";