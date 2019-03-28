<?php
class Teamwork_Universalcustomers_Model_Customer extends Mage_Customer_Model_Customer
{
    public function authenticate($login, $password)
    {
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$this->validatePasswordThroughSvs($login, $password)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }
        Mage::dispatchEvent('customer_customer_authenticated', array(
           'model'    => $this,
           'password' => $password,
        ));
        return true;
    }

    public function validatePasswordThroughSvs($login, $password)
    {
        if( $profile = Mage::getModel('teamwork_universalcustomers/svs')->loginCustomer($login, $password) )
        {
            Mage::getModel('teamwork_universalcustomers/universalcustomers')->updateCustomerAfterLogin($profile, $password);
            $this->setWebsiteId( Mage::app()->getStore()->getWebsiteId() );
            parent::loadByEmail($profile['email']);
            return true;
        }
        return false;
    }

    public function loadByTwUcGuid($customer_id)
    {
        $collection = $this->getCollection()
            ->addAttributeToSelect( Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid )
        ->addAttributeToFilter( Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid, $customer_id );
        
        if(Mage::getSingleton('customer/config_share')->isWebsiteScope())
        {
            $collection->addAttributeToFilter('website_id', (int)Mage::app()->getStore()->getWebsiteId());
        }
        $customers = $collection->load();

        if( $entityId = $customers->getFirstItem()->getEntityId() )
        {
            return Mage::getModel('customer/customer')->load($entityId);
        }
        return $this;
    }

    public function loadByEmail($customerEmail)
    {
        $trace = debug_backtrace( false );
        if( in_array($trace[1]['function'], array('forgotPasswordPostAction', 'forgotPasswordAction')) )
        {
            $svs = Mage::getModel('teamwork_universalcustomers/svs');
            $customer_id = $svs->checkCustomer($customerEmail);

            if( !empty($customer_id) )
            {
                $profile = $svs->getCustomer($customer_id); //TODO CHECK $customerData on empty?
                Mage::getModel('teamwork_universalcustomers/universalcustomers')->updateCustomerAfterLogin($profile);
            }
        }
        return parent::loadByEmail($customerEmail);
    }
    
    public function hashPassword($password, $salt = null)
    {
        $args = func_get_args();
        if( count($args) == 1 )
        {
            return parent::hashPassword($password);
        }
        else
        {
            if( Mage::getModel('teamwork_universalcustomers/svs')->loginCustomer($this->getEmail(), $password) )
            {
                return $this->getPasswordHash();
            }
        }
        return false;
    }
}