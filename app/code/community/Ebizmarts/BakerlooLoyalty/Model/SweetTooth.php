<?php

class Ebizmarts_BakerlooLoyalty_Model_SweetTooth extends Ebizmarts_BakerlooLoyalty_Model_Abstract
{

    public function init()
    {
        $reward = Mage::getModel('rewards/customer')->getRewardsCustomer($this->getCustomer());

        $this->_reward = $reward;
    }

    public function isEnabled()
    {
        $posConfig = ($this->getLoyaltyConfig() == 'TBT_Rewards');
        $active    = Mage::getStoreConfig('rewards/platform/is_connected');

        return $posConfig && $active;
    }

    public function rewardCustomer($customer, $points)
    {
        try {
            //Code taken from http://help.sweettoothrewards.com/article/259-create-a-points-transfer-from-code
            //and slightly modified.

            $customerId = $customer->getId();

            //load in transfer model
            $transfer = Mage::getModel('rewards/transfer');
            //Load it up with information
            $transfer->setId(null)
                ->setCurrencyId("1") // in versions of sweet tooth 1.0-1.2 this should be set to "1"
                ->setQuantity($points) // number of points to transfer. This number can be negative or positive, but not zero
                ->setCustomerId($customerId) // the id of the customer that these points will be going out to
                ->setComments("POS points transfer."); //This is optional
            //Checks to make sure you can actually move the transfer into the new status
            if ($transfer->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED)) { // STATUS_APPROVED would transfer the points in the approved status to the customer
                $transfer->save(); //Save everything and execute the transfer
            }
        } catch (Exception $e) {
            Mage::logException($e);

            return false;
        }

        return true;
    }

    public function getPointsBalance()
    {
        /*
        TODO:
        the only caution I would give you is accounting for anyone who may have added extra functionality to Sweet Tooth by extending the support for multiple currencies.
        We've seen a couple of clients who have implemented multi currency support in Sweet Tooth. We do have a currency model (TBT_Rewards_Model_Currency) which gives you
        a list of available currencies, but in most cases that will only return "1". hope this helps Pablo! */

        $usable  = $this->_reward->getUsablePoints();
        $balance = 0;

        if (is_array($usable)) {
            $balance = current($usable);
        }

        return $balance;
    }

    public function getMinumumToRedeem()
    {
        return 0;
    }

    public function getCurrencyAmount()
    {
        return "";
    }

    public function applyRewardsToQuote(Mage_Sales_Model_Quote $quote, $rules = array())
    {
        $applied = Mage::getModel('rewards/salesrule_list_applied')->initQuote($quote);

        foreach ($rules as $rule) {
            if ($rule['points_amount'] > 0) {
                $quote->setPointsSpending($rule['points_amount']);
            }

            $applied->add($rule['rule_id'])->saveToQuote($quote);
        }
    }

    public function getYouWillEarnPoints(Mage_Sales_Model_Quote $cart)
    {
        $rewardsSession = Mage::getSingleton('rewards/session');

        $pointsEarning = $rewardsSession->getTotalPointsEarnedOnCart($cart);
        $pointsString  = Mage::getModel('rewards/points')->set($pointsEarning)->getRendering()->setDisplayAsList(true)->toHtml();


        $cartPoints = array();

        $cartPoints['items']                      = array();
        $cartPoints['total_points_earned']        = (int)current($pointsEarning);
        $cartPoints['total_points_earned_string'] = strip_tags($pointsString);

        foreach ($cart->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $pointsToEarnHash = Mage::helper('rewards')->unhashIt($item->getEarnedPointsHash());
            if (empty($pointsToEarnHash)) {
                continue;
            }

            $pointsToEarn = (int)$pointsToEarnHash[0]->points_amt;
            $asString     = strip_tags((string)Mage::getModel('rewards/points')->set(array (1 => $pointsToEarn)));

            $cartPoints ['items'][] = array (
                'sku'                        => $item->getSku(),
                'total_points_earned'        => $pointsToEarn,
                'total_points_earned_string' => $asString,
            );
        }

        return $cartPoints;
    }

    public function productRedeemOptions($customer, $product)
    {
        Mage::getSingleton('rewards/session')->setCustomer($customer);

        $_product    = TBT_Rewards_Model_Catalog_Product::wrap($product);
        $ruleOptions = $_product->getCatalogRedemptionRules($customer);

        if (is_array($ruleOptions) and !empty($ruleOptions)) {
            $rulesCount = count($ruleOptions);

            $rule = Mage::getModel('rewards/catalogrule_rule');

            for ($i=0; $i < $rulesCount; $i++) {
                $rule->load($ruleOptions[$i]->rule_id);
                $ruleData = $rule->getData();

                if (isset($ruleData['conditions_serialized'])) {
                    unset($ruleData['conditions_serialized']);
                }
                if (isset($ruleData['actions_serialized'])) {
                    unset($ruleData['actions_serialized']);
                }
                if (isset($ruleData['customer_group_ids'])) {
                    unset($ruleData['customer_group_ids']);
                }
                if (isset($ruleData['website_ids'])) {
                    unset($ruleData['website_ids']);
                }
                if (isset($ruleOptions[$i]->applicable_qty)) {
                    unset($ruleOptions[$i]->applicable_qty);
                }


                $ruleOptions[$i]->name = $rule->getName(); //$ruleData;
                $ruleOptions[$i]->points_max_uses = (int)$rule->getPointsUsesPerProduct();
                $ruleOptions[$i]->points_max_qty = (int)$rule->getPointsMaxQty();
                $ruleOptions[$i]->points_max_percentage = (int)$rule->getPointsMaxRedeemPercentagePrice();
//                $ruleOptions[$i]->data = $ruleData;

                $rule->unsetData();
                $rule->unsetOldData();
                $ruleData = null;
            }
        }

        return $ruleOptions;
    }

    public function cartRedeemOptions($quote)
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        $options = array();

        $cartRules = Mage::getSingleton('rewards/session')->collectShoppingCartRedemptions($quote);

        $model = Mage::getModel('rewards/salesrule_rule');
        $rewardsHelper = Mage::helper('rewards');

        foreach ($cartRules['applicable'] as $id => $ruleData) {
            $model->load($id);

            $salesrule = Mage::helper('rewards/transfer')->getSalesRule($ruleData['rule_id']);

            $data = array(
                self::OPTIONS_POINTS_AMT            => abs($ruleData['amount']),
                self::OPTIONS_POINTS_CURR_ID        => (int)$ruleData['currency'],
                self::OPTIONS_RULE_ID               => (int)$ruleData['rule_id'],
                self::OPTIONS_RULE_NAME             => $ruleData['rule_name'],
                self::OPTIONS_POINTS_MAX_USES       => 0,
                self::OPTIONS_POINTS_MAX_QTY        => (int)$model->getPointsMaxQty(),
                self::OPTIONS_POINTS_MAX_PERCENTAGE => 0,
                self::OPTIONS_MAX_EXPENDABLE        => $this->_getNeededPointsRedeem($quote, $ruleData, 'applicable', $salesrule)
            );

            if (isset($ruleData['caption']) and !empty($ruleData['caption'])) {
                $data[self::OPTIONS_LEGEND] = $ruleData['caption'];
            } else {
                $data[self::OPTIONS_LEGEND] = strip_tags($rewardsHelper->__('Spend <b>%s</b>, Get <b>%s</b>', $ruleData['points_cost'], $ruleData['action_str']));
            }

            $options[] = $data;
        }

        /* dbps rules are included in the applied array with amount 0 if they haven't been applied yet */
        foreach ($cartRules['applied'] as $id => $ruleData) {
            if (!$ruleData['is_dbps']) {
                continue;
            }

            $model->load($id);

            $salesrule = Mage::helper('rewards/transfer')->getSalesRule($ruleData['rule_id']);

            $data = array(
                self::OPTIONS_POINTS_AMT            => (int)$model->getPointsAmount(), //abs($ruleData['amount']),
                self::OPTIONS_POINTS_CURR_ID        => (int)$ruleData['currency'],
                self::OPTIONS_RULE_ID               => (int)$ruleData['rule_id'],
                self::OPTIONS_RULE_NAME             => $ruleData['rule_name'],
                self::OPTIONS_POINTS_MAX_USES       => 0,
                self::OPTIONS_POINTS_MAX_QTY        => (int)$model->getPointsMaxQty(),
                self::OPTIONS_POINTS_MAX_PERCENTAGE => 0,
                self::OPTIONS_MAX_EXPENDABLE        => $this->_getNeededPointsRedeem($quote, $ruleData, 'applied', $salesrule, (int)$model->getPointsAmount())
            );

            if (isset($ruleData['caption']) and !empty($ruleData['caption'])) {
                $data[self::OPTIONS_LEGEND] = $ruleData['caption'];
            } else {
                $actionStr = (int)$model->getPointsDiscountAmount();
                if ($salesrule->getPointsDiscountAction() === 'by_percent')
                    $actionStr .= "% off";
                else
                    $actionStr .= Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
                $data[self::OPTIONS_LEGEND] = strip_tags($rewardsHelper->__('Spend <b>%s</b>, Get <b>%s</b>', $data['points_amt'], $actionStr));
            }

            $options[] = $data;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);
        return $options;
    }

    protected function _getNeededPointsRedeem($quote, $ruleData, $stateRule, $salesrule, $points = null)
    {
        if ($salesrule->getPointsAction() != 'discount_by_points_spent') {
            if ($stateRule === 'applicable') {
                return abs($ruleData['amount']);
            } else {
                return $points;
            }
        } else {
            return min($quote->getMaxSpendablePoints(), $this->getPointsBalance());
        }
    }
}
