<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$file = $_GET["file"];

if(empty($file)){
    die("Specify file path.");
}

$alphabets = range('A','Z');
$numbers = range('0','9');
$additional_characters = array('#','@','$');
$final_array = array_merge($alphabets,$numbers,$additional_characters);


$teamworkLog = "customer_in_teamwork.log";

$folderPath   = Mage::getBaseDir("var") . DS .$file;

$csvData = array();
if(($handle = fopen($folderPath, "r")) != false){
    $max_line_length = defined("MAX_LINE_LENGTH") ? MAX_LINE_LENGTH : 10000;
    $header = fgetcsv($handle, $max_line_length);
    foreach ($header as $c => $_cols){
        $header[$c] = strtolower(str_replace(" ", "_", $_cols));
    }
    
    $header_column_count = count($header);
    
    while (($row = fgetcsv($handle,$max_line_length)) != false){
        $row_column_count = count($row);
        if($row_column_count == $header_column_count){
            $entry = array_combine($header, $row);
            $csvData[] = $entry;
        }
    }
    fclose($handle);
    
    if(count($csvData)){
        $websiteId = 1;
        
        $existCnt = 0;
        $nonExistCnt = 0;
        
        foreach ($csvData as $data){
            
            $tData = unserialize($data["order"]);
            
            foreach ($tData as $receiptId => $oData){
                
                
                try{
                    
                    $orderObj = Mage::getModel('sales/order')->load($receiptId,'teamwork_receipt_id');
                    
                    if($orderObj->getId()){
                        Mage::log("Receipt Id:".$receiptId." Order Id:".$orderObj->getId()." present",Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $orderDetails = $oData["order_detail"];
                    
                    $email = trim($orderDetails["EmailAddress"]);
                    
                    if(empty($email)){
                        $nonExistCnt++;
                        Mage::log("Email Id is Empty." ,Zend_log::DEBUG,$teamworkLog,true);
                        continue;
                    }
                    
                    $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($websiteId)
                    ->loadByEmail($email);
                    
                    
                    
                    if($customer->getId()){
                        
                        $billingAddress = $customer->getDefaultBillingAddress();
                        
                        $quoteObj = Mage::getModel('sales/quote')
                        ->assignCustomer($customer);
                        
                        $quoteObj = $quoteObj->setStoreId(1);
                        
                        $discountTot    = 0;
                        $isDiscountTot  = false;
                        
                        $productDetails = $oData["product_details"];
                        
                        $extraOrderDetails = $oData["extra_details"];
                        
                        $productArr = array();
                        
                        foreach ($productDetails as $tmProduct){
                            
                            if($tmProduct["LineExtDiscountAmount"] > 0){
                                $isDiscountTot = true;
                                $discountTot += $tmProduct["LineExtDiscountAmount"];
                            }
                            
                            $styleNo = trim($tmProduct['StyleNo']);
                            
                            $tsku = ($tmProduct['SKU'])?$tmProduct['SKU']:$styleNo;
                            
                            $sku = strtoupper(trim($tsku));
                            $qty = $tmProduct['Qty'];
                            
                            $tempQty = $qty;
                            
                            $totalAmtT = trim($orderDetails["TotalAmountWithTax"]);
                            
                            //if($totalAmtT < 0){
                                if($qty < 0){
                                    $qty = $qty * (-1);
                                }
                            //}
                            
                            $origPriceWoutTax = $tmProduct["OriginalPriceWithoutTax"];
                            $origPriceWithTax = $tmProduct["OriginalPriceWithTax"];
                            
                            $pTaxAmt = $origPriceWithTax - $origPriceWoutTax;
                            $taxPer = 0;
                            if($pTaxAmt > 0){
                                $taxPer = (100 * $pTaxAmt) /$origPriceWithTax;
                                $taxPer = round($taxPer,2);
                            }
                            
                            $tmItemId = trim($tmProduct["ReceiptItemId"]);
                            
                            $productArr[$tmItemId] = array(
                                "orig_price_tax" => $origPriceWithTax,
                                "tax" => ($pTaxAmt * $qty),
                                "single_tax" => $pTaxAmt,
                                "tax_per" => $taxPer,
                                "row_total" => $tmProduct["OriginalPriceWithoutTax"],
                                "disc" => $tmProduct["LineExtDiscountAmount"],
                                "temp_qty" => $tempQty
                            );
                            
                            $price = $origPriceWoutTax;
                            
                            $productObj = Mage::getModel('catalog/product');
                            $productObj->setTypeId("simple");
                            //$productObj->setTaxClassId(1);
                            $productObj->setSku($sku);
                            $productObj->setName($tmProduct['Description4']);
                            $productObj->setShortDescription($tmProduct['Description4']);
                            $productObj->setDescription($tmProduct['Description4']);
                            $productObj->setPrice($price);
                            //
                            
                            $quoteItem = Mage::getModel("allure_counterpoint/item")
                            ->setProduct($productObj);
                            $quoteItem->setQty($qty);
                            //$quoteItem->setOtherSysQty($tempQty);
                            //$productObj->setPriceInclTax($origPriceWithTax);
                            //$productObj->setBasePriceInclTax($origPriceWithTax);
                            $quoteItem->setStoreId(1);
                            
                            $quoteItem->setOtherSysQty($tempQty);
                            
                            
                            $quoteItem->setTwItemId($tmItemId);
                            
                            $quoteObj->addItem($quoteItem);
                            
                            $productObj = null;
                        }
                        
                        $quoteBillingAddress = Mage::getModel('sales/quote_address');
                        $quoteBillingAddress->setData($billingAddress);
                        $quoteObj->setBillingAddress($quoteBillingAddress);
                        if(!$quoteObj->getIsVirtual()) {
                            $shippingAddress = $billingAddress;
                            $quoteShippingAddress = Mage::getModel('sales/quote_address');
                            $quoteShippingAddress->setData($shippingAddress);
                            $quoteObj->setShippingAddress($quoteShippingAddress);
                            // fixed shipping method
                            $quoteObj->getShippingAddress()
                            ->setShippingMethod("tm_storepickupshipping");
                        }
                        
                        $quoteObj->collectTotals();
                        
                        if($isDiscountTot){
                            $discountTot = $discountTot  ;
                            /* $quoteObj->setSubtotal($quoteObj->getSubtotal() + $discountTot);
                             $quoteObj->setBaseSubtotal($quoteObj->getBaseSubtotal() + $discountTot);
                             $quoteObj->setSubtotalWithDiscount($quoteObj->getSubtotalWithDiscount() + $discountTot);
                             $quoteObj->setBaseSubtotalWithDiscount($quoteObj->getBaseSubtotalWithDiscount() + $discountTot);
                             $quoteObj->setGrandTotal($quoteObj->getGrandTotal() + $discountTot);
                             $quoteObj->setBaseGrandTotal($quoteObj->getBaseGrandTotal() + $discountTot);
                             */
                        }
                        
                        $otherSysCur     = trim($orderDetails["CODE"]);
                        $otherSysCurCode = trim($orderDetails["CurrencyCode"]);
                        
                        $quoteObj->setOtherSysCurrency($otherSysCur);
                        $quoteObj->setOtherSysCurrencyCode($otherSysCurCode);
                        
                        $quoteObj->setOtherSysExtraInfo(json_encode($extraOrderDetails,true));
                        
                        $quoteObj->setTeamworkReceiptId($receiptId);
                        $quoteObj->setCreateOrderMethod(2);
                        $quoteObj->save();
                        
                        
                        $quoteObj->setIsActive(0);
                        $quoteObj->reserveOrderId();
                        
                        $incrementIdQ = $quoteObj->getReservedOrderId();
                        if($incrementIdQ){
                            $incrementIdQ = "TW-".$incrementIdQ;
                            $quoteObj->setReservedOrderId($incrementIdQ);
                        }
                        
                        $payment_method  = "tm_pay_cash";
                        $quotePaymentObj = $quoteObj->getPayment();
                        $quotePaymentObj->setMethod($payment_method);
                        $quoteObj->setPayment($quotePaymentObj);
                        
                        $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
                        if($quoteObj->getIsVirtual()) {
                            $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
                        }else{
                            $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
                        }
                        
                        $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
                        //$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                        if(!$quoteObj->getIsVirtual()) {
                            $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                        }
                        
                        $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                        
                        $tTax = 0;
                        
                        $items=$quoteObj->getAllItems();
                        foreach ($items as $item) {
                            $productId = Mage::getModel("catalog/product")->getIdBySku($item->getSku());
                            $orderItem = $convertQuoteObj->itemToOrderItem($item);
                            
                            if($item->getParentItem()) {
                                $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                            }
                            
                            //refunded code
                            if($orderItem->getData('qty_ordered') < 0){
                                $qtyItem = $orderItem->getData('qty_ordered');
                                if($qtyItem < 0){
                                    $qtyItem = $qtyItem * (-1);
                                }
                                $orderItem->setData('qty_ordered',$qtyItem);
                                $orderItem->setData('qty_refunded',$qtyItem);
                                //$orderItem->setData('qty_canceled',$qtyItem);
                            }
                            
                            if($productId){
                                $orderItem->setData('product_id',$productId);
                            }
                            
                            $iSku = $item->getTwItemId();//$item->getSku();
                            $taxI = $productArr[$iSku]["tax"];
                            
                            $singleTax = $productArr[$iSku]["single_tax"];
                            $rowTotal = $productArr[$iSku]["row_total"];
                            
                            $taxPer = $productArr[$iSku]["tax_per"];
                            
                            $orderItem->setData("price_incl_tax",$singleTax);
                            $orderItem->setData("base_price_incl_tax",$singleTax);
                            
                            $orderItem->setData("row_total_incl_tax",$taxI);
                            $orderItem->setData("base_row_total_incl_tax",$taxI);
                            
                            $orderItem->setData("tax_amount",$taxI);
                            $orderItem->setData("tax_percent",$taxPer);
                            
                            $orderItem->setData("base_tax_amount",$taxI);
                            
                            $disc = $productArr[$iSku]["disc"];
                            
                            $temQty = $productArr[$iSku]["temp_qty"];
                            
                            //Mage::log("tempqty :".$temQty,Zend_log::DEBUG,$teamworkLog,true);
                            $orderItem->setData("other_sys_qty",$temQty);
                            
                            
                            if($disc){
                                //$disc *= (-1);
                                $orderItem->setData("discount_amount",$disc);
                                $orderItem->setData("base_discount_amount",$disc);
                            }
                            
                            $tTax += $taxI;
                            
                            //$orderItem->setData("row_total",$rowTotal);
                            //$orderItem->setData("base_row_total",$rowTotal);
                            
                            
                            $orderObj->addItem($orderItem);
                        }
                        
                        $createAt = trim($orderDetails["StateDate"]);
                        
                        $orderObj->setCreatedAt($createAt);
                        
                        $orderObj->setCanShipPartiallyItem(false);
                        
                        $totalDue = $orderObj->getTotalDue();
                        
                        $totalAmmount = $quoteObj->getGrandTotal();
                        $taxAmmount     = $tTax;//$orderDetails['TAX'];
                        $discountAmount = $discountTot;
                        
                        
                        if(1){
                            $totalAmmount =$totalAmmount + $taxAmmount;
                            $orderObj->setTaxAmount($taxAmmount);
                        }
                        
                        if($discountAmount){
                            $discountAmount = 0 - $discountAmount;
                            $totalAmmount = $totalAmmount + $discountAmount;
                            $orderObj->setDiscountAmount($discountAmount);
                        }
                        
                        if($isDiscountTot){
                            $quoteSubTotal = $quoteObj->getSubtotal();
                            $orderObj->setSubtotal($quoteSubTotal);
                            $orderObj->setBaseSubtotal($quoteSubTotal);
                            $orderObj->setSubtotalInclTax($quoteSubTotal);
                            $orderObj->setBaseSubtotalInclTax($quoteSubTotal);
                        }
                        
                        $orderObj->setShippingDescription("Store Pickup"); //self::SHIPPING_METHOD_NAME
                        $orderObj->setGrandTotal($totalAmmount);
                        $orderObj->setBaseTaxAmount($taxAmmount);
                        $orderObj->setBaseGrandTotal($totalAmmount);
                        //$orderObj->setTotalPaid($totalAmmount);
                        
                        if(strtoupper($otherSysCur) != "MT"){
                            $orderObj->setData('base_currency_code',$otherSysCurCode)
                                ->setData('global_currency_code',$otherSysCurCode)
                                ->setData('order_currency_code',$otherSysCurCode)
                                ->setData('store_currency_code',$otherSysCurCode);
                        }
                        
                        $extStoreName = $extraOrderDetails["Name"];
                        $oldStoreId;
                        if($extStoreName == "Liberty London"){
                            $oldStoreId = 2;
                        }elseif ($extStoreName == "Rinascente Roma"){
                            $oldStoreId = 10;
                        }elseif ($extStoreName == "653 Broadway"){
                            $oldStoreId = 15;
                        }elseif ($extStoreName == "Wholesale"){
                            $oldStoreId = 16;
                        }else{
                            $oldStoreId = null;
                        }
                        
                        $orderObj->setData('old_store_id',$oldStoreId);
                        
                        //complete the order status
                        $orderObj->setData('state',"processing")
                        ->setData('status',"processing");
                        
                        
                        $orderObj->save();
                        $quoteObj->save();
                        Mage::log("Order Id:".$orderObj->getId(),Zend_log::DEBUG,$teamworkLog,true);
                    }
                    
                }catch (Exception $e){
                    Mage::log("Exception".$e->getMessage(),Zend_log::DEBUG,$teamworkLog,true);
                }
            }
        }
    }
}

Mage::log("Finish...",Zend_log::DEBUG,$teamworkLog,true);
die("Finish...");
