<?php
/**
 * Amazon Payments Checkout Controller
 *
 * @category    Amazon
 * @package     Amazon_Payments
 * @copyright   Copyright (c) 2014 Amazon.com
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Amazon_Payments_OnepageController extends Amazon_Payments_Controller_Checkout
{
    protected $_checkoutUrl = 'checkout/onepage';

    /**
     * Index action
     */
    public function indexAction()
    {
        // placeholder required
    }

    /**
     * Save widget (address/payment info)
     */
    public function saveWidgetAction()
    {
        $result = array();

        if ($this->_expireAjax()) {
            return;
        }

        try {
            /** gift item save for single address for amazon pay */
            $giftItems = $this->getRequest()->getParam("ship");
            $this->_getOnepage()->saveGiftItem($giftItems);
            
            $this->_saveShipping();
            $this->_getOnepage()->getCheckout()->setStepData('widget', 'complete', true);

            $this->_getOnepage()->savePayment(array(
                'method' => 'amazon_payments',
                'additional_information' => array(
                    'order_reference' => $this->getAmazonOrderReferenceId(),
                    'billing_agreement_id' => $this->getAmazonBillingAgreementId(),
                    'billing_agreement_consent' => $this->getAmazonBillingAgreementConsent(),
                )
            ));

            if ($this->_getOnepage()->getQuote()->isVirtual()) {
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            } else {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }

            $data = $this->getRequest()->getPost('billing', array());

            // Save Amasty Customer Attributes
            if (isset($data['amcustomerattr'])) {
                Mage::app()->getRequest()->setPost('amcustomerattr', $data['amcustomerattr']);
                Mage::getSingleton('customer/session')->getCustomer()->save();
            }

            // Sign Up for Newsletter
            if ($this->getRequest()->getPost('is_subscribed', false)) {
                Mage::getSingleton('customer/session')->getCustomer()
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setIsSubscribed(true)
                ->save();
            }

            // Validate country
            if (!$this->isCountryAllowed($this->_getCheckout()->getQuote()->getShippingAddress()->getCountry())) {
                $result['error'] = true;
                $result['message'] = $this->__('This order cannot be shipped to the selected country. Please use a different shipping address.');
            }

            // Check if state is blocked by config
            if ($this->_getCheckout()->getQuote()->getShippingAddress()->getCountry() == 'US' &&
                in_array($this->_getCheckout()->getQuote()->getShippingAddress()->getRegionCode(), Mage::getModel('amazon_payments/config')->getBlockStates())) {
                $result['error'] = true;
                $result['message'] = $this->__('This order cannot be shipped to the selected state. Please use a different shipping address.');
            }
            
            $_helper = Mage::helper('amazon_payments/data');
            if(!$_helper->isCheckoutAmazonSession()){
                $result['error'] = true;
                $result['message'] = $this->__('Amazon session expired. Please login once again by using amazon account.');
            }
            
        }
        // Catch any API errors like invalid keys
        catch (Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
        }
        
        //$result["totals_html"] = $this->_getRefreshTotalsHtml();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $no_signature_delivery = $this->getRequest()->getPost('no_signature_delivery', '');
            $no_signature_delivery = ($no_signature_delivery) ? 1 : 0;
            
            $result = $this->_getOnepage()->saveShippingMethod($data);
            
            $this->_getOnepage()
                ->getQuote()
                ->setData('no_signature_delivery', $no_signature_delivery)
                ->save();
            
            // $result will contain error data if shipping method is empty
            if (!$result) {
                Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method',
                     array(
                          'request' => $this->getRequest(),
                          'quote'   => $this->_getOnepage()->getQuote()));
                $this->_getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));


                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            
            $_helper = Mage::helper('amazon_payments/data');
            if(!$_helper->isCheckoutAmazonSession()){
                $result['error'] = true;
                $result['message'] = $this->__('Amazon session expired. Please login once again by using amazon account.');
            }

            $this->_getOnepage()->getQuote()->collectTotals()->save();

            $result["totals_html"] = $this->_getRefreshTotalsHtml();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Get order review step html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        if($customerGroupId == 2){
            $update->load('checkout_onepage_review');
        }else{
            $update->load('checkout_onepage_review_general_customer');
        }
        
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }
    
    /**
     * Get refresh totals html
     * @return string
     */
    protected function _getRefreshTotalsHtml ()
    {
        $block = $this->getLayout()
            ->createBlock('checkout/cart_totals')
            ->setTemplate('checkout/cart/totals.phtml');
        $childBlock = $this->getLayout()
            ->createBlock('checkout/cart_shipping')
            ->setTemplate('checkout/cart/shipping.phtml');
        $block->setChild("shipping", $childBlock);
        $output = $block->toHtml();
        return $output;
    }

}

