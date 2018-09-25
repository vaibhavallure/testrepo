<?php

class Allure_Sales_Model_Quote extends Mage_Sales_Model_Quote
{

    protected function _beforeSave()
    {
        /**
         * Currency logic
         *
         * global - currency which is set for default in backend
         * base - currency which is set for current website. all attributes that
         *      have 'base_' prefix saved in this currency
         * store - all the time it was currency of website and all attributes
         *      with 'base_' were saved in this currency. From now on it is
         *      deprecated and will be duplication of base currency code.
         * quote/order - currency which was selected by customer or configured by
         *      admin for current store. currency in which customer sees
         *      price thought all checkout.
         *
         * Rates:
         *      store_to_base & store_to_quote/store_to_order - are deprecated
         *      base_to_global & base_to_quote/base_to_order - must be used instead
         */

        $globalCurrencyCode  = Mage::app()->getBaseCurrencyCode();
        $baseCurrency = $this->getStore()->getBaseCurrency();


/**changes--------------------------------------------------------------------------------------*/
        /**check if product has custom attr then make $custAttr=true*/

        if (Mage::helper('core')->isModuleEnabled('Allure_MultiCurrency')) {

            /**change currency  of quote depending on country */

            if (Mage::helper("multicurrency")->getCurrentCountryCurrencyCode()) {
                $globalCurrencyCode = Mage::helper("multicurrency")->getCurrentCountryCurrencyCode();
                $baseCurrency = Mage::getModel("directory/currency")->load(Mage::helper("multicurrency")->getCurrentCountryCurrencyCode());
            }
        }

/**----------------------------------------------------------------------------------------------*/

        if ($this->hasForcedCurrency()){
            $quoteCurrency = $this->getForcedCurrency();
        } else {
            $quoteCurrency = $this->getStore()->getCurrentCurrency();
        }

        $this->setGlobalCurrencyCode($globalCurrencyCode);
        $this->setBaseCurrencyCode($baseCurrency->getCode());
        $this->setStoreCurrencyCode($baseCurrency->getCode());
        $this->setQuoteCurrencyCode($quoteCurrency->getCode());

        //deprecated, read above
        $this->setStoreToBaseRate($baseCurrency->getRate($globalCurrencyCode));
        $this->setStoreToQuoteRate($baseCurrency->getRate($quoteCurrency));

        $this->setBaseToGlobalRate($baseCurrency->getRate($globalCurrencyCode));
        $this->setBaseToQuoteRate($baseCurrency->getRate($quoteCurrency));

        if (!$this->hasChangedFlag() || $this->getChangedFlag() == true) {
            $this->setIsChanged(1);
        } else {
            $this->setIsChanged(0);
        }

        if ($this->_customer) {
            $this->setCustomerId($this->_customer->getId());
        }

        /**skip parent and call abstract class method*/
        Mage_Core_Model_Abstract::_beforeSave();
    }


}