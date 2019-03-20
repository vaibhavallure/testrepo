<?php
class Allure_Appointments_PopupController extends Mage_Core_Controller_Front_Action{
    public function indexAction()
    {
        // MODIFY ACTION start by bhagya
        $apt_id = $this->getRequest()->getParam('id');
        $apt_email = $this->getRequest()->getParam('email');

        if ($apt_id && $apt_email) {
            $models = Mage::getModel('appointments/appointments')->getCollection();
            $models->addFieldToFilter('id', $apt_id)->addFieldToFilter('email',
                $apt_email);
            if (count($models)) {
                foreach ($models as $model) {
                    $model = $model;
                    break;
                }
                Mage::register('apt_modify_data', $model);
                Mage::getSingleton("core/session")->setData(
                    'appointmentData_availablity', true);
            } else {
                Mage::getSingleton("core/session")->setData(
                    'appointmentData_availablity', false);
            }
        }
        // MODIFY ACTION
        $this->loadLayout();
        $this->renderLayout();
    }
    public function ajaxGetTimeAction ()
    {
        $result = array(
            'success' => false
        );
        $request = $this->getRequest()->getParam('request');
        // Mage::log($request,Zend_Log::DEBUG,'my-log',true);

        // $aptmodel =
        // Mage::getModel('appointments/timing')->load($request['qty'],'qty');
        $time = Mage::helper('appointments')->getTimeByStoreAndPeople(
            $request['qty'], $request['store']);

        Mage::log($time, Zend_Log::DEBUG, 'appointments_time.log', true);


        $storeCurrentTime = "";
        $configData = Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
        $storeKey = array_search ($request['store'], $configData['stores']);
        $timeZone = $configData['timezones'][$storeKey];
        $timePref = $configData['time_pref'][$storeKey];
        if(!empty($timeZone) && $request['date']==date("m/d/Y")){
            $storeCurrentTime = $this->date_convert(date('H:i'), 'UTC', 'H:i', $timeZone, 'H:i');
            $storeCurrentTime = explode(":", $storeCurrentTime);
            $storeCurrentTime = (($storeCurrentTime[0]*60)+$storeCurrentTime[1]) / 60;
        }

        $block = $this->getLayout()
            ->createBlock('core/template', 'appointments_popup_picktimes',
                array(
                    'template' => 'allure/appointments/popup/picktime.phtml'
                ))
            ->setData("timing", $time)
            ->setData("date", $request['date'])
            ->setData("store_id", $request['store'])
            ->setData("id", $request['id'])
            ->setData("store_current_time",$storeCurrentTime);
        $output = $block->toHtml();

        $result['success'] = true;
        $result['msg'] = $time;
        $result['output'] = $output;

        $collection = Mage::getModel("appointments/pricing")->getCollection()
            ->addFieldToFilter('store_id',$request['store']);

        $helper = Mage::helper("appointments/storemapping");
        $configData = $helper->getStoreMappingConfiguration();
        $storeKey = array_search ($request['store'], $configData['stores']);
        $storeMap = $configData['store_map'][$storeKey];

//        $blockIdentifier = $configData['piercing_pricing_block'][$storeKey];
//
//        $pricingBlock = $this->getLayout()->createBlock('appointments/pricing','appointments_piercing_pricing',
//            array('template' => 'appointments/pricing.phtml'))
//            ->setPricingCollection($collection)
//            ->setStoreMap($storeMap)
//            ->setCmsBlockId($blockIdentifier);
//        $pricingHtml = $pricingBlock->toHtml();
//        $result['pricing_html'] = $pricingHtml;

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        // $block =
        // $this->getLayout()->createBlock('core/template')->setTemplate('/cart/mycart.phtml')->toHtml();
    }

    public function saveAction ()
    {
        $rand_value = rand(1000000, 10000000);
        usleep($rand_value);

        $post_data = $this->getRequest()->getPost();
        Mage::log($post_data,Zend_Log::DEBUG,'myLog.log',true);
        $embeded = $this->getRequest()->getParam('embedded');
        $storep = $post_data['store-id'];

        if ($embeded == '1')
            $appendUrl = "?embedded=" . $embeded;
        if ($storep) {
            if ($appendUrl)
                $appendUrl .= "&";
            else
                $appendUrl = "?";
            $appendUrl .= "store=" . $storep;
        }

        if(!$this->helper()->validatePostData($post_data, "user"))
        {
            Mage::getSingleton("core/session")->addError("Sorry Something Went Wrong Please Try Again!");
            $this->_redirectReferer($appendUrl);
            return;
        }

        if ($post_data) {
            $configData = $this->getAppointmentStoreMapping();
            try {
                if (isset($post_data['id'])) {
                    $step="modification";
                    $action="modify";
                    $old_appointment = Mage::getModel('appointments/appointments')->load($post_data['id']);
                    if (empty($post_data['app_date']))
                        $post_data['app_date'] = date('m/d/Y', strtotime($old_appointment->getAppointmentStart()));
                } else {
                    $step="save";
                    $action="save";
                }

                // http://www.geoplugin.net/php.gp?ip=219.91.251.70
                $post_data['ip'] = $this->get_client_ip();
                $mail_apt_start =  $post_data['appointment_start']. " on " .$post_data['app_date'];

                $post_data['appointment_start'] = strtotime($post_data['app_date'] .' '. $post_data['appointment_start']);
                $post_data['appointment_start'] = date('Y-m-d H:i:s', $post_data['appointment_start']);

                $post_data['appointment_end'] = $post_data['app_date'] . " " . $post_data['appointment_end'];
                $post_data['appointment_end'] = strtotime("-1 minutes", strtotime($post_data['appointment_end'] . ":59"));
                $post_data['appointment_end'] = date('Y-m-d H:i:s', $post_data['appointment_end']);

                $booking_date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $post_data['booking_time'] = $booking_date;
                $post_data['app_status'] = '2'; // Set appointment status assigned

                $phno = preg_replace('/\s+/', '', $post_data['phone']); // remove the whitespaces from phone no

                $post_data['phone'] = $phno;
                $storeId = $post_data['store_id'];

                if($action=="save")
                    $this->addLog($this->createSaveLogString("Before ".$step,$post_data),$action);

                Mage::log('POST DATA UPDATED::',Zend_Log::DEBUG,'appointments.log',true);
                Mage::log($post_data, Zend_Log::DEBUG,'appointments.log',true);

                if ($this->helper()->validateSlotBeforeBookAppointment($post_data) && !isset($post_data['id'])) {
                    // Mage::getSingleton("core/session")->addError("Sorry This Slot Has Been Already Taken. Please Select Another Slot.");
                    $this->addLog($this->createSaveLogString("Err => Sorry This Slot Has Been Already Taken. Please Select Another Slot ",$post_data),"save");

                    $piercer = $this->helper()->checkIfAnotherPiercerAvailable($post_data);

                    if ($piercer['success']) {
                        $post_data['piercer_id']=$piercer['p_id'];
                        $this->addLog($this->createSaveLogString("new piercer assigned ",$post_data),"save");
                    } else {
                        $this->addLog($this->createSaveLogString("not found any other piercer ",$post_data),"save");
                        Mage::getSingleton('core/session')->setSlotInvalid("true");
                        $this->_redirectReferer($appendUrl);
                        return;
                    }
                }

                $storeKey = array_search($storeId, $configData['stores']);
                $model = Mage::getModel('appointments/appointments')->addData($post_data)->save();

                $bookingdata=$post_data;
                $bookingdata['booking_id']=$model->getId();

                $this->addLog($this->createSaveLogString("After ".$step,$bookingdata),$action);
                Mage::log('After Save'.json_encode($bookingdata,true),Zend_Log::DEBUG,'myLog.log',true);

                // Create customer if flag set
                if ($post_data['password'] != null || $post_data['password'] != '') {
                    $websiteId = Mage::app()->getWebsite()->getId();
                    $cust_exist = $this->IscustomerEmailExists($model->getEmail(), $websiteId);

                    if ($cust_exist) {
                        Mage::getSingleton('customer/session')->addError('Customer Email Exists Already');
                    } else {
                        $this->createCust($model);
                    }
                } // End of If creating customer

                // IF appointment is modified then send updates to ADMIN &
                // PIERCER & CUSTOMER

                $appointmentStart = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentStart()));
                $appointmentEnd = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentEnd()));
                if ($old_appointment) {
                    // If SMS is checked for notify me.
                    $oldAppointmentStart = date("F j, Y H:i", strtotime($old_appointment->getAppointmentStart()));
                    $oldAppointmentEnd = date("F j, Y H:i", strtotime($old_appointment->getAppointmentEnd()));
                }
                $email = $model->getEmail();
                $name = $model->getFirstname() . " " . $model->getLastname();
                $apt_modify_link = Mage::getUrl('appointments/popup/modify', array(
                    'id' => $model->getId(),
                    'email' => $model->getEmail(),
                    '_secure' => true
                ));
                Mage::getSingleton("core/session")->setData('appointment_submitted', $model);

                $app_string="id->".$model->getId()." email->".$model->getEmail() ." mobile->".$model->getPhone()." name->".$model->getFirstname()." ".$model->getLastname()." ";

                if (isset($post_data['noti_sms'])) {
                    if ($post_data['noti_sms']=='on') {
                        $post_data['notification_pref']='2';
                    }
                }

                if ($post_data['notification_pref'] === '2') {
                    if ($old_appointment) {
                        $smsText = $configData['modified_sms_message'][$storeKey];
                        $action_string="SMS/Modify";
                    } else {
                        $smsText = $configData['book_sms_message'][$storeKey];
                        $action_string="SMS/Create";
                    }

                    $timePref = $configData['time_pref'][$storeKey];
                    if($timePref == 24)
                        $time = date('H:i', strtotime($model->getAppointmentStart()));
                    else if($timePref == 12)
                        $time = date('h:i A', strtotime($model->getAppointmentStart()));
                    else
                        $time = date('H:i', strtotime($model->getAppointmentStart()));

                    $url=Mage::helper('appointments')->getShortUrl($apt_modify_link);
                    $date = date("F j, Y ", strtotime($model->getAppointmentStart()));

                    $smsText = str_replace("(time)", $time, $smsText);
                    $smsText = str_replace("(date)", $date, $smsText);
                    $smsText = str_replace("(modify_link)", $url, $smsText);

                    if ($post_data['phone']) {
                        $smsdata = Mage::helper('appointments')->sendsms($post_data['phone'], $smsText, $storeId);
                        $model->setSmsStatus($smsdata);
                        $model->save();
                        $this->notify_Log($action_string, $app_string);
                    }
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
                    'apt_starttime' => $mail_apt_start,
                    'apt_endtime' => $appointmentEnd,
                    'store_name' => $configData['store_name'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_name",$storeId),
                    'store_address' => $configData['store_address'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_address",$storeId),
                    'store_email_address' => $configData['store_email'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_email",$storeId),
                    'store_phone' => $configData['store_phone'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_phone",$storeId),
                    'store_hours' => $configData['store_hours_operation'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_hours",$storeId),
                    'store_map' => $configData['store_map'][$storeKey], // Mage::getStoreConfig("appointments/genral_email/store_map",$storeId),
                    'apt_modify_link' => $apt_modify_link,
                    'booking_id'=>$model->getId()
                );

                //send Customer email
                $enableCustomerEmail = $configData['customer_email_enable'][$storeKey];
                $enableAdminEmail = $configData['admin_email_enable'][$storeKey];
                $enablePiercerEmail = $configData['piercer_email_enable'][$storeKey];

                $sender = array(
                    'name' => Mage::getStoreConfig("trans_email/bookings/name"),
                    'email' => $configData['store_email'][$storeKey]
                );

                try {
                    if ($old_appointment) {
                        if ($enableCustomerEmail){
                            $templateId=$configData['email_template_appointment_modify'][$storeKey];
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                                $mailSubject);
                            foreach (explode(",",Mage::getStoreConfig('appointments/app_bcc/emails')) as $emails) {
                                $mail->addBcc($emails);
                            }
                            $mail->sendTransactional($templateId,
                                $sender, $email, $name, $vars);

                            $this->notify_Log("Email/Modify", $app_string);
                        }
                        if ($enableAdminEmail){
                            $adminEmail=$configData['admin_email_id'][$storeKey];
                            $name='';
                            $templateId=$configData['admin_email_template_modify'][$storeKey];
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                                $mailSubject)->sendTransactional($templateId,
                                $sender, $email, $name, $vars);
                        }
                        if ($enablePiercerEmail){
                            $templateId=$configData['piercer_email_template_modify'][$storeKey];
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                                $mailSubject)->sendTransactional($templateId,
                                $sender, $email, $name, $vars);
                        }

                    } else {
                        if ($enableCustomerEmail){
                            $templateId=$configData['email_template_appointment'][$storeKey];
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                                $mailSubject);
                            foreach (explode(",",Mage::getStoreConfig('appointments/app_bcc/emails')) as $emails) {
                                $mail->addBcc($emails);
                            }
                            $mail->sendTransactional($templateId,
                                $sender, $email, $name, $vars);

                            $this->notify_Log("Email/Create", $app_string);

                        }
                        if($enableAdminEmail){
                            $adminEmail=$configData['admin_email_id'][$storeKey];
                            $name='';
                            $templateId=$configData['admin_email_template'][$storeKey];
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                                $mailSubject)->sendTransactional($templateId,
                                $sender, $email, $name, $vars);
                        }
                        if($enablePiercerEmail){
                            $templateId=$configData['piercer_email_template'][$storeKey];
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                                $mailSubject)->sendTransactional($templateId,
                                $sender, $email, $name, $vars);
                        }
                    }
                } catch (Exception $e) {
                    Mage::log("Exception Occured",Zend_log::DEBUG,'appointments.log',true);
                    Mage::log($e->getMessage(),Zend_log::DEBUG,'appointments.log',true);
                }
//                Mage::getSingleton("core/session")->setData('appointment_submitted', $model);
                Mage::log('APPOINTMENT SUBMITTED','myLog.log',true);
                $this->getResponse()->setRedirect(Mage::getUrl("*/*/", array('_secure' => true)) . $appendUrl);
                $this->_redirectReferer($appendUrl);
                return;
            } catch(Exception $e) {
                Mage::getSingleton("core/session")->addError($e->getMessage());

                $this->_redirectReferer($appendUrl);
                return;
            }
        }
        Mage::log('END OF SAVE ACTION IN POPUP Controller',Zend_Log::DEBUG,'myLog.log',true);
        $this->_redirectReferer($appendUrl);
        // $this->getResponse()->setRedirect(Mage::getUrl("*/*/", array('_secure' => true)) . $appendUrl);
    }

    /* Modify or Cancel URL Action */
    public function modifyAction ()
    {

        $apt_id = $this->getRequest()->getParam('id');
        $apt_email = $this->getRequest()->getParam('email');

        // $apt_id = Mage::helper('core')->decrypt($encryptedId);
        // $apt_email = Mage::helper('core')->decrypt($encryptedEmail);

        if ($apt_id && $apt_email) {
            // $append_url = "?apt_id=".$apt_id."&email=".$apt_email;
            $models = Mage::getModel('appointments/appointments')->getCollection();
            $models->addFieldToFilter('id', $apt_id)
                ->addFieldToFilter('email', $apt_email)
                ->addFieldToFilter('app_status',
                    array(
                        'in' => array(
                            Allure_Appointments_Model_Appointments::STATUS_REQUEST,
                            Allure_Appointments_Model_Appointments::STATUS_ASSIGNED
                        )
                    ));
            if (count($models)) {
                foreach ($models as $model) {
                    $model = $model;
                    break;
                }


                $logdata=$model->getData();
                $logdata['ip']=$this->get_client_ip();
                $this->addLog($this->createSaveLogString("Modify INIT ",$logdata),"modify");


                Mage::register('appointment_modified', $model);
                Mage::getSingleton("core/session")->setData(
                    'appointment_availablity', true);
            } else {
                Mage::getSingleton("core/session")->setData(
                    'appointment_availablity', false);
            }
        }
        //Mage::log('IN MODIFY APPOINTMENT',Zend_Log::DEBUG,'myLog.log',true);
        $this->loadLayout();
        $this->renderLayout();
    }

/* CancelaptAction by bhagya */
    public function cancelaptAction ()
    {
        $apt_id = $this->getRequest()->getParam('id');
        $apt_email = $this->getRequest()->getParam('email');

        if ($apt_id || $apt_email) {
            $data = array(
                'app_status' => Allure_Appointments_Model_Appointments::STATUS_CANCELLED
            );
            $model = Mage::getModel('appointments/appointments')->load($apt_id);
            $storeId = $model->getStoreId();
            $model = Mage::getModel('appointments/appointments')->load($apt_id)->addData(
                $data);

            try {
                $model->setId($apt_id)->save();


                $logdata=$model->getData();
                $logdata['ip']=$this->get_client_ip();
                $this->addLog($this->createSaveLogString("Canceled",$logdata),"modify");


                echo "Your scheduled Appointment is Cancelled successfully.";
                $configData = $this->getAppointmentStoreMapping();
                $storeKey = array_search ($storeId, $configData['stores']);

                $app_string="id->".$model->getId()." email->".$model->getEmail() ." mobile->".$model->getPhone()." name->".$model->getFirstname()." ".$model->getLastname()." ";


                if ($model->getNotificationPref() === '2') {
                    $smsText = $configData['cancel_sms_message'][$storeKey];
                    $appointmentStart = date("F j, Y H:i",strtotime($model->getAppointmentStart()));
                    $date = date("F j, Y ",strtotime($model->getAppointmentStart()));
                    $timePref = $configData['time_pref'][$storeKey];
                    if($timePref == 24)
                        $time = date('H:i', strtotime($model->getAppointmentStart()));
                    else if($timePref == 12)
                        $time = date('h:i A', strtotime($model->getAppointmentStart()));
                    else
                        $time = date('H:i', strtotime($model->getAppointmentStart()));

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

                        $this->notify_Log("Email/Cancel", $app_string);

                    }
                }
                // SMS CODE TO CANCEL Appointment end
                $appointmentStart = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentStart()));
                $appointmentEnd = date("F j, Y \\a\\t H:i", strtotime($model->getAppointmentEnd()));
                $vars = array(
                    'name' => $model->getFirstname() . " " .$model->getLastname(),
                    'customer_name' => $model->getFirstname() ." " . $model->getLastname(),
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
                    'apt_modify_link' => $apt_modify_link,
                    'booking_id'=>$model->getId()
                );

                //send Customer email
                $enableCustomerEmail = $configData['customer_email_enable'][$storeKey];
                $enableAdminEmail = $configData['admin_email_enable'][$storeKey];
                $enablePiercerEmail = $configData['piercer_email_enable'][$storeKey];
                $sender = array(
                    'name' => Mage::getStoreConfig("trans_email/bookings/name"),
                    'email' => $configData['store_email'][$storeKey]
                );

                try {

                    if($enableCustomerEmail){
                        $email=$model->getEmail();
                        $name=$model->getFirstname() . " " .$model->getLastname();
                        $templateId=$configData['email_template_appointment_cancel'][$storeKey];
                        $mail = Mage::getModel('core/email_template');
                        foreach (explode(",",Mage::getStoreConfig('appointments/app_bcc/emails')) as $emails) {
                            $mail->addBcc($emails);
                        }
                        $mail->setTemplateSubject(
                            $mailSubject)->sendTransactional($templateId,
                            $sender, $email, $name, $vars);

                        $this->notify_Log("Email/Cancel", $app_string);

                    }
                    if($enableAdminEmail){
                        $adminEmail=$configData['admin_email_id'][$storeKey];
                        $name='';
                        $templateId=$configData['admin_email_template_cancel'][$storeKey];
                        $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                            $mailSubject)->sendTransactional($templateId,
                            $sender, $email, $name, $vars);
                    }
                    if($enablePiercerEmail){
                        $piercer=Mage::getModel('appointment/piercer')->load($model->getPiercerId());
                        $email=$piercer->getEmail();
                        $name=$piercer->getFirstname();
                        $templateId=$configData['piercer_email_template_cancel'][$storeKey];
                        $mail = Mage::getModel('core/email_template')->setTemplateSubject(
                            $mailSubject)->sendTransactional($templateId,
                            $sender, $email, $name, $vars);
                    }
                }catch(Exception $e){
                    echo $e->getMessage();
                }


            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $model->save();
        }
    }
    function date_convert($dt, $tz1, $df1, $tz2, $df2) {
        // create DateTime object
        $d = DateTime::createFromFormat($df1, $dt, new DateTimeZone($tz1));
        // convert timezone
        $d->setTimeZone(new DateTimeZone($tz2));
        // convert dateformat
        return $d->format($df2);
    }


    /* Create the customer*/
    public function createCust ($cust_data)
    {
        $customer_email = $cust_data->getEmail();
        $customer_fname = $cust_data->getFirstname();
        $customer_lname = $cust_data->getLastname();

        // $passwordLength = 10; // the lenght of autogenerated password
        $password = $cust_data->getPassword();
        $customer_phone = $cust_data->getPhone();
        $customer_street = $cust_data->getStreet();
        $customer_country = $cust_data->getCountry();
        $customer_state = $cust_data->getState();
        $customer_city = $cust_data->getCity();
        $customer_postal_code = $cust_data->getPostalCode();

        $addressData = array(
            'firstname' => $customer_fname,
            'lastname' => $customer_lname,
            'street' => $customer_street,
            'city' => $customer_city,
            'country_id' => $customer_country,
            'region' => $customer_state,
            // [region_id] => 1
            'postcode' => $customer_postal_code,
            'telephone' => $customer_phone,
            'is_default_billing' => 1,
            'is_default_shipping' => ''
        );
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()
            ->getId());
        $customer->loadByEmail($customer_email);

        $address = Mage::getModel('customer/address');
        $address->addData($addressData);

        // Check if the email exist on the system.If YES, it will not create a
        // user account.
        if (! $customer->getId()) {

            // setting data such as email, firstname, lastname, and password
            $customer->setEmail($customer_email);
            $customer->setFirstname($customer_fname);
            $customer->setLastname($customer_lname);

            // $customer->setPassword($customer->generatePassword($passwordLength));
            $customer->setPassword($password);
            // $customer->password_hash = md5($password);
            $customer->setPassword($password);
            $customer->addAddress($address);
        }
        try {
            // the save the data and send the new account email.
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();
            $customer->sendNewAccountEmail();
        }
        catch (Exception $e) {
            Mage::log(
                " Customer create when new customer book appointment :" .
                $e->getMessage(), Zend_Log::DEBUG, 'appointments.log',
                true);
            // Mage::log($e->getMessage());
            // print_r($e->getMessage());
        }
    }

    /* Check customer exists start */
    function IscustomerEmailExists ($email, $websiteId = null)
    {
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



    public function ajaxSupportDetailsAction()
    {
        $storeid = $this->getRequest()->getParam('storeid');

        $result['message']=$this->helper()->getSupportMessage($storeid);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }
    // To get the Working days depend on storeid by bhagya
    public function ajaxGetWorkingDaysAction ()
    {
        $result = array(
            'success' => false
        );
        $storeid = $this->getRequest()->getParam('storeid');
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $models = Mage::getModel('appointments/appointments')->load($id);
            // $models->addFieldToFilter('id',$apt_id)->addFieldToFilter('email',$apt_email)->addFieldToFilter('app_status',array('in'=>array(Allure_Appointments_Model_Appointments::STATUS_REQUEST,Allure_Appointments_Model_Appointments::STATUS_ASSIGNED)));
            if ($models->getId())
                Mage::register('apt_modify_data', $models);
        }
        // To modify the appointment get the data from registry end
        $piercers = Mage::getModel('appointments/piercers')->getCollection()
            ->addFieldToFilter('store_id', array(
                'eq' => $storeid
            ))
            ->addFieldToFilter('is_active', array(
                'eq' => '1'
            ));

        $avial_workDays = array();

        foreach ($piercers as $piercer) {
            // $workdays = explode(",",$piercer->getWorkingDays());
            $workdays = array_map('trim',
                explode(',', $piercer->getWorkingDays()));
            $avial_workDays[] = $workdays;
        }
        $available_wdays = array();
        foreach ($avial_workDays as $avail_wd) {
            foreach ($avail_wd as $wd) {
                $available_wdays[] = $wd;
            }
        }

        $notAvailableDatesCollection = Mage::getModel('appointments/dates')->getCollection()
            ->addFieldToFilter('store_id', array(
                'eq' => $storeid
            ))
            ->addFieldToFilter('is_available', array(
                'eq' => '0'
            ))
            ->addFieldToFilter('exclude', array(
                'eq' => '0'
            ));

        $notAvailabledays = array();

        foreach ($notAvailableDatesCollection as $singeDate) {
            $formattedDate = date("m/d/Y", strtotime($singeDate->getDate()));
            $notAvailabledays[strtotime($singeDate->getDate())] = $formattedDate;
        }

        $available_wdays = array();
        $available_wdays_ajax = array();

        foreach ($avial_workDays as $avail_wd) {
            foreach ($avail_wd as $wd) {
                if (! $notAvailabledays[strtotime($wd)]) {
                    $dateCurrent = Mage::getModel('core/date')->date('m/d/Y');
                    if (strtotime($dateCurrent) <= strtotime($wd)) {
                        $available_wdays[strtotime($wd)] = $wd;
                        if($wd!='03/04/2019') {
                            array_push($available_wdays_ajax, $wd);
                        }
                    }
                }
            }
        }

        $jsonDATA = "";

        if (! empty($available_wdays)) {
            $jsonDATA = json_encode(array_unique($available_wdays));
        }

        $configData = $this->getAppointmentStoreMapping();
        $storeKey = array_search ($storeid, $configData['stores']);

        //$schedule = Mage::getStoreConfig("appointments/piercer_schedule/schedule", $storeid);
        $schedule = $configData['piercers_available'][$storeKey];
        $result['success'] = true;
        $result['schedule'] = $schedule;
        $result['available_dates']=array_unique($available_wdays_ajax);
        Mage::log('Dates'.$jsonDATA,Zend_Log::DEBUG,'myLog.log',true);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    function get_client_ip ()
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) // check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) // to check ip is pass
            // from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    /**
     * return array of store mapping
     */
    private function getAppointmentStoreMapping(){
        return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    }





    /**
     * add customer log
     */
    private function addLog($data,$action){
        Mage::helper("appointments/logs")->addCustomerLog($data,$action);
    }

    private function createSaveLogString($step,$customerInfo)
    {
        $str=$step."=> ";
        if(array_key_exists("booking_id",$customerInfo))
            $str.="Booking_id=>".$customerInfo['booking_id']." ";

        if(array_key_exists("id",$customerInfo))
            $str.="Booking_id=>".$customerInfo['id']." ";


        $str.="Email=>".$customerInfo['email']." Cutomer_id=>".$customerInfo['customer_id']." store_id=>".$customerInfo['store_id']." piercer_id=>".$customerInfo['piercer_id']." piercing_qty=>".$customerInfo['piercing_qty']. " appointment_start=>".$customerInfo['appointment_start']." =>".$customerInfo['appointment_end']." ip=>".$customerInfo['ip'];

        return $str;
    }

    private function notify_Log($action,$string){
        Mage::helper("appointments/logs")->appointment_notification($action,$string);
    }

    private function helper(){
        return Mage::helper("appointments/data");
    }

    public function subscribeAction()
    {
        if(isset($_POST['email'])){
            $response='';
            $email = $_POST['email'];
            try {
                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);

                $response = [
                    'status' => 'OK',
                    'msg' => 'Thank you for your subscription.',
                    'sta'=> $status,
                ];
            } catch (Exception $exception) {
                Mage::log('ERROR SUBSCRIBE '.$exception->getMessage(),Zend_Log::DEBUG,'appointment_la.log',true);
                $response = [
                    'status' => 'ERROR',
                    'msg' => 'Sorry there is problem in subscription.',
                    'sta'=> $status,
                ];
            }
        } else {
            $response = [
                'status' => 'ERROR',
                'msg' => 'Please enter your Email',
            ];
        }
        $remarkety = Mage::getModel('mgconnector/observer');
        $remarkety->makeRequest('customers/create', array(
            'email' => $email,
            'tags' => array("LA_Popup"),
            'accepts_marketing' => true
        ));
        Mage::log('Remarkety : '.$email,Zend_Log::DEBUG,'popup_remarkety.log',true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
