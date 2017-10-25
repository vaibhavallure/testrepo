<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$product = Mage::getModel('catalog/product')->load(32189);
$orderId=177251;
$order = Mage::getModel('sales/order')->load($orderId);

$quote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter("entity_id", $order->getQuoteId())->getFirstItem();


// Create quote if no quote was found
if (!$quote->getId()) {
    $quote = Mage::getModel('sales/convert_order')
    ->toQuote($order)
    ->setIsActive(false)
    ->setReservedOrderId($order->getIncrementId())
    ->save();
}

// Create the item for the quote
$quoteItem = Mage::getModel('sales/quote_item')
->setProduct($product)
->setQuote($quote)
->setQty(2);
$quoteItem->save();

//$quoteItemAdded=Mage::getModel('sales/quote_item')->load(134390);

$orderItem1 = Mage::getModel('sales/convert_quote')
->itemToOrderItem($quoteItem)
->save($order->getId());


echo "Done";


