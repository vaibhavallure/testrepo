<?php

class Ebizmarts_BakerlooRestful_Model_Api_Taxrules extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const CALCULATE_SUBTOTAL    = 'calculate_subtotal';
    const CALC_OFF_SUBTOTAL     = 'calculate_off_subtotal_only';
    const CODE                  = 'code';
    const CUSTOMER_TAX_CLASSES  = 'customer_tax_classes';
    const ID                    = 'id';
    const POSITION              = 'position';
    const PRIORITY              = 'priority';
    const PRODUCT_TAX_CLASSES   = 'product_tax_classes';
    const RATES                 = 'rates';
    const RULE_ID               = 'tax_calculation_rule_id';
    const SORT_ORDER            = 'sort_order';
    const TAX_RATES             = 'tax_rates';

    const RATE_CODE              = 'code';
    const RATE_COUNTRY_ID        = 'country_id';
    const RATE_ID                = 'id';
    const RATE_POSTCODE          = 'postcode';
    const RATE_POSTCODE_IS_RANGE = 'postcode_is_range';
    const RATE_RATE              = 'rate';
    const RATE_REGION_ID         = 'region_id';
    const RATE_TAX_CLASS         = 'tax_class';

    /**
     * Model name.
     *
     * @var string
     */
    protected $_model   = "tax/calculation_rule";
    public $defaultSort = "tax_calculation_rule_id";
    public $pageSize    = 5;

    protected $_iterator = false;

    public function _beforePaginateCollection($collection, $page, $since)
    {
        $this->_collection
            ->addCustomerTaxClassesToResult()
            ->addProductTaxClassesToResult()
            ->addRatesToResult();
        return $this;
    }

    public function returnDataObject($data)
    {

        $_data = array(
            self::ID                    => (int)$data[self::RULE_ID],
            self::PRIORITY              => (int)$data[self::PRIORITY],
            self::CALC_OFF_SUBTOTAL     => (int)$data[self::CALCULATE_SUBTOTAL],
            self::CODE                  => $data[self::CODE],
            self::SORT_ORDER            => (int)$data[self::POSITION],
            self::CUSTOMER_TAX_CLASSES  => array_map('intval', array_values(array_unique($data[self::CUSTOMER_TAX_CLASSES], SORT_NUMERIC))),
            self::PRODUCT_TAX_CLASSES   => array_map('intval', array_values(array_unique($data[self::PRODUCT_TAX_CLASSES], SORT_NUMERIC))),
            self::RATES                 => array(),
        );

        $taxClasses = $_data[self::PRODUCT_TAX_CLASSES];

        $filterRate = (int)$this->getHelper('bakerloo_restful')->config('general/filter_tax_rates');

        $rateObj = $this->getModel('tax/calculation_rate');

        if (!empty($taxClasses)) {
            foreach ($taxClasses as $_taxC) {
                foreach ($data[self::TAX_RATES] as $_rate) {

                    $rt = $rateObj->load($_rate);

                    if ($rt->getId()) {
                        if ($filterRate) {
                            if (!$rt->getEbizmartsPosSynch()) {
                                continue;
                            }
                        }

                        if (!$rt->hasTaxPostcode()) {
                            $rt->setTaxPostcode('*');
                        }

                        $_data [self::RATES][] = array(
                            self::RATE_ID                => (int)$rt->getTaxCalculationRateId(),
                            self::RATE_CODE              => $rt->getCode(),
                            self::RATE_COUNTRY_ID        => $rt->getTaxCountryId(),
                            self::RATE_REGION_ID         => $rt->getTaxRegionId(),
                            self::RATE_TAX_CLASS         => (int)$_taxC,
                            self::RATE_RATE              => (float)$rt->getRate(),
                            self::RATE_POSTCODE          => $rt->getTaxPostcode(),
                            self::RATE_POSTCODE_IS_RANGE => (int)$rt->getZipIsRange(),
                        );
                    }

                    $rateObj->setData(null);
                }
            }
        }

        $result = new Varien_Object($_data);

        Mage::dispatchEvent($this->_eventPrefix . '_return_before', array($this->_eventObject => $result));

        return $result->getData();
    }

    public function put()
    {
        //@TODO: Return cart with taxes calculation.
        Mage::throwException('Not implemented.');
    }
}
