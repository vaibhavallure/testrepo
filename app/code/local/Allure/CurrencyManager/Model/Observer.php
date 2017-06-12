<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Model_Observer
{

    public function fixCurrencySwitchUrl(Varien_Event_Observer $observer)
    {
        $isFixEnabled = (int)Mage::getConfig()->getNode('default/currencymanager/additional/fix_currency_switch_url');
        if ($isFixEnabled) {
            //helper rewrite
            Mage::getConfig()->setNode('global/helpers/directory/rewrite/url', 'Allure_CurrencyManager_Helper_Url');

            //controller rewrite
            Mage::getConfig()->setNode('global/rewrite/currencymanager_switch_currency/from',
                '#^/directory/currency#');
            Mage::getConfig()->setNode('global/rewrite/currencymanager_switch_currency/to',
                '/currencymanager/currency');
        }
    }

    public function rewriteClasses(Varien_Event_Observer $observer)
    {
        $isRewriteEnabled = (int)Mage::getConfig()->getNode('default/currencymanager/additional/rewrite_classes');
        if ($isRewriteEnabled) {
            /** in CE version 1.8.1.0 tax functions declarations changed */
            Mage::getConfig()->setNode('global/helpers/tax/rewrite/data', 'Allure_CurrencyManager_Helper_Tax');
        }
    }

    /**
     * Remove html tags from currency symbol for PDF
     *
     * Event: currency_options_after_get
     *
     * @param Varien_Event_Observer $observer
     */
    public function removeHtmlTags(Varien_Event_Observer $observer)
    {
        $options = $observer->getData('options');
        if ($options instanceof Varien_Object) {

            /** @var Allure_CurrencyManager_Helper_Data $helper */
            $helper = Mage::helper('currencymanager');

            if ($helper->isNeedDropTags()) {
                $data = $options->getData();
                $data['format']['symbol'] = $helper->removeTags($data['format']['symbol']);
                $options->setData($data);
            }
        }
    }
}
