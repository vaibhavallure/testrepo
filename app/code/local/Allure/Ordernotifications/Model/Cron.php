<?php
class Allure_Ordernotifications_Model_Cron {
	public function processOrderNoftifications(){
		$userArrray=Mage::getStoreConfig('allure_ordernotifications/manage_users/usermapping');
		$userArrray=unserialize($userArrray);
		foreach ($userArrray as $userData)
		{
			if(!empty($userData['email']) && $userData['enabled'])
			{
				$name   = 'orders.csv';
				$file_path = Mage::getBaseDir('var') . DS . 'export' . DS;
				$file = $file_path . DS . $name;
				
				$io = new Varien_Io_File();
				$io->setAllowCreateFolders(true);
				$io->open(array('path' => $path));
				/* $io->streamOpen($file, 'w+');
				 $io->streamLock(true); */
				Mage::log("Config Data",Zend_log::DEBUG,'notifications',true);
				Mage::log($userData,Zend_log::DEBUG,'notifications',true);
				$noOfWeeks=2;
				if($userData['no_of_weeks'])
					$noOfWeeks=$userData['no_of_weeks'];
				$emailAddress=$userData['email'];
				
				$currentTime = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
				$date = strtotime(date("Y-m-d H:i:s", strtotime($currentTime)) . " -".$noOfWeeks." week");
				$fromDate = date("Y-m-d H:i:s", $date);
				$orderStatus=$userData['order_status'];
				
				
				$ordersCollection = Mage::getModel('sales/order')->getCollection();
				$ordersCollection->addFieldToFilter('main_table.status',$orderStatus);
				$ordersCollection->getSelect()->joinLeft(array("t1" => 'sales_flat_order_status_history'), "t1.parent_id = main_table.entity_id",array("updated_satus_at" => "t1.created_at"));
				$ordersCollection->addFieldToFilter('t1.created_at', array('lt'=>array($fromDate)));
				$ordersCollection->getSelect()->group('main_table.entity_id');
				if(count($ordersCollection))
				{
					$fp = fopen($file, 'w');
					$csvHeader = array("Order#","Purchased From", "Purchased On","Customer Name",'Email','Shipping Description','Grand Total');
					fputcsv( $fp, $csvHeader,",");
					foreach ($ordersCollection as $order){
							
						$orderId = $order->getIncrementId();
						$purchasedFrom = $order->getStoreName();;
						$purchasedOn = $order->getCreatedAt();
						$customerName = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
						$email = $order->getCustomerEmail();
						$shippingMethod = $order->getShippingDescription();
						$grandTotal = $order->getGrandTotal();
						fputcsv($fp, array($orderId,$purchasedFrom,$purchasedOn,$customerName,$email,$shippingMethod,$grandTotal), ",");
					}
					fclose($fp);
				}
				$status = Mage::getModel('sales/order_status')->loadDefaultByState($orderStatus);
				if($status->getStoreLabel())
					$label=$status->getStoreLabel();
				else 
					$label=$orderStatus;
				
				$mailTemplate = Mage::getModel('core/email_template');
				//$recipient=Mage::getStoreConfig('inventory/email/recipient_email');
				$recipientArr=explode(",",$emailAddress);
				$mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name')); // use general Mage::getStoreConfig('trans_email/ident_general/name');
				$mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email')); // use general Mage::getStoreConfig('trans_email/ident_general/email')
				$mailTemplate->setTemplateSubject('Orders having status'.' '.$label.' '.since.' '.$noOfWeeks.' '.'Weeks');
				$mailTemplate->setTemplateText('Orders having status'.' '.$label.' '.since.' '.$noOfWeeks.' '.'Weeks');
				$mailTemplate->getMail()->createAttachment(
						file_get_contents($file),
						Zend_Mime::TYPE_OCTETSTREAM,
						Zend_Mime::DISPOSITION_ATTACHMENT,
						Zend_Mime::ENCODING_BASE64,
						$name
				);
				
				try {
					
					$mailTemplate->send($recipientArr);
				
				} catch (Exception $e) {
					Mage::log($e,Zend_log::DEBUG,'notifications',true);
				}
			

			}
		
		
		} //End of For each
		echo "Done";
	} //End Of Method

	
} //End Of class