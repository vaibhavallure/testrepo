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
    
    			/*Email Code*/
    			$toSend = Mage::getStoreConfig("appointments/piercer/send_piercer_email",$storeId);
    			if($toSend)
    			{
    				$templateId = Mage::getStoreConfig("appointments/piercer/piercer_welcome_template",$storeId);
    				$mailSubject="sample subject";
    				$sender         = array('name'=>Mage::getStoreConfig("trans_email/bookings/name"), 'email'=> Mage::getStoreConfig("trans_email/bookings/email"));
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
    						'store_name'	=> Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
    						'store_address'	=> Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
    						'store_email_address'	=> Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
    						'store_phone'	=> Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
    						'store_hours'	=> Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
    						'store_map'	=> Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
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
    			$model = Mage::getModel('appointments/appointments')->load($this->getRequest()->getParam("id"));
    			$model->setAppStatus(Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
    			$model->save();
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
        $this->_initAction();
        $this->_title($this->__('Appointments'))
        ->_title($this->__('Print'));
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
        if($post_data['piercer_id']!=0){
            $appointments->addFieldToFilter('piercer_id',$post_data['piercer_id']);
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
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
            $this->_redirect('*/*/print');
        }
        
    }
    
}