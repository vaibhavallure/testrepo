<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);
echo "<pre>";

try{
    $orderId = '198316';
    
    $product = Mage::getModel('catalog/product')->setStoreId(1)->load(32286);
    //var_dump($product->getData('price'));
    //die;
    /* $product->setTypeId("simple");
    $product->setTaxClassId(1);
    $product->setSku("RST4SC35D|ROSE GOLD|6.5MM|16");
    $product->setName("Sagar");
    $product->setShortDescription("Test");
    $product->setDescription("Test");
    $product->setPrice(10.00); */
    
    $order = Mage::getModel("sales/order")->load($orderId);
    
    $quote = Mage::getModel('sales/convert_order')
        ->toQuote($order)
        ->setIsActive(false)
        ->setReservedOrderId($order->getIncrementId())
        ->save();
    /* $quote = Mage::getModel('sales/quote')->getCollection()
        ->addFieldToFilter("entity_id", $order->getQuoteId())
        ->getFirstItem();
     */
    echo "Fi.";
    $qty = 1;
    $price = $product->getPrice();
    $rowTotal = $price * $qty;
    
    $quoteItem = Mage::getModel('allure_counterpoint/item')
        ->setProduct($product);
    $quoteItem->setStoreId(1)
        ->setQty($qty);
    
    $quoteItem->setQuote($quote);
    $quoteItem->setPrice($product->getPrice());
    $quoteItem->setBasePrice($price);
    $quoteItem->setRowTotal($rowTotal);
    $quoteItem->setBaseRowTotal($rowTotal);
    $quoteItem->setPriceInclTax($price);
    $quoteItem->setBasePriceInclTax($price);
    $quoteItem->setRowTotalInclTax($rowTotal);
    $quoteItem->setBaseRowTotalInclTax($rowTotal);
    $quoteItem->save();
    
    $items = array();
    $quoteItemId = $quoteItem->getId();
    $items[$quoteItemId]["product_id"] = $quoteItem->getProductId();
    $items[$quoteItemId]["description"] = $quoteItem->getDescription();
    $items[$quoteItemId]["original_price"] = $price;
    $items[$quoteItemId]["price"] = $price;
    $items[$quoteItemId]["price_incl_tax"] = $price;
    $items[$quoteItemId]["qty"] = $quoteItem->getQty() ;
    $items[$quoteItemId]["subtotal"] = $rowTotal;
    $items[$quoteItemId]["subtotal_incl_tax"] = $rowTotal;
    $items[$quoteItemId]["tax_amount"] = $quoteItem->getTaxAmount();
    $items[$quoteItemId]["hidden_tax_amount"] = $quoteItem->getHiddenTaxAmount();
    $items[$quoteItemId]["weee_tax_applied_row_amount"] = $quoteItem->getWeeeTaxAppliedRowAmount();
    $items[$quoteItemId]["tax_percent"] = $quoteItem->getTaxPercent();
    $items[$quoteItemId]["discount_amount"] = $quoteItem->getDiscountAmount();
    $items[$quoteItemId]["discount_percent"] = $quoteItem->getDiscountPercent();
    $items[$quoteItemId]["row_total"] = $rowTotal;
    $items[$quoteItemId]["quote_item"] = $quoteItem->getId();
    
    //$quote->addItem($quoteItem);
    /* $quote->collectTotals();
    $quote->save();
    echo "Fii..";
    
    $orderItem = Mage::getModel('sales/convert_quote')
        ->itemToOrderItem($quoteItem)
        ->setOrderId($order->getId());
    
    $orderItem->save($order->getId()); */
    
    Mage::getModel('iwd_ordermanager/order_edit')
    ->editItems($order->getId(), $items);
    
    echo "Fiii...";
    
}catch (Exception $e){
    print_r($e);
}
die("finish");