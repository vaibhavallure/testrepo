<?php
class Teamwork_Universalcustomers_Model_Observer extends Mage_Core_Model_Abstract
{
    protected $_uc, $_svs;
    const OBSERVER_IGNORE_PRODUCT_DISPATCH = 'uc_dispatch_product';

    public function __construct()
    {
        $this->_uc = Mage::getModel('teamwork_universalcustomers/universalcustomers');
        $this->_svs = Mage::getModel('teamwork_universalcustomers/svs');
    }
    
    public function onCustomerSave($observer)
    {
        $isTeamworkCustomer = Mage::getSingleton("core/session")->getIsTeamworkCustomer();
        if($isTeamworkCustomer){
            return ;
        }
        
        if( !Mage::registry(self::OBSERVER_IGNORE_PRODUCT_DISPATCH) && !Mage::helper('teamwork_universalcustomers')->skipCustomer($observer['customer']) )
        {
            $customerData = $this->_uc->prepareCustomerDataForSvs( $observer['customer'], null);
            if(!empty($customerData))
            {
                if( empty($observer['customer'][Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid]) )
                {
                    Mage::register(self::OBSERVER_IGNORE_PRODUCT_DISPATCH, true);
                    $observer['customer'][Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid] = $this->_svs->registerCustomer($customerData);
                }
                else
                {
                    $customerData['customer_id'] = $observer['customer'][Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid];
                    $this->_svs->updateCustomer( $customerData );
                }
            }
        }
    }

    public function onCustomerAfterSave($observer)
    {
        $isTeamworkCustomer = Mage::getSingleton("core/session")->getIsTeamworkCustomer();
        if($isTeamworkCustomer){
            return ;
        }
        
        if( !$observer['customer']->getOrigData() && !Mage::registry(self::OBSERVER_IGNORE_PRODUCT_DISPATCH) && !Mage::helper('teamwork_universalcustomers')->skipCustomer($observer['customer']) )
        {
            $customerData = $this->_uc->prepareCustomerDataForSvs( $observer['customer'] );
            $customerData['customer_id'] = $observer['customer'][Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid];
            $this->_svs->updateCustomer( $customerData  );
        }
    }

    public function onAddressSave($observer)
    {
        $isTeamworkCustomer = Mage::getSingleton("core/session")->getIsTeamworkCustomer();
        if($isTeamworkCustomer){
            return ;
        }
        
        if( !empty($observer['customer_address']['customer_id']) && !Mage::registry(self::OBSERVER_IGNORE_PRODUCT_DISPATCH) && !Mage::helper('teamwork_universalcustomers')->skipCustomer($observer['customer']) )
        {
            $customer = Mage::getModel('customer/customer')->load($observer['customer_address']['customer_id']);
            $customerData = $this->_uc->prepareCustomerDataForSvs( $customer, $observer['customer_address'] );

            if(!empty($customerData))
            {
                $customerData['customer_id'] = $customer[Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid];
                $this->_svs->updateCustomer( $customerData );
            }
        }
    }
    
    public function onAddressDelete($observer)
    {
        if( empty($observer['customer_address']['customer_id']) && !Mage::registry(self::OBSERVER_IGNORE_PRODUCT_DISPATCH) && !Mage::helper('teamwork_universalcustomers')->skipCustomer($observer['customer']) )
        {
            if( $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId() )
            {
                $observer['customer_address']->setData('_deleted',true);
                $customer = Mage::getModel('customer/customer')->load($customer_id);
                $customerData = $this->_uc->prepareCustomerDataForSvs( $customer, $observer['customer_address'] );
                
                if(!empty($customerData))
                {
                    $customerData['customer_id'] = $customer[Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid];
                    $this->_svs->updateCustomer( $customerData  );
                }
            }
        }
    }
    
    public function addWebOrderData($observer)
    {
        $billing = $observer['order']->getBillingAddress();
        $shipping = $observer['order']->getShippingAddress();
        
        if( $billing && ($addressId = $billing->getCustomerAddressId()) && ($ucAddressInfo = Mage::helper('teamwork_universalcustomers')->getUcAddressInfoByAddressId($addressId)) )
        {
            $observer['weborder']['BillAddressId'] = $ucAddressInfo['uc_id'];
            $observer['weborder']['BillAddressType'] = $ucAddressInfo['uc_type'];
        }
        
        if( $shipping && ($addressId = $shipping->getCustomerAddressId()) && ($ucAddressInfo = Mage::helper('teamwork_universalcustomers')->getUcAddressInfoByAddressId($addressId)) )
        {
            $observer['weborder']['ShipAddressId'] = $ucAddressInfo['uc_id'];
            $observer['weborder']['ShipAddressType'] = $ucAddressInfo['uc_type'];
        }
        
        if( $observer['order']->getCustomerId() )
        {
            $observer['weborder']['CustomerId'] = Mage::helper('teamwork_universalcustomers')->getUcGuidByCustomerId($observer['order']->getCustomerId());
        }
    }
    
    public function beforeCheckout($observer)
    {
        $customer = $observer['quote']->getCustomer();
        if( ($observer['quote']->getCheckoutMethod() != Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST) && empty($customer[Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid]) )
        {
            if( !empty($customer['customer_email']) && $this->_svs->checkCustomer($customer['customer_email']) )
            {
                throw new Mage_Core_Exception( Mage::helper('customer')->__('The email address you have entered is already registered.') );
            }
        }
        
        if( !Mage::registry(self::OBSERVER_IGNORE_PRODUCT_DISPATCH) )
        {
            Mage::register(self::OBSERVER_IGNORE_PRODUCT_DISPATCH, true);
        }
    }
    
    public function checkoutCustomerSave($observer)
    {
        if( $observer['quote']->getCheckoutMethod() != Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST )
        {
            Mage::unregister(self::OBSERVER_IGNORE_PRODUCT_DISPATCH);
            $observer['customer'] = $observer['quote']->getCustomer();
            
            $register = false;
            if( empty($observer['customer'][Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid]) )
            {
                $register = true;
            }
            
            $this->onCustomerSave($observer);
            if($register)
            {
                Mage::getResourceSingleton('customer/customer')->saveAttribute($observer['customer'], Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid);
            }
        }
    }
}