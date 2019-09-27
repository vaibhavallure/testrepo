<?php
/**
 * 
 * @author allure
 *
 */
require_once ('app/code/core/Mage/Checkout/controllers/MultishippingController.php');
class Allure_RedesignCheckout_MultishippingController extends Mage_Checkout_MultishippingController
{
    
    public function changeShippingAddressAction()
    {
        $requestData = $this->getRequest()->getParams();
        try {
            $this->_getCheckout()->changeShippingAddress($requestData);
        }catch (Exception $e){
            $this->_getCheckoutSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/shipping');
    }
    
    /**
     * Multishipping checkout shipping information page
     */
    public function shippingAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }
        
        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES)) {
            $this->_redirect('*/*/addresses');
            return $this;
        }
        
        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
            );
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
    
    /**
     * Multishipping checkout after the shipping page
     */
    public function shippingPostAction()
    {
        if ($this->isFormkeyValidationOnCheckoutEnabled() && !$this->_validateFormKey()) {
            $this->_redirect('*/*/shipping');
            return;
        }
        $shippingMethods = $this->getRequest()->getPost('shipping_method');
        try {
            Mage::dispatchEvent(
                'checkout_controller_multishipping_shipping_post',
                array('request'=>$this->getRequest(), 'quote'=>$this->_getCheckout()->getQuote())
                );
            Mage::dispatchEvent(
                'checkout_controller_multishipping_shipping_signature_post',
                array('request'=>$this->getRequest(), 'quote'=>$this->_getCheckout()->getQuote())
                );
            $this->_getCheckout()->setShippingMethods($shippingMethods);
            $this->_getState()->setActiveStep(
                Allure_RedesignCheckout_Model_Checkout_Type_Multishipping_State::STEP_DELIVERY_OPTION
                );
            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
                );
            $this->_redirect('*/*/delivery');
        }
        catch (Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/shipping');
        }
    }
    
    public function deliveryAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }
        
        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING)) {
            $this->_redirect('*/*/shipping');
            return $this;
        }
        
        $this->_getState()->setActiveStep(
            Allure_RedesignCheckout_Model_Checkout_Type_Multishipping_State::STEP_DELIVERY_OPTION
            );
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
    
    public function deliveryPostAction()
    {
        if ($this->isFormkeyValidationOnCheckoutEnabled() && !$this->_validateFormKey()) {
            $this->_redirect('*/*/delivery');
            return;
        }
        try {
            $this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
                );
            $this->_getState()->setCompleteStep(
                Allure_RedesignCheckout_Model_Checkout_Type_Multishipping_State::STEP_DELIVERY_OPTION
                );
            $this->_redirect('*/*/overview');
        }
        catch (Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/delivery');
        }
    }
    
    /**
     * Multishipping checkout after the overview page
     */
    public function overviewPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_forward('backToAddresses');
            return;
        }
        
        if (!$this->_validateMinimumAmount()) {
            return;
        }
        
        try {
            Mage::dispatchEvent(
                'checkout_controller_multishipping_overview_giftmessage_post',
                array('request'=>$this->getRequest(), 'quote'=>$this->_getCheckout()->getQuote())
                );
            /* echo "<pre>";
            print_r($this->getRequest()->getParams());
            die; */
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $this->_getCheckoutSession()->addError($this->__('Please agree to all Terms and Conditions before placing the order.'));
                    $this->_redirect('*/*/billing');
                    return;
                }
            }
            
            $payment = $this->getRequest()->getPost('payment');
            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
            if (isset($payment['cc_number'])) {
                $paymentInstance->setCcNumber($payment['cc_number']);
            }
            if (isset($payment['cc_cid'])) {
                $paymentInstance->setCcCid($payment['cc_cid']);
            }
            $this->_getCheckout()->createOrders();
            /* $this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SUCCESS
                ); */
            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW
                );
            $this->_getCheckout()->getCheckoutSession()->clear();
            $this->_getCheckout()->getCheckoutSession()->setDisplaySuccess(true);
            $this->_redirect('*/*/success');
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if ( !empty($message) ) {
                $this->_getCheckoutSession()->addError($message);
            }
            $this->_redirect('*/*/billing');
        } catch (Mage_Checkout_Exception $e) {
            Mage::helper('checkout')
            ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckout()->getCheckoutSession()->clear();
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/cart');
        }
        catch (Mage_Core_Exception $e) {
            Mage::helper('checkout')
            ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')
            ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($this->__('Order place error.'));
            $this->_redirect('*/*/billing');
        }
    }
}
