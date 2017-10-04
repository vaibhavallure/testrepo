<?php
class Allure_Appointments_Helper_Data extends Mage_Core_Helper_Abstract
{
	const SMS_BASEURL= 'appointments/general/soap_url'; //https://www.smsglobal.com/mobileworks/soapserver.php?wsdl
	const SMS_USERNAME =  'appointments/general/soap_username';//s51fn3hf;
	const SMS_PASSWORD = "appointments/general/soap_password";//pEtXTfsk;
	const SMS_FROM = "appointments/general/soap_from";//VMT;

	public function getServicesSelect()
	{
		 
		$model = Mage::getModel('appointments/services');
		$collection = $model->getCollection();
		$options = array();
		foreach ($collection as $row)
		{
			$options[$row->getId()] = $row->getTitle();
		}
		return $options;
	}
	
	public function getTimingSelect()
	{
	
		$startTime = Mage::getStoreConfig('appointments/general/working_start_time');
		//var_dump($startTime);
		$endTime = Mage::getStoreConfig('appointments/general/working_end_time');
		//var_dump($endTime);
		$timing =array();
		$timings=Mage::getModel('appointments/adminhtml_source_timing')->toOptionArray();
		/* echo "<pre>";
		print_r($timings); */
		foreach ($timings as $time){
			if ($startTime<=$time['value'] && $endTime>=$time['value']){
				$key = (string)$time['value'];
				$value = $time['label'];
				$timing[$key]= $value;
			}
		}
	/* 	echo "<pre>";
		
		print_r($timing);
		die; */
		//$timing[$i]= sprintf("%02d", $i).":00";
		return $timing;
	}
	public function  getDaysSelect(){
		$daysArray=array();
		$daysArray['Sunday']='Sunday';
		$daysArray['Monday']='Monday';
		$daysArray['Tuesday']='Tuesday';
		$daysArray['Wednesday']='Wednesday';
		$daysArray['Thursday']='Thursday';
		$daysArray['Friday']='Friday';
		$daysArray['Saturday']='Saturday';
		return $daysArray;
	}
	public function  getDaysSelectHtml($val=null){
		$output = "";
		$days = $this->getDaysSelect();
		foreach ($days as $key => $value)
		{
			$selected = ($val==$key) ? 'selected' : '';
			$output .= "<option value=".$key." $selected>".$value."</option>";
		}
		$output.="";
		return $output;
	}
	public function getTimeSelectHtml($val=null)
	{
		$output = "";
		$timing = $this->getTimingSelect();
		foreach ($timing as $key => $time)
		{
			$selected = ($val==$key) ? 'selected' : '';
			$output .= "<option value=".$key." $selected>".$time."</option>";
		}
		$output.="";
		return $output;
	}
	public function getPiercerName($id)
	{
		$model = Mage::getModel('appointments/piercers')->load($id);
		if($model->getId())
		{
			return $model->getFirstname()." ".$model->getLastname();
		}
		else{
			return "NOT ASSIGNED";
		}
	}
	
	
	//Send SMS through SOAP API
	public function sendsms($phone,$text,$storeId)
	{
		Mage::log("In Send appointment message",Zend_Log::DEBUG,'appointments',true);
		if(!$storeId)
			$storeId=0;
		$username = Mage::getStoreConfig(self::SMS_USERNAME);
		$password = Mage::getStoreConfig(self::SMS_PASSWORD);
		$url = Mage::getStoreConfig(self::SMS_BASEURL);
		$smsfrom = Mage::getStoreConfig(self::SMS_FROM);

		try {
			
			$api = new SoapClient($url,array( 'cache_wsdl' => WSDL_CACHE_NONE,'soap_version' => SOAP_1_1));
			$session = $api->apiValidateLogin($username,$password);
			preg_match("/<ticket>(?<ticket>.+)<\/ticket>/", $session, $response);
			$status = $api->apiSendSms($response['ticket'], $smsfrom, $phone, $text, 'text', '0', '0');
			preg_match("/<resp err=\"(?<error>.+)\">(<res>(<dest>(?<dest>.+)<\/dest>)?(<msgid>(?<msgid>.+)<\/msgid>)?.*<\/res>)?<\/resp>/", $status, $statusData);
			
			Mage::log("**************************************",Zend_Log::DEBUG,'appointments_sms_log',true);
			Mage::log("From Event",Zend_Log::DEBUG,'appointments_sms_log',true);
			Mage::log(" Mobile Number:".$phone,Zend_Log::DEBUG,'appointments_sms_log',true);
			Mage::log(" Text Message: ".$text,Zend_Log::DEBUG,'appointments_sms_log',true);
			Mage::log("**************************************",Zend_Log::DEBUG,'appointments_sms_log',true);
			
			
			return json_encode($statusData);
		} catch (Exception $e) {
			Mage::log(" Exception Occured :".$e->getMessage(),Zend_Log::DEBUG,'appointments_sms_log',true);
		} 
	}
	public function getTimezoneForeStore($storeId){
		$timezone="";
		$config=Mage::getStoreConfig('appointments/general/storemapping');
		$config=unserialize($config);
		foreach ($config as $conf)
		{
			if($conf['store']==$storeId)
			{
				$timezone=$conf['timezoneabbr'];
				break;
			}
		}
		return $timezone;
	}
	public function getTimezoneShortCodeForeStore($storeId){
		$timezone="EST";
		$config=Mage::getStoreConfig('appointments/general/storemapping');
		$config=unserialize($config);
		foreach ($config as $conf)
		{
			if($conf['store']==$storeId)
			{
				$timezone=$conf['timezoneabbr'];
				break;
			}
		}
		return $timezone;
	}
	public function getTimeByValue($value){
	
		$timings=Mage::getModel('appointments/adminhtml_source_timing')->toOptionArray();
		$label="";
		foreach ($timings as $time){
			if ($time['value']==$value){
				$label = $time['label'];
				break;
			}
		}
		return $label;
	}
	public function getTimeByStoreAndPeople($qty ,$storeId){
		$collection=Mage::getModel('appointments/timing')->getCollection()->addFieldToFilter('qty', $qty)->addFieldToFilter('store_id', $storeId);
		 if($collection->getFirstItem()->getTime()){
			return $collection->getFirstItem()->getTime();
		}else {
			return  15 * $qty;
		}
	}
	public function decimalToTime($val){
	       $hr=(int)$val/1;
	       $min = fmod($val, 1)*60;
	       return $hr.":".$min.':00';
	    
	}
	public function getShortUrl($url){
	    $apiKey = '';
	    $apiKey = Mage::getStoreConfig('appointments/general/google_api_key');
	    if (!$apiKey)
	        $apiKey = 'AIzaSyCZ3hFq9zcuXks44WNSdpwtr4Zz1kRi6BI';
	    $postData = array('longUrl' => $url);
	    $jsonData = json_encode($postData);
	    $curlObj = curl_init();
	    curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
	    curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curlObj, CURLOPT_HEADER, 0);
	    curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
	    curl_setopt($curlObj, CURLOPT_POST, 1);
	    curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
	    $response = curl_exec($curlObj);
	    $json = json_decode($response);
	    curl_close($curlObj);
	    return $json->id;
	}
	public function getAvailablePiercers($id){
	    
	    $appointments= Mage::getModel('appointments/appointments')->load($id);
	    $storeId=$appointments->getStoreId();
	    $appDate=date("m/d/Y",strtotime($appointments->getAppointmentStart()));
	    $appDay=date('l', strtotime($appDate));
	    $fromTime=$appointments->getAppointmentStart();
	    $toTime=$appointments->getAppointmentEnd();
	    $piercersArray=array();
	    $piercersArray[]=$appointments->getPiercerId();
	    try {
	        $collection = Mage::getModel('appointments/piercers')->getCollection()->addFieldToFilter('store_id', array('eq' => $storeId))
	        ->addFieldToFilter('is_active', array('eq' => '1'));
	        $collection->addFieldToFilter('working_days', array('like' => '%'.$appDate.'%'));
	        foreach ($collection as $piercer)
	        {
	            
	            $appCollection = Mage::getModel('appointments/appointments')->getCollection();
	            $appCollection->addFieldToFilter(array('appointment_start', 'appointment_end'), array(array('from'=>$fromTime, 'to'=>$toTime), array('from'=>$fromTime, 'to'=>$toTime)))
	            ->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
	            ->addFieldToFilter('piercer_id', array('eq' => $piercer->getId()));
	            
	            if(isset($appointmentId))
	                $appCollection->addFieldToFilter('id', array('neq' => $appointmentId));
	                $appCollection2=null;
	                if(!count($appCollection)){
	                    $appCollection2 = Mage::getModel('appointments/appointments')->getCollection();
	                    $appCollection2->addFieldToFilter('appointment_start', array('lteq'=>$fromTime))
	                    ->addFieldToFilter("appointment_end",array('gteq'=>$toTime))
	                    ->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
	                    ->addFieldToFilter('piercer_id', array('eq' => $piercer->getId()));
	                    if(isset($appointmentId))
	                        $appCollection2->addFieldToFilter('id', array('neq' => $appointmentId));
	                }
	                
	                //echo count($appCollection)." for ".$fromTime." - ".$toTime." piercer".$piercer->getId()."<br/>";
	                if(count($appCollection2))
	                    continue;
	                    if(count($appCollection))
	                        continue;
	                        
	                        /* End of check */
	                        
	                        $workingHours = $piercer->getWorkingHours();
	                        $workingHours = unserialize($workingHours);
	                        
	                        //Mage::log($workingHours,Zend_Log::DEBUG, 'appointments', true );
	                        
	                        foreach ($workingHours as $workSlot)
	                        {
	                            //$workStart = $workSlot['start'].":00";
	                            
	                            if($workSlot['day']!=$appDay){
	                                continue;
	                                
	                            }
	                            $workStart = $this->getTimeByValue($workSlot['start']);
	                            //$workEnd = $workSlot['end'].":00";
	                            $workEnd = $this->getTimeByValue($workSlot['end']);
	                            
	                            //Break
	                            $breakStart = $this->getTimeByValue($workSlot['break_start']);
	                            $breakEnd = $this->getTimeByValue($workSlot['break_end']);
	                            
	                            $fromDateTime=date("H:i",strtotime($fromTime));
	                            
	                            $toDateTime=date("H:i",strtotime($toTime));
	                            
	                            if((strtotime($workStart)<=strtotime($fromDateTime) && strtotime($toDateTime)<=strtotime($breakStart)) ||
	                                (strtotime($breakEnd)<=strtotime($fromDateTime) && strtotime($toDateTime)<=strtotime($workEnd)))
	                            {
	                                $piercersArray[]=$piercer->getId();
	                            }
	                            
	                        }
	                        
	        }
	    } catch (Exception $e) {
	    }
	    return $piercersArray;
	}
	public  function  storeOptionArray(){
	    $stores=Mage::getModel('core/store')->getCollection();
	    $stores->setOrder('store_id', 'ASC');
	    $storeArray=array();
	    $storeArray[0]= 'Any';
        foreach ($stores as $store): 
        $storeArray[$store->getId()]=$store->getName();
		endforeach;
		return $storeArray;
	    
	}
	
	public function piercerOptionArray(){
	    $piercers=Mage::getModel('appointments/piercers')->getCollection();
	    $piercers->addFieldToFilter('is_active',1);
	    $piercerArray=array();
	    $piercerArray[0]= 'Any';
	    foreach ($piercers as $piercer):
	    $piercerArray[$piercer->getId()]=$piercer->getFirstname().' '.$piercer->getLastname();
	    endforeach;
	    return $piercerArray;
	    
	}
}
	 