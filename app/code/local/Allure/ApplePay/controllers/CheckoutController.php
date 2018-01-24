<?php
/**
 * Apple Pay
 *
 * @category    Allure
 * @package     Allure_ApplePay
 * @copyright   Copyright (c) 2017 Allure Inc
 * @license     http://opensource.org/licenses/Apache-2.0  Apache License, Version 2.0
 */

class Allure_ApplePay_CheckoutController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      *
      * @access public
      * @return void
      */
    public function indexAction()
    {
    }

    public function validateMerchantAction() {
        $validationUrl="https://apple-pay-gateway-cert.apple.com/paymentservices/startSession";

        $pemPwd = '';
        $displayName    = "Maria Tash";
        $domainName     = $_SERVER['HTTP_HOST'];
        $merchantId     = 'merchant.com.mariatash.authorizenet';

        $payload = array (
            "merchantIdentifier" => $merchantId,
            "domainName"    => $domainName,
            "displayName"   => $displayName
        );

        // JSON Payload
        //$validationPayload = '{"merchantIdentifier": "merchant.com.mariatash.authorizenet","domainName": "www.venusbymariatash.com","displayName":"Venus By Maria Tash"}';

        $validationPayload = json_encode($payload);

        try{	
            //setting the curl parameters.
            $ch = curl_init();
            if (FALSE === $ch) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, $validationUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $validationPayload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
            // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
            // Any code used in production should either remove these lines or set them to the appropriate
            // values to properly use secure connections for PCI-DSS compliance.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);	//for production, set value to true or 1
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	//for production, set value to 2
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__).'/../certs/Identity_VenusByMariaTash.pem');
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pemPwd);
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
            $content = curl_exec($ch);

            if (FALSE === $content) {
                print_r(curl_error($ch));
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
            curl_close($ch);
            die($content);

        } catch (Exception $e) {
            trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
    }


    public function addProductAction() {

        $cart   = $this->_getCart();

        $cart->init()->save();

        $this->truncateCart();

        $this->cleanQuotes();

        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->getResponse()->setBody(false);
                exit;
            }

            $cart->addProduct($product, $params);

            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $this->_getQuote()->setTotalsCollectedFlag(false);

            $this->_getSession()->setCartWasUpdated(true);
            
            $this->_getQuote()->setTotalsCollectedFlag(false);
            
            $cart->save();

            $data = array(
                    //'request' => $this->getRequest(),
                    'params'        => $this->getRequest()->getParams(),
                    'totals'        => $this->_getQuote()->getTotals(),
                    'quote_id'      => $this->_getQuote()->getId(),
                    'currency'      => $this->_getQuote()->getGlobalCurrencyCode(),
                    'grand_total'   => $this->_getQuote()->getBaseGrandTotal(),
                    'total'         => $product->getFinalPrice()
            );

            $this->getResponse()->setBody(json_encode($data));
            return;
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
            return;
        } catch (Exception $e) {
            $this->getResponse()->setBody('Cannot add the item to shopping cart.');
            Mage::logException($e);
            return;
        }

        Mage::log(json_encode($data), Zend_Log::DEBUG, 'applepay.log', true);
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction() {

        if ($this->getRequest()->isPost()) {
            //            $postData = $this->getRequest()->getPost('billing', array());
            //            $data = $this->_filterPostData($postData);
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                            'name' => 'payment-method',
                            'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction() {

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $no_signature_delivery = $this->getRequest()->getPost('no_signature_delivery', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            $this->getOnepage()->getQuote()->setData('no_signature_delivery', $no_signature_delivery)->save();
            /*
             $result will have erro data if shipping method is empty
             */
            if (!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(),
                        'quote' => $this->getOnepage()->getQuote()));
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentAction() {

        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            // set payment to quote
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                        'name' => 'review',
                        'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Create order action
     */
    public function saveOrderAction ()
    {
        $result = array();
        $_checkoutHelper = Mage::helper('allure_multicheckout');
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__(
                            'Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', array());
            // Mage::log($data,Zend_log::DEBUG,'abc',true);
            if ($data) {
                // Mage::log($data,Zend_log::DEBUG,'abc',true);die;
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT |
                Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY |
                Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY |
                Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX |
                Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;

                if (strtolower($this->getOnepage()->getQuote()->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP)) {
                    $this->getOnepage()
                    ->getQuote()
                    ->getPayment()
                    ->importData($data);
                } else {
                    if (! $this->getOnepage()->getQuoteOrdered()->getIsCheckoutCart())
                        $this->getOnepage()
                        ->getQuoteOrdered()
                        ->getPayment()
                        ->importData($data);
                        // Mage::log($this->getOnepage()->getQuoteOrdered()->getPayment()->getCcNumber(),Zend_log::DEBUG,'abc',true);
                        if (! $this->getOnepage()->getQuoteBackordered()->getIsCheckoutCart())
                            $this->getOnepage()
                            ->getQuoteBackordered()
                            ->getPayment()
                            ->importData($data);
                            // Mage::log($this->getOnepage()->getQuoteBackordered()->getPayment()->getCcNumber(),Zend_log::DEBUG,'abc',true);
                }
            }

            // die;
            // Mage::log($data,Zend_log::DEBUG,'abc',true);die;
            $this->getOnepage()->saveCustomOrder($data);

            $redirectUrl = $this->getOnepage()
            ->getCheckout()
            ->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (! empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('allure_exception')->notifyExceptionForPayment(
                    $this->getOnepage()
                    ->getQuote(), $e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()
                    ->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            $gotoSection = $this->getOnepage()
            ->getCheckout()
            ->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()
                ->getCheckout()
                ->setGotoSection(null);
            }
            $updateSection = $this->getOnepage()
            ->getCheckout()
            ->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                            'name' => $updateSection,
                            'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()
                ->getCheckout()
                ->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('allure_exception')->notifyExceptionForPayment(
                    $this->getOnepage()
                    ->getQuote(), $e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()
                    ->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__(
                    'There was an error processing your order. Please contact us or try again later.');
        }

        if (strtolower($this->getOnepage()
                ->getQuote()
                ->getDeliveryMethod()) == strtolower($_checkoutHelper::ONE_SHIP)) {
                    $this->getOnepage()
                    ->getQuote()
                    ->save();
                    if ($result['success']) {
                        if ($this->getOnepage()->getQuoteOrdered()) {
                            $this->getOnepage()
                            ->getQuoteOrdered()
                            ->setIsActive(false)
                            ->save();
                        }
                        if ($this->getOnepage()->getQuoteBackordered()) {
                            $this->getOnepage()
                            ->getQuoteBackordered()
                            ->setIsActive(false)
                            ->save();
                        }
                    }
                } else {
                    $this->getOnepage()
                    ->getQuoteOrdered()
                    ->save();
                    $this->getOnepage()
                    ->getQuoteBackordered()
                    ->save();
                    if ($result['success']) {
                        Mage::getSingleton('checkout/session')->getQuote()
                        ->setIsActive(false)
                        ->save();
                        $this->getOnepage()
                        ->getQuoteOrdered()
                        ->setIsActive(false)
                        ->save();
                        $this->getOnepage()
                        ->getQuoteBackordered()
                        ->setIsActive(false)
                        ->save();
                    }
                }

                /**
                 * when there is redirect to third party, we don't want to save order
                 * yet.
                 * we will save the order in return action.
                 */
                if (isset($redirectUrl)) {
                    $result['redirect'] = $redirectUrl;
                }
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveTransactionAction() {

        $data = $_POST;

        $session = $this->_getSession();

        $cart = $this->_getCart();

        $quote = $session->getQuote();

        //var_dump($quote);
        var_dump($cart->getQuote()->getData());die;

        Mage::log(json_encode($data), Zend_Log::DEBUG, 'applepay.log', true);

        die('DONE');
    }

    public function truncateCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }

    private function cleanQuotes()
    {
        $customerId = $this->_getCart()->getCustomerSession()->getCustomerId();
        $quoteId = $this->_getCart()->getQuote()->getId();

        $quote_collection = Mage::getModel('sales/quote')->getCollection()
        ->addFieldToFilter('customer_id', $customerId)
        ->addFieldToFilter('is_active', 1)
        ->addFieldToFilter('is_applepay', 1);

        foreach ($quote_collection as $quote) {
            if ($quote->getId() != $quoteId) {
                $quote->setIsActive(false)->save();
            }
        }

        $this->_getCart()->getQuote()->setIsActive(true);
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('allure_applepay/cart');
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage() {
        return Mage::getSingleton('allure_applepay/type_onepage');
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('allure_applepay/session');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
}
