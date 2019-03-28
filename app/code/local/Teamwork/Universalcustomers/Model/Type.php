<?php
class Teamwork_Universalcustomers_Model_Type extends Mage_Core_Model_Abstract
{
    protected $_prefix = 'Magento ';
    protected $_pattern;
    public $addressTypeIncrement = array();
    
    public function __construct()
    {
        $this->_pattern = "/^{$this->_prefix}(\d)+$/";
    }
    
    public function getType($addressCollection, $address)
    {
        if( !$addressCollection instanceof Mage_Customer_Model_Resource_Address_Collection && !empty($address['customer_id']) )
        {
            $addressCollection = Mage::getModel('customer/address')
                ->getCollection()->addAttributeToSelect('*')
            ->addFieldToFilter('parent_id', $address['customer_id']);
        }
        
        foreach($addressCollection as $customerAddress)
        {
            preg_match($this->_pattern, $customerAddress[Teamwork_Universalcustomers_Model_Address::$twUcAddressType], $matches);
            if(!empty($matches[1]))
            {
                $this->addressTypeIncrement[] = $matches[1];
            }
        }
        
        $increment = !empty($this->addressTypeIncrement) ? $this->getTypeIncrement($this->addressTypeIncrement) : 1;
        $type = "{$this->_prefix}{$increment}";
        
        $address->setData(Teamwork_Universalcustomers_Model_Address::$twUcAddressType, $type);
        return $type;
    }
    
    public function getTypeIncrement($addressTypeIncrement)
    {
        $comparison = range(1, max($addressTypeIncrement));
        if( $diff = array_diff($comparison, $addressTypeIncrement) )
        {
            return min($diff);
        }
        else
        {
            return max($addressTypeIncrement)+1;
        }
    }
}