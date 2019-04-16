<?php
class Teamwork_Universalcustomers_Model_Universalcustomers extends Mage_Core_Model_Abstract
{
    public static $twUcGuid = 'tw_uc_guid';
    protected $_typeConversion = array('gender' => 'int', 'is_subscribed' => 'boolean');
    protected $_typeDateConversion = array('dob' => 'Y-m-d');
    protected $_dependency = array('password' => 'password_hash');
    protected $_hardDependency = array('entity_id' => 'is_subscribed');

    protected $_staticElements;

    public function __construct()
    {
        $this->_staticElements = (array)Mage::getConfig()->getNode('teamwork_universalcustomers/static_customer_fields');
        $this->_ignoreStaticElementsForIn = (array)Mage::getConfig()->getNode('teamwork_universalcustomers/ignore_static_customer_for_in_fields');
    }

    public function addStaticElements($element)
    {
        $this->_staticElements[] = $element;
    }

    public function prepareCustomerDataForSvs($customer, $address=null)
    {
        $customerData = array();
        foreach($this->_staticElements as $element)
        {
            if(isset($customer[$element]))
            {
                $customerData[$element] = $this->_prepare($element, $customer);
            }
            elseif(array_key_exists($element, $this->_dependency) && !empty($customer[$this->_dependency[$element]]) && $customer->getData($this->_dependency[$element]) != $customer->getOrigData($this->_dependency[$element]))
            {
                $customerData[$this->_dependency[$element]] = $this->_prepare($this->_dependency[$element], $customer);
            }

            if( array_key_exists($element, $this->_hardDependency) && empty($customer[$element]) && empty($customer[$this->_hardDependency[$element]]) )
            {
                $customerData[$this->_hardDependency[$element]] = false;
            }
        }

        $addressModel = Mage::getModel('teamwork_universalcustomers/address');
        $addressModel->rebuildAddressesForSvs($customerData, $customer, $address);

        return $customerData;
    }

    public function updateCustomerAfterLogin($profile, $password=null)
    {
        Mage::register(Teamwork_Universalcustomers_Model_Observer::OBSERVER_IGNORE_PRODUCT_DISPATCH, true);
        
        $customerData = array();
        foreach($this->_staticElements as $element)
        {
            if( isset($profile[$element]) && in_array($element, $this->_ignoreStaticElementsForIn) === FALSE)
            {
                $customerData[$element] = $this->_prepare($element, $profile);
            }
        }
        
        $customer = Mage::getModel('customer/customer')->setWebsiteId( Mage::app()->getStore()->getWebsiteId() )->loadByTwUcGuid( $profile['customer_id'] );


        /*allure code start--------------------------------------------------------------------------------------*/
        /*-------------------------------set tw_uc_guid if not present-------------------------------------------------*/
        if(!$customer->getId())
        {
            Mage::log("customer not found for this guid =>".$profile['customer_id'], Zend_Log::DEBUG, "tw_guid_changes.log", true);
            $customer=Mage::getModel("customer/customer")->setWebsiteId( Mage::app()->getStore()->getWebsiteId() )->loadByEmail($profile['email']);
            Mage::log("searching customer by email id =>".$profile['email'], Zend_Log::DEBUG, "tw_guid_changes.log", true);
            if($customer->getId()) {

                    Mage::log("customer found for email id =>" . $profile['email'] . " customer id=> " . $customer->getId(), Zend_Log::DEBUG, "tw_guid_changes.log", true);
                    Mage::log(" " . $profile['email'] . " customer id=> " . $customer->getId() . " old guid => " . $customer->getTwUcGuid(), Zend_Log::DEBUG, "tw_guid_changes.log", true);
                    Mage::log("customer magento_entity_id=>".$customer->getId()." tw_entity_id =>".$profile['entity_id'], Zend_Log::DEBUG, "tw_guid_changes.log", true);

                        $customer->setTwUcGuid($profile['customer_id']);
                        $customer->setTeamworkCustomerId($profile['customer_id']);
            }
        }
        /*--------------------------------------------------------------------------------------------------*/
        /*allure code end------------------------------------------------------------------------------------*/



        $addressModel = Mage::getModel('teamwork_universalcustomers/address');
        $addressModel->updateAddressesAfterLogin($customerData, $customer, $profile);
        
        if( !$customer->getId() )
        {
            $customerData['password'] = $password;
            $customerData[self::$twUcGuid] = $profile['customer_id'];
        }
        
        foreach($customerData as $attribute => $value)
        {
            if(!is_array($value))
            {
                $customer->setData( $attribute, $value );
            }
        }
        $customer->setImportMode(true);
        $customer->save();
    }

    protected function _prepare($element, $object)
    {
        if(isset($object[$element]))
        {
            if(array_key_exists($element, $this->_typeConversion))
            {
                $setValue = $object[$element];
                settype($setValue, $this->_typeConversion[$element]);
                $object[$element] = $setValue;
            }
            if(array_key_exists($element, $this->_typeDateConversion) && !empty($object[$element]))
            {
               $object[$element] = date($this->_typeDateConversion[$element], strtotime($object[$element]));
            }
            if($object[$element] === "")
            {
                $object[$element] = null;
            }
            $data = $object[$element];
            if(is_string($data))
            {
                $data = trim($data);
            }
        }
        return (!empty($data) || (isset($data) && is_bool($data))) ? $data : null;
    }
}