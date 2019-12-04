<?php
/**
 * One page checkout status
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Allure_MultiCheckout_Block_Checkout_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    /**
     * Initialize shipping address step
     */
    protected function _construct()
    {
        parent::_construct();
        $this->getCheckout()->setStepData('shipping', array(
            'label'     => Mage::helper('checkout')->__('Billing & Shipping Address'),
            'is_show'   => $this->isShow()
        ));
        //if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('shipping', 'allow', true);
        //}
        
    }
}
