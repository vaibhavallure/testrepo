<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart model
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Allure_ApplePay_Model_Cart extends Mage_Checkout_Model_Cart
{
    
    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('allure_applepay/session');
    }
    
    public function getProductIds()
    {
        $quoteId = Mage::getSingleton('allure_applepay/session')->getQuoteId();
        if (null === $this->_productIds) {
            $this->_productIds = array();
            if ($this->getSummaryQty()>0) {
                foreach ($this->getQuote()->getAllItems() as $item) {
                    $this->_productIds[] = $item->getProductId();
                }
            }
            $this->_productIds = array_unique($this->_productIds);
        }
        return $this->_productIds;
    }
    
    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = Mage::getSingleton('allure_applepay/session')->getQuoteId();
        
        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && Mage::getSingleton('customer/session')->isLoggedIn()) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
        }
        
        if ($quoteId && $this->_summaryQty === null) {
            if (Mage::getStoreConfig('checkout/cart_link/use_qty')) {
                $this->_summaryQty = $this->getItemsQty();
            } else {
                $this->_summaryQty = $this->getItemsCount();
            }
        }
        return $this->_summaryQty;
    }
}
