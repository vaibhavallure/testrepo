<?php

class Allure_MultiCheckout_Block_Checkout_Cart_Totals extends Mage_Checkout_Block_Cart_Abstract
{

    protected $_totalRenderers;

    protected $_defaultRenderer = 'checkout/total_default';

    protected $_totals = null;

    protected $_totals_ordered = null;

    protected $_totals_backordered = null;

    public function getTotals ()
    {
        if (is_null($this->_totals)) {
            return parent::getTotals();
        }
        return $this->_totals;
    }

    public function getTotalsOrdered ()
    {
        if (is_null($this->_totals_ordered)) {
            $this->_totals_ordered = $this->getOrderedQuote()->getTotals();
        }
        return $this->_totals_ordered;
    }

    public function getTotalsBackordered ()
    {
        if (is_null($this->_totals_backordered)) {
            $this->_totals_backordered = $this->getBackorderedQuote()->getTotals();
        }
        return $this->_totals_backordered;
    }

    public function setTotals ($value)
    {
        $this->_totals = $value;
        return $this;
    }

    protected function _getTotalRenderer ($code)
    {
        $blockName = $code . '_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        
        if (! $block) {
            $block = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode(
                    "global/sales/quote/totals/{$code}/renderer");
            if ($config) {
                $block = (string) $config;
            }
            
            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotals());
        return $block;
    }

    protected function _getTotalRendererOrdered ($code)
    {
        $blockName = $code . '_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        if (! $block) {
            $block = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode(
                    "global/sales/quote/totals/{$code}/renderer");
            if ($config) {
                $block = (string) $config;
            }
            
            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotalsOrdered());
        return $block;
    }

    protected function _getTotalRendererBackordered ($code)
    {
        $blockName = $code . '_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        if (! $block) {
            $block = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode(
                    "global/sales/quote/totals/{$code}/renderer");
            if ($config) {
                $block = (string) $config;
            }
            
            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotalsBackordered());
        return $block;
    }

    public function renderTotal ($total, $area = null, $colspan = 1)
    {
        $code = $total->getCode();
        if ($total->getAs()) {
            $code = $total->getAs();
        }
        return $this->_getTotalRenderer($code)
            ->setTotal($total)
            ->setColspan($colspan)
            ->setRenderingArea(is_null($area) ? - 1 : $area)
            ->toHtml();
    }

    public function renderTotalOrdered ($total, $area = null, $colspan = 1)
    {
        $code = $total->getCode();
        if ($total->getAs()) {
            $code = $total->getAs();
        }
        return $this->_getTotalRendererOrdered($code)
            ->setTotal($total)
            ->setColspan($colspan)
            ->setRenderingArea(is_null($area) ? - 1 : $area)
            ->toHtml();
    }

    public function renderTotalBackordered ($total, $area = null, $colspan = 1)
    {
        $code = $total->getCode();
        if ($total->getAs()) {
            $code = $total->getAs();
        }
        return $this->_getTotalRendererBackordered($code)
            ->setTotal($total)
            ->setColspan($colspan)
            ->setRenderingArea(is_null($area) ? - 1 : $area)
            ->toHtml();
    }

    /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param null|string $area
     * @param int $colspan
     * @return string
     */
    public function renderTotals ($area = null, $colspan = 1)
    {
        $html = '';
        foreach ($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != - 1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }

    public function renderTotalsOrdered ($area = null, $colspan = 1)
    {
        $html = '';
        foreach ($this->getTotalsOrdered() as $total) {
            if ($total->getArea() != $area && $area != - 1) {
                continue;
            }
            $html .= $this->renderTotalOrdered($total, $area, $colspan);
        }
        return $html;
    }

    public function renderTotalsBackordered ($area = null, $colspan = 1)
    {
        $html = '';
        foreach ($this->getTotalsBackordered() as $total) {
            if ($total->getArea() != $area && $area != - 1) {
                continue;
            }
            $html .= $this->renderTotalBackordered($total, $area, $colspan);
        }
        return $html;
    }

    /**
     * Check if we have display grand total in base currency
     *
     * @return bool
     */
    public function needDisplayBaseGrandtotal ()
    {
        $quote = $this->getQuote();
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            return true;
        }
        return false;
    }

    public function needDisplayBaseGrandtotalOrdered ()
    {
        $quote = $this->getOrderedQuote();
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            return true;
        }
        return false;
    }

    public function needDisplayBaseGrandtotalBackordered ()
    {
        $quote = $this->getBackorderedQuote();
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            return true;
        }
        return false;
    }

    /**
     * Get formated in base currency base grand total value
     *
     * @return string
     */
    public function displayBaseGrandtotal ()
    {
        $firstTotal = reset($this->_totals);
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            return Mage::app()->getStore()
                ->getBaseCurrency()
                ->format($total, array(), true);
        }
        return '-';
    }

    public function displayBaseGrandtotalOrdered ()
    {
        $firstTotal = reset($this->_totals_ordered);
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            return Mage::app()->getStore()
                ->getBaseCurrency()
                ->format($total, array(), true);
        }
        return '-';
    }

    public function displayBaseGrandtotalBackordered ()
    {
        $firstTotal = reset($this->_totals_backordered);
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            return Mage::app()->getStore()
                ->getBaseCurrency()
                ->format($total, array(), true);
        }
        return '-';
    }

    /**
     * Get active or custom quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote ()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote();
        }
        
        if (null === $this->_quote) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function getOrderedQuote ()
    {
        $quote = Mage::getSingleton("allure_multicheckout/ordered_session")->getQuote();
        $quote->collectTotals()->save();
        return $quote;
    }

    public function getBackorderedQuote ()
    {
        $quote = Mage::getSingleton("allure_multicheckout/backordered_session")->getQuote();
        $quote->collectTotals()->save();
        return $quote;
    }
}
