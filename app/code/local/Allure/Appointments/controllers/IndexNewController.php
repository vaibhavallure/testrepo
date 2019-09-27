<?php
class Allure_Appointments_IndexNewController extends Mage_Core_Controller_Front_Action{

    /*save new */

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


        if ($this->helper()->validateSlotBeforeBookAppointment($post_data)) {

            $this->log()->addStoreWiseLog('Err => Sorry This Slot Has Been Already Taken', $post_data['store_id']);

            $piercer = $this->helper()->checkIfAnotherPiercerAvailable($post_data);

            $this->log()->addStoreWiseLog('checking for another piercer for requested slot', $post_data['store_id']);


            if ($piercer['success']) {
                $post_data['piercer_id'] = $piercer['p_id'];
                $this->log()->addStoreWiseLog('New piercer assigned new_piercer_id=' . $piercer['p_id'], $post_data['store_id']);

            } else {
                $this->log()->addStoreWiseLog('no piercer available for this slot', $post_data['store_id']);
                Mage::getSingleton('core/session')->setSlotInvalid("true");
                $this->_redirectReferer();
                return;
            }
        }

        try {

            $model = $this->appointment()->addData($post_data)->save();

            foreach ($post_data['customer'] as $customer) {
                $customer['appointment_id'] = $model->getId();
                $customer['sms_notification'] = ($customer['noti_sms'] == '2') ? 1 : 0;
                $customer['piercing'] = 1;

                if ($customer['piercing'] > 0)
                    $isPiercingAppointment = true;

                $customer['checkup'] =0;
                $customer['language_pref'] ='en';


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

            if ($this->notify()->sendEmailNotification($model, 'release'))
                $this->log()->addStoreWiseLog('Notified by email type=>release', $post_data['store_id']);

            if ($this->notify()->sendSmsNotification($model, 'release'))
                $this->log()->addStoreWiseLog('Notified By SMS(if selected) type=>release', $post_data['store_id']);


            /*---------------------------------notification section end--------------------------------*/


             $this->getResponse()->setRedirect(Mage::getUrl("*/*/*", array('_secure' => true)));
             $this->_redirectReferer();

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


//        $this->log()->addStoreWiseLog($this->baseurl() . '' . http_build_query($post_data), $post_data['store_id']);
        $post_data['app_date'] = date('m/d/Y', strtotime($post_data['app_date']));
        $post_data['appointment_start'] = strtotime($post_data['app_date'] . ' ' . $post_data['appointment_start']);
        $post_data['appointment_start'] = date('Y-m-d H:i:s', $post_data['appointment_start']);

        $post_data['appointment_end'] = $post_data['app_date'] . " " . $post_data['appointment_end'];
        $post_data['appointment_end'] = strtotime($post_data['appointment_end']);
        $post_data['appointment_end'] = date('Y-m-d H:i:59', $post_data['appointment_end']);

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

    private function appointment()
    {
        return Mage::getModel('appointments/appointments');
    }
    private function log()
    {
        return Mage::helper("appointments/logs");
    }
    private function notify()
    {
        return Mage::helper('appointments/notification');
    }
    private function getAppId()
    {
        return $this->getRequest()->getParam('id');
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

    private function helper(){
        return Mage::helper("appointments/data");
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

}
