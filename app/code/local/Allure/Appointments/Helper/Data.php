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
	    $configData     = $this->getAppointmentStoreMapping();
	    $currentStoreId = Mage::app()->getStore()->getId();
	    $storeKey = array_search ($currentStoreId, $configData['stores']);

	    $startTime = $configData['start_work_time'][$storeKey];

	    $endTime = $configData['end_work_time'][$storeKey];

		$timing =array();

		$specialStoreId = Mage::helper('allure_virtualstore')->getStoreId('nordstrom_la');

		$specialStore = false;

		if ($specialStoreId == $currentStoreId) {
			$specialStore = true;
		}

		if ($specialStore) {
			$timings = Mage::getModel('appointments/adminhtml_source_timing')->toSpecialOptionArray();
		} else {
			$timings = Mage::getModel('appointments/adminhtml_source_timing')->toOptionArray();
		}

		foreach ($timings as $time){
			if ($startTime<=$time['value'] && $endTime>=$time['value']){
				$key = (string)$time['value'];
				$value = $time['label'];
				$timing[$key]= $value;
			}
		}

		return $timing;
	}


	public function getTimingSelectNew($storeId = 1)
	{
	    $configData     = $this->getAppointmentStoreMapping();
	    $storeKey       = array_search ($storeId, $configData['stores']);

		$startTime = $endTime = NULL;

		if (isset($configData['start_work_time']) && $configData['start_work_time'][$storeKey]) {
		    $startTime = $configData['start_work_time'][$storeKey];
		    $endTime = $configData['end_work_time'][$storeKey];
		}

	    $timing  = array();

		$specialStoreId = Mage::helper('allure_virtualstore')->getStoreId('nordstrom_la');

		$specialStore = false;

		if ($specialStoreId == $storeId) {
			$specialStore = true;
		}

		if ($specialStore) {
			$timings = Mage::getModel('appointments/adminhtml_source_timing')->toSpecialOptionArray();
		} else {
			$timings = Mage::getModel('appointments/adminhtml_source_timing')->toOptionArray();
		}

	    foreach ($timings as $time){

	        if ($startTime !== NULL && $endTime !== NULL && $startTime<=$time['value'] && $endTime>=$time['value']){
	            $key = (string)$time['value'];
	            $value = $time['label'];
	            $timing[$key] = $value;
	        } else if ($startTime == NULL && $endTime == NULL) {
				$key = (string)$time['value'];
				$value = $time['label'];
				$timing[$key] = $value;
			}
	    }
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

	/**
	 * used for virtual stores
	 * @return string
	 */
	public function getTimeSelectHtmlNew($storeId = 1, $val = null)
	{
	    $output = "";

	    $timing = $this->getTimingSelectNew($storeId);

	    foreach ($timing as $key => $time) {
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
			$status = $api->apiSendLongSms($response['ticket'], $smsfrom, $phone, $text, 'text', '0', '0');
			preg_match("/<resp err=\"(?<error>.+)\">(<res>(<dest>(?<dest>.+)<\/dest>)?(<msgid>(?<msgid>.+)<\/msgid>)?.*<\/res>)?<\/resp>/", $status, $statusData);

			Mage::log("**************************************",Zend_Log::DEBUG,'appointments_sms_log.log',true);
			Mage::log("From Event",Zend_Log::DEBUG,'appointments_sms_log',true);
			Mage::log(" Mobile Number:".$phone,Zend_Log::DEBUG,'appointments_sms_log.log',true);
			Mage::log(" Text Message: ".$text,Zend_Log::DEBUG,'appointments_sms_log.log',true);
			Mage::log("**************************************",Zend_Log::DEBUG,'appointments_sms_log.log',true);


			return json_encode($statusData);
		} catch (Exception $e) {
			Mage::log(" Exception Occured :".$e->getMessage(),Zend_Log::DEBUG,'appointments_sms_log.log',true);
		}
	}
	/*return abbreviation*/
	public function getTimezoneStore($storeId){
		$configData     = $this->getAppointmentStoreMapping();
		$storeKey = array_search ($storeId, $configData['stores']);
		$timezone = $configData['timezone_abbr'][$storeKey];

		return $timezone;
	}
	/*return actual timezone*/
    public function getStoreTimezone($storeId){
        $configData     = $this->getAppointmentStoreMapping();
        $storeKey = array_search ($storeId, $configData['stores']);
        $timezone = $configData['timezones'][$storeKey];

        return $timezone;
    }
	public function getTimezoneShortCodeForeStore($storeId){
		$timezone="EST";

		$configData     = $this->getAppointmentStoreMapping();
		$currentStoreId = Mage::app()->getStore()->getId();
		$storeKey = array_search ($currentStoreId, $configData['stores']);
		$timezone = $configData['timezone_abbr'][$storeKey];

		return $timezone;
	}
	public function getTimeByValue ($value) {

		$timings = Mage::getModel('appointments/adminhtml_source_timing')->toOptionArray();

		foreach ($timings as $time) {
			$timeValue = $time['value'];
			if ("$timeValue" >=  "$value"){
				return $time['label'];
			}
		}
		return '';
	}

	public function getSpecialTimeByValue($value)
	{
		$timings = Mage::getModel('appointments/adminhtml_source_timing')->toSpecialOptionArray();

		foreach ($timings as $time) {
			$timeValue = $time['value'];
			if ("$timeValue" >=  "$value"){
				return $time['label'];
			}
		}
		return '';
	}

	public function getTimeByKey ($key) {

		$timings = Mage::getModel('appointments/adminhtml_source_timing')->toOptionArray();

		foreach ($timings as $time) {
			$timeValue = $time['label'];
			if ("$timeValue" ==  "$key"){
				return $time['value'];
			}
		}
		return '';
	}

	public function getSpecialTimeByKey ($key) {

		$timings = Mage::getModel('appointments/adminhtml_source_timing')->toSpecialOptionArray();

		foreach ($timings as $time) {
			$timeValue = $time['label'];
			if ("$timeValue" ==  "$key"){
				return $time['value'];
			}
		}
		return '';
	}

	public function getBreakTimeByValue($value)
	{
		return $this->getSpecialTimeByValue($value);
	}

	public function getTimeByStoreAndPeople($qty , $storeId){
		$collection = Mage::getModel('appointments/timing')->getCollection()->addFieldToFilter('qty', $qty)->addFieldToFilter('store_id', $storeId);

		if($collection->getFirstItem()->getTime()) {
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
	    /*$apiKey = '';
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
	    return $json->id;*/


        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
	}
	public function getAvailablePiercers($id)
	{
	    $appointments= Mage::getModel('appointments/appointments')->load($id);
	    $storeId=$appointments->getStoreId();
	    $appDate=date("m/d/Y",strtotime($appointments->getAppointmentStart()));
	    $appDay=date('l', strtotime($appDate));
	    $fromTime=$appointments->getAppointmentStart();
	    $toTime=$appointments->getAppointmentEnd();
	    $piercersArray=array();
	    $piercersArray[]=$appointments->getPiercerId();

		$specialStoreId = Mage::helper('allure_virtualstore')->getStoreId('nordstrom_la');

		$specialStore = false;

		if ($specialStoreId == $storeId) {
			$specialStore = true;
		}

	    try {
	        $collection = Mage::getModel('appointments/piercers')->getCollection()->addFieldToFilter('store_id', array('eq' => $storeId))
	        ->addFieldToFilter('is_active', array('eq' => '1'));
	        $collection->addFieldToFilter('working_days', array('like' => '%'.$appDate.'%'));

			foreach ($collection as $piercer) {
	            $appCollection = Mage::getModel('appointments/appointments')->getCollection();
	            $appCollection->addFieldToFilter(array('appointment_start', 'appointment_end'), array(array('from'=>$fromTime, 'to'=>$toTime), array('from'=>$fromTime, 'to'=>$toTime)))
	            ->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
	            ->addFieldToFilter('piercer_id', array('eq' => $piercer->getId()));

	            if(isset($appointmentId))
	                $appCollection->addFieldToFilter('id', array('neq' => $appointmentId));

                $appCollection2=null;

                if (!count($appCollection)) {
                    $appCollection2 = Mage::getModel('appointments/appointments')->getCollection();
                    $appCollection2->addFieldToFilter('appointment_start', array('lteq'=>$fromTime))
                    ->addFieldToFilter("appointment_end",array('gteq'=>$toTime))
                    ->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
                    ->addFieldToFilter('piercer_id', array('eq' => $piercer->getId()));
                    if(isset($appointmentId))
                        $appCollection2->addFieldToFilter('id', array('neq' => $appointmentId));
                }

                if(count($appCollection2))
                    continue;

                if(count($appCollection))
                    continue;

                $workingHours = $piercer->getWorkingHours();
                $workingHours = unserialize($workingHours);

                foreach ($workingHours as $workSlot) {;
                    if($workSlot['day']!=$appDay){
                        continue;
                    }

					if ($specialStore) {
						$workStart = $this->getSpecialTimeByValue($workSlot['start']);
						$workEnd = $this->getSpecialTimeByValue($workSlot['end']);
						$breakStart = $this->getSpecialTimeByValue($workSlot['break_start']);
						$breakEnd = $this->getSpecialTimeByValue($workSlot['break_end']);
					} else {
						$workStart = $this->getTimeByValue($workSlot['start']);
						$workEnd = $this->getTimeByValue($workSlot['end']);
						$breakStart = $this->getTimeByValue($workSlot['break_start']);
						$breakEnd = $this->getTimeByValue($workSlot['break_end']);
					}

                    $fromDateTime = date("H:i",strtotime($fromTime));
                    $toDateTime = date("H:i",strtotime($toTime));

                    if((strtotime($workStart)<=strtotime($fromDateTime) && strtotime($toDateTime)<=strtotime($breakStart)) ||
                        (strtotime($breakEnd)<=strtotime($fromDateTime) && strtotime($toDateTime)<=strtotime($workEnd))) {
                        $piercersArray[]=$piercer->getId();
                    }
                }
	        }
	    } catch (Exception $e) {
	    }
	    return $piercersArray;
	}
	public function  storeOptionArray(){
	    $storeArray=array();
	    $storeArray[0]= 'Any';

	    if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
	        $stores=Mage::getModel('allure_virtualstore/store')->getCollection();
	        $stores->addFieldToFilter('store_id',array('neq'=>0));
	        $stores->setOrder('store_id', 'ASC');
	    } else {
	        $stores=Mage::getModel('core/store')->getCollection();
	        $stores->setOrder('store_id', 'ASC');
	    }

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

	/**
	 * return array of store mapping
	 */
	private function getAppointmentStoreMapping(){
	    return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
	}

	/**
	 * aws02
	 * get list of piercers as an option array
	 */
	public function getPiercersAsOptions(){
	    $piercerArray=array();
	    $collection=Mage::getModel('appointments/piercers')->getCollection();
	    foreach ($collection as $percer){
	        $piercerArray[$percer->getId()]=$percer->getFirstname()." ".$percer->getLastname();
	    }
	    return $piercerArray;
	}

	/**
	 * aws02
	 * check piercer is available at particular date or not
	 */
	public function isPiercerAvailable($piercerId,$availabilityDate){
	    $collection = Mage::getModel('appointments/piercers')->getCollection();
	    $collection->addFieldToFilter('id',$piercerId)
	    ->addFieldToFilter('working_days',array('like'=>'%'.$availabilityDate.'%'));
	    if($collection->getSize() > 0){
	        return true;
	    }
	    return false;
	}

	public function getStoreId() {

		$storeId = Mage::app()->getRequest()->getParam('store');

		$storeCode = Mage::app()->getRequest()->getParam('code');

		if (!$storeId && $storeCode) {
			$storeId = Mage::helper('allure_virtualstore')->getStoreId($storeCode);
		}

        if($storeId) {
            $group_id=Mage::helper('allure_virtualstore')->getGroupId($storeId);

		    if (in_array($group_id,explode(",",Mage::getStoreConfig('appointments/grouping_on/groups'))))
                $storeId = Mage::helper('allure_virtualstore')->getStoresIdsByGroupId($group_id);
        }

		return $storeId;
	}

    public function getReqStoreId() {

        $storeId = Mage::app()->getRequest()->getParam('store');

        $storeCode = Mage::app()->getRequest()->getParam('code');

        if (!$storeId && $storeCode) {
            $storeId = Mage::helper('allure_virtualstore')->getStoreId($storeCode);
        }

        return $storeId;
    }

	public function getStoreCode() {
		$storeId = $this->getStoreId();

		return Mage::helper('allure_virtualstore')->getStoreCode($storeId);
	}

	public function storeAppearName($store_id)
    {
        $configData = Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
        $storeKey = array_search ($store_id, $configData['stores']);
        return $configData['appears'][$storeKey];
    }

    public function getStoreEmail($store_id=null)
    {
        if($store_id==null)
            return null;

        $configData = Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
        $storeKey = array_search ($store_id, $configData['stores']);
        return $configData['store_email'][$storeKey];
    }

    public function getStorePhone($store_id=null)
    {
        if($store_id==null)
            return null;

        $configData = Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
        $storeKey = array_search ($store_id, $configData['stores']);
        return $configData['store_phone'][$storeKey];
    }

    public function getSupportPhone($store_id=null)
    {
        if($store_id==null)
            return null;

        $configData = Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
        $storeKey = array_search ($store_id, $configData['stores']);
        return $configData['support_phone'][$storeKey];
    }


    public function getSupportMessage($store_id=null)
    {
        if($store_id==null)
            return null;

        $configData = Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
        $storeKey = array_search ($store_id, $configData['stores']);
        return $configData['limit_message'][$storeKey];
    }

    public function checkPiercerAvailable($data,$piercer)
    {
        $result=array();
        $result['success']=false;

        $collection = Mage::getModel('appointments/piercers')->getCollection()->addFieldToFilter('store_id', array('eq' =>$data['store_id']));

		$collection->addFieldToFilter('is_active', array('eq' => '1'));

        if ($piercer=="another") {
            $collection->addFieldToFilter('id', array('neq' => $data['piercer_id']));
        	$this->addLog("checking for another piercer","save");
        } elseif ($piercer=="same") {
            $this->addLog("checking for same piercer","save");
            $collection->addFieldToFilter('id', array('eq' => $data['piercer_id']));
        }

        $collection->addFieldToFilter('working_days', array('like' => '%'.$data['app_date'].'%'));

        $day = date('l', strtotime($data['app_date']));

        if ($collection->getSize()) {

			$specialStoreId = Mage::helper('allure_virtualstore')->getStoreId('nordstrom_la');

			$specialStore = false;

			if ($specialStoreId == $data['store_id']) {
				$specialStore = true;
			}

            foreach ($collection as $p) {
                $workStart="";
                $workend="";
                $breakend="";
                $breakstart="";

                $workingHours = $p->getWorkingHours();
                $workingHours = unserialize($workingHours);

                foreach ($workingHours as $workSlot) {

                    if ($workSlot['day'] != $day) {
                        continue;
                    }
                    $this->addLog("piercer id ==>".$p->getId(),"save");

					if ($specialStore) {
						$workStart = $this->getSpecialTimeByValue($workSlot['start']);
						$workEnd = $this->getSpecialTimeByValue($workSlot['end']);
						$breakStart = $this->getSpecialTimeByValue($workSlot['break_start']);
						$breakEnd = $this->getSpecialTimeByValue($workSlot['break_end']);
					} else {
						$workStart = $this->getTimeByValue($workSlot['start']);
						$workEnd = $this->getTimeByValue($workSlot['end']);
						$breakStart = $this->getTimeByValue($workSlot['break_start']);
						$breakEnd = $this->getTimeByValue($workSlot['break_end']);
					}
                }

                if ($this->checkPiercerTime(date("H:i",strtotime($data['appointment_start'])),date("H:i",strtotime($data['appointment_end'])), $breakStart, $breakEnd, $workStart, $workEnd)) {

                    $this->addLog("checking piercer slot already booked","save");

                    $collection = Mage::getModel('appointments/appointments')->getCollection();
                    $collection->addFieldToFilter('piercer_id', array('eq' => $p->getId()));
                    $collection->addFieldToFilter('store_id', array('eq' => $data['store_id']));
                    $collection->addFieldToFilter('app_status', array('eq' => 2));

                    if ($piercer=="same") {
                        $collection->addFieldToFilter('id', array('neq' => $data['id']));
                    }

//                    $collection->addFieldToFilter('appointment_start', array('lteq' => $data['appointment_start']));
//                    $collection->addFieldToFilter('appointment_end', array('gteq' => $data['appointment_start']));

                    $collection->getSelect()->where("(((`appointment_start` <= '{$data['appointment_start']}') AND (`appointment_end` >= '{$data['appointment_start']}')) OR ((`appointment_start` <= '{$data['appointment_end']}') AND (`appointment_end` >= '{$data['appointment_end']}')))");


                    if (!$collection->getSize()) {
                        $this->addLog("checking piercer slot open => id =".$p->getId(),"save");
                        $result['success'] = true;
                        $result['p_id'] = $p->getId();
                        break;
                    }
                }
            }
        }

        return $result;
    }
    public function checkIfAnotherPiercerAvailable($data)
    {
        return $this->checkPiercerAvailable($data,"another");
    }

    public function checkPiercerTime($app_start,$app_end,$bstart,$bend,$open,$close)
    {
        $this->addLog("app_start=>".$app_start." app_end=>".$app_end." break_start=>".$bstart." break_end=>".$bend." open=>".$open." close=>".$close,"save");

        $app_start = DateTime::createFromFormat('H:i', $app_start);
        $app_end = DateTime::createFromFormat('H:i', $app_end);

        $bstart = DateTime::createFromFormat('H:i', $bstart);
        $bend = DateTime::createFromFormat('H:i', $bend);

        $open = DateTime::createFromFormat('H:i', $open);
        $close = DateTime::createFromFormat('H:i', $close);

        if ((($open<= $app_start && $close > $app_start) && ($app_end > $open && $app_end < $close))) {
            /*open-------------------*/
            $this->addLog("open","save");

            if ((($bstart <= $app_start && $bend > $app_start) || ($app_end > $bstart && $app_end < $bend)) || (($bstart >= $app_start && $bstart < $app_end) || ($app_end < $bend && $app_end > $bend))) {
                /*between break---------------------*/
                $this->addLog("between break","save");
                return false;
            } else {
                /*not between break--------------------*/
                $this->addLog("not between break","save");

                return true;
            }
        } else {
            /*close*/
            $this->addLog("close","save");

            return false;
        }
    }

    public function validatePostData($post,$from="user")
    {
        $store_id= $post['store_id'];
        $piercer_id= $post['piercer_id'];
        $qty=$post['piercing_qty'];
        $date= $post['app_date'];
        $ap_start= $post['appointment_start'];
        $ap_end= $post['appointment_end'];
        $email=$post['email'];

        /*check store id and piercer id */

        if (empty($store_id) || empty($piercer_id)) {
            $this->addLog("error store id or piercer id empty ".$email,"save");
            return false;
        }

        $piercer= Mage::getModel('appointments/piercers')->load($piercer_id);

        if ($piercer->getStoreId()!=$store_id) {
            $this->addLog("store id and piercer id does not match ".$email,"save");
            return false;
        }
        /* ----------------------------       */

        if ($from=="admin")
            $no_of_people=Mage::getStoreConfig('appointments/no_of_people/admin_side');
        else
            $no_of_people=Mage::getStoreConfig('appointments/no_of_people/user_side');

        $popupStoreId=Mage::getStoreConfig('appointments/popup_setting/store');

        if($popupStoreId==$store_id)
        {
            $no_of_people=4;
        }
        /* check no of people  */
        if (empty($qty) || $qty<1 || (!empty($no_of_people) && $qty > $no_of_people)) {
            $this->addLog("invalid no of people qty=".$qty." ".$email,"save");
            return false;
        }
        /*-----------------------*/

        /*check date and time*/

        if(empty($ap_start) || empty($ap_end) || empty($date)) {
            $this->addLog("empty date or time ".$email,"save");
            return false;
        }

        return true;
    }


    public function isPopupStore($store_id)
    {
        return ($this->getPopupStoreId()==$store_id);
    }

    public function validateSlotBeforeBookAppointment($data)
    {
        /*If Already Booked then Check for Same Slot Selected or Not*/
        if($data['id'])
        {
            Mage::log('IN MODIFY',Zend_Log::DEBUG,'myLog.log',true);
            $model = Mage::getModel('appointments/appointments')->load($data['id']);
            if(($model->getPiercerId()==$data['piercer_id']) && ($model->getAppointmentStart()==$data['appointment_start']) && ($model->getAppointmentEnd()==$data['appointment_end']))
            {
                $this->addLog("same time and date","modify");
                Mage::log('Same date',Zend_Log::DEBUG,'myLog.log',true);//It's Temp Log do not remove it
                return false;
            }
            else
            {
                $this->addLog("different date or time","modify");
                Mage::log('Different date',Zend_Log::DEBUG,'myLog.log',true);//It's Temp Log do not remove it

            }
        }
        /*If Not Same Slot or New Slot*/
        $collection = Mage::getModel('appointments/appointments')->getCollection();
        $collection->addFieldToFilter('piercer_id', array('eq' => $data['piercer_id']));
        $collection->addFieldToFilter('store_id', array('eq' => $data['store_id']));
        $collection->addFieldToFilter('app_status', array('eq' => 2));

        if($data['id'])
        $collection->addFieldToFilter('id', array('neq' => $data['id']));


//        $collection->addFieldToFilter('appointment_start', array('lteq' => $data['appointment_start']));
//        $collection->addFieldToFilter('appointment_end', array('gteq' => $data['appointment_start']));

        $collection->getSelect()->where("(((`appointment_start` <= '{$data['appointment_start']}') AND (`appointment_end` >= '{$data['appointment_start']}')) OR ((`appointment_start` <= '{$data['appointment_end']}') AND (`appointment_end` >= '{$data['appointment_end']}')))");

        if ($collection->getSize()) {
            return true;
        } else {
            $result = $this->checkPiercerAvailable($data, "same");
            if($result['success']==false)
                return true;
            else
                return false;
        }


    }

    /**
     * add customer log
     */
    private function addLog($data,$action){
        Mage::helper("appointments/logs")->addCustomerLog($data,$action);
    }


    /*new functions for popup stores*/

    public function getCurrentDatetime($store_id)
    {
        $user_tz = new DateTimeZone($this->getStoreTimezone($store_id));
        $user = new DateTime('now', $user_tz);
        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $ar=(array)$usersTime;
        $date = $ar['date'];
        return $date = strtotime($date);
    }

    public function getPopupStoreId() {
        $popupStoreId=Mage::getStoreConfig('appointments/popup_setting/store');
        return $popupStoreId;
    }
}
