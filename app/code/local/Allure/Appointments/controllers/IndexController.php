<?php
class Allure_Appointments_IndexController extends Mage_Core_Controller_Front_Action{

    public function modifyAction ()
    {
        if($this->appointment()->getSpecialStore()==0):
        if(!$this->getAppCustomers()->getSize()):
           $this->setCustomer();
           $this->setAppointment();
        endif;
        endif;

            Mage::app()->getResponse()
                ->setRedirect($this->getRedirectionUrl(), 301)
                ->sendResponse();

    }

    private function setCustomer()
    {
        $app=$this->appointment();
        $customer['appointment_id']=$app->getId();
        $customer['firstname']=$app->getFirstname();
        $customer['lastname']=$app->getLastname();
        $customer['email']=$app->getEmail();
        $customer['phone']=$app->getPhone();
        $customer['checkup']=0;
        $customer['piercing']=1;
        $customer['piercing']=1;
        $customer['sms_notification']=($app->getNotificationPref()==2)? 1 : 0;
        $customer['language_pref']='en';
        try {
            Mage::getModel('appointments/customers')->addData($customer)->save();
        }catch (Exception $e)
        {
            return false;
        }

        return true;
    }
    private function setAppointment()
    {
        $app=$this->appointment();
        try {
        $app->setSpecialStore(2);
        $app->setLanguagePref('en');

            $app->save();
        }
        catch(Exception $e)
        {
             return false;
        }
        return true;
    }
    private function getAppCustomers()
    {
        $appointment_id = $this->getAppId();

        $collection = Mage::getModel('appointments/customers')->getCollection();
        $collection->addFieldToFilter('appointment_id', $appointment_id);

        return $collection;
    }

    private function getRedirectionUrl()
    {
        if($this->isAdmin())
            return Mage::getBaseUrl()."appointments/book/modify/user/admin/id/" . $this->getAppId();

        $customer['appointment_id']='not_found';
        $customer['id']='not_found';

        if($this->getAppCustomers()->getSize())
            $customer=$this->getAppCustomers()->getFirstitem();

        return $this->helper("notification")->getModifyLink($customer);
    }
    private function helper($name=null)
    {
        if(!$name)
            $name="data";

        return Mage::helper("appointments/".$name);
    }

    private function appointment()
    {
        return Mage::getModel('appointments/appointments')->load($this->getAppId());
    }
    private function getAppId()
    {
        return $this->getRequest()->getParam('id');
    }
    private function isAdmin()
    {
        if($this->getRequest()->getParam('user')=="admin");
            return true;

        return false;
    }

}
