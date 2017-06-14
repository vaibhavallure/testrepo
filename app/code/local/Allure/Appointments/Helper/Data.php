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
			echo $e->getMessage();
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
}
	 