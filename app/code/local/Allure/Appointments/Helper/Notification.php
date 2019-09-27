<?php
/**
 type mapping

'new'
'modify'
'cancel'
'reminder'
'reminder_week'
'reminder_day'
'release'
'release_reminder_week'
'release_reminder_day'

 USE only Two Functions
 1.sendEmailNotification(current_appointment,type,old_appointment,old_appointment_customers);
 2.sendSmsNotification(current_appointment,type)
 **/
class Allure_Appointments_Helper_Notification extends Mage_Core_Helper_Abstract{
    const SMS_BASEURL= 'appointments/general/soap_url'; //https://www.smsglobal.com/mobileworks/soapserver.php?wsdl
    const SMS_USERNAME =  'appointments/general/soap_username';//s51fn3hf;
    const SMS_PASSWORD = "appointments/general/soap_password";//pEtXTfsk;
    const SMS_FROM = "appointments/general/soap_from";//VMT
    const BCC_EMAILS = "appointments/app_bcc/emails";//VMT
    const NOTIFICATION_LOG = 'appointmentNotfication.log';

    var $month=array("fr"=>array(
    "January"=>"janvier", "February"=>"février", "March"=>"mars", "April"=>"avril", "May"=>"mai",
    "June"=>"juin",
    "July"=>"juillet",
    "August"=>"aout",
    "September"=>"septembre",
    "October"=>"octobre",
    "November"=>"novembre",
    "December"=>"décembre"
    ));

    public function sendEmailNotification( $appointment = null,$type = null, $oldAppointment = null, $oldAppointmentCustomers = null)
    {
        $bcc_emails = $this->getBccMails();

        $release_url = 'https://www.mariatash.com/digital-release';
        $cnt_old_cust  = 0;
        $old_piercing = array();
        if($appointment == null){
            return;
        }

        $configData = $this->getAppointmentStoreMapping();
        $appointmentArray =$this->getAppointmentArray($appointment);
        $store = $appointment->getStoreId();
        $store_id =  array_search($store, $configData['stores']);
        $storeInfoArray = $this->getStoreInfo($configData,$store_id);
        $isSpecialPopup = Mage::helper('appointments')->getCustomersInfo($store);
        $oldAppointmentArray = array();
        $oldCustomerListHtml ='';


        /*If Old Appointment Data Exist Then  Take it.*/
        if($oldAppointment != null) {

            $oldAppointmentArray = $this->getAppointmentArray($oldAppointment);
            $oldAppointmentCustomerArray = $oldAppointmentCustomers;

            $appointmentArray['pre_apt_starttime'] = $this->getDateTime($oldAppointmentArray['appointment_start'],$appointmentArray['language_pref']);
            $appointmentArray['pre_apt_endtime'] = $this->getDateTime($oldAppointmentArray['appointment_end'],$appointmentArray['language_pref']);

            $appointmentArray['pre_apt_date'] = $this->getDate($appointmentArray['appointment_start'],$appointmentArray['language_pref']);
            $appointmentArray['pre_apt_time'] = $this->getTime($appointmentArray['appointment_start']);
            $appointmentArray['oldappointmentdate'] = $this->getDate($oldAppointmentArray['appointment_start'],'en');

            $cnt = 0;

            /*To create old customers list of appointment*/
            foreach ($oldAppointmentCustomerArray as $customer){
                $old_piercing[$cnt_old_cust] = $customer['piercing'];
                $listLabelArray = $this->getLanguageMapping();
                $cnt++;
                $create_rows = $cnt%2;
                if($create_rows != 0){
                    $oldCustomerListHtml.='<tr>';
                }
                $language = $appointmentArray['language_pref'];
                if($language != 'en') {
                    $listLabelArray = $this->getLanguageMapping($language);
                }
                    $customer_name = ucfirst($customer['firstname']) . ' ' . ucfirst($customer['lastname']);
                    $oldCustomerListHtml .= '<td style="padding: 20px 0px 10px 0px;"><h3>'.$listLabelArray['customer'].' #' . $cnt . '</h3>';
                    $oldCustomerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['name'].': </b>' . $customer_name . '</p>';
                    $oldCustomerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['email'].': </b>' . $customer['email'] . '</p>';
                    $oldCustomerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['phone'].': </b>' . $customer['phone'] . '</p>';
                    $oldCustomerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['no_of_piercing'].': </b>' . $customer['piercing'] . '</p>';
                    $oldCustomerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['no_of_checkup'].': </b>' . $customer['checkup'] . '</p>';
                    $oldCustomerListHtml .= '</td>';

                if($create_rows == 0){
                    $oldCustomerListHtml.='</tr>';
                }

                $cnt_old_cust++;
            }


        }


        $sender =$this->getSenderName($store_id);

        $appointmentCustomers = $this->getAppointmentCustomerArray($appointment);

        $appointmentArray['apt_starttime'] = $this->getDateTime($appointmentArray['appointment_start'],$appointmentArray['language_pref']);
        $appointmentArray['apt_endtime'] = $this->getDateTime($appointmentArray['appointment_end'],$appointmentArray['language_pref']);

        $appointmentArray['apt_date'] = $this->getDate($appointmentArray['appointment_start'],$appointmentArray['language_pref']);
        $appointmentArray['apt_time'] = $this->getTime($appointmentArray['appointment_start']);
        $appointmentArray['appointmentdate'] = $this->getDate($appointmentArray['appointment_start'],'en');
        $customerListHtml ='';
        $cnt = 0;


/*To create customers list in appointment to use in email*/

        foreach ($appointmentCustomers as $customer){
            $listLabelArray = $this->getLanguageMapping('en');
            $cnt++;
            $create_rows = $cnt%2;
            if($create_rows != 0){
                $customerListHtml.='<tr>';
            }
            $language = $customer['language_pref'];
            if($language != 'en') {
                $listLabelArray = $this->getLanguageMapping($language);
            }
                $customer_name = ucfirst($customer['firstname']) . ' ' . ucfirst($customer['lastname']);
                $customerListHtml .= '<td style="padding: 20px 0px 10px 0px;"><h3>'.$listLabelArray['customer'].'#' . $cnt . '</h3>';
                $customerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['name'].': </b>' . $customer_name . '</p>';
                $customerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['email'].': </b>' . $customer['email'] . '</p>';
                $customerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['phone'].': </b>' . $customer['phone'] . '</p>';
                $customerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['no_of_piercing'].': </b>' . $customer['piercing'] . '</p>';
                $customerListHtml .= '<p style="margin: 10px 0px;padding: 0px"><b>'.$listLabelArray['no_of_checkup'].': </b>' . $customer['checkup'] . '</p>';
                $customerListHtml .= '</td>';


            if($create_rows == 0){
            $customerListHtml.='</tr>';
            }

        }


/*Send email to  every customer separately */
        $cnt_new_cust = 0;
        foreach ($appointmentCustomers as $appointmentCustomer) {
            $piercing = $appointmentCustomer['piercing'];
            $checkup = $appointmentCustomer['checkup'];
            $release_send = true;
            if(isset($old_piercing[$cnt_new_cust])){
                if($old_piercing[$cnt_new_cust] != 0);
                {
                    $release_send = false;
                }
            }

            if($type != 'cancel'){
                $appointmentArray['apt_modify_link'] = $this->getModifyLink($appointmentCustomer,$isSpecialPopup);
            }


            $emailVariables= array();
            $language = $appointmentCustomer['language_pref'];
            $email = $appointmentCustomer['email'];
            $customer_name = ucfirst($appointmentCustomer['firstname']);/*. ' ' . ucfirst($appointmentCustomer['lastname'])*/
            $appointmentCustomer['customer_name'] = $customer_name;
            $secondLanguage = '';
            if ($language != 'en') {
                $secondLanguage = '_second';
            }

            $emailType = $this->getEmailTypeMapping();

            /*CHECK IF LANGUAGE IS SECOND BUT MAILS DISABLED OR NOT*/
            if($configData['customer_email_enable_second'][$store_id] == 0){
                $secondLanguage ='';
            }
            $emailTemplatePath = $emailType[$type] . $secondLanguage;
            $templateId = $configData[$emailTemplatePath][$store_id];
            $this->writeLog("**************************************");
            $this->writeLog("---SENDING EMAIL---");
            $this->writeLog("type : " . $type);
            $this->writeLog("Appointment Id :" . $appointmentCustomer['appointment_id']);
            $this->writeLog("Customer Id (Apt.):" . $appointmentCustomer['id']);
            $this->writeLog("Language :" . $language);
            $this->writeLog("Template Path :" . $emailTemplatePath);

            /*Digital Release Form Link*/
            $release_url = $configData['digital_form_url'][$store_id];
            $release_id = $appointmentCustomer['appointment_id'].'-'.$appointmentCustomer['id'];
            $appointmentCustomer['release_url'] = $release_url.'/appointment?id='.$this->encrypt($release_id);


            /*EMAIL VARIABLES*/


            $appointmentObject = new Varien_Object();
            $appointmentObject->setData($appointmentArray);

            $storeInfoObject = new Varien_Object();
            $storeInfoObject->setData($storeInfoArray);

            $customerObject = new Varien_Object();
            $customerObject->setData($appointmentCustomer);

            $oldAppointmentObject = new Varien_Object();
            $oldAppointmentObject->setData($oldAppointmentArray);



            $emailVariables['appointment']=$appointmentObject;
            $emailVariables['storeinfo']=$storeInfoObject;
            $emailVariables['customer']=$customerObject;
            $emailVariables['oldappointment']=$oldAppointmentObject;
            $emailVariables['customerlist']=$customerListHtml;
            $emailVariables['oldcustomerlist']=$oldCustomerListHtml;

           if(($type != 'release') && ($type != 'release_reminder_week') && ($type != 'release_reminder_day') ) {
               $this->sendEmail($templateId, $sender, $email, $customer_name, $emailVariables,$bcc_emails);
           }else{
               if(($piercing > 0) && ($release_send) && ($appointmentArray['info_update'] != 1)){
                   $this->sendEmail($templateId, $sender, $email, $customer_name, $emailVariables,$bcc_emails);
               }else{
                   $this->writeLog('Email not send for release : piercing :'.$piercing.'\n checkup : '.$checkup.'\ninfo_update :'.$appointmentArray['info_update']);
               }
           }
            $this->writeLog("**************************************");
        }
    }


    public function sendSmsNotification($appointment = null,$type = 'new'){

        if($appointment == null){
            return;
        }
        $store = $appointment->getStoreId();
        $configData = $this->getAppointmentStoreMapping();
        $store = $appointment->getStoreId();
        $store_id =  array_search($store, $configData['stores']);
        $isSpecialPopup = Mage::helper('appointments')->getCustomersInfo($store);

        $smsType = $this->getSmsTypeMapping();

        $appointmentCustomers = $this->getAppointmentCustomerArray($appointment);


        foreach ($appointmentCustomers as $appointmentCustomer) {

            if($appointmentCustomer['sms_notification']) {
                $language = $appointmentCustomer['language_pref'];
                $phone = $appointmentCustomer['phone'];

                $secondLanguage = '';
                if ($language != 'en') {
                    $secondLanguage = '_second';
                }


                $smsTemplatePath = $smsType[$type] . $secondLanguage;

                $smsText = $configData[$smsTemplatePath][$store_id];

                $this->writeLog("**************************************");
                $this->writeLog("---SENDING SMS---");
                $this->writeLog("type : " . $type);
                $this->writeLog("Appointment Id :" . $appointmentCustomer['appointment_id']);
                $this->writeLog("Customer Id (Apt.):" . $appointmentCustomer['id']);
                $this->writeLog("Language :" . $language);
                $this->writeLog("Phone :" . $phone);

                $this->getModifyLink($appointmentCustomer,$isSpecialPopup);

                try {
                    $url = $this->getShortUrl($this->getModifyLink($appointmentCustomer,$isSpecialPopup));
                    if (strlen($url) < 1) {
                        $url = $this->getModifyLink($appointmentCustomer,$isSpecialPopup);
                    }

                    $date = $this->getDate($appointment->getAppointmentStart(),$appointment->getLanguagePref());
                    $date_en = $this->getDate($appointment->getAppointmentStart(),'en');
                    $time = $this->getTime($appointment->getAppointmentStart());


                    $smsText = str_replace("(apt_id)", $appointmentCustomer['appointment_id'], $smsText);
                    $smsText = str_replace("(time)", $time, $smsText);
                    if($isSpecialPopup){
                        $smsText = str_replace("(date)", $date_en, $smsText);
                    }else{
                        $smsText = str_replace("(date)", $date, $smsText);
                    }
                    $smsText = str_replace("(modify_link)", $url, $smsText);

                    if (strlen(trim($phone)) > 0) {
                        $smsdata = $this->sendsms($phone, $smsText, $store_id);

                        $appointment->setSmsStatus($smsdata);
                        $appointment->save();
                    } else {
                        $this->writeLog('Phone number not exist');
                    }
                } catch (Exception $ex) {
                    $this->writeLog($ex->getMessage());
                }
            }
        }
        return true;
    }

    public function getEmailTypeMapping(){
        return array(
            'new'       =>'email_template_appointment',
            'modify'    =>'email_template_appointment_modify',
            'cancel'    =>'email_template_appointment_cancel',
            'reminder'    =>'email_template_appointment_remind',
            'reminder_week'    =>'email_template_appointment_remind_week',
            'reminder_day'    =>'email_template_appointment_remind_day',
            'release'=>'email_template_release',
            'release_reminder_week'=>'email_template_release_remind_week',
            'release_reminder_day'=>'email_template_release_remind_day'
        );
    }

    public function getSmsTypeMapping(){
       return array(
            'new'       =>'book_sms_message',
            'modify'    =>'modified_sms_message',
            'cancel'    =>'cancel_sms_message',
            'reminder'    =>'reminder_sms_message',
            'reminder_week'    =>'week_reminder_sms_message',
            'reminder_day'    =>'day_reminder_sms_message',
            'release'=>'release_sms_message',
            'release_reminder_week'=>'week_release_sms_message',
            'release_reminder_day'=>'day_release_sms_message'
        );

    }

    public function sendEmail($templateId,$sender,$email,$customer_name,$variableArray,$bcc_emails = null){
        $this->writeLog("In Send Email");

        if($bcc_emails!=null){
            $bcc_emails = explode(',',$bcc_emails);
        }
        try {
            $mail = Mage::getModel('core/email_template')->addBcc($bcc_emails)->sendTransactional($templateId,
                $sender, $email, $customer_name, $variableArray);
            $this->writeLog("---EMAIL SENT---");
        } catch (Exception $ex) {
            $this->writeLog($ex->getMessage());;
        }
    }
    //Send SMS through SOAP API
    public function sendsms($phone,$text,$storeId)
    {
        $this->writeLog('In Send SMS');
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

            $this->writeLog(" Mobile Number:".$phone);
            $this->writeLog(" Text Message: ".$text);
            $this->writeLog("**************************************");


            return json_encode($statusData);
        } catch (Exception $e) {
            $this->writeLog(" Exception Occur :".$e->getMessage());
        }
    }


    public function getModifyLink($apt_customer,$isSpecialPopup = false){

        if(!$isSpecialPopup):
        $apt_url = 'appointments/popup/modify';
        $id =  urlencode(Mage::getModel('core/encryption')->encrypt($apt_customer['appointment_id'].'-'.$apt_customer['id']));
        $apt_modify_link = Mage::getUrl($apt_url, array(
            '_secure'   => true,
            '_query'=>'id='.$id
        ));
        else:
            $appointment = Mage::getModel('appointments/appointments')->load($apt_customer['appointment_id']);
            $apt_modify_link = Mage::getBaseUrl().'appointments/index/modify/id/'.$appointment->getId().'/email/'.$appointment->getEmail().'/store/'.$appointment->getStoreId();
            endif;
        return $apt_modify_link;

    }
    public function getAppointmentArray($appointment){
        return $appointment->getData();
    }
    public function getAppointmentCustomerArray($appointment){

        $appointment_customer = Mage::getModel('appointments/customers')->getCollection()
            ->addFieldToFilter('appointment_id' ,array('eq'=>$appointment->getId()));

        return $appointment_customer->getData();
    }
    public function getStoreInfo($configData,$store_id){

        return array(
            'store_name' => $configData['store_name'][$store_id], // Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
            'store_address' => $configData['store_address'][$store_id], // Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
            'store_email_address' => $configData['store_email'][$store_id], // Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
            'store_phone' => $configData['store_phone'][$store_id], // Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
            'store_hours' => $configData['store_hours_operation'][$store_id], // Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
            'store_map' => $configData['store_map'][$store_id]
        );
    }
    public function getDateTime($date,$lang='en'){
        $date=date("j F Y H:i", strtotime($date));
        if($lang!='en') {
        $monthName=date("F", strtotime($date));
            $date=str_replace($monthName, $this->month[$lang][$monthName],$date);
       }
        return $date;
    }
    public function getTime($date){
       return date('H:i', strtotime($date));
    }
    public function getDate($date,$lang='en'){
        $date= date(" j F Y ", strtotime($date));
        if($lang!='en') {
            $monthName=date("F", strtotime($date));
            $date=str_replace($monthName, $this->month[$lang][$monthName],$date);
        }
        return $date;
    }
    public function getSenderName($store_id){

        $configData = $this->getAppointmentStoreMapping();

        $sender = array(
            'name' => Mage::getStoreConfig("trans_email/bookings/name"),
            'email' => $configData['store_email'][$store_id]
        );

        return $sender;
    }
    public function getAppointmentStoreMapping(){
        return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    }
    public function getLanguageMapping($language = 'en'){
        if($language == 'fr'){
            return array(
                'customer' => 'Client',
                'name'     => 'Nom',
                'email'    => 'Email',
                'phone'    => 'Tel',
                'no_of_piercing' => 'No de piercing',
                'no_of_checkup' => 'No de bilans'


            );
        }
        return array(
            'customer' => 'Customer',
            'name'     => 'Name',
            'email'    => 'Email',
            'phone'    => 'Phone',
            'no_of_piercing' => 'No of Piercing',
            'no_of_checkup' => 'No of Checkup'


        );
    }
    public function writeLog($message){
        Mage::log($message,Zend_Log::DEBUG,self::NOTIFICATION_LOG,true);
    }
    public function getShortUrl($url){
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function encrypt($string){
        return base64_encode(base64_encode($string));
    }
    public function getBccMails(){
        return Mage::getStoreConfig(self::BCC_EMAILS);
    }

}