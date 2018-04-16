<?php
class Teamwork_Universalcustomers_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    public function validate()
    {
        $errors = parent::validate();

        if( $this->getShouldIgnoreValidation() )
        {
            return true;
        }

        if( !is_array($errors) )
        {
            $errors = array();
        }

        $this->_addCustomValidation($errors);
        if( empty($errors) )
        {
            return true;
        }

        return implode("\n", $errors);
    }

    protected function _addCustomValidation(&$errors)
    {
        $checkoutMethod = $this->getQuote()->getCheckoutMethod();
        if( empty($checkoutMethod) )
        {
            if( $this->getQuote()->isAllowedGuestCheckout() )
            {
                $checkoutMethod = Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST;
            }
            else
            {
                $checkoutMethod = Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER;
            }
        }
        
        if($this->getQuote() && !$this->getQuote()->getCustomerId() && $checkoutMethod != Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST )
        {
            $request = Mage::app()->getRequest()->getParam('billing');
            $phone = !empty($request['telephone']) ? trim($request['telephone']) : null;
            // $phone = $this->getTelephone();

            $email = !empty($request['email']) ? trim($request['email']) : null;

            $svs = Mage::getModel('teamwork_universalcustomers/svs');

            /*if( !empty($phone) && $svs->checkCustomerPhone($phone) )
            {
                $errors[] = Mage::helper('customer')->__('There is already a customer registered using that phone number.');
            }*/

            if( !empty($email) && $svs->checkCustomer($email) )
            {
                $errors[] = Mage::helper('customer')->__('There is already a customer registered using this email address. Please login.');
            }
        }
    }
}
