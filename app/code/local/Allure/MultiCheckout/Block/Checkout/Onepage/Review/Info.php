<?php

/**
 * One page checkout order review
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Allure_MultiCheckout_Block_Checkout_Onepage_Review_Info extends Mage_Sales_Block_Items_Abstract
{

    public function getItems ()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
    }

    public function getTotals ()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getTotals();
    }

    // new code added by mt-allure
    public function getOrderedItems ()
    {
        return Mage::getSingleton('allure_multicheckout/ordered_session')->getQuote()->getAllVisibleItems();
    }

    // new code added by mt-allure
    public function getBackorderedItems ()
    {
        return Mage::getSingleton('allure_multicheckout/backordered_session')->getQuote()->getAllVisibleItems();
    }
    
    
    /*
     *Paypal Methods added
     *
     */
    
    public function getQuoteOrdered(){
        return Mage::getSingleton('checkoutstep/ordered_session')->getQuote();
    }
    
    public function getQuoteBackordred(){
        return Mage::getSingleton('checkoutstep/backordered_session')->getQuote();
    }
    
    public function getShippingMethodSubmitUrl(){
        return $this->getUrl("paypal/express/saveShippingMethod");
    }
    public function getCanEditShippingMethod(){
        $this->getQuoteOrdered()->getMayEditShippingMethod();
    }
    
    public function getCurrentShippingRate(){
        $address = $this->getQuoteOrdered()->getShippingAddress();
        $groups = $address->getGroupedAllShippingRates();
        $currentShippingRate = null;
        if ($groups && $address) {
            // determine current selected code & name
            foreach ($groups as $code => $rates) {
                foreach ($rates as $rate) {
                    if ($address->getShippingMethod() == $rate->getCode()) {
                        $currentShippingRate = $rate;
                        break(2);
                    }
                }
            }
        }
        return $currentShippingRate;
    }
    
    public function getShippingRateGroups(){
        $address = $this->getQuoteOrdered()->getShippingAddress();
        $groups = $address->getGroupedAllShippingRates();
        return $groups;
    }
    
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig("carriers/{$carrierCode}/title")) {
            return $name;
        }
        return $carrierCode;
    }
    
    public function renderShippingRateValue(Varien_Object $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }
        return $rate->getCode();
    }
    
    protected function _getShippingPrice($price, $isInclTax)
    {
        return $this->_formatPrice($this->helper('tax')->getShippingPrice($price, $isInclTax, $this->getQuoteOrdered()->getShippingAddress()));
    }
    
    protected function _getShippingPrice2($price, $isInclTax)
    {
        return $this->_formatPrice2($this->helper('tax')->getShippingPrice($price, $isInclTax, $this->getQuoteBackordred()->getShippingAddress()));
    }
    
    /**
     * Format price base on store convert price method
     *
     * @param float $price
     * @return string
     */
    protected function _formatPrice($price)
    {
        return $this->getQuoteOrdered()->getStore()->convertPrice($price, true);
    }
    
    protected function _formatPrice2($price)
    {
        return $this->getQuoteBackordred()->getStore()->convertPrice($price, true);
    }
    
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->_getShippingPrice($rate->getPrice(),
                $this->helper('tax')->displayShippingPriceIncludingTax());
            
            $incl = $this->_getShippingPrice($rate->getPrice(), true);
            if (($incl != $price) && $this->helper('tax')->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, Mage::helper('tax')->__('Incl. Tax'), $incl);
            }
        }
        return sprintf($format, $this->escapeHtml($rate->getMethodTitle()), $price, $renderedInclTax);
    }
    
    
    public function renderShippingRateOption2($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->_getShippingPrice2($rate->getPrice(),
                $this->helper('tax')->displayShippingPriceIncludingTax());
            
            $incl = $this->_getShippingPrice2($rate->getPrice(), true);
            if (($incl != $price) && $this->helper('tax')->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, Mage::helper('tax')->__('Incl. Tax'), $incl);
            }
        }
        return sprintf($format, $this->escapeHtml($rate->getMethodTitle()), $price, $renderedInclTax);
    }
    
    
    
    
    public function getCurrentShippingRate2(){
        $address = $this->getQuoteBackordred()->getShippingAddress();
        $groups = $address->getGroupedAllShippingRates();
        $currentShippingRate = null;
        if ($groups && $address) {
            // determine current selected code & name
            foreach ($groups as $code => $rates) {
                foreach ($rates as $rate) {
                    if ($address->getShippingMethod() == $rate->getCode()) {
                        $currentShippingRate = $rate;
                        break(2);
                    }
                }
            }
        }
        return $currentShippingRate;
    }
    
    public function getShippingRateGroups2(){
        $address = $this->getQuoteBackordred()->getShippingAddress();
        $groups = $address->getGroupedAllShippingRates();
        return $groups;
    }
    
    const GIFT_TYPE = "gift";
    
    public function addGiftItemRender($type, $block, $template)
    {
        parent::addItemRender($type, $block, $template);
        return $this;
    }
    
    /**
     * Return row-level item html
     *
     * @param Varien_Object $item
     * @return string
     */
    public function getGiftItemHtml(Varien_Object $item)
    {
        $block = $this->getGiftItemRenderer(self::GIFT_TYPE);
        $block->setItem($item);
        $this->_prepareItem($block);
        return $block->toHtml();
    }
    
    
    public function getGiftItemRenderer($type = self::GIFT_TYPE)
    {
        if (!isset($this->_itemRenders[$type])) {
            $type = self::GIFT_TYPE;
        }
        
        if (is_null($this->_itemRenders[$type]['renderer'])) {
            $this->_itemRenders[$type]['renderer'] = $this->getLayout()
            ->createBlock($this->_itemRenders[$type]['block'])
            ->setTemplate($this->_itemRenders[$type]['template'])
            ->setRenderedBlock($this);
        }
        return $this->_itemRenders[$type]['renderer'];
    }
}
