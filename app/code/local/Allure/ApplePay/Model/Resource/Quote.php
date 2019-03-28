<?php
class Allure_ApplePay_Model_Resource_Quote extends Mage_Sales_Model_Resource_Quote
{
    public function loadByApplePayCustomerIdX($quote, $customerId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $this->_getLoadSelect('customer_id', $customerId, $quote)
        ->where('is_active = ?', 1)
        ->where('is_applepay = ?', 1)
        ->order('updated_at ' . Varien_Db_Select::SQL_DESC)
        ->limit(1);
        
        $customer = Mage::getSingleton('customer/session');
        
        $data    = $adapter->fetchRow($select);
        
        //var_dump($select->__toString());die;
        
        if ($data) {
            $quote->setData($data);
        }
        
        $this->_afterLoad($quote);
        
        return $this;
    }
    
    /**
     * Load quote data by customer identifier
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int $customerId
     * @return Mage_Sales_Model_Resource_Quote
     */
    public function loadByCustomerIdX($quote, $customerId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $this->_getLoadSelect('customer_id', $customerId, $quote)
        ->where('is_active = ?', 1)
        ->where('is_applepay = ?', 0)
        ->order('updated_at ' . Varien_Db_Select::SQL_DESC)
        ->limit(1);
        
        $data    = $adapter->fetchRow($select);
        
        if ($data) {
            $quote->setData($data);
        }
        
        $this->_afterLoad($quote);
        
        return $this;
    }
}