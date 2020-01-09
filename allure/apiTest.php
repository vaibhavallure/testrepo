<?php
require_once '../app/Mage.php';
umask(0);

/*$function = $_GET['function'];
$password = $_GET['pass'];*/
Mage::app('admin');

$num_quotes = 5;

define('ALLURE_TEST', true);

defined('ALLURE_TEST');

$query = Mage::getResourceModel('sales/quote_collection')
->addFieldToFilter('converted_at', array('null' => true))
->addFieldToFilter('create_order_method', '0')
->addOrder('updated_at', 'desc')
->setPageSize($num_quotes)
->setCurPage(1);

$quotes = array();
foreach ($query as $quote) {
	$email = $quote->getCustomerEmail();
	
	if ($email) {
		$pieces = explode('@', $email, 2);
		
		// Obfuscates the email address from `someone@example.com` to `so******@example.com`.
		$email = substr($pieces[0], 0, 2) . str_repeat('*', 6) . '@' . $pieces[1];
	}
	
	$quotes[] = array(
		'id'             => $quote->getEntityId(),
		'store_id'       => $quote->getStoreId(),
		'gmt_created_at'     => Mage::getSingleton('core/date')->gmtDate($quote->getCreatedAt()),
		'gmt_updated_at'     => Mage::getSingleton('core/date')->gmtDate($quote->getUpdatedAt()),
		'customer_email' => $email,
		'remote_ip'      => $quote->getRemoteIp(),
		'num_items'      => count($quote->getItemsCollection()),
		'is_active'      => $quote->getIsActive()
	);
}

print_r($quotes);

$ruleId = 483;

$rule = Mage::getModel('salesrule/rule')->load($ruleId);

$couponCollection = Mage::getResourceModel('salesrule/coupon_collection');
$couponCollection->addRuleToFilter($rule);

$coupons = $couponCollection->load()->toArray();

print_r($coupons);
die;

