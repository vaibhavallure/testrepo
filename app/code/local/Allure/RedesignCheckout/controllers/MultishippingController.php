<?php
/**
 * 
 * @author allure
 *
 */
require_once ('app/code/core/Mage/Checkout/controllers/MultishippingController.php');
class Allure_RedesignCheckout_MultishippingController extends Mage_Checkout_MultishippingController
{
    const WHOLESALE_GROUP_ID = 2;
    
    /**
     * Check customer group id is wholesale & if it is 
     * wholesale then redirect to onepage checkout.
     * @return Mage_Checkout_MultishippingController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if($customerGroupId == self::WHOLESALE_GROUP_ID){
            $this->_redirect("*/onepage");
        }
        return $this;
    }
    
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
    public function shippingAction1()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }
        
        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES)) {
            $this->_redirect('*/*/addresses');
            return $this;
        }
        
        $this->_getCheckout()->removeBackOrderAddresses();
        
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
                Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
                );
            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
                );
            $this->_redirect('*/*/billing');
        }
        catch (Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/shipping');
        }
    }
    
    /**
     * Multishipping checkout after the shipping page
     */
    public function shippingPostAction1()
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
        
        $this->_getCheckout()->removeIsbackOrderedAddress();
        
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
        $deliveryParams = $this->getRequest()->getPost("delivery");
        try {
            $this->_getCheckout()->setCollectRatesFlag(false);
            $this->_getCheckout()->setDeliveryOptions($deliveryParams);
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
    
    public function refreshtotalsAction()
    {
        $this->_getCheckout()->getQuote()->collectTotals()->save();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        $isAjax = $this->getRequest()->getParam('ajax', false);
        $response = array(
            'error' => true,
            'message' => '',
            'disable' => false
        );
        
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCheckout()->getQuote()->getItemsCount()) {
            if (!$isAjax) $this->_redirect('checkout/cart');
            else die(json_encode($response));
            return;
        }
        
        $couponCode = (string)$this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getCheckout()->getQuote()->getCouponCode();
        
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            if (!$isAjax) $this->_redirect('checkout/multishipping/billing');
            else die(json_encode($response));
            return;
        }
        
        try {
            $this->_getCheckout()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getCheckout()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
            ->collectTotals()
            ->save();
            
            if (strlen($couponCode)) {
                if ($couponCode == $this->_getCheckout()->getQuote()->getCouponCode()) {
                    if (!$isAjax) {
                        $this->_getCheckoutSession()->addSuccess(
                            $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                            );
                    } else {
                        $response['error'] = false;
                        $response['message'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                        $response['disable'] = true;
                    }
                } else {
                    if (!$isAjax) {
                        $this->_getCheckoutSession()->addError(
                            $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                            );
                    } else {
                        $response['error'] = true;
                        $response['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                    }
                    
                }
            } else {
                if (!$isAjax) {
                    $this->_getCheckoutSession()->addSuccess($this->__('Coupon code was canceled.'));
                } else {
                    $response['error'] = false;
                    $response['message'] = $this->__('Coupon code was canceled.');
                }
                
            }
            
        } catch (Mage_Core_Exception $e) {
            if (!$isAjax) {
                $this->_getCheckoutSession()->addError($e->getMessage());
            } else {
                $response['error'] = true;
                $response['message'] = $e->getMessage();
            }
        } catch (Exception $e) {
            if (!$isAjax) {
                $this->_getCheckoutSession()->addError($this->__('Cannot apply the coupon code.'));
            } else {
                $response['error'] = true;
                $response['message'] = $this->__('Cannot apply the coupon code.');
            }
            Mage::logException($e);
        }
        
        if (!$isAjax)
            $this->_redirect('checkout/multishipping/billing');
        else
            die(json_encode($response));
    }
    
    public function agreeToUseAction()
    {
        $result = array();
        $q = Mage::getSingleton('giftcards/session')->getActive() ? 0 : 1;
        Mage::getSingleton('giftcards/session')->setActive($q);
        //$result['goto_section'] = 'payment';
        $this->_getCheckout()->getQuote()->collectTotals()->save();
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => '',//$this->_getPaymentMethodsHtml()
        );
        $result['giftcard_section'] = array(
            'html' => $this->_getUpdatedCoupon()
        );
        
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function ajaxActivateGiftCardAction()
    {
        $result = array();
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');
        
        if ($card->getId() && ($card->getCardStatus() == 1)) {
            
            Mage::getSingleton('giftcards/session')->setActive('1');
            $this->_setSessionVars($card);
            $this->_getCheckout()->getQuote()->collectTotals();
            
        } else {
            if($card->getId() && ($card->getCardStatus() == 2)) {
                $result['error'] = $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode));
            } else {
                $result['error'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
            }
        }
        
        //$result['goto_section'] = 'payment';
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => '',//$this->_getPaymentMethodsHtml()
        );
        $result['giftcard_section'] = array(
            'html' => $this->_getUpdatedCoupon()
        );
        
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function ajaxDeActivateGiftCardAction()
    {
        $result = array();
        $oSession = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $cardIds = $oSession->getGiftCardsIds();
        $sessionBalance = $oSession->getGiftCardBalance();
        $newSessionBalance = $sessionBalance - $cardIds[$cardId]['balance'];
        unset($cardIds[$cardId]);
        if(empty($cardIds))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }
        $oSession->setGiftCardBalance($newSessionBalance);
        $oSession->setGiftCardsIds($cardIds);
        
        //$result['goto_section'] = 'payment';
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => '',//$this->_getPaymentMethodsHtml()
        );
        $result['giftcard_section'] = array(
            'html' => $this->_getUpdatedCoupon()
        );
        
        $this->_getCheckout()->getQuote()->collectTotals()->save();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    private function _setSessionVars($card)
    {
        $oSession = Mage::getSingleton('giftcards/session');
        
        $giftCardsIds = $oSession->getGiftCardsIds();
        
        //append applied gift card id to gift card session
        //append applied gift card balance to gift card session
        if (!empty($giftCardsIds)) {
            $giftCardsIds = $oSession->getGiftCardsIds();
            if (!array_key_exists($card->getId(), $giftCardsIds)) {
                $giftCardsIds[$card->getId()] =  array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
                $oSession->setGiftCardsIds($giftCardsIds);
                
                $newBalance = $oSession->getGiftCardBalance() + $card->getCardBalance();
                $oSession->setGiftCardBalance($newBalance);
            }
        } else {
            $giftCardsIds[$card->getId()] = array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
            $oSession->setGiftCardsIds($giftCardsIds);
            
            $oSession->setGiftCardBalance($card->getCardBalance());
        }
    }
    
    protected function _getUpdatedCoupon()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load(array('checkout_onepage_paymentmethod', 'giftcard_onepage_coupon'));
        $layout->generateXml();
        $layout->generateBlocks();
        $layout->removeOutputBlock('root');
        $output = $layout->getOutput();
        return $output;
    }
}
