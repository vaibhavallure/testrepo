<?php

class Ebizmarts_BakerlooRestful_Model_Shift extends Mage_Core_Model_Abstract
{

    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'bakerloo_restful_shift';

    /**
     * Parameter name in event
     * @var string
     */
    protected $_eventObject = 'shift';

    public function _construct()
    {
        $this->_init('bakerloo_restful/shift');
    }

    public function getOpenAmounts()
    {
        return json_decode($this->getJsonOpenCurrencies(), true);
    }

    public function getCloseAmounts()
    {
        return json_decode($this->getJsonCloseCurrencies(), true);
    }

    public function getVatBreakdown()
    {
        return json_decode($this->getJsonVatbreakdown());
    }

    public function getNextdayCurrencies($asObject = false)
    {
        if ($asObject) {
            $nextDayCurrenciesArray = $this->getJsonNextdayCurrencies() ? json_decode($this->getJsonNextdayCurrencies(), true) : array();

            foreach ($nextDayCurrenciesArray as $key => $currency) {
                $currencyObject = new Varien_Object();
                $currencyObject->setData($currency);
                $nextDayCurrenciesArray[$key] = $currencyObject;
            }

            $jsonObject = new Varien_Object();
            $jsonObject->setData('nextday_currencies', $nextDayCurrenciesArray);

            $result = $jsonObject;
        } else {
            $jsonReturn = json_decode($this->getJsonNextdayCurrencies());
            if (is_null($jsonReturn)) {
                $jsonReturn = new stdClass();
            }

            $result = $jsonReturn;
        }

        return $result;
    }

    public function getNextdayCurrenciesByCode()
    {
        $nextday = $this->getNextdayCurrencies(true);

        $result = array();

        foreach ($nextday->getNextdayCurrencies() as $_next) {
            if ($_next->getCurrencyCode()) {
                $result[$_next->getCurrencyCode()] = $_next;
            }
        }
        return $result;
    }

    public function getTransactions()
    {
        $transactions = Mage::getResourceModel('bakerloo_restful/shift_activity_collection')
            ->addFieldToFilter('shift_id', array('eq' => $this->getId()))
            ->addFieldToFilter('type', array('eq' => Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION))
            ->getItems();

        return $transactions;
    }

    public function getActivities()
    {
        $activities = Mage::getResourceModel('bakerloo_restful/shift_activity_collection')
            ->addFieldToFilter('shift_id', array('eq' => $this->getId()))
            ->addFieldToFilter('type', array('neq' => Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION))
            ->getItems();

        return $activities;
    }
}
