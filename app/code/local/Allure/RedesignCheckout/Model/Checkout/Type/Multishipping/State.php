<?php
/**
 * 
 * @author allure
 *
 */
class Allure_RedesignCheckout_Model_Checkout_Type_Multishipping_State extends Mage_Checkout_Model_Type_Multishipping_State
{
    const STEP_DELIVERY_OPTION = "multishipping_delivery_option";
    
    /**
     * Init model, steps
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_steps = array(
            self::STEP_SELECT_ADDRESSES => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Shipping & Billing Address')
            )),
            self::STEP_SHIPPING => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Shipping method')
            )),
            self::STEP_DELIVERY_OPTION => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Delivery Option')
            )),
            self::STEP_BILLING => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Payment')
            )),
            self::STEP_OVERVIEW => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Order Review')
            )),
            /* self::STEP_SUCCESS => new Varien_Object(array(
                'label' => Mage::helper('checkout')->__('Order Success')
            )), */
        );
        
        foreach ($this->_steps as $step) {
            $step->setIsComplete(false);
        }
        
        $this->_checkout = Mage::getSingleton('checkout/type_multishipping');
        $this->_steps[$this->getActiveStep()]->setIsActive(true);
    }
}
