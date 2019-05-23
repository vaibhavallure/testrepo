<?php
class Allure_Appointments_Adminhtml_AppointmentsController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		return true;
	}
	protected function _initAction() {
	    $this->loadLayout()
	    ->_setActiveMenu($this->_menu_path)
	    ->_addBreadcrumb(
	        Mage::helper('adminhtml')->__('Manage Stock'), Mage::helper('adminhtml')->__('Appointments')
	        );
	    return $this;
	}
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Appointments"));
	   $this->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointments'));
	   $this->renderLayout();
    }
    
    public function gridAction()
    {
    	
    	$this->loadLayout();
    	/* $this->getResponse()->setBody(
    			$this->getLayout()->createBlock('appointments/adminhtml_appointments')->toHtml()
    			); */
    	$this->getResponse()->setBody(
    			$this->getLayout()->createBlock('appointments/adminhtml_appointments_grid')->toHtml()
    			);
    	date_default_timezone_set("UTC");
    }
    
    public function viewAction ()
    {
    	$id = $this->getRequest()->getParam('id');
    	$model = Mage::getModel('appointments/appointments')->load($id);
    	if($model->getId())
    	{
    		if(Mage::registry('allure_appointment')){
    			Mage::unregister('allure_appointment');
    		}
    		Mage::register('allure_appointment', $model);
	    	$this->loadLayout();
	    	$this->_setActiveMenu("allure/appointments");
	    	$this->getLayout()
	    	->getBlock("head")
	    	->setCanLoadExtJs(true);
	    	$this
	    	->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointments_view'));
	    	$this->renderLayout();
    	}
    	else{
    		Mage::getSingleton("adminhtml/session")->addError(Mage::helper('appointments')->__("Appointment not available"));
    		$this->_redirect("*/*/");
    	}
    }
    
    public function viewCalenderAction ()
    {
    	$post_data = $this->getRequest()->getParam('store');
    	$store_id = 0;
    	if($post_data){
    		$store_id = $post_data;
    	}
    	
    	Mage::register('store_iddd', $store_id);
    	
    		$this->loadLayout();
    		$this->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointments_calenderview'));
    		$this->renderLayout();
    	
    }
    
    public function savePiercerAction ()
    {
    	$post_data = $this->getRequest()->getPost();
    	$id = $this->getRequest()->getParam('id');
    	if ($id) {
    
    		if(!$post_data['appointment_piercer']){
    			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("Plese select a valid Piercer"));
    			$this->_redirect("*/*/view",array('id'=>$id));
    		}
    		try {
    			 
    			$model = Mage::getModel('appointments/appointments')->load($id);
    			$storeId=$model->getStoreId();
    			if(!$model){
    				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("Appointment Not Available"));
    				$this->_redirect("*/*/view",array('id'=>$id));
    			}
    			$oldPiercerId = $model->getPiercerId();
    			$piercer = Mage::getModel('appointments/piercers')->load($post_data['appointment_piercer']);	
    			if(!$piercer){
    				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("Piercer Not Available"));
    				$this->_redirect("*/*/view",array('id'=>$id));
    			}
    			if($model->getAppStatus() == Allure_Appointments_Model_Appointments::STATUS_REQUEST)
    				$model->setAppStatus(Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
    			$model->setData('piercer_id',$post_data['appointment_piercer'])->save();
    			
    			//add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");
    
    			/*Email Code*/
    			//$toSend = Mage::getStoreConfig("appointments/piercer/send_piercer_email",$storeId);
    			
    			$configData = $this->getAppointmentStoreMapping();     //new code store clnup
    			$storeKey = array_search ($storeId, $configData['stores']);
    			$toSend = $configData['piercer_email_enable'][$storeKey]; //new code store clnup
    			if($toSend)
    			{
    				//$templateId = Mage::getStoreConfig("appointments/piercer/piercer_welcome_template",$storeId);
    				
    			    $templateId = $configData['piercer_email_template_welcome'][$storeKey];
    				$mailSubject="sample subject";
    				
    				$sender         = array('name'=>Mage::getStoreConfig("trans_email/bookings/name",$storeId), 'email'=> Mage::getStoreConfig("trans_email/bookings/email",$storeId));
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
    						'apt_starttime'  => $model->getAppointmentStart(),
    				        'store_name'	=> $configData['store_name'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
    				        'store_address'	=> $configData['store_address'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
    				        'store_email_address'	=> $configData['store_email'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
    				        'store_phone'	=> $configData['store_phone'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
    				        'store_hours'	=> $configData['store_hours_operation'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
    				        'store_map'	=> $configData['store_map'][$storeKey],// Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
    						'apt_endtime'    => $model->getAppointmentEnd());
    				$mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$email,$name,$vars);
    			}
    			/*End of Email Code*/

    			Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Piercer saved sucessfully"));
    			$this->_redirect("*/*/view",array('id'=>$id));
    			return;
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			$this->_redirect("*/*/view",array('id'=>$id));
    			return;
    		}
    	}
    	$this->_redirect("*/*/view",array('id'=>$id));
    }
    
    
    public function newAction()
    {
    /* 	$id = $this->getRequest()->getParam('id');
    	$model = Mage::getModel('appointments/piercers')->load($id);
    
    	$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
    	if (!empty($data)) {
    		$model->setData($data);
    	} */
    
    	$this->loadLayout();
    	//$this->_setActiveMenu('blog/posts');
    	$this->_title('Create Appointment');
    
    	/* $this->getLayout()->getBlock('head')->setCanLoadExtJs(true); */
    
    	/* $this
    	->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointments_edit'))
    	->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_appointments_edit_tabs'))
    	;
    	$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true); */
    	$this->renderLayout();
    	
    	
    	
    }
    
    public function saveAction ()
    {
    	$post_data = $this->getRequest()->getPost();
    	if ($post_data) {
    
    		if(!$post_data['firstname']){
    			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("Firstname is required"));
    			$this->_redirect("*/*/");
    		}
    		try {
    			
    			
    			/* logic for serialization */
    			$raw_timing_array = $post_data['working_hours']['value'];
    			$deleted_array = $post_data['working_hours']['delete'];
    			$timingData = array();
    			foreach ($raw_timing_array as $key=>$raw_field)
    			{
    				if($deleted_array[$key]=='')
    					$timingData[$key]=$raw_field;
    			}
    			$serializedTime = serialize($timingData);
    			unset($post_data['working_hours']);
    			$post_data['working_hours'] = $serializedTime;
    			
    			$post_data['working_days'] = implode(',', $post_data['working_days']);
    			
    			$model = Mage::getModel('appointments/piercers')->addData($post_data)
    			->setId($this->getRequest()
    					->getParam("id"))
    					->save();
    			
    			//add logs
    		    $helperLogs = $this->getLogsHelper();
    		    $helperLogs->saveLogs("admin");
    
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Piercer saved sucessfully"));
    					Mage::getSingleton("adminhtml/session")->setAppointmentpiercersData(false);
    
    					if ($this->getRequest()->getParam("back")) {
    						$this->_redirect("*/*/edit", array(
    								"id" => $model->getId()
    						));
    						return;
    					}
    					$this->_redirect("*/*/");
    					return;
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			Mage::getSingleton("adminhtml/session")->setAppointmentpiercersData($this->getRequest()
    					->getPost());
    			$this->_redirect("*/*/edit", array(
    					"id" => $this->getRequest()
    					->getParam("id")
    			));
    			return;
    		}
    	}
    	$this->_redirect("*/*/");
    }
    
    public function cancelAction ()
    {
    	if ($this->getRequest()->getParam("id") > 0) {
    		try {
    			$model = Mage::getModel('appointments/appointments')->load($this->getRequest()->getParam("id"));
    			$model->setAppStatus(Allure_Appointments_Model_Appointments::STATUS_CANCELLED);
    			$model->save();
                $this->notifyCancel($model);


    			//add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");
    			
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Appointment was successfully Cancelled"));
    					$this->_redirect("*/*/");
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			$this->_redirect("*/*/view", array(
    					"id" => $this->getRequest()
    					->getParam("id")
    			));
    		}
    	}
    	$this->_redirect("*/*/");
    }
    
    public function undoCancelAction ()
    {
    	if ($this->getRequest()->getParam("id") > 0) {
    		try {
                $old_appointment=$model = Mage::getModel('appointments/appointments')->load($this->getRequest()->getParam("id"));
                if($this->validateSlotBeforeBookAppointment($model))
                {
                    Mage::getSingleton("adminhtml/session")->addError(
                        Mage::helper("adminhtml")->__("Can Not Undo ".$model->getId()." Another Appointment Present For Same Date And Time"));
                    $this->_redirect("*/*/");
                    return;
                }
    			$model->setAppStatus(Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
    			$model->save();

                $status_changed=" From Cancel To Assigned";
                $this->notifyModify($old_appointment,$model,$status_changed);
    			
    			//add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");
    			
    			Mage::getSingleton("adminhtml/session")->addSuccess(
    					Mage::helper("adminhtml")->__("Appointment was successfully Resheduled"));
    			$this->_redirect("*/*/");
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			$this->_redirect("*/*/view", array(
    					"id" => $this->getRequest()
    					->getParam("id")
    			));
    		}
    	}
    	$this->_redirect("*/*/");
    }
    
    
    public function deleteAction ()
    {
    	if ($this->getRequest()->getParam("id") > 0) {
    		try {
    			$model = Mage::getModel('appointments/appointments');
    			$model->setId($this->getRequest()
    					->getParam("id"))
    					->delete();
    			
    		    //add logs
    		    $helperLogs = $this->getLogsHelper();
    		    $helperLogs->saveLogs("admin");
    					
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Piercer was successfully deleted"));
    					$this->_redirect("*/*/");
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			$this->_redirect("*/*/edit", array(
    					"id" => $this->getRequest()
    					->getParam("id")
    			));
    		}
    	}
    	$this->_redirect("*/*/");
    }
    
    
    public function calendereventsAction(){
    		$calenderEvents = array();
    		
    		/* $name = "test name";
    		
    		$time = strtotime('3/16/2017 3:28 AM');
    		
    		$newformat = date('Y-m-d H:i',$time);
    		
    		$start_time = $newformat;
    		$end_time = $newformat;
    		$url = "not found"; */
    		$store_id = $this->getRequest()->getParam('store_id');
    		
    		$url = "not found";
    		$allAppointments = Mage::getModel('appointments/appointments')->getCollection();
    		if($store_id){
    			$allAppointments->addFieldToFilter('store_id',$store_id);
    		}    		
    		$allAppointments->addFieldToFilter('app_status',array('in'=>array('1','2')));
    		
    		if($allAppointments){
    			
    		foreach ($allAppointments as $appointment){
    		    $piercer=Mage::getModel("appointments/piercers")->load($appointment->getPiercerId());
    		    
    		    $calenderEvents[] = array('title'=>$appointment->getFirstname()." ".$appointment->getLastname()." (".$piercer->getFirstname()." ".$piercer->getLastname().")",
	    				'start'=>$appointment->getAppointmentStart(),
	    				'end'=>$appointment->getAppointmentEnd(),
	    				'url'=>$this->getUrl('admin_appointments/adminhtml_appointments/view/id/'.$appointment->getId(),array('_secure' => true)),
	    		        'color'=>$piercer->getColor()
	    		);
    		}
    	 }
    	$_currentStore=Mage::app()->getStore();
    	$code1 = $_currentStore->getCode();
    	$lanCode = substr(strrchr($code1, "_"), 1);
    
    	$response = array('status'=>true,'events'=>$calenderEvents,'lang'=>$lanCode);
    	$jsonData = json_encode ( compact ( 'success', 'response', 'data' ) );
    	$this->getResponse ()->setHeader ( 'Content-type', 'application/json' );
    	$this->getResponse ()->setBody ( $jsonData );
    }
    
    
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction ()
    {
    	$fileName = 'appointments.csv';
    	$grid = $this->getLayout()->createBlock('appointments/adminhtml_appointments_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction ()
    {
    	$fileName = 'appointments.xml';
    	$grid = $this->getLayout()->createBlock('appointments/adminhtml_appointments_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    public function printAction() {
       /*  $this->_initAction();
        $this->_title($this->__('Appointments'))
        ->_title($this->__('Print'));
        $this->renderLayout(); */
        
       
        
        $this->loadLayout();
        //$this->_setActiveMenu('blog/posts');
        $this->_title('Print Appointments');
        
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        
        $this
        ->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointments_print'))
        ->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_appointments_print_tabs'))
        ;
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->renderLayout();
    }
    
    public function pdfdocsAction(){
        $post_data = $this->getRequest()->getPost();
        $appointments =  Mage::getModel('appointments/appointments')->getCollection();
        $appointments->getSelect()->joinLeft('allure_appointment_piercers', 'allure_appointment_piercers.id = main_table.piercer_id', array('firstname as fname','lastname as lname'));
        $appointments->addFieldToFilter('app_status', '2'); //Only assigned Appointments
        if($post_data['store_id']!=0){
            $appointments->addFieldToFilter('main_table.store_id',$post_data['store_id']);
        }
        /* if($post_data['piercer_id']!=0){
            $appointments->addFieldToFilter('piercer_id',$post_data['piercer_id']);
        } */
        if(!empty($post_data['from_date']) && !empty($post_data['to_date'])){
            $fromDate = date('Y-m-d', strtotime($post_data['from_date']))." 00:00:00";
            $toDate = date('Y-m-d', strtotime($post_data['to_date']))." 23:59:00";
            $appointments->addFieldToFilter('appointment_start', array('from'=>$fromDate, 'to'=>$toDate));
            
        }
        $appointments->getSelect()->order('appointment_start', 'ASC');

        if ($appointments->getSize()){
            $flag = true;
            if (!isset($pdf)){
                $pdf = Mage::getModel('appointments/pdf')->getPdf($appointments);
            } else {
                $pages = Mage::getModel('appointments/pdf')->getPdf($appointments);
                $pdf->pages = array_merge ($pdf->pages, $pages->pages);
            }
        }
        if ($flag) {
            return $this->_prepareDownloadResponse(
                'docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                $pdf->render(), 'application/pdf'
                );
        } else {
            $this->_getSession()->addError($this->__('There are no printable appointments related to store or Piercer.'));
            $this->_redirect('*/*/print');
        }
        
    }
    
    /**
     * return logs helper object
     */
    private function getLogsHelper(){
        return Mage::helper("appointments/logs");
    }
    public function transferAction(){
        $this->loadLayout();
        //$this->_setActiveMenu('blog/posts');
        $this->_title('Transfer Appointments');
        
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        
        $this
        ->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointments_transfer'))
        ->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_appointments_transfer_tabs'))
        ;
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->renderLayout();
    }
    public function doTransferAction(){
        $post_data = $this->getRequest()->getPost();
        if($post_data['source_piercer']==$post_data['destination_piercer']){
            $this->_getSession()->addError($this->__('Source Piercer and Destination Piercer can not be same.'));
            $this->_redirect('*/*/transfer');
        }else{
            $currentDateTimestamp = strtotime(date('m/d/Y'));
            $selectedDateTimestamp = strtotime($post_data['date']);
            $destPiercerId = $post_data['destination_piercer'];
            $isDestPiercerAvailable = Mage::helper("appointments") //aws02 - added line
            ->isPiercerAvailable($destPiercerId,date('m/d/Y',strtotime($post_data['date'])));
            if($isDestPiercerAvailable){ //aws02 - added line - check piercer is available or not at particular date
                if($selectedDateTimestamp >= $currentDateTimestamp){ //aws02 - added line - don't transfer appointment to past dates.
                    echo "<pre>";
                    
                    $fromDate = date('Y-m-d', strtotime($post_data['date']))." 00:00:00";
                    $toDate = date('Y-m-d', strtotime($post_data['date']))." 23:59:00";
                    $appCollection = Mage::getModel('appointments/appointments')->getCollection();
                    $appCollection->addFieldToFilter('appointment_start', array('from'=>$fromDate, 'to'=>$toDate))
                    ->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
                    ->addFieldToFilter('piercer_id', array('eq' => $post_data['source_piercer']));
                    $notTransfer=array();
                    foreach ($appCollection as $app){
                     
                        $appCollection1 = Mage::getModel('appointments/appointments')->getCollection();
                        $appCollection1->addFieldToFilter(array('appointment_start', 'appointment_end'), array(array('from'=>$app->getAppointmentStart(), 'to'=>$app->getAppointmentEnd())))
                        ->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
                        ->addFieldToFilter('piercer_id', array('eq' =>  $post_data['destination_piercer']));
                      /*   print_r($appCollection1->getData());
                        die; */
                        if(count($appCollection1)){
                            $notTransfer[]=$appCollection1->getFirstItem()->getId();
                            continue;
                        }
                        else 
                        {
                            try {
                                Mage::log("Updating Piercer for appointment:",Zend_log::DEBUG,'appointments.log',true);
                                Mage::log(json_encode($app->getData()),Zend_log::DEBUG,'appointments.log',true);
                                $app->setPiercerId($post_data['destination_piercer'])->save();
                            } catch (Exception $e) {
                            }
                        }
                    }
                    $helperLogs = $this->getLogsHelper();
                    $helperLogs->saveLogs("admin");
                    if(count($notTransfer))
                        $this->_getSession()->addError($this->__('Unbale to transfer some of appointments as timeslot is not availbale'));
                    else 
                        $this->_getSession()->addSuccess($this->__('Appointments transfered succesfully'));
                        $this->_redirect('*/*/transfer');
                }else{//aws02 - Start 
                    $this->_getSession()->addError($this->__('Please select correct date.'));
                    $this->_redirect('*/*/transfer');
                } 
            }else{ 
                $this->_getSession()->addError($this->__("Piercer is not avaible on %s.",date('d M Y', strtotime($post_data['date']))));
                $this->_redirect('*/*/transfer');
            }//aws02 - End 
       }
            
       // print_r($post_data);
    }
    /**
     * return array of store mapping
     */
    private function getAppointmentStoreMapping(){
        return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    }

    public function changeStatusAction()
    {
        $data=$post_data = $this->getRequest()->getPost();

        $status_changed_flag=false;

        if (count($data['allure_appointments_ids']) > 0) {
            try {

                foreach ($data['allure_appointments_ids'] as $id) {
                    $old_appointment=$model = Mage::getModel('appointments/appointments')->load($id);

                    if($model->getAppStatus()=="4")
                    {


                        if($this->validateSlotBeforeBookAppointment($model))
                        {
                            Mage::getSingleton("adminhtml/session")->addError(
                                Mage::helper("adminhtml")->__("Can Not Undo ".$model->getId()." Another Appointment Present For Same Date And Time"));
                            continue;
                        }
                    }

                    $oldstatus=$model->getAppStatus();
                    $model->setAppStatus($data['status']);
                    $model->save();

                    $status_changed_flag=true;

                    if($data['status']=="4") {
                        $this->notifyCancel($model);
                    }
                    else if($data['status']=="2")
                    {
                        $status_changed=" From ".$this->getStatus($oldstatus)." To ".$this->getStatus($data['status']);
                        $this->notifyModify($old_appointment,$model,$status_changed);
                    }
                    else
                    {
                       /*$status_changed=" From ".$this->getStatus($oldstatus)." To ".$this->getStatus($data['status']);
                       $this->notifyModify($model,$status_changed);*/
                    }

                }
                    //add logs
                    $helperLogs = $this->getLogsHelper();
                    $helperLogs->saveLogs("admin");

                    if($status_changed_flag) {
                        Mage::getSingleton("adminhtml/session")->addSuccess(
                            Mage::helper("adminhtml")->__("Appointment Status Changed"));
                    }
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                       $this->_redirect("*/*/");

            }
        }


    }



    public function sendReminderAction()
    {
        $data=$post_data = $this->getRequest()->getPost();

        if (count($data['allure_appointments_ids']) > 0) {

                foreach ($data['allure_appointments_ids'] as $id) {
                    $appointment = Mage::getModel('appointments/appointments')->load($id);

                    if($appointment->getAppStatus()!=2)
                        continue;

                    $appointments[]=$appointment;
                }


            Mage::getModel('appointments/cron')->sendNotification($appointments,"manual",$data['reminder_type']);

            Mage::getSingleton("adminhtml/session")->addSuccess(
                Mage::helper("adminhtml")->__("Reminder sent to selected appointments"));
        }

        $this->_redirect("*/*/");

    }


    public function notifyModify($old_appointment,$model,$status_changed){
        $sendSms = false;
        $sendEmail = false;
        $notification_pref = $model->getNotificationPref();

        if($notification_pref == 2){
            $sendSms = true;
            $sendEmail = true;
        }else{
            $sendEmail = true;
        }


        $storeId = $model->getStoreId();
        $configData = $this->getAppointmentStoreMapping();
        $storeKey = array_search ($storeId, $configData['stores']);
        $store_nm = $configData['store_name'][$storeKey];
        $app_string="id->".$model->getId()." email->".$model->getEmail() ." mobile->".$model->getPhone()." name->".$model->getFirstname()." ".$model->getLastname()." ";

        $email_status_changed="Your Appointment Changed ".$status_changed;
        if(trim($store_nm)=='Nordstrom Local Melrose') {
            $apt_modify_link = Mage::getUrl('appointments/popup/modify', array(
                'id' => $model->getId(),
                'email' => $model->getEmail(),
                '_secure' => true
            ));
        }
        else {
            $apt_modify_link = Mage::getUrl('appointments/index/modify', array(
                'id' => $model->getId(),
                'email' => $model->getEmail(),
                '_secure' => true
            ));
        }

        if($sendEmail){
            $appointmentStart = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentStart()));
            $appointmentEnd = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentEnd()));
            if ($old_appointment) {
                // If SMS is checked for notify me.
                $oldAppointmentStart = date("F j, Y \\a\\t H:i", strtotime($old_appointment->getAppointmentStart()));
                $oldAppointmentEnd = date("F j, Y \\a\\t H:i", strtotime($old_appointment->getAppointmentEnd()));
            }
            $vars = array(
                'pre_name' => $old_appointment ? $old_appointment->getFirstname() . " " . $old_appointment->getLastname() : '',
                'pre_customer_name' => $old_appointment ? $old_appointment->getFirstname() . " " . $old_appointment->getLastname() : '',
                'pre_customer_email' => $old_appointment ? $old_appointment->getEmail() : '',
                'pre_customer_phone' => $old_appointment ? $old_appointment->getPhone() : '',
                'pre_no_of_pier' => $old_appointment ? $old_appointment->getPiercingQty() : '',
                'pre_piercing_loc' => $old_appointment ? $old_appointment->getPiercingLoc() : '',
                'pre_special_notes' => $old_appointment ? $old_appointment->getSpecialNotes() : '',
                'pre_apt_starttime' => $old_appointment ? $oldAppointmentStart : '',
                'pre_apt_endtime' => $old_appointment ? $oldAppointmentEnd : '',

                'name' => $model->getFirstname() . " " . $model->getLastname(),
                'customer_name' => $model->getFirstname() . " " . $model->getLastname(),
                'customer_email' => $model->getEmail(),
                'customer_phone' => $model->getPhone(),
                'no_of_pier' => $model->getPiercingQty(),
                'piercing_loc' => $model->getPiercingLoc(),
                'special_notes' => $model->getSpecialNotes(),
                'apt_starttime' => $appointmentStart,
                'apt_endtime' => $appointmentEnd,
                'store_name' => $configData['store_name'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
                'store_address' => $configData['store_address'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
                'store_email_address' => $configData['store_email'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
                'store_phone' => $configData['store_phone'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
                'store_hours' => $configData['store_hours_operation'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
                'store_map' => $configData['store_map'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
                'apt_modify_link' => $apt_modify_link,
                'booking_id'=>$model->getId(),
                'status_changed'=>$email_status_changed
            );

            //send Customer email
            $enableCustomerEmail = $configData['customer_email_enable'][$storeKey];
            /*$sender = array(
                'name' => Mage::getStoreConfig("trans_email/bookings/name"),
                'email' => Mage::getStoreConfig("trans_email/bookings/email")
            );*/

            $sender = array('name' => Mage::getStoreConfig("trans_email/bookings/name", 1),
                'email' => $configData['store_email'][$storeKey]);

            $mailSubject = "Appointment Modified";

            try {

                if ($enableCustomerEmail) {
                    $email = $model->getEmail();
                    $name = $model->getFirstname() . " " . $model->getLastname();
                    $templateId = $configData['email_template_appointment_modify'][$storeKey];

                    $mail = Mage::getModel('core/email_template');
                    foreach (explode(",",Mage::getStoreConfig('appointments/app_bcc/emails')) as $emails) {
                        $mail->addBcc($emails);
                    }
                    $mail->setTemplateSubject(
                        $mailSubject)->sendTransactional($templateId,
                        $sender, $email, $name, $vars);

                    $this->notify_Log("Email/Status_changed/admin", $app_string);

                }

            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        if($sendSms)
        {
            $smsText="Your Maria Tash appointment status has been changed ".$status_changed;

            if ($model->getPhone()) {
                $phno_forsms = preg_replace('/\s+/', '', $model->getPhone());
                $smsdata = Mage::helper('appointments')->sendsms($phno_forsms, $smsText, $storeId);
                $model->setSmsStatus($smsdata);
                $model->save();
                $this->notify_Log("SMS/Status_changed/admin", $app_string);
            }
        }
    }

    public function notifyCancel($model)
    {
        $sendSms = false;
        $sendEmail = false;
        $notification_pref = $model->getNotificationPref();

        if($notification_pref == 2){
            $sendSms = true;
            $sendEmail = true;
        }else{
            $sendEmail = true;
        }


        $storeId = $model->getStoreId();
        $configData = $this->getAppointmentStoreMapping();
        $storeKey = array_search ($storeId, $configData['stores']);

        $app_string="id->".$model->getId()." email->".$model->getEmail() ." mobile->".$model->getPhone()." name->".$model->getFirstname()." ".$model->getLastname()." ";


if($sendEmail) {
    $appointmentStart = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentStart()));
    $appointmentEnd = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentEnd()));
    $vars = array(
        'name' => $model->getFirstname() . " " . $model->getLastname(),
        'customer_name' => $model->getFirstname() . " " . $model->getLastname(),
        'customer_email' => $model->getEmail(),
        'customer_phone' => $model->getPhone(),
        'no_of_pier' => $model->getPiercingQty(),
        'piercing_loc' => $model->getPiercingLoc(),
        'special_notes' => $model->getSpecialNotes(),
        'apt_starttime' => $appointmentStart,
        'apt_endtime' => $appointmentEnd,
        'store_name' => $configData['store_name'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
        'store_address' => $configData['store_address'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
        'store_email_address' => $configData['store_email'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
        'store_phone' => $configData['store_phone'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
        'store_hours' => $configData['store_hours_operation'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
        'store_map' => $configData['store_map'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
        'booking_id'=>$model->getId()
    );

    //send Customer email
    $enableCustomerEmail = $configData['customer_email_enable'][$storeKey];
    /*$sender = array(
        'name' => Mage::getStoreConfig("trans_email/bookings/name"),
        'email' => Mage::getStoreConfig("trans_email/bookings/email")
    );*/

    $sender = array('name' => Mage::getStoreConfig("trans_email/bookings/name", 1),
        'email' => $configData['store_email'][$storeKey]);

    $mailSubject = "Appointment Canceled";

    try {

        if ($enableCustomerEmail) {
            $email = $model->getEmail();
            $name = $model->getFirstname() . " " . $model->getLastname();
            $templateId = $configData['email_template_appointment_cancel'][$storeKey];

            $mail = Mage::getModel('core/email_template');
            foreach (explode(",",Mage::getStoreConfig('appointments/app_bcc/emails')) as $emails) {
                $mail->addBcc($emails);
            }
            $mail->setTemplateSubject(
                $mailSubject)->sendTransactional($templateId,
                $sender, $email, $name, $vars);

            $this->notify_Log("Email/Cancel/admin", $app_string);

        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
if($sendSms)
{
    $smsText = $configData['cancel_sms_message'][$storeKey];
    $appointmentStart = date("F j, Y H:i",strtotime($model->getAppointmentStart()));
    $date = date("F j, Y ",strtotime($model->getAppointmentStart()));
    $time = date('h:i A', strtotime($model->getAppointmentStart()));

    $booking_link = Mage::getBaseUrl('web') . 'appointments/';
    $booking_link = Mage::helper('appointments')->getShortUrl($booking_link);
    $smsText = str_replace("(time)", $time, $smsText);
    $smsText = str_replace("(date)", $date, $smsText);
    $smsText = str_replace("(book_link)", $booking_link,$smsText);

    if ($model->getPhone()) {
        $phno_forsms = preg_replace('/\s+/', '', $model->getPhone());
        $smsdata = Mage::helper('appointments')->sendsms($phno_forsms, $smsText, $storeId);
        $model->setSmsStatus($smsdata);
        $model->save();
        $this->notify_Log("SMS/Cancel/admin", $app_string);
    }
}


    }


    private function notify_Log($action,$string){
        Mage::helper("appointments/logs")->appointment_notification($action,$string);
    }

    public function getStatus($key)
    {
        return Mage::getModel('appointments/appointments')->getStatus($key);
    }

    public function validateSlotBeforeBookAppointment($model)
    {
        $collection = Mage::getModel('appointments/appointments')->getCollection();
        $collection->addFieldToFilter('piercer_id', array('eq' => $model->getPiercerId()));
        $collection->addFieldToFilter('store_id', array('eq' => $model->getStoreId()));
        $collection->addFieldToFilter('app_status', array('eq' => 2));
        $collection->addFieldToFilter('id', array('neq' => $model->getId()));
        $collection->addFieldToFilter('appointment_start', array('lteq' => $model->getAppointmentStart()));
        $collection->addFieldToFilter('appointment_end', array('gteq' => $model->getAppointmentStart()));





        if($collection->getSize())
            return true;
        else
            return false;

    }

}