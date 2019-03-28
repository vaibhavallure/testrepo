<?php

class Ebizmarts_BakerlooRestful_Model_Api_Loyalty extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const TYPE_LOYALTY  = 'loyalty';
    protected $_model   = "bakerloo_restful/integrationDispatcher";

    public function get()
    {
        Mage::throwException('Not implemented.');
    }

    public function post()
    {
        //ToDo: Give points to the customer: Ebizmarts_BakerlooLoyalty_Model_Abstract::rewardCustomer
        Mage::throwException('Not implemented.');
    }

    /**
     * Return qty of points to earn.
     *
     * PUT
     */
    public function put()
    {

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Varien_Profiler::start('POS::' . __METHOD__);

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload(true);

        $loyalty = Mage::getModel(
            $this->_model,
            array('integration_type' => self::TYPE_LOYALTY)
        );

        $h = $this->getHelper('bakerloo_restful/sales');

        $quote = $h->buildQuote($this->getStoreId(), $data, false);

        $points = $loyalty->getYouWillEarnPoints($quote);

        $h->clearSessions();

        $quote->delete();

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $points;
    }

    /**
     * GET
     *
     * @see http://docs.ebizmartspos.apiary.io/#loyalty
     */
    public function productRedeemOptions()
    {
        $customerId = (int)$this->_getQueryParameter('customer_id');
        $productIdentifier  = (int)$this->_getQueryParameter('product');

        $customer = Mage::getModel('customer/customer')->load($customerId);
        if (!$customer->getId()) {
            return array();
        }

        $productModel = Mage::getModel('catalog/product');
        $product = $productModel->load($productIdentifier);
        if (!$product->getId()) {
            $productId = $productModel->getIdBySku($productIdentifier);

            $productModel->unsetData();
            $productModel->unsetOldData();
            $product = $productModel->load($productId);
            if (!$product->getId()) {
                return array();
            }
        }

        $loyalty = Mage::getModel(
            $this->_model,
            array('integration_type' => self::TYPE_LOYALTY, 'customer' => $customer)
        );

        $options = $loyalty->productRedeemOptions($customer, $product);

        return array('reward_points_balance' => (float)$loyalty->getPointsBalance(), 'rules' => $options);
    }

    /**
     * Get the redemption rules applicable to a cart.
     */
    public function cartRedeemOptions()
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data     = $this->getJsonPayload(true);

        $customer = $this->getModel('customer/customer')->load($data['customer']['customer_id']);

        $loyalty  = $this->getModel(
            $this->_model,
            false,
            array(
                'integration_type'  => self::TYPE_LOYALTY,
                'website_id'        => Mage::app()->getStore()->getWebsiteId(),
                'customer'          => $customer
            )
        );


        $h = $this->getHelper('bakerloo_restful/sales');

        $quote = $h->buildQuote($this->getStoreId(), $data, false);

        $options = $loyalty->cartRedeemOptions($quote);

        $quote->delete();

        $h->clearSessions();

        Varien_Profiler::stop('POS::' . __METHOD__);

        return array('reward_points_balance' => (float)$loyalty->getPointsBalance(), 'rules' => $options);
    }

    public function redeem()
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload(true);

        $h = $this->getHelper('bakerloo_restful/sales');

        $quote = $h->buildQuote($this->getStoreId(), $data, false);

        $quote->setTotalsCollectedFlag(false)->collectTotals();

        $cartData = $h->getCartData($quote, false, true);

        $quote->delete();

        $h->clearSessions();

        Varien_Profiler::start('POS::' . __METHOD__);

        return $cartData;
    }
}
