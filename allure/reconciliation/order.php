<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

$file=$_GET['file'];
$currentRate=$_GET['rate'];
$otherSysCurCode=$_GET['currency'];

$fileName="./order/".$file;
$lines = file($fileName);
$order_count=0; //user for total order count
$order=array();
if(!file_exists($fileName))
die("File Not Found...!");

if($file && $currentRate && $otherSysCurCode) {

    foreach ($lines as $lineNumber => $line) {
        if ($lineNumber > 0) {
//            var_dump($lines[$lineNumber]);
            $line_data = $lines[$lineNumber];
            $row = explode(',', trim($line_data));


            //check for is total row or not if not then add order item
            if (strpos($row[1], 'Total') == false) {
//                var_dump($row);

                $item = array();
                $transaction_id = $row[3];
                $time = $row[1] ? $row[1] : '00:00:00';
                $sku = trim($row[8]) ? strtoupper(trim($row[8])) : "NO SKU";
                $sku = str_replace(' ', '|', $sku);
                $item['sku'] = $sku;
                $item['retail_value'] = abs(trim($row[9])) ? abs(trim($row[9])) : 0;
                $item['sales'] = trim($row[10]) ? abs(trim($row[10])) : 0;
                $item['qty'] = trim($row[14]) ? trim($row[14]) : 0;

                // for replace color code into long descritiption
                if ($item['sku'] != null) {
                    $color = substr($sku, -2);
                    switch ($color) {
                        case "YG":
                            $color = "YELLOW GOLD";
                            $sku = substr($sku, 0, -2) . $color;
                            break;
                        case "WG":
                            $color = "WHITE GOLD";
                            $sku = substr($sku, 0, -2) . $color;
                            break;
                        case "RG":
                            $color = "ROSE GOLD";
                            $sku = substr($sku, 0, -2) . $color;
                            break;
                        case "BG":
                            $color = "BLACK GOLD";
                            $sku = substr($sku, 0, -2) . $color;
                            break;
                        case "AC":
                            $color = "AFTER CARE";
                            $sku = substr($sku, 0, -2) . $color;
                            break;
                    }
                    $item['sku'] = $sku;
                } else {
                    if ($item['retail_value'] >= 6) {
                        $item['sku'] = "AFTER CARE|" . $item['retail_value'];
                    }

                }
                array_push($order, $item);

            } else {
                if ($order) {


//                        var_dump($order);
                    $order_date = trim($row[0]); //order date specified in first row of total
                    echo "ORDER DATE :".$order_date." TIME:".$time."<br>";
                    $increment_number=createOrder($order,$order_date,$time,$transaction_id,$otherSysCurCode,$currentRate);//function for creating manual order
                    $order = null;
                    $order = array();
                    $transaction_id = "";
                    if ($increment_number)
                    {
                        $order_count++;
                        $lines[$lineNumber] = trim($lines[$lineNumber]) . "," . $increment_number .","."ORDER:".$order_count. PHP_EOL;
                        file_put_contents($fileName, $lines);

                    }
                    else
                    {
                        $lines[$lineNumber] = trim($lines[$lineNumber]) . "," . "ORDER ID NA" . PHP_EOL;
                        file_put_contents($fileName, $lines);
                    }
                }
            }

        }

    }
    Mage::log("Total Orders : ".$order_count,Zend_Log::DEBUG,'reconciliation.log',true);
    echo "<br>Total Order Created :".$order_count;
}

//CREATE ORDER FUNCTION

function createOrder($order,$order_date,$time,$transaction_id,$_otherSysCurCode,$_currentRate)
{

    Mage::app()->setCurrentStore(0);
    $websiteId = Mage::app()->getWebsite()->getId();
    $store=Mage::app()->getStore();
    $email = "ebizmart@ebiz.com";


    try {

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);

        if($customer->getId()==""){
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname('Ebiz')
                ->setLastname('Mart')
                ->setEmail($email)
                ->setPassword("password");
            $customer->save();
        }
//var_dump($customer);
        $billingAddress = Mage::getModel('sales/quote_address')
            ->setFirstname($customer->getFirstname())
            ->setLastname($customer->getLastname());

        $billingAddress->setCity("London");
        $billingAddress->setRegion("London");
        $billingAddress->setPostcode("WC2N 5DU");
        $billingAddress->setCountryId("UK");
        $billingAddress->setStreet(array(
            '0' => '907 Palmer Avenue',
            '1' => 'H3'
        ));
        $billingAddress->setTelephone("123456789");

        $quoteObj = Mage::getModel('sales/quote')
            ->assignCustomer($customer);
        $quoteObj = $quoteObj->setStoreId(1);


        $otherSysCurCode = $_otherSysCurCode;
        $currencyRate = $_currentRate;


        $quoteObj->setOldStoreId(2);

        $discount=0;
        foreach ($order as $item) {
            $productObj = Mage::getModel('catalog/product');
            $productObj->setTypeId("simple");
            $productObj->setSku($item['sku']);
            $productObj->setName($item['sku']);
            $productObj->setShortDescription("Test Reconciliation Product");
            $productObj->setDescription("Test Reconciliation Description");
            $price=$item['retail_value']/abs($item['qty']);
            $productObj->setPrice($price);

            $discount = $discount+($item['retail_value'] - $item['sales']);

            $quoteItem = Mage::getModel("allure_counterpoint/item")
                ->setProduct($productObj);
            $quoteItem->setQty($item['qty']);


            $quoteItem->setStoreId(1);

            $quoteObj->addItem($quoteItem);
            $productObj = null;

        }
        $quoteObj->setBillingAddress($billingAddress);
        if (!$quoteObj->getIsVirtual()) {
            $shippingAddress = $billingAddress;
            $quoteObj->setShippingAddress($shippingAddress);
            // fixed shipping method
            $quoteObj->getShippingAddress()
                ->setShippingMethod("tm_storepickupshipping");
        }

        $quoteObj->collectTotals();
        $quoteObj->save();

        if ($discount > 0) {

            $quoteObj->setGrandTotal($quoteObj->getBaseSubtotal() - $discount)
                ->setBaseGrandTotal($quoteObj->getBaseSubtotal() - $discount)
                ->setSubtotalWithDiscount($quoteObj->getBaseSubtotal() - $discount)
                ->setBaseSubtotalWithDiscount($quoteObj->getBaseSubtotal() - $discount)
                ->save();
        }
        //To Identify Ebiz Order
        if($transaction_id){
            $transaction_id = "EB-".$transaction_id;
//            echo "<br>Transaction ID".$transaction_id."<br>";
            $quoteObj->setReservedOrderId($transaction_id);
        }

        $quoteObj->setData('base_currency_code', $otherSysCurCode)
            ->setData('global_currency_code', "USD")
            ->setData('quote_currency_code', $otherSysCurCode)
            ->setData('store_currency_code', $otherSysCurCode);

        $quoteObj->setStoreToBaseRate($currencyRate)
            ->setStoreToQuoteRate(1)
            ->setBaseToGlobalRate($currencyRate)
            ->setBaseToQuoteRate(1);

        $payment_method = "tm_pay_cash";
        $quotePaymentObj = $quoteObj->getPayment();
        $quotePaymentObj->setMethod($payment_method);
        $quoteObj->setPayment($quotePaymentObj);


        $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
        if ($quoteObj->getIsVirtual()) {
            $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
        } else {
            $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
        }

        $items = $quoteObj->getAllItems();
        foreach ($items as $item) {
            $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
            $orderItem = $convertQuoteObj->itemToOrderItem($item);

            if ($item->getParentItem()) {
                $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
            }

            $orderObj->addItem($orderItem);

        }
        $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));

        if (!$quoteObj->getIsVirtual()) {
            $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
        }

        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
//        echo "Discount :".$discount;
        if($discount>0) {
            $orderObj->setGrandTotal($orderObj->getGrandTotal() - $discount);
            $orderObj->setBaseGrandTotal($orderObj->getBaseGrandTotal() - $discount);
            $orderObj->setBaseDiscountAmount($discount);
            $orderObj->setDiscountAmount($discount);
        }
        $orderObj->save();



        echo "Order Created : ".$orderObj->getIncrementId()."<br>";
        $orderId=$orderObj->getIncrementId();
        $orderEntityId=$orderObj->getId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $order->setCreatedAt($order_date.$time); //or whatever date you wish
        $order->save();
        Mage::log("Order Created : ".$orderId,Zend_Log::DEBUG,'reconciliation.log',true);
        Mage::log("Entity ID : ".$orderEntityId,Zend_Log::DEBUG,'reconciliation.log',true);
        return $orderId;
    } catch (Exception $e) {
//echo $e->getMessage();
        Mage::log($e->getMessage(),Zend_Log::DEBUG,'reconciliation.log',true);
    }

}

