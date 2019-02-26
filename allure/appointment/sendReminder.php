<?php
require_once '../../app/Mage.php';
umask(0);
Mage::app();
$config=Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();

if(isset($_GET['storeid']))
{

    $store=$_GET['storeid'];
    $storeKey = array_search ($store, $config['stores']);
    $timezone = $config['timezones'][$storeKey];
    date_default_timezone_set($timezone);
    $storeDate=date('Y-m-d H:i:s');

    $nextTime =$storeDate;
    $next2Time= date("Y-m-d 23:59:59",strtotime($storeDate));


    $allAppointments = Mage::getModel('appointments/appointments')->getCollection();
    $allAppointments->addFieldToFilter('appointment_start', array('gteq' => $nextTime));
    $allAppointments->addFieldToFilter('appointment_start', array('lteq' => $next2Time));
    $allAppointments->addFieldToFilter('app_status',Allure_Appointments_Model_Appointments::STATUS_ASSIGNED);
    $allAppointments->addFieldToFilter('store_id', array('eq' => $store));

}
else if(isset($_GET['id']))
{
    $allAppointments[] = Mage::getModel('appointments/appointments')->load($_GET['id']);

}
else
{
    die();
}


if(count($allAppointments)>0) {


    $sendEmail = false;
    $sendSms = false;

    $configData = $config;

    foreach ($allAppointments as $appointment) {
         $storeId = $appointment->getStoreId();
        $storeKey = array_search($storeId, $configData['stores']);
        //$toSend = Mage::getStoreConfig("appointments/customer/send_customer_email",$storeId);
         $toSend = $configData['customer_email_enable'][$storeKey];

        //$templateId = Mage::getStoreConfig("appointments/customer/customer_reminder_template",$storeId);
        $templateId = $configData['email_template_appointment_remind'][$storeKey];
         $sender = array('name' => Mage::getStoreConfig("trans_email/bookings/name", 1),
            'email' => $configData['store_email'][$storeKey]);

        //$toSendAdmin = Mage::getStoreConfig("appointments/admin/send_admin_email",$storeId);
        $toSendAdmin = $configData['admin_email_enable'][$storeKey];
        $sendEmail = false;
        $sendSms = false;

        $startDate = $appointment->getAppointmentStart();
        $bookedDate = $appointment->getBookingTime();
        $notification_pref = $appointment->getNotificationPref();
        $phone = $appointment->getPhone();
        $appstatus = $appointment->getAppStatus();


        $appointment->setLastNotified($date);
        $appointment->save();


        Mage::log(" notication type  " . $notification_pref, Zend_Log::DEBUG, 'appointments.log', true);
        if ($notification_pref == 2) {
            $sendSms = true;
            $sendEmail = true;
        } else {
            $sendEmail = true;
        }
        $model = $appointment;
        $appointmentStart = date("F j, Y H:i", strtotime($model->getAppointmentStart()));
        $appointmentEnd = date("F j, Y H:i", strtotime($model->getAppointmentEnd()));
        if ($sendEmail) {
            /*Email Code*/
            if ($toSend) {
              
                $mailSubject = "Appointment booking Reminder";
                $apt_modify_link = Mage::getUrl('appointments/index/modify', array('id' => $model->getId(), 'email' => $model->getEmail(), '_secure' => true));
                $email = $model->getEmail();
                $name = $model->getFirstname() . " " . $model->getLastname();
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
                    'store_name' => $configData['store_name'][$storeKey],// Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
                    'store_address' => $configData['store_address'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
                    'store_email_address' => $configData['store_email'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
                    'store_phone' => $configData['store_phone'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
                    'store_hours' => $configData['store_hours_operation'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
                    'store_map' => $configData['store_map'][$storeKey],//Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
                    'apt_modify_link' => $apt_modify_link);
                $mail = Mage::getModel('core/email_template')
                    ->setTemplateSubject($mailSubject)
                    ->sendTransactional($templateId, $sender, $email, $name, $vars);

                Mage::log("reminder email sent to " . $email, Zend_Log::DEBUG, 'appointments.log', true);
echo "reminder email sent to" . $email."<br>";
            }
            /*End of Email Code*/

            if ($sendSms) {
                Mage::log(" In send Sms ", Zend_Log::DEBUG, 'appointments.log', true);
                $model = $appointment;
                $username = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_USERNAME);
                $password = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_PASSWORD);
                $url = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_BASEURL);
                $smsfrom = Mage::getStoreConfig(Allure_Appointments_Helper_Data::SMS_FROM);
                //$smsText = Mage::getStoreConfig("appointments/api/smstext_reminder",$storeId);
                $smsText = $configData['reminder_sms_message'][$storeKey];
                $appointmentStart = date("F j, Y H:i", strtotime($model->getAppointmentStart()));
                $date = date("F j, Y ", strtotime($model->getAppointmentStart()));
                $time = date('h:i A', strtotime($model->getAppointmentStart()));
                $smsText = str_replace("(time)", $time, $smsText);
                $smsText = str_replace("(date)", $date, $smsText);
                /* 		$smsText=str_replace("(book_link)",$booking_link,$smsText); */

                if ($phone) {//if NotificationPref set to text sms i.e. 2
                    $api = new SoapClient($url, array('cache_wsdl' => WSDL_CACHE_NONE, 'soap_version' => SOAP_1_1));
                    $session = $api->apiValidateLogin($username, $password);
                    preg_match("/<ticket>(?<ticket>.+)<\/ticket>/", $session, $response);
                    $status = $api->apiSendSms($response['ticket'], $smsfrom, $phone, $smsText, 'text', '0', '0');
                    preg_match("/<resp err=\"(?<error>.+)\">(<res>(<dest>(?<dest>.+)<\/dest>)?(<msgid>(?<msgid>.+)<\/msgid>)?.*<\/res>)?<\/resp>/", $status, $statusData);

                    Mage::log(" sent sms".$phone, Zend_Log::DEBUG, 'appointments.log', true);

                    echo " sent sms".$phone."<br>";

                }
            }
        }


    }


}






date_default_timezone_set('UTC');
