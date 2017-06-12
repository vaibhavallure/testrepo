<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();


$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();

$alphabets = range('A','Z');
$numbers = range('0','9');
$additional_characters = array('#','@','$');
$final_array = array_merge($alphabets,$numbers,$additional_characters);


$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql        = "SELECT a.doc_id,a.tkt_no order_id,a.tkt_dt,concat(b.item_no,'|',cell_descr) sku,
				b.qty_sold qty,b.prc,a.sub_tot,a.tot_ext_cost,a.tax_amt,a.tot, c.nam name,
				c.EMAIL_ADRS_1 as email,c.adrs_1 street,c.city,c.state,c.zip_cod ,
				c.cntry as country FROM `ps_ord_hist` a JOIN `ps_doc_lin` b on(a.tkt_no=b.tkt_no) 
				join `ps_ord_hist_cell` c on(a.doc_id=c.doc_id) where c.EMAIL_ADRS_1 <> '' 
				and b.qty_sold > 0";
$rows       = $connection->fetchAll($sql);

$newArr = array();
foreach ($rows as $row){
	//echo implode(",", $row)."<br><br>";
	if(!array_key_exists($row['order_id'], $newArr)){
		$order_id = $row['order_id'];
		$subArr = array();
		$count = 0;
		$address = array();
		foreach ($rows as $row1){
			if($row1['order_id']==$order_id){
				$subArr[] = $row1;
				//if($count==0)
				//	$address=$row1;
			}
			$count++;
		}
		$newArr[$order_id]=array('items'=>$subArr,'address'=>$subArr[0]);
	}
}
echo "<pre>";
//print_r($newArr);die;
foreach ($newArr as $key=>$data){
	$counterpointOrderId = "".$key;
	$order = Mage::getModel('sales/order')->load($counterpointOrderId, 'increment_id');
	
	if(!$order->getId()){
		$order = Mage::getModel('sales/order')->load($counterpointOrderId, 'counterpoint_order_id');
		if(!$order->getId()){
			
			echo "Order Id:".$counterpointOrderId." Not present";
			Mage::log("Order Id:".$counterpointOrderId." Not present", Zend_Log::DEBUG,"counter_point_order",true);
			
			//sales order quote
			$quoteObj = Mage::getModel('sales/quote');
			$quoteObj = $quoteObj->setStoreId(Mage::app()->getStore()->getId());
			$quoteFlag = false;
			$productArr = array();
			foreach ($data['items'] as $value){
				$sku = strtoupper($value['sku']);
				$sku = rtrim($sku,'|');
				$sku = str_replace('/', '|', $sku);
				$qty = $value['qty'];
				
				$productId = Mage::getModel('catalog/product')->getIdBySku($sku);
				if($productId){
					$quoteFlag = true;
					$productArr[$productId] = $qty;
					//echo "<br>Product_id:".$productId;
					echo "<br>";
					
				}else {
					//break;
				}
			}
			
			if($quoteFlag){
				$address = $data['address'];
				$email = $address['email'];
				$street = $address['street'];
				$city = $address['city'];
				$state = $address['state'];
				$country = $address['country']?$address['country']:"";
				$zip_code = $address['zip_cod'];
				$phone = $address['phone'];
				$name = $address['name'];
				$name = explode(" ", $name);
				$firstName = $name[0];
				$lastName = $name[0];
				if(count($name)>1)
					$lastName = $name[1];
				
					// Start New Sales Order Quote
					$quote = Mage::getModel('sales/quote')->setStoreId($store->getId());
					
					$customer = Mage::getModel('customer/customer')
					->setWebsiteId($websiteId)
					->loadByEmail($email);
					
					if(!$customer->getId()){
						$groupId = 1;
						$storeId = 1;
						
						$password = '';
						$length = 6;  //password length
						while($length--) {
							$key = array_rand($final_array);
							$password .= $final_array[$key];
						}
						
						$customer = Mage::getModel("customer/customer");
						$customer->setWebsiteId($websiteId)
						->setStoreId($storeId)
						->setGroupId($groupId)
						->setFirstname($firstName)
						->setLastname($lastName)
						->setEmail($email)
						->setPassword($password)
						->save();
					}
					
					
					// assign this customer to quote object, before any type of magento order, first create quote.
					$quoteObj = Mage::getModel('sales/quote')->assignCustomer($customer);
					$quoteObj = $quoteObj->setStoreId(Mage::app()->getStore()->getId());
					
					//$productId = Mage::getModel('catalog/product')->getIdBySku($sku);
					//$productObj = Mage::getModel('catalog/product')->load($productId);
					
					foreach ($productArr as $productId=>$qty){
						$params = array();
						$params['qty'] = $qty;
						$request = new Varien_Object();
						$request->setData($params);
						$productObj = Mage::getModel('catalog/product')->load($productId);
						$quoteObj->addProduct($productObj , $request);
					}
					
					// sample billing address
					$billingAddress = array
					(
							'email' => $email,
							'firstname' => $firstName,
							'lastname' => $lastName,
							'telephone' => $phone,
							'street' => $street,
							'country_id' => $country,
							'city' => $city,
							'postcode' => $zip_code,
							//'region_id' => "12",
							//'region' => 'California',
							'company' => "",
							'fax' => "",
							'save_in_address_book' => 1
					);
					
					$quoteBillingAddress = Mage::getModel('sales/quote_address');
					$quoteBillingAddress->setData($billingAddress);
					$quoteObj->setBillingAddress($quoteBillingAddress);
					
					//if product is not virtual
					if (!$quoteObj->getIsVirtual()) {
						$shippingAddress = $billingAddress;
						$quoteShippingAddress = Mage::getModel('sales/quote_address');
						$quoteShippingAddress->setData($shippingAddress);
						$quoteObj->setShippingAddress($quoteShippingAddress);
						// fixed shipping method
						$quoteObj->getShippingAddress()->setShippingMethod('webpos_shipping_storepickup');
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
					
					$quoteObj->setCreateOrderMethod(1); //order status as counterpoint 1
					$quoteObj->setCounterpointOrderId($counterpointOrderId);
					
					$ccInfo = array();
					// assign payment method
					$payment_method = 'codforpos';
					
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
					
					$transaction->addObject($orderObj);
					$transaction->addCommitCallback(array($orderObj, 'place'));
					$transaction->addCommitCallback(array($orderObj, 'save'));
					
					try {
						$transaction->save();
					} catch (Exception $e){
						Mage::throwException('Order Cancelled Bad Response from Credit Authorization.');
					}
					
					$increment_id = $orderObj->getRealOrderId();
					
					$quoteObj->setIsActive(0);
					$quoteObj->save();
					
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
					//$invoice->save();
					$transactionSave = Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder());
					$transactionSave->save();
					
					echo "New Order Create Id:".$increment_id;
					Mage::log("New Order Create Id:".$increment_id, Zend_Log::DEBUG,"counter_point_order",true);
					//var_dump("order:".$orderObj->getId());
					//$orderData = Mage::getModel('sales/order')->load($orderObj->getId());
					
					if ($orderObj->hasInvoices()) {
						if($transId!=0){
							foreach ($order->getInvoiceCollection() as $invoce) {
								$invoce->setState(2);
								$invoce->setCanVoidFlag(0);
								$invoce->save();
							}
						}
					}
					
					//complete the order status
					$orderData = Mage::getModel('sales/order')->load($orderObj->getId());
					$orderData->setData('state','complete')
						->setData('status','complete')
						->save();
					
			}else{
				echo "CountertPoint Oder Id ".$counterpointOrderId." not created.Product Not match to magento";
				Mage::log("CountertPoint Oder Id ".$counterpointOrderId." not created.Product Not match to magento", Zend_Log::DEBUG,"counter_point_order",true);
			}
		}else{
			echo "CountertPoint Oder Id:".$counterpointOrderId." Already Created in magento";
			Mage::log("CountertPoint Oder Id:".$counterpointOrderId." Already Created in magento", Zend_Log::DEBUG,"counter_point_order",true);
		}
		
	}else{ 
		echo "CountertPoint Oder Id:".$counterpointOrderId." present in Magento";
		Mage::log("CountertPoint Oder Id:".$counterpointOrderId." present in Magento", Zend_Log::DEBUG,"counter_point_order",true);
	}
	echo "<br>";
	
}

die;




