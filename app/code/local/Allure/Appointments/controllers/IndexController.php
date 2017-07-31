<?php
class Allure_Appointments_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {  
    	
    	//MODIFY ACTION start by bhagya
    	$apt_id = $this->getRequest()->getParam('id');
    	$apt_email = $this->getRequest()->getParam('email');
    	 
    	if($apt_id && $apt_email)
    	{	
    		$models = Mage::getModel('appointments/appointments')->getCollection();
    		$models->addFieldToFilter('id',$apt_id)->addFieldToFilter('email',$apt_email);
    		//$models->addFieldToFilter('id',$apt_id)->addFieldToFilter('email',$apt_email)->addFieldToFilter('app_status',array('in'=>array(Allure_Appointments_Model_Appointments::STATUS_REQUEST,Allure_Appointments_Model_Appointments::STATUS_ASSIGNED)));
    		if(count($models)){
    			foreach ($models as $model){
    				$model=$model;break;
    			}
    			Mage::register('apt_modify_data',$model);
    			Mage::getSingleton("core/session")->setData('appointmentData_availablity',true);
    		}
    		else{
    			Mage::getSingleton("core/session")->setData('appointmentData_availablity',false);
    		}
    	}
    	//MODIFY ACTION
    	
	  	$this->loadLayout();   
	  	$this->getLayout()->getBlock("head")->setTitle($this->__("Appointments"));
	 	/*        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
	      $breadcrumbs->addCrumb("home", array(
	                "label" => $this->__("Home Page"),
	                "title" => $this->__("Home Page"),
	                "link"  => Mage::getBaseUrl()
			   ));
	
	      $breadcrumbs->addCrumb("appointments", array(
	                "label" => $this->__("Appointments"),
	                "title" => $this->__("Appointments")
			   )); */	 
		$this->renderLayout();
    }
    
    /* Ajax Login action */
    public function ajaxLoginAction()
    {
    	$session = Mage::getSingleton('customer/session');
    	if ($session->isLoggedIn()) {
    		// is already login redirect to account page
    		return;
    	}
    	
    	$result = array('success' => false);
    	if ($this->getRequest()->getParam('request'))
    	{
    		$request = $this->getRequest()->getParam('request');
    		if (empty($request['usrname']) || empty($request['passwd'])) {
    			$result['error'] = Mage::helper('appointments')->__('Login and password are required.');
    		}
    		else
    		{
    			try
    			{
    				$session->login($request['usrname'], $request['passwd']);
    				$block = $this->getLayout()->createBlock('appointments/registration','appointments_register',array('template' => 'appointments/signin_register.phtml'));
    				$output = $block->toHtml();
    				$result['success'] = true;
    				$result['msg'] = Mage::helper('appointments')->__('Login Successfull');
    				$result['output'] = $output;
    			}
    			catch (Mage_Core_Exception $e)
    			{
    				switch ($e->getCode()) {
    					case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
    						$message = Mage::helper('appointments')->__('Email is not confirmed. <a href="%s">Resend confirmation email.</a>', Mage::helper('customer')->getEmailConfirmationUrl($request['usrname']));
    						break;
    					default:
    						$message = $e->getMessage();
    				}
    				$result['error'] = $message;
    				$session->setUsername($request['usrname']);
    			}
    		}
    	}
    
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function stateAction() {
    	$countrycode = $this->getRequest()->getParam('country');
    	$html = "";
    	$statearray = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($countrycode)->load();
    	if(count($statearray) > 0){
    		$html .= "<select name='state' class='ele_width stateSelect'><option value=''>--Please Select--</option>";
    		foreach ($statearray as $_state) {
    			$html .= "<option value='" . $_state->getCode() . "'>" . $_state->getDefaultName() . "</option>";
    		}
    		$html .= "</select>";
    	} else {
    		$html .= "<input name='state' id='state' title='".Mage::helper('appointments')->__('State')."' value='' class='ele_width' type='text' />";
    	}
    	echo $html;
    }
    //To get the time depend on received qty by bhagya
    public function ajaxGetTimeAction()
    {
    	$result = array('success' => false);
    	$request = $this->getRequest()->getParam('request');
    	//Mage::log($request,Zend_Log::DEBUG,'my-log',true);
    	
   		//$aptmodel = Mage::getModel('appointments/timing')->load($request['qty'],'qty');
    	$time=Mage::helper('appointments')->getTimeByStoreAndPeople($request['qty'],$request['store']);
   		
   		
   		Mage::log($time,Zend_Log::DEBUG,'appointments',true);
   		
   		
   		
   	/* 	$coreResource = Mage::getSingleton('core/resource');
   		$connection = $coreResource->getConnection('core_read');
   		$sql = "SELECT * FROM allure_appointment_piercers WHERE your_field_here REGEXP '.*"array_key_here";s:[0-9]+:"your_value_here".*'";
   		//Mage::log($country_data,Zend_Log::DEBUG, 'store', true );
   		$value = $connection->fetchRow($sql, array($item->getProductId(),$countryCode->getWarehouseId())); */
   			
   		
   		$block = $this->getLayout()->createBlock('core/template','appointments_picktime',array('template' => 'appointments/pickurtime.phtml'))->setData("timing",$time)->setData("date",$request['date'])->setData("store_id",$request['store']);
   		$output = $block->toHtml();
   		
   		$result['success'] = true;
   		$result['msg'] = $time;
   		$result['output'] = $output;
   		
   		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
   		
   		//$block = $this->getLayout()->createBlock('core/template')->setTemplate('/cart/mycart.phtml')->toHtml();
   		
    }
    
    
    
    public function saveAction ()
    {
    	$post_data = $this->getRequest()->getPost();
    	$embeded = $this->getRequest()->getParam('embedded');
    	$storep = $this->getRequest()->getParam('store');
    	
    
    	
    	if($embeded=='1')
    		$appendUrl = "?embedded=".$embeded;
    	if($storep)
    	{
    		if($appendUrl)
    			$appendUrl.="&";
    		else 
    			$appendUrl="?";
    		$appendUrl.= "store=".$storep;	
    	}
    	
    	if ($post_data) {
    		Mage::log(" ***********Register appointment**********",Zend_Log::DEBUG,'appointments-register.log',true);
    		Mage::log($post_data,Zend_Log::DEBUG,'appointments-register.log',true);
    		try {
    			 $post_data['appointment_start'] = $post_data['app_date']." ". $post_data['appointment_start'];
    			 $post_data['appointment_start'] = strtotime($post_data['appointment_start'].":00");
    			 $post_data['appointment_start'] = date('Y-m-d H:i:s', $post_data['appointment_start']);
    			 
    			 $post_data['appointment_end'] = $post_data['app_date']." ". $post_data['appointment_end'];
    			 $post_data['appointment_end'] = strtotime("-1 minutes", strtotime($post_data['appointment_end'].":59"));
    			 $post_data['appointment_end'] = date('Y-m-d H:i:s', $post_data['appointment_end']);
    			
    			 $booking_date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
    			 $post_data['booking_time']= $booking_date;
    			 $post_data['app_status']='2';  // Set appointment status assigned
				 
    			 $phno = preg_replace('/\s+/', '', $post_data['phone']);//remove the whitespaces from phone no
    			 $post_data['phone'] = $phno;
    			 $storeId=$post_data['store_id'];
    			 if(isset($post_data['id'])){
    			 	$old_appointment = Mage::getModel('appointments/appointments')->load($post_data['id']);
    			 }
    			 $model = Mage::getModel('appointments/appointments')->addData($post_data)
    				->save();
    				
    				//$this->createCust($model);
    				
    		     if($post_data['password']!=null || $post_data['password']!=''){
    					$websiteId = Mage::app()->getWebsite()->getId();
	    				$cust_exist = $this->IscustomerEmailExists($model->getEmail(),$websiteId);
	    				
	    				if($cust_exist){
	    					Mage::getSingleton('customer/session')->addError('Customer Email Exists Already');
	    				}
	    				else{
	    					$this->createCust($model);
	    				}
    			 }  //End of If creating customer
    				
    			//IF appointment is modified then send updates to ADMIN & PIERCER & CUSTOMER
    			 
    			if($old_appointment)
    			{
    	         		//If SMS is checked for notify me.
        				$oldAppointmentStart=date("F j, Y H:i", strtotime($old_appointment->getAppointmentStart()));
        				$oldAppointmentEnd=date("F j, Y H:i", strtotime($old_appointment->getAppointmentEnd()));
        				$appointmentStart=date("F j, Y H:i", strtotime($model->getAppointmentStart()));
        				$appointmentEnd=date("F j, Y H:i", strtotime($model->getAppointmentEnd()));
        				
    	    			if($post_data['notification_pref'] === '2')
    	    		    {
    	    		    
    	    			    $smsText = Mage::getStoreConfig("appointments/api/smstext_modified",$storeId);
    	    			  
    	    			    $date = date("F j, Y ", strtotime($model->getAppointmentStart()));
    	    			    $time=date('h:i A', strtotime($model->getAppointmentStart()));
    	    			    $smsText=str_replace("(time)",$time,$smsText);
    	    			    $smsText=str_replace("(date)",$date,$smsText);
    	    			    
    	    			    Mage::log($smsText,Zend_Log::DEBUG,'appointments',true);
    	    			    
    	    			    //Do not send mofify link for Modify again
    	    			    
    	    			    if($post_data['phone']){
    	    			       $smsdata = Mage::helper('appointments')->sendsms($post_data['phone'],$smsText,$storeId);
    	    				   Mage::log("Appointment Modification Email Sent",Zend_Log::DEBUG,'appointments',true);
    	    				   $model->setSmsStatus($smsdata);
    	    				   $model->save();
    	    				}
    	    		    }
	    				$toSend = Mage::getStoreConfig("appointments/customer/send_customer_email",$storeId);
	    				
	    				//Notifiy Customer by Email
	    				
	    				if($toSend)
	    				{
	    					$templateId = Mage::getStoreConfig("appointments/customer/customer_modify_template",$storeId);
	    					$mailSubject="Appointment Modified";
	    					$sender     = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
	    					$email = $model->getEmail();
	    					$name = $model->getFirstname()." ".$model->getLastname();
	    					$apt_modify_link = Mage::getUrl('appointments/index/modify',array('id'=>$model->getId(),'email'=>$model->getEmail(),'_secure' => true));
	    					$vars = array(
	    							'pre_name'        => $old_appointment->getFirstname()." ".$old_appointment->getLastname(),
	    							'pre_customer_name'        => $old_appointment->getFirstname()." ".$old_appointment->getLastname(),
	    							'pre_customer_email'  => $old_appointment->getEmail(),
	    							'pre_customer_phone'      => $old_appointment->getPhone(),
	    							'pre_no_of_pier' => $old_appointment->getPiercingQty(),
	    							'pre_piercing_loc' => $old_appointment->getPiercingLoc(),
	    							'pre_special_notes' => $old_appointment->getSpecialNotes(),
	    							'pre_apt_starttime'  => $oldAppointmentStart,
	    							'pre_apt_endtime'    => $oldAppointmentEnd,
	    							
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
	    					$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
	    				}
	    				//Admin Email Code to Modify the customer Appointment
	    				$toSend = Mage::getStoreConfig("appointments/admin/send_admin_email",$storeId);
	    				if($toSend)
	    				{
	    					$templateId = Mage::getStoreConfig("appointments/admin/admin_modify_template",$storeId);
	    					$adminEmail = Mage::getStoreConfig("appointments/admin/admin_email",$storeId);
	    					$adminEmail=explode(",",$adminEmail);
	    					$mailSubject="Appointment Modified";
	    					$sender         = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
	    					$email = $adminEmail;
	    					$name = "Admin";
	    					$vars = array(
	    							'pre_name'        => $old_appointment->getFirstname()." ".$old_appointment->getLastname(),
	    							'pre_customer_name'        => $old_appointment->getFirstname()." ".$old_appointment->getLastname(),
	    							'pre_customer_email'  => $old_appointment->getEmail(),
	    							'pre_customer_phone'      => $old_appointment->getPhone(),
	    							'pre_no_of_pier' => $old_appointment->getPiercingQty(),
	    							'pre_piercing_loc' => $old_appointment->getPiercingLoc(),
	    							'pre_special_notes' => $old_appointment->getSpecialNotes(),
	    							'pre_apt_starttime'  => $oldAppointmentStart,
	    							'pre_apt_endtime'    => $oldAppointmentEnd,
	    							
	    							'name'        => $model->getFirstname()." ".$model->getLastname(),
	    							'customer_name'        => $model->getFirstname()." ".$model->getLastname(),
	    							'customer_email'  => $model->getEmail(),
	    							'customer_phone'      => $model->getPhone(),
	    							'no_of_pier' => $model->getPiercingQty(),
	    							'piercing_loc' => $model->getPiercingLoc(),
	    							'special_notes' => $model->getSpecialNotes(),
	    							'store_name'	=> Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
	    							'store_address'	=> Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
	    							'store_email_address'	=> Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
	    							'store_phone'	=> Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
	    							'store_hours'	=> Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
	    							'store_map'	=> Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
	    							'apt_starttime'  => $appointmentStart,
	    							'apt_endtime'    => $appointmentEnd);
	    					
	    					$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
	    				}
	    			    // Piercer Email Code to modify the Appointment
	    			
	    				$piercer_id = $old_appointment->getPiercerId();
	    				$piercer = Mage::getModel('appointments/piercers')->load($piercer_id);
	    				$toSend = $piercer->getEmail();
	    				if($toSend)
	    				{
	    					$templateId = Mage::getStoreConfig("appointments/piercer/piercer_modify_template",$storeId);
	    					$mailSubject="Appointment Modified";
	    					$sender = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
	    					$email = $piercer->getEmail();
	    					$name = $piercer->getFirstname()." ".$piercer->getLastname();
	    					$vars = array(
	    							'pre_name'        => $piercer->getFirstname()." ".$piercer->getLastname(),
	    							'pre_customer_name'        => $old_appointment->getFirstname()." ".$old_appointment->getLastname(),
	    							'pre_customer_email'  => $old_appointment->getEmail(),
	    							'pre_customer_phone'      => $old_appointment->getPhone(),
	    							'pre_no_of_pier' => $old_appointment->getPiercingQty(),
	    							'pre_piercing_loc' => $old_appointment->getPiercingLoc(),
	    							'pre_special_notes' => $old_appointment->getSpecialNotes(),
	    							'pre_apt_starttime'  => $oldAppointmentStart,
	    							'pre_apt_endtime'    =>$oldAppointmentEnd,
	    							
	    							'name'        => $piercer->getFirstname()." ".$piercer->getLastname(),
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
	    					$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
	    				}
	    				
    				}
    				else //FOR new appointment book
    				{
    					//If SMS is checked for notify me.
    					
    					$appointmentStart=date("F j, Y H:i", strtotime($model->getAppointmentStart()));
    					$appointmentEnd=date("F j, Y H:i", strtotime($model->getAppointmentEnd()));
    					
    					if($model->getNotificationPref() === '2')
    					{
    						Mage::log("New appointment Bookig",Zend_Log::DEBUG,'appointments',true);
    						$smsText = Mage::getStoreConfig("appointments/api/smstext_book",$storeId);
    						$date = date("F j, Y ", strtotime($model->getAppointmentStart()));
    						$time=date('h:i A', strtotime($model->getAppointmentStart()));
    						$smsText=str_replace("(time)",$time,$smsText);
    						$smsText=str_replace("(date)",$date,$smsText);
    						$apt_modify_link = Mage::getUrl('appointments/index/modify',array('id'=>$model->getId(),'email'=>$model->getEmail(),'_secure' => true));
    						$shortUrl=Mage::helper('appointments')->getShortUrl($apt_modify_link);
    						$smsText=str_replace("(modify_link)",$shortUrl,$smsText);
    						Mage::log($smsText,Zend_Log::DEBUG,'appointments',true);
    						if($model->getPhone()){
    							$smsdata = Mage::helper('appointments')->sendsms($model->getPhone(),$smsText,$storeId);
    							$model->setSmsStatus($smsdata);
    							$model->save();
    							Mage::log("New appointment Message Sent",Zend_Log::DEBUG,'appointments',true);
    						}
    					}
    				    //Customer Email Code
    					$toSend = Mage::getStoreConfig("appointments/customer/send_customer_email",$storeId);
    					
    					
    					Mage::log(" *********** appointment Start**********",Zend_Log::DEBUG,'appointments-register.log',true);
    					Mage::log($appointmentStart,Zend_Log::DEBUG,'appointments-register.log',true);
    					Mage::log(" *********** appointment End**********",Zend_Log::DEBUG,'appointments-register.log',true);
    					Mage::log($appointmentEnd,Zend_Log::DEBUG,'appointments-register.log',true);
    					
    					if($toSend)
    					{
    						$templateId = Mage::getStoreConfig("appointments/customer/customer_template",$storeId);
    						$mailSubject="Appointment booking";
    						$sender         = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
    						$email = $model->getEmail();
    						$name = $model->getFirstname()." ".$model->getLastname();
    						$apt_modify_link = Mage::getUrl('appointments/index/modify',array('id'=>$model->getId(),'email'=>$model->getEmail(),'_secure' => true));
    					
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
    						$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
    					}
    					//Admin Email Code
    					$toSend = Mage::getStoreConfig("appointments/admin/send_admin_email",$storeId);
    					if($toSend)
    					{
    						$templateId = Mage::getStoreConfig("appointments/admin/admin_template",$storeId);
    						$adminEmail = Mage::getStoreConfig("appointments/admin/admin_email",$storeId);
    						$recipientArr=explode(",",$adminEmail);
    						$mailSubject="Appointment booking";
    						$sender         = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
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
    								'apt_endtime'    =>$appointmentEnd);
    						$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
    					}
    					//Piercer Email Code
    					$toSend = Mage::getStoreConfig("appointments/piercer/send_piercer_email",$storeId);
    					$piercer_id = $model->getPiercerId();
    					$piercer = Mage::getModel('appointments/piercers')->load($piercer_id);
    					if($toSend)
    					{
    						$templateId = Mage::getStoreConfig("appointments/piercer/piercer_template",$storeId);
    						$mailSubject="Appointment Booking";
    						$sender         = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
    						$email = $piercer->getEmail();
    						$name = $piercer->getFirstname()." ".$piercer->getLastname();
    						$vars = array(
    								'name'        => $piercer->getFirstname()." ".$piercer->getLastname(),
    								'customer_name'        => $model->getFirstname()." ".$model->getLastname(),
    								'customer_email'  => $model->getEmail(),
    								'customer_phone'      => $model->getPhone(),
    								'no_of_pier' => $model->getPiercingQty(),
    								'piercing_loc' => $model->getPiercingLoc(),
    								'special_notes' => $model->getSpecialNotes(),
    								'apt_starttime'  =>$appointmentStart,
    								'store_name'	=> Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
    								'store_address'	=> Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
    								'store_email_address'	=> Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
    								'store_phone'	=> Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
    								'store_hours'	=> Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
    								'store_map'	=> Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
    								'apt_endtime'    =>$appointmentEnd);
    						$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
    					}
    					/*End of Piercer Email Code*/
    					
    				} //End of New Piearcer 
    				
    				Mage::getSingleton("core/session")->setData('appointment_submitted',$model);
    				$this->getResponse()->setRedirect(Mage::getUrl("*/*/",array('_secure' => true)).$appendUrl);
    				return;
    		} catch (Exception $e) {
    			Mage::getSingleton("core/session")->addError($e->getMessage());
    			$this->getResponse()->setRedirect(Mage::getUrl("*/*/",array('_secure' => true)).$appendUrl);
    			return;
    		}
    	}
    	$this->getResponse()->setRedirect(Mage::getUrl("*/*/",array('_secure' => true)).$appendUrl);
    }
    
    /* Create the customer by bhagya*/
    public function createCust($cust_data){    	
    	$customer_email = $cust_data->getEmail();
    	$customer_fname = $cust_data->getFirstname();
    	$customer_lname = $cust_data->getLastname();
    	
    	//$passwordLength = 10; // the lenght of autogenerated password 
    	$password = $cust_data->getPassword();    	
    	$customer_phone = $cust_data->getPhone();
    	$customer_street = $cust_data->getStreet();
    	$customer_country = $cust_data->getCountry();
    	$customer_state = $cust_data->getState();
    	$customer_city = $cust_data->getCity();
    	$customer_postal_code = $cust_data->getPostalCode();
    	
    	$addressData =  array (
    			'firstname' => $customer_fname,
    			'lastname' => $customer_lname,
    			'street' => $customer_street,
    			'city' => $customer_city,
    			'country_id' => $customer_country,
    			'region' => $customer_state,
    			//[region_id] => 1
    			'postcode' => $customer_postal_code,
    			'telephone' => $customer_phone,
    			'is_default_billing' => 1,
    			'is_default_shipping' => ''
    	);
    	$customer = Mage::getModel('customer/customer');
    	$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    	$customer->loadByEmail($customer_email);
    	
    	$address   = Mage::getModel('customer/address');
    	$address->addData($addressData);
    	
    	//Check if the email exist on the system.If YES,  it will not create a user account.
    	if(!$customer->getId()) {
    	
    		//setting data such as email, firstname, lastname, and password    	
    		$customer->setEmail($customer_email);
    		$customer->setFirstname($customer_fname);
    		$customer->setLastname($customer_lname);
    		
    		//$customer->setPassword($customer->generatePassword($passwordLength));
    		$customer->setPassword($password);
    		//$customer->password_hash = md5($password);
    		$customer->setPassword($password);
    		$customer->addAddress($address);
    	
    	}
    	try{
    		//the save the data and send the new account email.
    		$customer->save();
    		$customer->setConfirmation(null);
    		$customer->save();
    		$customer->sendNewAccountEmail();
    	}
    	
    	catch(Exception $ex){
    		Mage::log(" Customer create when new customer book appointment :".$e->getMessage(),Zend_Log::DEBUG,'appointments',true);
    		//Mage::log($e->getMessage());
    		//print_r($e->getMessage());
    	}    	
    }
    
    /* Check customer exists start */
    function IscustomerEmailExists($email, $websiteId = null){
    	$customer = Mage::getModel('customer/customer');
    
    	if ($websiteId) {
    		$customer->setWebsiteId($websiteId);
    	}
    	$customer->loadByEmail($email);
    	if ($customer->getId()) {
    		return $customer->getId();
    	}
    	return false;
    }
    
    /* Modify or Cancel URL Action by bhagya */
    public function modifyAction(){
    	
    	$apt_id = $this->getRequest()->getParam('id');
    	$apt_email = $this->getRequest()->getParam('email');
    	
    	//$apt_id = Mage::helper('core')->decrypt($encryptedId);
    	//$apt_email = Mage::helper('core')->decrypt($encryptedEmail);
    	
    	
    	if( $apt_id && $apt_email )
    	{
    		//$append_url = "?apt_id=".$apt_id."&email=".$apt_email;    		
    		$models = Mage::getModel('appointments/appointments')->getCollection();
    		$models->addFieldToFilter('id',$apt_id)->addFieldToFilter('email',$apt_email)->addFieldToFilter('app_status',array('in'=>array(Allure_Appointments_Model_Appointments::STATUS_REQUEST,Allure_Appointments_Model_Appointments::STATUS_ASSIGNED)));
    		if(count($models)){    			
    			foreach ($models as $model){    				
    				$model=$model;break;
    			}
    			Mage::register('appointment_modified',$model);
    			Mage::getSingleton("core/session")->setData('appointment_availablity',true);
    		}
    		else{
    			Mage::getSingleton("core/session")->setData('appointment_availablity',false);
    		}
    	}    	
    	$this->loadLayout();
    	$this->getLayout()->getBlock("head")->setTitle($this->__("Appointments"));
    	$this->renderLayout();    	
    	
    }
    
    /* CancelaptAction by bhagya */
    public function cancelaptAction(){
    	$apt_id = $this->getRequest()->getParam('id');
    	$apt_email = $this->getRequest()->getParam('email');
    	
    	if( $apt_id ||$apt_email ){
    		$data = array('app_status'=> Allure_Appointments_Model_Appointments::STATUS_CANCELLED);
    		$model = Mage::getModel('appointments/appointments')->load($apt_id);
    		$storeId=$model->getStoreId();
    		$model = Mage::getModel('appointments/appointments')->load($apt_id)->addData($data);
    		
    			try {
	    			$model->setId($apt_id)->save();
	    			echo "Your scheduled Appointment is Cancelled successfully.";
	    			
	    			
	    			/*Customer Email Code to cancel the Appointment*/
	    			
	    			//SMS CODE TO CANCEL Appointment start
	    			if($model->getNotificationPref() ==='2')
	    			{
		    			//$smsText = "Your Appointment is Cancelled successfully";
	    				$smsText = Mage::getStoreConfig("appointments/api/smstext_cancel",$storeId);
	    				$appointmentStart=date("F j, Y H:i", strtotime($model->getAppointmentStart()));
	    				$date = date("F j, Y ", strtotime($model->getAppointmentStart()));
	    				$time=date('h:i A', strtotime($model->getAppointmentStart()));
	    				
	    				$booking_link= Mage::getBaseUrl('web').'appointments/';
	    				$booking_link=Mage::helper('appointments')->getShortUrl($booking_link);
	    				$smsText=str_replace("(time)",$time,$smsText);
	    				$smsText=str_replace("(date)",$date,$smsText);
	    				$smsText=str_replace("(book_link)",$booking_link,$smsText);
	    				
	    				Mage::log($smsText,Zend_Log::DEBUG,'appointments',true);
		    			if($model->getPhone()){	    			
		    				$phno_forsms = preg_replace('/\s+/', '', $model->getPhone());
		    				$smsdata = Mage::helper('appointments')->sendsms($phno_forsms,$smsText,$storeId);
		    				$model->setSmsStatus($smsdata);
		    				$model->save();
		    				
		    			}
	    			}
	    			//SMS CODE TO CANCEL Appointment end
	    			$appointmentStart=date("F j, Y H:i", strtotime($model->getAppointmentStart()));
	    			$appointmentEnd=date("F j, Y H:i", strtotime($model->getAppointmentEnd()));
	    			
	    			$toSend = Mage::getStoreConfig("appointments/customer/send_customer_email",$storeId);
	    			if($toSend)
	    			{
	    				$templateId = Mage::getStoreConfig("appointments/customer/customer_cancel_template",$storeId);
	    				$mailSubject="Appointment Cancellation";
	    				$sender         = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
	    				$email = $model->getEmail();
	    				$name = $model->getFirstname()." ".$model->getLastname();
	    				$apt_modify_link = Mage::getUrl('appointments/index/modify',array('id'=>$model->getId(),'email'=>$model->getEmail(),'_secure' => true));
	    			
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
	    				$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
	    			}
	    			/*End of Email Code*/
	    			
	    			/*Admin Email Code to cancel the Appointment*/
	    			$toSend = Mage::getStoreConfig("appointments/admin/send_admin_email",$storeId);
	    			if($toSend)
	    			{
	    				$templateId = Mage::getStoreConfig("appointments/admin/admin_cancel_template",$storeId);
	    				$adminEmail = Mage::getStoreConfig("appointments/admin/admin_email",$storeId);
	    				$adminEmail=explode(",",$adminEmail);
	    				$mailSubject="Appointment Cancellation";
	    				$sender         = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
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
	    				$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
	    			}
	    			/*End of Email Code*/
					
	    			/*Piercer Email Code to cancel the Appointment*/
	    			/*Email Code*/
	    			$toSend = Mage::getStoreConfig("appointments/piercer/send_piercer_email",$storeId);
	    			$piercer_id = $model->getPiercerId();
	    			$piercer = Mage::getModel('appointments/piercers')->load($piercer_id);
	    			if($toSend)
	    			{
	    				$templateId = Mage::getStoreConfig("appointments/piercer/piercer_cancel_template",$storeId);
	    				$mailSubject="Appointment Cancellation";
	    				$sender = array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
	    				$email = $piercer->getEmail();
	    				$name = $piercer->getFirstname()." ".$piercer->getLastname();
	    				$vars = array(
	    						'name'        => $piercer->getFirstname()." ".$piercer->getLastname(),
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
	    				$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
	    			}
	    			/*End of Email Code*/
	    			
	    			
	    			 
	    		} catch (Exception $e){
	    			echo $e->getMessage();
	    		}
    		$model->save();
    	}
    }
    
    
    //To get the Working days depend on storeid by bhagya
    public function ajaxGetWorkingDaysAction()
    {
    	$result = array('success' => false);
    	$storeid = $this->getRequest()->getParam('storeid');
    	 
    	//To modify the appointment get the data from registry end
    	$piercers = Mage::getModel('appointments/piercers')->getCollection()
    				->addFieldToFilter('store_id', array('eq' => $storeid))
    				->addFieldToFilter('is_active', array('eq' => '1'));
    	
    	$avial_workDays = array();
    	
    	foreach ($piercers as $piercer){
    		//$workdays = explode(",",$piercer->getWorkingDays());
    		$workdays = array_map('trim', explode(',', $piercer->getWorkingDays()));
    		$avial_workDays[] = $workdays;
    	
    	}
    	$available_wdays=array();
    	foreach ($avial_workDays as $avail_wd){
    		foreach ($avail_wd as $wd){
    			$available_wdays[]=$wd;
    		}
    	}
    	$jsonDATA="";

    	if(!empty($available_wdays)) {
    		$jsonDATA = json_encode(array_unique($available_wdays));
        }

    	$block = $this->getLayout()->createBlock('core/template','appointments_pickurday',array('template' => 'appointments/pickurday.phtml'))->setData("workingdays",$jsonDATA);
    	
    	$output = $block->toHtml();
    	$schedule= Mage::getStoreConfig("appointments/piercer_schedule/schedule",$storeid);
    	$result['success'] = true;
    	$result['output'] = $output;
    	$result['schedule'] = $schedule;
    	 
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
