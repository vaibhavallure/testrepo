<?php

class Ebizmarts_BakerlooLoyalty_Model_AheadworksPoints extends Ebizmarts_BakerlooLoyalty_Model_Abstract
{

    public function init()
    {
        $this->_reward = Mage::getModel('points/summary')
                        ->loadByCustomerID($this->getCustomerId());
    }

    public function isEnabled()
    {
        $posConfig       = ($this->getLoyaltyConfig() == 'AW_Points');
        $active          = Mage::helper('points/config')->isPointsEnabled();

        return $posConfig && $active;
    }

    public function getPointsBalance()
    {
        return (int)$this->_reward->getPoints();
    }

    public function getMinumumToRedeem()
    {
        return Mage::helper('points/config')->getMinimumPointsToRedeem($this->getStoreId());
    }

    public function getCurrencyAmount()
    {
        return "";
    }

    public function getYouWillEarnPoints(Mage_Sales_Model_Quote $cart)
    {
        $cartPoints = array();
        $configHelper = Mage::helper('points/config');

        $cartDiscount           = $cart->getBaseDiscountAmount() < 0 ? $cart->getBaseDiscountAmount() : -$cart->getBaseDiscountAmount();
        $cartBaseMoneyForPoints = $cart->getBaseMoneyForPoints() < 0 ? $cart->getBaseMoneyForPoints() : -$cart->getBaseMoneyForPoints();
        $cartTaxAmount          = $cart->getBaseTaxAmount() > 0 ? $cart->getBaseTaxAmount() : $cart->getShippingAddress()->getBaseTaxAmount();

        $applyAfter = $configHelper->getPointsCollectionOrder($cart->getStoreId()) == AW_Points_Helper_Config::AFTER_TAX;
        if ($applyAfter) {
            $amountToPoint = $cart->getBaseSubtotal() + $cartDiscount + $cartBaseMoneyForPoints + $cartTaxAmount;
        } else {
            $amountToPoint = $cart->getBaseSubtotal() + $cartDiscount + $cartBaseMoneyForPoints;
        }

        $pointsEarned = Mage::getModel('points/api')->changeMoneyToPoints($amountToPoint, $cart->getCustomer(), $cart->getStore()->getWebsite());
        $pointsForItems = $this->getPointsForItems($cart);

        $pointsEarned += $pointsForItems['points_for_items'];

        $cartPoints['items']                      = $pointsForItems['items'];
        $cartPoints['total_points_earned']        = $pointsEarned;
        $cartPoints['total_points_earned_string'] = $configHelper->__('You will earn %s %s.', $pointsEarned, $configHelper->getPointUnitName());

        return $cartPoints;
    }

    private function getPointsForItems(Mage_Sales_Model_Quote $quote)
    {
        $items = array();
        $additionalPoints = 0;

        $ruleCollection = Mage::getModel('points/rule')
            ->getCollection()
            ->addAvailableFilter()
            ->addFilterByCustomerGroup($quote->getCustomer()->getGroupId())
            ->addFilterByWebsiteId($quote->getStore()->getWebsiteId())
            ->setOrder('priority', Varien_Data_Collection::SORT_ORDER_ASC);

        foreach ($ruleCollection as $rule) {
            if ($rule->checkRule($quote)) {
                $items[] = array(
                    'sku'                        => $rule->getId(),
                    'total_points_earned'        => $rule->getPointsChange(),
                    'total_points_earned_string' => Mage::helper('points')->__('Rule #%s, %s', $rule->getId(), $rule->getName()),
                );

                $additionalPoints += $rule->getPointsChange();

                if ($rule->getStopRules())
                    break;
            }
        }

        return array('points_for_items' => $additionalPoints, 'items' => $items);
    }

    public function rewardCustomer($customer, $points)
    {
        return false;
    }

    /**
     * Return redeem options for cart.
     *
     * @param $quote
     * @throws Mage_Core_Exception
     */
    public function cartRedeemOptions($quote)
    {
        Varien_Profiler::start('POS::' . __METHOD__);

        $options = array();

        if ($this->getCustomer()) {

            $rateCollection = Mage::getModel('points/rate')->getCollection();
            $rateCollection->getSelect()
                ->where('FIND_IN_SET(?, customer_group_ids)', $this->getCustomer()->getGroupId())
                ->where('FIND_IN_SET(?, website_ids)', Mage::app()->getWebsite()->getId())
                ->where('direction = ?', AW_Points_Model_Rate::POINTS_TO_CURRENCY);
            $rateCollection->load();

            /** @var AW_Points_Helper_Config $configHelper */
            $configHelper = Mage::helper('points/config');
            $neededPoints = Mage::helper('points')->getNeededPoints($quote->getBaseSubtotalWithDiscount(), $this->getCustomer());

            /** @var AW_Points_Model_Rate $rate */
            foreach ($rateCollection as $rate) {
                $options[] = array(
                    self::OPTIONS_POINTS_AMT            => $rate->getPoints(),
                    self::OPTIONS_POINTS_CURR_ID        => Mage::app()->getStore()->getCurrentCurrencyCode(),
                    self::OPTIONS_RULE_ID               => $rate->getId(),
                    self::OPTIONS_RULE_NAME             => $configHelper->getPointUnitName(Mage::app()->getStore()->getId()),
                    self::OPTIONS_POINTS_MAX_USES       => 0,
                    self::OPTIONS_POINTS_MAX_QTY        => $neededPoints,
                    self::OPTIONS_POINTS_MAX_PERCENTAGE => 0,
                    self::OPTIONS_MAX_EXPENDABLE        => min($this->getPointsBalance(), $neededPoints),
                    self::OPTIONS_LEGEND                => $rate->getRateText()
                );
            }
        }


        Varien_Profiler::stop('POS::' . __METHOD__);

        return $options;
    }

    public function applyRewardsToQuote(Mage_Sales_Model_Quote $quote, $rules = array())
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($quote->getId());
        $session->setQuote($quote);
        $session->setUsePoints(true);

        $pointsAmount = 0;
        foreach ($rules as $rule) {
            $pointsAmount += $rule['points_amount'];
        }

        $session->setPointsAmount($pointsAmount);
        Mage::register(Ebizmarts_BakerlooLoyalty_Helper_Data::AW_POINTS_AMOUNT, $pointsAmount);
    }
}
