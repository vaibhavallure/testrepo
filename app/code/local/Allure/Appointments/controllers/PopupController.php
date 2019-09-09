<?php

class Allure_Appointments_PopupController extends Mage_Core_Controller_Front_Action
{
    private $currentDateFlag = false;



    public function indexAction()
    {
        if ($this->getAppId()) {

            $collection = $this->getValidAppointmentCollection();

            if ($collection->getSize()) {

                Mage::register('apt_modify_data', $collection->getFirstItem());
                $this->session()->setData(
                    'apt_customer_data', $this->getAppCustomers($this->getModifyDecryptedApptId()));

                $this->session()->setData(
                    'appointmentData_availablity', true);
            } else {
                $this->session()->setData(
                    'appointmentData_availablity', false);
            }
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Maria Tash'));
        $this->renderLayout();
    }

    private function session()
    {
        return Mage::getSingleton("core/session");
    }

    public function saveAction()
    {


        $post_data = $this->getFormattedPostData();

        /*------------------notification setting start ----------------------*/
        $oldAppointment = null;
        $oldCustomersCollection = null;
        $isPiercingAppointment = false;
        $type = "new";

        if ($this->getAppId()) {
            $oldAppointment = $this->appointment()->load($post_data['id']);
            $oldCustomersCollection = $this->getAppCustomers()->getData();
            $type = "modify";
        }
        /*------------notification setting end------------------------------*/


        if (!$this->helper()->validatePostData($post_data, "user")) {
            $this->log()->addStoreWiseLog('Sorry Something Went Wrong Please Try Again! validation error please check save_appointment_customer.log for more detail', $post_data['store_id']);
            Mage::getSingleton('core/session')->setSomethingWrong("true");
            // Mage::getSingleton("core/session")->addError("Sorry Something Went Wrong Please Try Again!");
            $this->_redirectReferer();
            return;
        }


        $this->taskStart();
        if ($this->helper()->validateSlotBeforeBookAppointment($post_data)) {

            $this->log()->addStoreWiseLog('Err => Sorry This Slot Has Been Already Taken', $post_data['store_id']);

            $piercer = $this->helper()->checkIfAnotherPiercerAvailable($post_data);

            $this->log()->addStoreWiseLog('checking for another piercer for requested slot', $post_data['store_id']);


            if ($piercer['success']) {
                $post_data['piercer_id'] = $piercer['p_id'];
                $this->log()->addStoreWiseLog('New piercer assigned new_piercer_id=' . $piercer['p_id'], $post_data['store_id']);

            } else {
                $this->log()->addStoreWiseLog('no piercer available for this slot', $post_data['store_id']);
                $this->taskEnd();
                Mage::getSingleton('core/session')->setSlotInvalid("true");
                $this->_redirectReferer();
                return;
            }
        }
        $this->taskEnd();

        try {

            $model = $this->appointment()->addData($post_data)->save();

            foreach ($post_data['customer'] as $customer) {
                $customer['appointment_id'] = $model->getId();
                $customer['sms_notification'] = ($customer['noti_sms'] == 'on') ? 1 : 0;
                $customer['piercing'] = ($customer['piercing']) ? $customer['piercing'] : 0;

                if ($customer['piercing'] > 0)
                    $isPiercingAppointment = true;

                $customer['checkup'] = ($customer['checkup']) ? 1 : 0;
                $customer['language_pref'] = ($post_data['language_pref']) ? $post_data['language_pref'] : 'en';


                $customerObj = $customers[] = Mage::getModel('appointments/customers')->addData($customer)->save();

                if ($customerObj->getId())
                    $customer_ids[] = $customerObj->getId();
            }

            if ($post_data['id'] && count($customer_ids) > 0) {
                $customerCollection = $this->getAppCustomers($post_data['id'], $customer_ids);
                if ($customerCollection->getSize()) {
                    foreach ($customerCollection as $customer) {
                        $customer->delete();
                    }
                }
            }

            $this->log()->addStoreWiseLog('appointment saved/updated successfully--------->', $post_data['store_id']);

            Mage::getSingleton("core/session")->setData('appointment_submitted', $model);
            Mage::getSingleton("core/session")->setData('isPiercingAppointment', $isPiercingAppointment);


            /*-----------------notification section start--------------------------------*/
            if ($this->notify()->sendEmailNotification($model, $type, $oldAppointment, $oldCustomersCollection))
                $this->log()->addStoreWiseLog('Notified by email type=>' . $type, $post_data['store_id']);

            if ($this->notify()->sendSmsNotification($model, $type))
                $this->log()->addStoreWiseLog('Notified By SMS(if selected) type=>' . $type, $post_data['store_id']);

//
//            if($type=="new")
//            {
            if ($this->notify()->sendEmailNotification($model, 'release'))
                $this->log()->addStoreWiseLog('Notified by email type=>release', $post_data['store_id']);

            if ($this->notify()->sendSmsNotification($model, 'release'))
                $this->log()->addStoreWiseLog('Notified By SMS(if selected) type=>release', $post_data['store_id']);

//            }

            /*---------------------------------notification section end--------------------------------*/


            //  $this->getResponse()->setRedirect(Mage::getUrl("*/*/*", array('_secure' => true)));
            // $this->_redirectReferer();

            if ($post_data['user'] == "admin")
                $this->_redirectUrl(Mage::getBaseUrl() . "appointments/popup/index/user/admin");
            else
                $this->_redirectUrl(Mage::getBaseUrl() . "appointments/popup/");

            return;

        } catch (Exception $e) {
            Mage::getSingleton("core/session")->addError($e->getMessage());

            $this->_redirectReferer();
            return;
        }


    }

    public function getFormattedPostData()
    {

        $post_data = $this->getRequest()->getPost();


        if ($post_data['id'])
            $action = 'update';
        else
            $action = 'save';

        $this->log()->addStoreWiseLog('appointment_' . $action . '_action---------->', $post_data['store_id']);


        $this->log()->addStoreWiseLog($this->baseurl() . '' . http_build_query($post_data), $post_data['store_id']);
        $post_data['app_date'] = date('m/d/Y', strtotime($post_data['app_date']));
        $post_data['appointment_start'] = strtotime($post_data['app_date'] . ' ' . $post_data['appointment_start']);
        $post_data['appointment_start'] = date('Y-m-d H:i:s', $post_data['appointment_start']);

        $post_data['appointment_end'] = $post_data['app_date'] . " " . $post_data['appointment_end'];
        $post_data['appointment_end'] = strtotime($post_data['appointment_end']);
        $post_data['appointment_end'] = date('Y-m-d H:i:s', $post_data['appointment_end']);

        $booking_date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $post_data['booking_time'] = $booking_date;
        $post_data['app_status'] = '2'; // Set appointment status assigned
        $post_data['special_store'] = 1;

        extract($post_data['customer'][1]);

        $post_data['firstname'] = $firstname;
        $post_data['lastname'] = $lastname;
        $post_data['email'] = $email;
        $post_data['phone'] = $phone;

        $post_data['ip'] = $this->get_client_ip();


        return $post_data;
    }

    public function modifyAction()
    {

        $collection = $this->getValidAppointmentCollection();

        if ($collection->getSize()) {

            $model = $collection->getFirstItem();
            $logdata = $model->getData();
            $logdata['ip'] = $this->get_client_ip();

            $this->log()->addStoreWiseLog('appointment_modify_init---------->' . $model->getId() . ' by user=>' . $this->getModifyDecryptedCustId(), $model->getStoreId());

            if (strtotime($model->getAppointmentStart()) < $this->helper()->getCurrentDatetime($model->getStoreId()))
                Mage::getSingleton("core/session")->setData('appointment_expired', true);
            else
                Mage::getSingleton("core/session")->setData('appointment_expired', false);

            Mage::register('appointment_modified', $model);
            Mage::getSingleton("core/session")->setData('appointment_availablity', true);
        } else {
            Mage::getSingleton("core/session")->setData('appointment_availablity', false);
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Maria Tash'));
        $this->renderLayout();

    }

    public function cancelaptAction()
    {
        try {
            if ($this->getAppId()) {
                $appointment = $this->appointment()->load($this->getAppId());
                $appointment->setData('app_status', Allure_Appointments_Model_Appointments::STATUS_CANCELLED);
                $appointment->save();

                $this->log()->addStoreWiseLog('appointment_canceled_action ------------->' . $appointment->getId(), $appointment->getStoreId());

                echo "Your scheduled Appointment is Cancelled successfully.";

                if ($this->notify()->sendEmailNotification($appointment, "cancel"))
                    $this->log()->addStoreWiseLog('cancel Notified by email', $appointment->getStoreId());

                if ($this->notify()->sendSmsNotification($appointment, "cancel"))
                    $this->log()->addStoreWiseLog('cancel Notified By SMS(if selected)', $appointment->getStoreId());

            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function getValidAppointmentCollection()
    {
        $collection = $this->appointment()->getCollection();
        $collection->addFieldToFilter('id', $this->getModifyDecryptedApptId())
            ->addFieldToFilter('app_status',
                array(
                    'in' => array(
                        Allure_Appointments_Model_Appointments::STATUS_REQUEST,
                        Allure_Appointments_Model_Appointments::STATUS_ASSIGNED
                    )
                ));

        return $collection;
    }

    private function getAppId()
    {
        return $this->getRequest()->getParam('id');
    }

    private function getStoreId()
    {
        return $this->getRequest()->getParam('storeid');
    }

    private function getAppEmail()
    {
        return $this->getRequest()->getParam('email');
    }

    private function getAppCustomers($appointment_id = null, $exceptCustomerId = null)
    {
        $appointment_id = ($appointment_id != null) ? $appointment_id : $this->getAppId();

        $collection = Mage::getModel('appointments/customers')->getCollection();
        $collection->addFieldToFilter('appointment_id', $appointment_id);

        if ($exceptCustomerId != null) {
            $collection->addFieldToFilter('id',
                array(
                    'nin' => $exceptCustomerId
                ));
        }

        return $collection;
    }

    /**
     * add customer log
     */
    private function addLog($data, $action)
    {
        Mage::helper("appointments/logs")->addCustomerLog($data, $action);
    }

    private function helper()
    {
        return Mage::helper("appointments/data");
    }

    private function log()
    {
        return Mage::helper("appointments/logs");
    }

    public function logStringAction()
    {
        echo "<pre>";
        print_r($this->getRequest()->getParams());
        die();
    }

    private function appointment()
    {
        return Mage::getModel('appointments/appointments');
    }

    private function baseurl()
    {
        return Mage::getBaseUrl() . 'appointments/popup/logstring?';
    }

    public function ajaxGetWorkingDaysAction()
    {
        $piercers = Mage::getModel('appointments/piercers')->getCollection()->addFieldToFilter('store_id', array('eq' => $this->getStoreId()))->addFieldToFilter('is_active', array('eq' => '1'));
        $available_wdays = array();

        foreach ($piercers as $piercer) {
            $workdays = array_map('trim', explode(',', $piercer->getWorkingDays()));
            $available_wdays = array_unique(array_merge($available_wdays, $workdays));
        }

        $currentDate = $this->getCurrentDate();

        if(strtotime("08/31/2019")>strtotime($currentDate))
        {
            $currentDate="08/31/2019";
        }

        $available_wdays = array_filter(array_map(function ($dateTime) use ($currentDate) {
            $time1 = strtotime($dateTime);
            $time2 = strtotime($currentDate);
            if ($time1 >= $time2) {
                return $dateTime;
            }
        }, $available_wdays));

        $result['current_date'] = $currentDate;
        $result['available_dates'] = array_values($available_wdays);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    private function getCurrentDate()
    {
        return date('m/d/Y', $this->helper()->getCurrentDatetime($this->getStoreId()));
    }


    public function getAvailableSlotsAction()
    {
        if (empty($this->getStoreId()) || empty($this->getSlotTime()) || $this->getSlotTime() < 1) {
            $response = array('success' => 'false', 'error' => 'Invalid store id or slot time');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $storeId = $this->getStoreId();

        if (!$this->getRequestedDate()) {
            $response = array('success' => 'false', 'error' => 'Invalid Requesting Date');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }

        $date = $this->getRequestedDate();

        $appDay = date('l', strtotime($date));
        $slots = array();


        try {
            $collection = Mage::getModel('appointments/piercers')->getCollection()->addFieldToFilter('store_id', array('eq' => $storeId))
                ->addFieldToFilter('is_active', array('eq' => '1'));
            $collection->addFieldToFilter('working_days', array('like' => '%' . date("m/d/Y", strtotime($date)) . '%'));

            foreach ($collection as $piercer) {
                $appCollection = Mage::getModel('appointments/appointments')->getCollection();
                $appCollection->addFieldToFilter('app_status', array('eq' => Allure_Appointments_Model_Appointments::STATUS_ASSIGNED))
                    ->addFieldToFilter('appointment_start', array('like' => '%' . date("Y-m-d", strtotime($date)) . '%'))
                    ->addFieldToFilter('piercer_id', array('eq' => $piercer->getId()))
                    ->addFieldToSelect('appointment_start')
                    ->addFieldToSelect('appointment_end');

                $this->checkIfModifyAppointment($appCollection);

                $booked_timeslots = $appCollection->getData();


                $workingHours = $piercer->getWorkingHours();
                $workingHours = unserialize($workingHours);

                $options = array_shift(array_filter(array_map(function ($workSlot) use ($appDay, $date) {
                    if (strtolower($workSlot['day']) == strtolower($appDay)) {
                        $option['work']['start'] = $this->formatWorkTime($this->validatePiercerStartTime($this->helper()->getTimeByValue($workSlot['start'])));
                        $option['work']['end'] = $this->formatWorkTime($this->helper()->getTimeByValue($workSlot['end']));
                        $option['break']['appointment_start'] = $this->formatBreakTime($this->helper()->getTimeByValue($workSlot['break_start']));
                        $option['break']['appointment_end'] = $this->formatBreakTime($this->helper()->getTimeByValue($workSlot['break_end']), "end");
                        return $option;
                    }
                }, $workingHours)));

                array_push($booked_timeslots, $options['break']);
                $slots = array_merge($slots, $this->getSlots($options['work'], $booked_timeslots, $piercer->getId()));
            }

            $response['success'] = true;
            $response['slots'] = $this->unique_multidim_array($slots, 'start');

//            echo "<pre>";
//            print_r($this->unique_multidim_array($slots,'start'));

            $this->log()->addStoreWiseLog('slot array', $this->getStoreId());
            $this->log()->addStoreWiseLog($this->unique_multidim_array($slots, 'start'), $this->getStoreId());


            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));


        } catch (Exception $e) {

        }

    }

    private function checkIfModifyAppointment(&$appCollection)
    {
        if (!empty($this->getRequest()->getParam('appointment_id'))) {
            $appCollection->addFieldToFilter('id', array('neq' => $this->getRequest()->getParam('appointment_id')));
        }
    }

    private function getSlots($piercer_work_time, $booked, $piercer_id)
    {
        /* echo "<pre>";
         print_r($booked);*/

        $this->log()->addStoreWiseLog('booked slots--------' . $piercer_id, $this->getStoreId());
        $this->log()->addStoreWiseLog($booked, $this->getStoreId());


        $slot_time = $this->getSlotTime();

        $start_time = $piercer_work_time['start'];
        $end_time = $piercer_work_time['end'];

        $slotArray = array();

        $start = DateTime::createFromFormat('Y-m-d H:i', date("Y-m-d H:i", strtotime($start_time)));
        $end = DateTime::createFromFormat('Y-m-d H:i', date("Y-m-d H:i", strtotime($end_time)));

        for ($i = $start; $i < $end;) {
            $booked_flag = false;
            $booked_endtime = null;
            $t1 = date_timestamp_get($i);
            $t2 = $t1 + ((60 * $slot_time) - 1);
//            echo "<br>" . date("H:i", $t1) . "-" . date("H:i", $t2);

            $this->log()->addStoreWiseLog('slot start and end time', $this->getStoreId());
            $this->log()->addStoreWiseLog(date("H:i", $t1) . "-" . date("H:i", $t2), $this->getStoreId());

            foreach ($booked as $book) {

                $st_datetime = DateTime::createFromFormat('Y-m-d H:i', date("Y-m-d H:i", strtotime($book['appointment_start'])));
                $en_datetime = DateTime::createFromFormat('Y-m-d H:i', date("Y-m-d H:i", strtotime($book['appointment_end'])));
                $st = date_timestamp_get($st_datetime);
                $en = date_timestamp_get($en_datetime);
                if (($t1 >= $st && $t1 < $en) || ($t2 > $st && $t2 <= $en) || ($st >= $t1 && $st < $t2) || ($en > $t1 && $en <= $t2) || ($t2 > date_timestamp_get($end))) {
//                    echo " inside break";
                    $this->log()->addStoreWiseLog('inside break', $this->getStoreId());

                    $booked_flag = true;
                    $booked_endtime = $en_datetime;
                    break;
                }

            }

            if (!$booked_flag) {
                $slotArray[] = array('id' => $piercer_id, 'start' => date("H:i", $t1), 'end' => date("H:i", $t2));
                $i->modify("+" . $slot_time . " minutes");

            } else {
                if ($booked_endtime) {
                    if ($t2 < date_timestamp_get($end)) {
                        $newStart = $booked_endtime;
                        $i = $newStart;
                        $i->modify("+1 minutes");
                        $booked_endtime = null;
                    } else {
                        $i->modify("+" . $slot_time . " minutes");
                    }
                } else {
                    $i->modify("+" . $slot_time . " minutes");
                }
            }

        }

        return $slotArray;
    }

    private function formatBreakTime($time, $s = null)
    {
        $datetime = date("Y-m-d " . $time . ":00", strtotime($this->getRequestedDate()));
        if ($s == "end") {
            $dateObj = DateTime::createFromFormat('Y-m-d H:i', date("Y-m-d H:i", strtotime($datetime)));
            $dateObj->modify("-1 minutes");
            $datetime = $dateObj->format('Y-m-d H:i:s');
        }
        return $datetime;
    }

    private function formatWorkTime($time, $s = null)
    {
        /*temporary break added for end time 20:00 and 20:30     note:Must remove after bonmarche popup*/
        $this->addBuffer($time);

        $datetime = date("Y-m-d " . $time . ":00", strtotime($this->getRequestedDate()));
        return $datetime;
    }

    private function addBuffer(&$time)
    {
         $timeChange=array("20:00"=>"19:45","20:30"=>"20:15");

         if(array_key_exists($time,$timeChange))
             $time=$timeChange[$time];
    }

    private function getRequestedDate()
    {
        $currentStoreDate = date("m/d/Y", $this->helper()->getCurrentDatetime($this->getStoreId()));

        if (!empty($this->getRequest()->getParam('date')))
            $date = date("m/d/Y", strtotime($this->getRequest()->getParam('date')));
        else
            $date = date("m/d/Y", $this->helper()->getCurrentDatetime($this->getStoreId()));

        if ($date == $currentStoreDate)
            $this->currentDateFlag = true;
        else
            $this->currentDateFlag = false;

        if (strtotime($this->getRequest()->getParam('date')) < strtotime($currentStoreDate))
            return false;


        $this->log()->addStoreWiseLog('requested date:' . $date, $this->getStoreId());

        return $date;
    }

    private function getSlotTime()
    {
        return $this->getRequest()->getParam('slottime');
    }

    private function validatePiercerStartTime($time)
    {
        if ($this->currentDateFlag) {
            $currentTime = date("H:i", $this->helper()->getCurrentDatetime($this->getStoreId()));
            if (strtotime($currentTime) > strtotime($time))
                return $this->roundToQuarterHour($currentTime);
            else
                return $time;
        } else {
            return $time;
        }
    }

    private function roundToQuarterHour($time)
    {
        $time = strtotime($time);
        $round = 10 * 60;
        $rounded = ceil($time / $round) * $round;
        return date("H:i", $rounded);
    }

    private function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }

        usort($temp_array, function ($a, $b) use ($key) {
            return $a[$key] <=> $b[$key];
        });

        return $temp_array;
    }

    private function notify()
    {
        return Mage::helper('appointments/notification');
    }

    private function getModifyDecryptedApptId()
    {
        if ($this->getUser() == "admin")
            return $this->getAppId();
        else
            return explode("-", Mage::getModel('core/encryption')->decrypt($this->getAppId()))[0];
    }

    private function getModifyDecryptedCustId()
    {
        if ($this->getUser() == "admin")
            return "admin";

        else
            return explode("-", Mage::getModel('core/encryption')->decrypt($this->getAppId()))[1];

    }

    private function getUser()
    {
        return $this->getRequest()->getParam('user');
    }


    public function subscribeAction()
    {
        if (isset($_POST['email'])) {
            $response = '';
            $email = $_POST['email'];
            try {
                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                $response = ['status' => 'OK', 'msg' => 'Thank you for your subscription.', 'sta' => $status,];
            } catch (Exception $exception) {
                Mage::log('ERROR SUBSCRIBE ' . $exception->getMessage(), Zend_Log::DEBUG, 'appointment_la.log', true);
                $response = ['status' => 'ERROR', 'msg' => 'Sorry there is problem in subscription.', 'sta' => $status,];
            }
        } else {
            $response = ['status' => 'ERROR', 'msg' => 'Please enter your Email',];
        }
        $remarkety = Mage::getModel('mgconnector/observer');
        $remarkety->makeRequest('customers/create', array('email' => $email, 'tags' => array("Paris_Popup"), 'accepts_marketing' => true));
        Mage::log('Remarkety : ' . $email, Zend_Log::DEBUG, 'popup_remarkety.log', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function ajaxSupportDetailsAction()
    {
        $storeid = $this->getRequest()->getParam('storeid');

        $result['message'] = $this->helper()->getSupportMessage($storeid);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

    }


    function get_client_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) // check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) // to check ip is pass
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
    private function getAppointmentStoreMapping()
    {
        return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    }



    /*------------------code to queue appointment request----------------------------*/
    private function read()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_read');
    }
    private function write()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_write');
    }
    private function getAppFlag()
    {
        $results = $this->read()->fetchAll('SELECT `flag` FROM allure_appointment_flag');
        return $results[0]['flag'];
    }
    private function enableAppFlag()
    {
        $this->write()->query("UPDATE `allure_appointment_flag` SET flag = 1");

    }
    private function disableAppFlag()
    {
        $this->write()->query("UPDATE `allure_appointment_flag` SET flag = 0");
    }
    private function taskStart()
    {
        $rand_value = rand(10000, 1000000);
        usleep($rand_value);
        while ($this->getAppFlag()==1) {
            usleep(rand(1000000, 2000000));
        }
        $this->enableAppFlag();
//        Mage::log("task start----------".date('H:i:s'), Zend_Log::DEBUG, 'adi.log', true);

    }
    private function taskEnd()
    {
//        Mage::log("task end----------".date('H:i:s'), Zend_Log::DEBUG, 'adi.log', true);
        $this->disableAppFlag();
    }

    public function testAction()
    {
        $this->taskStart();

        $this->taskEnd();
    }



}
