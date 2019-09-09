<?php
class Allure_Appointments_Helper_Counts extends Mage_Core_Helper_Abstract
{
    public function getPiercingCount($piercing_data){

            $customers = $this->getCustomers($piercing_data['id']);
            $customers = $customers->getData();
            $piercing = 0;
            foreach ($customers as $customer) {

                if (isset($customer['piercing'])) {
                    $piercing += $customer['piercing'];
                }
            }
            if ($piercing > 0):
                return $piercing;
            else:
                return '';
            endif;

    }
    public function getCheckupCount($piercing_data){


            $customers = $this->getCustomers($piercing_data['id']);
            $customers = $customers->getData();
            $checkup = 0;
            foreach ($customers as $customer) {

                if (isset($customer['checkup'])) {
                    $checkup += $customer['checkup'];
                }
            }
            if ($checkup > 0):
                return $checkup;
            else:
                return '';
            endif;

    }

    public function getCustomers($appointment_id){
        $appointment_customer = Mage::getModel('appointments/customers')->getCollection()
            ->addFieldToFilter('appointment_id' ,array('eq'=>$appointment_id));

        return $appointment_customer;
    }
}