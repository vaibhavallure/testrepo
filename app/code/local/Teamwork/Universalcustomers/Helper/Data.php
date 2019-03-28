<?php
class Teamwork_Universalcustomers_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getRouteName($path)
    {
        $originalPath = explode('/',trim($path,'/'));
        $originalPath[1] = empty($originalPath[1]) ? 'index' : $originalPath[1];
        $originalPath[2] = empty($originalPath[2]) ? 'index' : $originalPath[2];
        return "{$originalPath[0]}/{$originalPath[1]}/{$originalPath[2]}";
    }
    
    public function generateGuid($namespace='')
    {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : '';
        $data .= !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data .= !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $data .= !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $data .= !empty($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '';
        $hash = strtolower(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = '' .
                substr($hash, 0, 8) .
                '-' .
                substr($hash, 8, 4) .
                '-' .
                substr($hash, 12, 4) .
                '-' .
                substr($hash, 16, 4) .
                '-' .
                substr($hash, 20, 12) .
                '';
        return strtoupper($guid);
    }
    
    public function skipCustomer($customer)
    {
        return (isset($customer['website_id']) && $customer['website_id'] == 0 && Mage::getSingleton('customer/config_share')->isWebsiteScope());
    }
    
    public function checkUpdateNeeded($customerData)
    {
        $flag = false;
        $addressesKeys = !empty($customerData['addresses']) ? array_keys($customerData['addresses']) : array();
        if( Mage::registry(Teamwork_Universalcustomers_Model_Svs::LAST_SVS_SAVED_ADDRESSES) )
        {
            $savedAddresses = unserialize(Mage::registry(Teamwork_Universalcustomers_Model_Svs::LAST_SVS_SAVED_ADDRESSES));
            foreach($addressesKeys as $addressesKey)
            {
                if( !in_array($addressesKey, $savedAddresses) )
                {
                    $flag = true;
                    $savedAddresses[] = $addressesKey;
                    Mage::unregister(Teamwork_Universalcustomers_Model_Svs::LAST_SVS_SAVED_ADDRESSES);
                    Mage::register(Teamwork_Universalcustomers_Model_Svs::LAST_SVS_SAVED_ADDRESSES, serialize($savedAddresses));
                }
            }
        }
        else
        {
            Mage::register(Teamwork_Universalcustomers_Model_Svs::LAST_SVS_SAVED_ADDRESSES, serialize($addressesKeys));
            $flag = true;
        }
        return $flag;
    }
    
    public function getUcGuidByCustomerId($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if($customer)
        {
            return $customer->getData(Teamwork_Universalcustomers_Model_Universalcustomers::$twUcGuid);
        }
    }
    
    public function getUcAddressInfoByAddressId($addressId)
    { 
        $address = Mage::getModel('customer/address')->load($addressId);
        return array(
            'uc_id'     => $address->getData(Teamwork_Universalcustomers_Model_Address::$twUcAddressGuid),
            'uc_type'   => $address->getData(Teamwork_Universalcustomers_Model_Address::$twUcAddressType)
        );
    }
}