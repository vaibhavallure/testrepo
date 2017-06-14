<?php
class Allure_Counterpoint_Model_Data{	
	
	public function synkCounterpointOrders($from,$to){
		$websiteId = Mage::app()->getWebsite()->getId();
		$store = Mage::app()->getStore();
		
		$alphabets = range('A','Z');
		$numbers = range('0','9');
		$additional_characters = array('#','@','$');
		$final_array = array_merge($alphabets,$numbers,$additional_characters);
		
		$helper = Mage::helper('allure_counterpoint');
		
		$hostName = $helper->getHostName();//"CPSQL";
		$dbUsername = $helper->getDBUserName();//"sa";
		$dbPassword = $helper->getDBPassword();//"root";
		$dbName = "Venus84";
		
		$conn = odbc_connect($hostName, $dbUsername,$dbPassword);
		if($conn){
			try{
				Mage::log("Connection established.", Zend_Log::DEBUG,"counter_point_order",true);
				
				$query = "select a.DOC_ID,a.TKT_NO order_id,a.TKT_DT order_date,a.TAX_OVRD_REAS place,a.SUB_TOT subtotal,a.tax_amt tax,
					a.tot total,concat(b.ITEM_NO,'|',b.CELL_DESCR) sku,b.QTY_SOLD qty,b.prc,b.descr pname,
	 				c.EMAIL_ADRS_1 as email,c.nam name,c.adrs_1 street,c.city,c.state,c.zip_cod , c.cntry as country,c.phone_1 phone
					from ps_tkt_hist a join
					ps_tkt_hist_lin b on a.TKT_NO=b.TKT_NO
					join ps_tkt_hist_contact c  on(a.doc_id=c.doc_id)
					where c.CONTACT_ID=1 and b.QTY_SOLD>0 and (TAX_OVRD_REAS<>'MAGENTO' or TAX_OVRD_REAS is null)
					and a.tkt_dt <='".$from."' and a.tkt_dt >='".$to."' order by a.BUS_DAT desc;";
				//and tkt_dt >='2017-05-30'
				$query1 = "select * from dbo.ps_ord_hist where tkt_no='2017003176'";
				$result = odbc_exec($conn, $query);
				$count = 0;
				$i 	   = 0;
				$mainArr = array();
				$itemHeader = array('qty','sku','prc','pname');
				$addressHeader = array('email','name','street','city','state','zip_cod','country','phone');
				while(odbc_fetch_row($result)){
					$order_id = odbc_result($result, 'order_id');
					$arr 		= array();
					$items 		= array();
					$address 	= array();
					$info		= array();
					
					//parse row data as required format
					for ($j = 1; $j <= odbc_num_fields($result); $j++){
						$field_name  = odbc_field_name($result, $j);
						$field_value = odbc_result($result, $field_name);
						if(in_array($field_name, $itemHeader)){
							if($field_name == 'sku'){
								$sku = strtoupper($field_value);
								$sku = rtrim($sku,'|');
								$sku = str_replace('/', '|', $sku);
								$items[$field_name] = $sku;
							}else{
								$items[$field_name] = $field_value;
							}
						}elseif(in_array($field_name, $addressHeader)){
							if($field_name == 'email')
								$field_value = strtolower($field_value);
								$address[$field_name] = $field_value;
						}else{
							$info[$field_name] = $field_value;
						}
					}
					
					if(!array_key_exists($order_id, $mainArr)){
						$mainArr[$order_id] = array('items'=>array($items),
								'address'=>$address,'info'=>$info);
					}else{
						$tempItems = $mainArr[$order_id]['items'];
						$tempItems[] = $items;
						$mainArr[$order_id]['items'] = $tempItems;
					}
					$i++;
				}
				Mage::log("Total order-".count($mainArr), Zend_Log::DEBUG,"counter_point_order",true);
				odbc_close($conn);
			}catch (Exception $e){
				odbc_close($conn);
				print_r($e);
			}
		}else{
			Mage::log("Connection could not be established.", Zend_Log::DEBUG,"counter_point_order",true);
			die( print_r( sqlsrv_errors(), true));
		}
		
		$cnt = 1;
		try{
			foreach ($mainArr as $key=>$data){
				$orderIdKey = explode("-", $key);
				$counterpointOrderId = "".$orderIdKey[0];
				if(strlen($counterpointOrderId)<2 && !empty($orderIdKey[1]))
					$counterpointOrderId = "".$orderIdKey[1];
				$order = Mage::getModel('sales/order')->load($counterpointOrderId, 'increment_id');
				
				if(!$order->getId()){
					$order = Mage::getModel('sales/order')->load($counterpointOrderId, 'counterpoint_order_id');
					if(!$order->getId()){
						//echo "Order Id:".$counterpointOrderId." Not present";
						Mage::log("Order Id:".$counterpointOrderId." Not present"." - Order date:-".$data['info']['order_date'], Zend_Log::DEBUG,"counter_point_order",true);
						
						//sales order quote
						$quoteObj = Mage::getModel('sales/quote');
						$quoteObj = $quoteObj->setStoreId(Mage::app()->getStore()->getId());
						$quoteFlag = false;
						$productArr = array();
						$newProductArr = array();
						foreach ($data['items'] as $value){
							$sku = strtoupper($value['sku']);
							$qty = $value['qty'];
							
							$productId = Mage::getModel('catalog/product')->getIdBySku($sku);
							if($productId){
								$quoteFlag = true;
								$productArr[$productId] = $qty;
								//echo "<br>Product_id:".$productId;
								//echo "<br>";
							}else {
								$quoteFlag = true;
								$product = Mage::getModel('catalog/product');
								$product->setTypeId('simple');
								$product->setTaxClassId(1);
								$product->setSku($sku);
								$product->setName($value['pname']);
								$product->setShortDescription($value['pname']);
								$product->setDescription($value['pname']);
								$product->setPrice($value['prc']);
								$newProductArr[] = array("item"=>$product,'qty'=>$qty);
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
								
								if(empty($email)){  //create customer with new email
									$emailName = $firstName."".$lastName;
									$email = strtolower($emailName)."@mariatash.com";
								}
								
								$email = strtolower($email);
								$customer = Mage::getModel('customer/customer')
								->setWebsiteId($websiteId)
								->loadByEmail($email);
								if(!$customer->getId()){
									$groupId = 1;
									$storeId = 1;
									
									$password = '';
									$length = 6;  //password length
									while($length--) {
										$keyV = array_rand($final_array);
										$password .= $final_array[$keyV];
									}
									$customer = Mage::getModel("customer/customer");
									$customer->setWebsiteId($websiteId)
									->setStoreId($storeId)
									->setGroupId($groupId)
									->setFirstname($firstName)
									->setLastname($lastName)
									->setEmail($email)
									->setPassword($password)
									->setCustomerType(1)  //counterpoint
									->save();
									//if(!empty($street)){
									$_custom_address = array (
											'firstname'  => $customer->getFirstname(),
											'lastname'   => $customer->getLastname(),
											'street'     => array (
													'0' => $street
											),
											'city'       => $city,
											'postcode'   => $zip_code,
											'country_id' => $country,
											'region' 	=> 	$state,
											'telephone'  => $phone,
											'fax'        => '',
									);
									
									$address = Mage::getModel("customer/address");
									$address->setData($_custom_address)
									->setCustomerId($customer->getId())
									->setIsDefaultBilling('1')
									->setIsDefaultShipping('1')
									->setSaveInAddressBook('1');
									$address->save();
									Mage::log("New Customer Address create.Customer Id:".$customer->getId()." Address Id:".$address->getId(), Zend_Log::DEBUG,"counter_point_order",true);
									//}
									
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
								
								if(count($newProductArr) > 0){
									foreach ($newProductArr as $newProduct){
										$newItem = $newProduct['item'];
										$newQty = $newProduct['qty'];
										$request = new Varien_Object();
										$request->setData($params);
										$quoteObj->addProduct($newItem, $request);
									}
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
									//$quoteObj->getShippingAddress()->setCollectShippingRates(true);
									//$quoteObj->getShippingAddress()->collectShippingRates();
								}
								$quoteObj->collectTotals();
								
								//$quoteObj->save();
								$transaction = Mage::getModel('core/resource_transaction');
								if ($quoteObj->getCustomerId()) {
									$transaction->addObject($quoteObj->getCustomer());
								}
								
								$quoteObj->setIsActive(0);
								
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
								
								$taxAmmount = $data['info']['tax'];
								$totalAmmount = $quoteObj->getGrandTotal() + $taxAmmount;
								
								$orderObj->setTaxAmount($taxAmmount);
								$orderObj->setGrandTotal($totalAmmount);
								$orderObj->setBaseTaxAmount($taxAmmount);
								$orderObj->setBaseGrandTotal($totalAmmount);
								
								//complete the order status
								$orderObj->setData('state','complete')
								->setData('status','complete');
								
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
								
								//echo "New Order Create Id:".$increment_id;
								Mage::log("No-".$cnt." - New Order Create Id:".$increment_id, Zend_Log::DEBUG,"counter_point_order",true);
								//var_dump("order:".$orderObj->getId());
								//$orderData = Mage::getModel('sales/order')->load($orderObj->getId());
								
								/* if ($orderObj->hasInvoices()) {
								 if($transId!=0){
								 foreach ($order->getInvoiceCollection() as $invoce) {
								 $invoce->setState(2);
								 $invoce->setCanVoidFlag(0);
								 $invoce->save();
								 }
								 }
								 } */
								
								//complete the order status
								
								/* $orderData = Mage::getModel('sales/order')->load($orderObj->getId());
								 $orderData->setData('state','complete')
								 ->setData('status','complete')
								 ->save(); */
						}else{
							Mage::log("No-".$cnt." - CountertPoint Order Id ".$counterpointOrderId." not created.Product Not match to magento", Zend_Log::DEBUG,"counter_point_order",true);
						}
					}else{
						Mage::log("No-".$cnt." - CountertPoint Order Id:".$counterpointOrderId." Already Created in magento", Zend_Log::DEBUG,"counter_point_order",true);
					}
					
				}else{
					Mage::log("No-".$cnt." - CountertPoint Order Id:".$counterpointOrderId." present in Magento", Zend_Log::DEBUG,"counter_point_order",true);
				}
				$cnt++;
			}
		}catch (Exception $e){
			Mage::throwException('Order Cancelled '.$e->getMessage());
			Mage::log("Exception-", Zend_Log::DEBUG,"counter_point_order",true);
			Mage::log($e, Zend_Log::DEBUG,"counter_point_order",true);
		}
		Mage::log("Finish.....", Zend_Log::DEBUG,"counter_point_order",true);
	}
}