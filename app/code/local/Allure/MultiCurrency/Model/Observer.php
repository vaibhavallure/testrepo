<?php
/**
 * Created by PhpStorm.
 * User: adityagatare
 * Date: 17/9/18
 * Time: 2:30 AM
 */

class Allure_MultiCurrency_Model_Observer
{

    public function setItemCurrency($observer)
    {


        try{

        $baseCurrency="USD";
        $currentCurrency=Mage::app()->getStore()->getCurrentCurrencyCode();
        $currentCountry=Mage::getSingleton('core/session')->getGeoCountry();

        $item = $observer->getQuoteItem();
        if(Mage::Helper('multicurrency')->getCustomAttrPriceByProductId($item->getProductId()))
        {
            $baseCurrency=Mage::Helper('multicurrency')->getCurrentCountryCurrencyCode();
        }

        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rate = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrency, array_values($allowedCurrencies));



        $qtItem=$item;
        $qtItem->setBaseCurrency($baseCurrency);
        $qtItem->setCurrentCurrency($currentCurrency);
        $qtItem->setCurrentCountry($currentCountry);
        $qtItem->setConversionRate($rate[$currentCurrency]);


        }catch (Exception $e)
        {
            Mage::log($e->getMessage(),Zend_Log::DEBUG,'allure_log.log',true);
        }

    }


    public function changeQuoteCurrency($observer)
    {
//      $session = Mage::getSingleton('checkout/session');
//      $quote=$session->getQuote();
//
//        $quote = Mage::getSingleton('sales/quote')->load(20425679);
//
//
//            $quote->setCurrency('GBP');
//            $quote->setBaseCurrencyCode('GBP');
//            $quote->setQuoteCurrencyCode('GBP');
//
//            $quote->save();

        //Mage::app()->getStore()->setBaseCurrencyCode('GBP');

    // Mage::log($quote,Zend_Log::DEBUG,'allure_log.log',true);

      //  Mage::app()->getStore()->setBaseCurrency();

       // Mage::app()->getStore()->setBaseCurrency(Mage::getModel("directory/currency")->load("GBP"));
        $quote=$observer->getEvent()->getQuote();

        //$customer_id = $quote->getCustomerId();
      //  Mage::log($quote->getBaseCurrency(),Zend_Log::DEBUG,'allure_log.log',true);


    //  Mage::log(Mage::getSingleton('core/session')->getGeoCountry(),Zend_Log::DEBUG,'allure_log.log',true);

    }
}