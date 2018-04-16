<?php

class Ebizmarts_BakerlooLoyalty_Model_Observer {

    /** @var Ebizmarts_BakerlooLoyalty_Helper_Data $helper */
    private $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('bakerloo_loyalty');
    }

    public function cartDataReturnBefore(Varien_Event_Observer $observer) {

        if ($this->helper->getIntegrationFromConfig() == Ebizmarts_BakerlooLoyalty_Helper_Data::CODE_AHEADWORKS) {
            $quote = $observer->getEvent()->getQuote();
            $cartData = $observer->getEvent()->getCartData();

            $cartData->setBaseSubtotalWithDiscount($cartData->getBaseSubtotalWithDiscount() - $quote->getBaseMoneyForPoints());
            $cartData->setSubtotalWithDiscount($cartData->getSubtotalWithDiscount() - $quote->getMoneyForPoints());
            $cartData->setBaseDiscount($cartData->getBaseDiscount() + $quote->getBaseMoneyForPoints());
            $cartData->setDiscount($cartData->getDiscount() + $quote->getMoneyForPoints());
            $cartData->setBaseGrandTotal($cartData->getBaseGrandTotal() - $quote->getBaseMoneyForPoints());
            $cartData->setGrandTotal($cartData->getGrandTotal() - $quote->getMoneyForPoints());
        }

        return $this;
    }

    public function awSessionRefresh(Varien_Event_Observer $observer)
    {
        if ($this->helper->getIntegrationFromConfig() == Ebizmarts_BakerlooLoyalty_Helper_Data::CODE_AHEADWORKS)
        {
            $order = $observer->getEvent()->getOrder();
            $quote = $order->getQuote();
            $pointsAmount = Mage::registry(Ebizmarts_BakerlooLoyalty_Helper_Data::AW_POINTS_AMOUNT);

            if ($quote->getMoneyForPoints() and $pointsAmount) {
                $order->setAmountToSubtract(-$pointsAmount);
                $order->setBaseMoneyForPoints($quote->getBaseMoneyForPoints());
                $order->setMoneyForPoints($quote->getMoneyForPoints());
            }
        }

        return $this;
    }
}