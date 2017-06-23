<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$store_id = $_GET['store_id'];

if(empty($store_id)){
	die('Please add store_id');
}


$proxy = new SoapClient('http://mariatash.allurecommerce.com/api/v2_soap?wsdl=1'); 

$sessionId = $proxy->login((object)array('username' => 'allureinc', 'apiKey' => '12qwaszx')); 
$filter = array('filter' => array(array('key' => 'store_id', 'value' => $store_id)));
$orderResult = $proxy->salesOrderList((object)array('sessionId' => $sessionId->result, 'filters' => $filter));   
$successCount=0;
$failCount=0;
foreach ($orderResult->result->complexObjectArray as $order){
 try {
 	
 	$resultData = $proxy->salesOrderInfo((object)array('sessionId' => $sessionId->result, 'orderIncrementId' => $order->increment_id));
 	Mage::log("Creating order for:".$order->increment_id,Zend_log::DEBUG,'orderimport',true);
 	$result=$resultData->result;
 	$products=$result->items->complexObjectArray;
 	
 	$storeId=$result->store_id;
 	$store=Mage::getModel('core/store')->load($storeId);
 	$websiteId=$store->getWebsiteId();
 	
 	$quoteObj = Mage::getModel('sales/quote')->setStoreId($storeId);
 	
 	
 	$quoteObj->setCurrency($result->base_currency_code);
 	$customer = Mage::getModel('customer/customer')
 	->setWebsiteId($websiteId)
 	->loadByEmail($result->customer_email);
 	if(!$customer->getId()){
 		$customer = Mage::getModel('customer/customer');
 		$customer->setWebsiteId($websiteId)
 		->setStore($store)
 		->setFirstname($result->customer_firstname)
 		->setLastname($result->customer_lastname)
 		->setEmail($result->customer_email)
 		->setPassword("password");
 		$customer->save();
 	}
 	
 	// Assign Customer To Sales Order Quote
 	$quoteObj->assignCustomer($customer);
 	
 	// Configure Notification
 	$quoteObj->setSendCconfirmation(1);
 	foreach ($products as $prod){
 		$buyReuest=	new Varien_Object(unserialize($prod->product_options)['info_buyRequest']);
 		$product=Mage::getModel('catalog/product')->load($prod->product_id);
 		$quoteObj->addProduct($product,$buyReuest);
 	}
 	
 	$shipping_address=$result->shipping_address;
 	$billing_address=$result->billing_address;
 	// Set Sales Order Billing Address
 	$billingAddress = array(
 			'firstname' => $billing_address->firstname,
 				
 			'lastname' =>$billing_address->lastname,
 			'street' => array(
 					'0' => $billing_address->street,
 					'1' => ''
 			),
 			'city' => $billing_address->city,
 			'country_id' => $billing_address->country_id,
 			'region' => $billing_address->region,
 			'region_id' => $billing_address->region_id,
 			'postcode' => $billing_address->postcode,
 			'telephone' => $billing_address->telephone,
 	);
 	
 	$shippingAddress = array(
 			'firstname' => $shipping_address->firstname,
 	
 			'lastname' =>$shipping_address->lastname,
 	
 			'street' => array(
 					'0' => $shipping_address->street,
 					'1' => ''
 			),
 			'city' => $shipping_address->city,
 			'country_id' => $shipping_address->country_id,
 			'region' => $shipping_address->region,
 			'region_id' => $shipping_address->region_id,
 			'postcode' => $shipping_address->postcode,
 			'telephone' => $shipping_address->telephone,
 	);
 	
 	$quoteBillingAddress = Mage::getModel('sales/quote_address');
 	$quoteBillingAddress->setData($billingAddress);
 	$quoteObj->setBillingAddress($quoteBillingAddress);
 	
 	//if product is not virtual
 	if (!$quoteObj->getIsVirtual()) {
 	
 		$quoteShippingAddress = Mage::getModel('sales/quote_address');
 		$quoteShippingAddress->setData($shippingAddress);
 		$quoteObj->setShippingAddress($quoteShippingAddress);
 		// fixed shipping method
 		$quoteObj->getShippingAddress()->setShippingMethod($result->shipping_method);
 		$quoteObj->getShippingAddress()->setCollectShippingRates(true);
 		$quoteObj->getShippingAddress()->collectShippingRates();
 	}
 	
 	$quoteObj->collectTotals();
 	$quoteObj->save();
 	
 	$transaction = Mage::getModel('core/resource_transaction');
 	if ($quoteObj->getCustomerId()) {
 		$transaction->addObject($quoteObj->getCustomer());
 	}
 	$transaction->addObject($quoteObj);
 	$quoteObj->reserveOrderId();
 	
 	//$quoteObj->setCreateOrderMethod(1); //order status as counterpoint 1
 	// $quoteObj->setCounterpointOrderId($counterpointOrderId);
 	
 	$ccInfo = array();
 	// assign payment method
 	$payment_method = $result->payment->method;
 	
 	$quotePaymentObj = $quoteObj->getPayment();
 	$quotePaymentObj->setMethod($payment_method);
 	$quoteObj->setPayment($quotePaymentObj);
 	
 	$convertQuoteObj = Mage::getSingleton('sales/convert_quote');
 	if ($quoteObj->getIsVirtual()) {
 		$orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
 	} else {
 		$orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
 	}
 	
 	$orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
 	
 	$orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
 	$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
 	if (!$quoteObj->getIsVirtual()) {
 		$orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
 	}
 	
 	$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
 	
 	$items=$quoteObj->getAllItems();
 	
 	foreach ($items as $item) {
 		//@var $item Mage_Sales_Model_Quote_Item
 		$orderItem = $convertQuoteObj->itemToOrderItem($item);
 		if ($item->getParentItem()) {
 			$orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
 		}
 		$orderObj->addItem($orderItem);
 	}
 	$orderObj->setCanShipPartiallyItem(false);
 	
 	$totalDue = $orderObj->getTotalDue();
 	
 	$taxAmmount = $data['info']['tax'];
 	$totalAmmount = $quoteObj->getGrandTotal() + $taxAmmount;
 	
 	$orderObj->setTaxAmount($taxAmmount);
 	$orderObj->setGrandTotal($totalAmmount);
 	$orderObj->setBaseTaxAmount($taxAmmount);
 	$orderObj->setBaseGrandTotal($totalAmmount);
 	
 	//complete the order status
 	$orderObj->setData('state',$result->state)
 	->setData('status',$result->status);
 	
 	//$orderObj->setCreatedAt($result->created_at);
 	
 	$transaction->addObject($orderObj);
 	/* $transaction->addCommitCallback(array($orderObj, 'place'));
 	 $transaction->addCommitCallback(array($orderObj, 'save')); */
 	
 	try {
 		$transaction->save();
 	} catch (Exception $e){
 		Mage::log("Trans Exception-".$e->getMessage(), Zend_Log::DEBUG,"counter_point_order",true);
 		Mage::throwException('Order Cancelled.');
 	}
 	$increment_id = $orderObj->getRealOrderId();
 	
 	//	$quoteObj->setIsActive(0);
 	//	$quoteObj->save();
 	
 	//create invoice for created order
 	$ordered_items = $orderObj->getAllItems();
 	$savedQtys = array();
 	foreach($ordered_items as $item){     //item detail
 		$savedQtys[$item->getItemId()] = $item->getQtyOrdered();
 	}
 	$invoice = Mage::getModel('sales/service_order', $orderObj)->prepareInvoice($savedQtys);
 	$captureCase = "offline";
 	$invoice->setRequestedCaptureCase($captureCase);
 	$invoice->register();
 	$invoice->getOrder()->setIsInProcess(true);
 	
 	$invoice->setState(2);
 	$invoice->setCanVoidFlag(0);
 	
 	//$invoice->save();
 	$transactionSave = Mage::getModel('core/resource_transaction')
 	->addObject($invoice)
 	->addObject($invoice->getOrder());
 	$transactionSave->save();
 	
 	Mage::log("New Order Created:".$increment_id,Zend_log::DEBUG,'orderimport',true);
 	echo $increment_id;
 	echo "<br>";
 	$successCount++;
 } catch (Exception $e) {
 	$failCount++;
 	Mage::log("Exception Occured:".$e->getMessage(),Zend_log::DEBUG,'orderimport',true);
 	Mage::log("Unable to Create order for :".$order->increment_id,Zend_log::DEBUG,'orderimport',true);
 }
}
echo "successCount:".$successCount;
echo "<br>";
echo "failCount:".$failCount;

