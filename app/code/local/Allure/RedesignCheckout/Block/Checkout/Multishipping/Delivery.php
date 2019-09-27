<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Block_Checkout_Multishipping_Delivery
extends Mage_Sales_Block_Items_Abstract
{
    /**
     * Get multishipping checkout model
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/type_multishipping');
    }
    
    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(Mage::helper('checkout')->__('Delivery') . ' - ' . $headBlock->getDefaultTitle());
        }
        return parent::_prepareLayout();
    }
    
    public function getAddresses()
    {
        return $this->getCheckout()->getQuote()->getAllShippingAddresses();
    }
    
    public function getAddressCount()
    {
        $count = $this->getData('address_count');
        if (is_null($count)) {
            $count = count($this->getAddresses());
            $this->setData('address_count', $count);
        }
        return $count;
    }
    
    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/deliveryPost');
    }
}

