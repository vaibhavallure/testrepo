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
				Mage::log($userData,Zend_log::DEBUG,'notifications',true);
				$noOfWeeks=explode(",",$userData['no_of_weeks']);
				
				$emailAddress=$userData['email'];
				$storeIds=explode(",",$userData['store']);
				$currentTime = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
				if($noOfWeeks[0]){
					$date = strtotime(date("Y-m-d H:i:s", strtotime($currentTime)) . " -".$noOfWeeks[0]." week");
					$fromDate = date("Y-m-d H:i:s", $date);
				}
				if($noOfWeeks[1]){
					$date = strtotime(date("Y-m-d H:i:s", strtotime($currentTime)) . " -".$noOfWeeks[1]." week");
					$toDate = date("Y-m-d H:i:s", $date);
				}
				$orderStatus=$userData['order_status'];
				$ordersCollection = Mage::getModel('sales/order')->getCollection();
				$ordersCollection->addFieldToFilter('main_table.status',$orderStatus);
				$ordersCollection->getSelect()->joinLeft(array("t1" => 'sales_flat_order_status_history'), "t1.parent_id = main_table.entity_id",array("updated_satus_at" => "t1.created_at"));
				if($noOfWeeks[0])
					$ordersCollection->addFieldToFilter('t1.created_at', array('lt'=>array($fromDate)));
				if($noOfWeeks[1])
					$ordersCollection->addFieldToFilter('t1.created_at', array('gteq'=>array($toDate)));
				if(!empty($storeIds))
					$ordersCollection->addFieldToFilter('main_table.store_id', array("in" => $storeIds));
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
				
			/* 	$mailTemplate = Mage::getModel('core/email_template');
				//$recipient=Mage::getStoreConfig('inventory/email/recipient_email');
				$recipientArr=explode(",",$emailAddress);
				$mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name')); // use general Mage::getStoreConfig('trans_email/ident_general/name');
				$mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email')); // use general Mage::getStoreConfig('trans_email/ident_general/email')
				$mailTemplate->setTemplateSubject('Orders having status '.' '.$label.' '.'since'.' '.$noOfWeeks.' '.'Weeks');
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
				} */
				
				$emailVariables = array();
				$emailVariables['status'] = $label;
				$emailVariables['no_of_weeks'] = $noOfWeeks[0];
				
				$templateId =  Mage::getStoreConfig('allure_ordernotifications/general/template');
				
				$emailTemplate  = Mage::getModel('core/email_template');
				if ($templateId)
					$emailTemplate  = $emailTemplate->load($templateId);
				$emailTemplate->setTemplateSubject('Orders having status '.' '.$label.' '.'since'.' '.$noOfWeeks[0].' '.'Weeks');
				$sender= array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
				$emailTemplate->setSenderName($sender['name']);
				$emailTemplate->setSenderEmail($sender['email']);
				$recipientEmail=explode(",",$emailAddress);
				$copyTo = Mage::getStoreConfig('allure_ordernotifications/general/copy_to');
				if (!empty($copyTo)) {
					$copyTo =  explode(',', $copyTo);
				}
				$copyMethod = Mage::getStoreConfig('allure_ordernotifications/general/copy_method');
				Mage::log('Method:', Zend_Log::DEBUG, 'mylogs', true);
				Mage::log($copyMethod, Zend_Log::DEBUG, 'mylogs', true);
				if ($copyTo && $copyMethod == 'bcc') {
					foreach ($copyTo as $email)
					{
						$emailTemplate->getMail()->addBcc($email);
					}
				}
				if ($copyTo && $copyMethod == 'copy') {
					foreach ($copyTo as $email) {
						$emailTemplate->getMail()->addCc($email);
					}
				}
				
				try {
					$emailTemplate->getMail()->createAttachment(
							file_get_contents($file),
							Zend_Mime::TYPE_OCTETSTREAM,
							Zend_Mime::DISPOSITION_ATTACHMENT,
							Zend_Mime::ENCODING_BASE64,
							$name
					);
					$emailTemplate->setDesignConfig(array('area' => 'frontend'))
					->sendTransactional(
							$templateId,
							$sender,
							$recipientEmail,
							null,
							$emailVariables
					);
				} catch (Exception $e) {
					Mage::log("Exception Occured".$e->getMessage(), Zend_Log::DEBUG,'notifications',true);
				}
				if (!$emailTemplate->getSentSuccess()) {
					Mage::log('mail sending exception', Zend_Log::DEBUG, 'notifications', true);
				}
				else {
					Mage::log('mail sending done', Zend_Log::DEBUG, 'notifications', true);
				}
			

			}
		
		
		} //End of For each

	} //End Of Method

	
} //End Of class