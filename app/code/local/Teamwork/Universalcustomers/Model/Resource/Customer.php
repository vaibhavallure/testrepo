<?php
class Teamwork_Universalcustomers_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer
{
    public function changePassword(Mage_Customer_Model_Customer $customer, $newPassword)
    {
        // TODO switch off for Enterprise
        $customerData = array
        (
            'customer_id'   => $customer[Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid],
            'password'      => $newPassword,
        );

        Mage::getModel('teamwork_universalcustomers/svs')->updateCustomer($customerData,true);

        $customer->setPassword($newPassword);
        $this->saveAttribute($customer, 'password_hash');
        return $this;
    }
}