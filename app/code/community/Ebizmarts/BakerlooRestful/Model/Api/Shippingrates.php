<?php

class Ebizmarts_BakerlooRestful_Model_Api_Shippingrates extends Ebizmarts_BakerlooRestful_Model_Api_Api
{

    public function get()
    {
        throw new Exception('Incorrect request. GET not implemented.');
    }

    public function post()
    {

        parent::post();

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload(true);

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->getHelper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, true);

        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = $this->getHelper('tax');

        /** @var Mage_Core_Model_Store $store */
        $store = Mage::app()->getStore($this->getStoreId());

        $shippingTaxRate = $this->getShippingTaxRate($quote, $store);

        $groups = $quote->getShippingAddress()->getGroupedAllShippingRates();

        $rates = array();

        foreach ($groups as $_rates) {
            foreach ($_rates as $_rate) {

                $shippingTax = 0;
                if (!$taxHelper->shippingPriceIncludesTax()) {
                    $shippingTax = (float)$_rate->getPrice() * $shippingTaxRate/100;
                }

                array_push(
                    $rates,
                    array(
                        'code'  => $_rate->getCode(),
                        'title' => $_rate->getMethodTitle(),
                        'price' => (float)$_rate->getPrice(),
                        'tax'   => $store->roundPrice($shippingTax),
                        'sort_order' => (int)$_rate->getCarrierSortOrder()
                    )
                );
            }
        }

        //DELETE quote so we don't leave garbage in db
        $quote->delete();

        return $rates;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return float
     */
    public function getShippingTaxRate(Mage_Sales_Model_Quote $quote, $store)
    {
        $shippingTaxClass = $this->getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store->getId());

        /** @var Mage_Tax_Model_Calculation $taxCalculationModel */
        $taxCalculationModel = $this->getModel('tax/calculation', true);

        $request = $taxCalculationModel->getRateRequest(
            $quote->getShippingAddress(),
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $store
        );

        $rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass));

        return $rate;
    }
}
