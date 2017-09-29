<?php

class Allure_Appointments_Model_Cron extends Mage_Core_Model_Abstract
{

	
	public  function autoProcess(){
		
		//Get all stores with time zone
		$config=Mage::getStoreConfig('appointments/general/storemapping');
		$config=unserialize($config);
		foreach ($config as $conf)
		{
			date_default_timezone_set($conf['timezone']);
			$storeDate=date('Y-m-d H:i:s');
			
			//Send notification at store date time only
			Mage::log("Store Time Zone:".$conf['timezone'],Zend_Log::DEBUG,'appointments',true);
			$this->processCollection($storeDate,$conf['store'],$conf['timezone']);
			date_default_timezone_set("UTC");
		}
	}
	
	
	public function processCollection($storeDate, $storeId){
		
		//Send notification before one day
		Mage::log("*************** Server Time*************",Zend_Log::DEBUG,'appointments',true);
		$nextTime = date("Y-m-d H:i:00",strtotime("1 day",strtotime($storeDate)));
		Mage::log("nextTime After 1 day:".$nextTime,Zend_Log::DEBUG,'appointments',true);
		$next2Time= date("Y-m-d H:i:59",strtotime("1 day 15 minutes",strtotime($storeDate)));
		Mage::log("next2Time After 1 day 15 min:".$next2Time,Zend_Log::DEBUG,'appointments',true);
		
		$allAppointments = Mage::getModel('appointments/appointments')->getCollection();
		$allAppointments->addFieldToFilter('appointment_start', array('gteq' => $nextTime));
		$allAppointments->addFieldToFilter('appointment_start', array('lteq' => $next2Time));
		$allAppointments->addFieldToFilter('app_status',Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
		$allAppointments->addFieldToFilter('store_id', array('eq' => $storeId));
		if(count($allAppointments) > 0){
			Mage::log(" diff is less than 1 days",Zend_Log::DEBUG,'appointments',true);
			$this->sendNotification($allAppointments);
		}
		Mage::log("Check more than 7 day remaining appointments",Zend_Log::DEBUG,'appointments',true);
		$nextTime = date("Y-m-d H:i:00",strtotime("7 day",strtotime($storeDate)));
		Mage::log("nextTime After 7 day:".$nextTime,Zend_Log::DEBUG,'appointments',true);
		$next2Time= date("Y-m-d H:i:59",strtotime("7 day 15 minutes",strtotime($storeDate)));
		Mage::log("$next2Time After 7 day 15 minutes:".$nextTime,Zend_Log::DEBUG,'appointments',true);
		$allAppointments = Mage::getModel('appointments/appointments')->getCollection();
		$allAppointments->addFieldToFilter('app_status',Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
		$allAppointments->addFieldToFilter('appointment_start', array('gteq' => $nextTime));
		$allAppointments->addFieldToFilter('appointment_start', array('lteq' => $next2Time));
		//$allAppointments->getSelect()->where('DATEDIFF(appointment_start,booking_time)>=7');
		$allAppointments->addFieldToFilter('store_id', array('eq' => $storeId));
		if(count($allAppointments) > 0){
			Mage::log(" diff is greater than 7 days",Zend_Log::DEBUG,'appointments',true);
			$this->sendNotification($allAppointments);
		}
	}
	
	public function sendNotification($allAppointments){
		$sendEmail = false;
		$sendSms = false;
		
		
		foreach ($allAppointments as $appointment){
			$storeId=$appointment->getStoreId();
			$toSend = Mage::getStoreConfig("appointments/customer/send_customer_email",$storeId);
			$templateId = Mage::getStoreConfig("appointments/customer/customer_reminder_template",$storeId);
			$sender = array('name'=>Mage::getStoreConfig("trans_email/bookings/name",$storeId),
			    'email'=> Mage::getStoreConfig("trans_email/bookings/email",$storeId));
			
			$toSendAdmin = Mage::getStoreConfig("appointments/admin/send_admin_email",$storeId);
			
			$sendEmail = false;
			$sendSms = false;
			
			$startDate = $appointment->getAppointmentStart();
			$bookedDate = $appointment->getBookingTime();
			$notification_pref = $appointment->getNotificationPref();
			$phone = $appointment->getPhone();
			$appstatus = $appointment->getAppStatus();
			
			$date=Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
			$dStart = new DateTime($appointment->getLastNotified());
			$dEnd  = new DateTime($date);
			$dDiff = $dStart->diff($dEnd);
			$dDiff->format('%R'); // use for point out relation: smaller/greater
			$dDiff->days;
			
			if($dDiff->days <= 0)
			   continue;
			
			
			$appointment->setLastNotified($date);
			$appointment->save();
			
			   
			Mage::log(" notication type  ".$notification_pref,Zend_Log::DEBUG,'appointments',true);
			if($notification_pref == 2){
				$sendSms = true;
				$sendEmail = true;
			}else{
				$sendEmail = true;
			}
			$model = $appointment;
			$appointmentStart=date("F j, Y H:i", strtotime($model->getAppointmentStart()));
			$appointmentEnd=date("F j, Y H:i", strtotime($model->getAppointmentEnd()));
			if($sendEmail){
				
				/*Email Code*/
				if($toSend){
					$mailSubject="Appointment booking Reminder";
					$apt_modify_link = Mage::getUrl('appointments/index/modify',array('id'=>$model->getId(),'email'=>$model->getEmail(),'_secure' => true));
					$email = $model->getEmail();
					$name = $model->getFirstname()." ".$model->getLastname();
					$vars = array(
							'name'        => $model->getFirstname()." ".$model->getLastname(),
							'customer_name'        => $model->getFirstname()." ".$model->getLastname(),
							'customer_email'  => $model->getEmail(),
							'customer_phone'      => $model->getPhone(),
							'no_of_pier' => $model->getPiercingQty(),
							'piercing_loc' => $model->getPiercingLoc(),
							'special_notes' => $model->getSpecialNotes(),
							'apt_starttime'  => $appointmentStart,
							'apt_endtime'    => $appointmentEnd,
							'store_name'	=> Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
							'store_address'	=> Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
							'store_email_address'	=> Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
							'store_phone'	=> Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
							'store_hours'	=> Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
							'store_map'	=> Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
							'apt_modify_link'=> $apt_modify_link);
					$mail = Mage::getModel('core/email_template')
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId,$sender,$email,$name,$vars);
				}
				/*End of Email Code*/
				
				/*Admin Email Code*/
				
				if($toSendAdmin){
					$templateId = Mage::getStoreConfig("appointments/admin/admin_template",$storeId);
					$adminEmail = Mage::getStoreConfig("appointments/admin/admin_email",$storeId);
					$mailSubject="Appointment booking Reminder";
					$sender         = array('name'=>Mage::getStoreConfig("trans_email/bookings/name",$storeId), 'email'=> Mage::getStoreConfig("trans_email/bookings/email"));
					$email = $adminEmail;
					$name = "Admin";
					$vars = array(
							'name'        => $model->getFirstname()." ".$model->getLastname(),
							'customer_name'        => $model->getFirstname()." ".$model->getLastname(),
							'customer_email'  => $model->getEmail(),
							'customer_phone'      => $model->getPhone(),
							'no_of_pier' => $model->getPiercingQty(),
							'piercing_loc' => $model->getPiercingLoc(),
							'special_notes' => $model->getSpecialNotes(),
							'apt_starttime'  => $appointmentStart,
							'store_name'	=> Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
							'store_address'	=> Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
							'store_email_address'	=> Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
							'store_phone'	=> Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
							'store_hours'	=> Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
							'store_map'	=> Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
							'apt_endtime'    => $appointmentEnd);
					$mail = Mage::getModel('core/email_template')
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId,$sender,$email,$name,$vars);
					Mage::log(" mail send true",Zend_Log::DEBUG,'appointments',true);
				}
				/*End of Email Code*/
			}/* END sendemail if */
			
			if($sendSms){
				Mage::log(" In send Sms ",Zend_Log::DEBUG,'appointments',true);
				$model = $appointment;
				$username = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_USERNAME);
				$password = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_PASSWORD);
				$url = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_BASEURL);
				$smsfrom = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_FROM);
				$smsText = Mage::getStoreConfig("appointments/api/smstext_reminder",$storeId);
				$appointmentStart=date("F j, Y H:i", strtotime($model->getAppointmentStart()));
				$date = date("F j, Y ", strtotime($model->getAppointmentStart()));
				$time=date('h:i A', strtotime($model->getAppointmentStart()));
				/* $booking_link= Mage::getBaseUrl('web').'appointments/';
				$booking_link=Mage::helper('appointments')->getShortUrl($booking_link); */
				$smsText=str_replace("(time)",$time,$smsText);
				$smsText=str_replace("(date)",$date,$smsText);
		/* 		$smsText=str_replace("(book_link)",$booking_link,$smsText); */
				
				if($phone){//if NotificationPref set to text sms i.e. 2
					$api = new SoapClient($url,array( 'cache_wsdl' => WSDL_CACHE_NONE,'soap_version' => SOAP_1_1));
					$session = $api->apiValidateLogin($username,$password);
					preg_match("/<ticket>(?<ticket>.+)<\/ticket>/", $session, $response);
					$status = $api->apiSendSms($response['ticket'], $smsfrom, $phone, $smsText, 'text', '0', '0');
					preg_match("/<resp err=\"(?<error>.+)\">(<res>(<dest>(?<dest>.+)<\/dest>)?(<msgid>(?<msgid>.+)<\/msgid>)?.*<\/res>)?<\/resp>/", $status, $statusData);
				
					Mage::log("******************************",Zend_Log::DEBUG,'appointments_sms_log',true);
					Mage::log("From Cron",Zend_Log::DEBUG,'appointments_sms_log',true);
					Mage::log(" Mobile Number:".$phone,Zend_Log::DEBUG,'appointments_sms_log',true);
					Mage::log(" Text Message: ".$session,Zend_Log::DEBUG,'appointments_sms_log',true);
					Mage::log("**************************************",Zend_Log::DEBUG,'appointments_sms_log',true);
				}
			}
		}
	}
	
}