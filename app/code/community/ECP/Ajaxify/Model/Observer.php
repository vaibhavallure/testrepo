<?php
/**
* Flush top AJAX block cache after user login / logout
*
* @category  ecp
* @package   ecp_Ajaxify
* @author    Oleksandr Zirka <oleksandr.zirka@smile.fr>
* @copyright 2013 Smile
*/
class ECP_Ajaxify_Model_Observer
{
    /**
     * Cookie that will be set if cart is not empty
     */
    const CART_NOT_EMPTY_COOKIE_NAME = 'cart_not_empty';
    
    /**
     * Cookie that will be set if product viewed
     */
    const PRODUCTS_VIEWED_COOKIE_NAME = 'products_viewed';
    
    /**
     * Cookie that will be set if user is logged in
     */
    const IS_LOGGED_IN_COOKIE_NAME = 'is_logged_in';
    
    /**
     * Cookie that will be set if user is logged in
     */
    const MESSAGE_ADDED_COOKIE_NAME = 'message_added';

    /**
     * @var array Blocks to flush
     */
    protected $_cachedBlockName = array('top_cart'=>'top_cart', 
                                        'product_viewed'=>'product_viewed',
                                        'top_links'=>'top_links',
                                        'shippingtext'=>'shippingtext',
                                        'messages'=>'messages');
    
    /**
     * Get cache key for top cart block
     *
     * @return void
     */
    public function removeAjaxBlockCache()
    {
        foreach ($this->_cachedBlockName as $cachedBlockName) {
            $cacheId = Mage::helper('ecp_ajaxify')->getBlockCacheId($cachedBlockName);
            Mage::app()->getCache()->remove($cacheId);
        }
    }
       
    /**
     * Get cache key for top cart block
     *
     * @return void
     */
    public function removeTopCartBlockCache()
    {   
            $cacheId = Mage::helper('ecp_ajaxify')->getBlockCacheId($this->_cachedBlockName['top_cart']);
            Mage::app()->getCache()->remove($cacheId);
    }
    
    /**
     * Get cache key for top cart block
     *
     * @return void
     */
    public function removeMessagesBlockCache()
    {   
            $cacheId = Mage::helper('ecp_ajaxify')->getBlockCacheId($this->_cachedBlockName['messages']);
            Mage::app()->getCache()->remove($cacheId);
    }
    
    /**
     * Get cache key for top cart block
     *
     * @return void
     */
    public function removeViewedBlockCache()
    {   
            $cacheId = Mage::helper('ecp_ajaxify')->getBlockCacheId($this->_cachedBlockName['product_viewed']);
            Mage::app()->getCache()->remove($cacheId);
    }
    
    /**
     * Get cache key for top cart block
     *
     * @return void
     */
    public function removeTopLinksBlockCache()
    {   
            $cacheId = Mage::helper('ecp_ajaxify')->getBlockCacheId($this->_cachedBlockName['top_links']);
            Mage::app()->getCache()->remove($cacheId);
    }

    /**
     * Add 'cart not empty' cookie if product placed in cart
     *
     * @return void
     */
    public function addCartNotEmptyCookie()
    {
        Mage::getSingleton('core/cookie')->set(self::CART_NOT_EMPTY_COOKIE_NAME, true, null, null, null, null, FALSE);
    }
    
    /**
     * Add 'message added' cookie.
     *
     * @return void
     */
    public function addMessageAddedCookie()
    {   
        $this->removeMessagesBlockCache();
        //Mage::getSingleton('core/cookie')->set(self::MESSAGE_ADDED_COOKIE_NAME, true, null, null, null, null, FALSE);
    }

    /**
     * Add 'is logged in' cookie after customer login
     *
     * @return void
     */
    public function addIsLoggedInCookie()
    {   
        $this->addCartNotEmptyCookie();
        Mage::getSingleton('core/cookie')->set(self::IS_LOGGED_IN_COOKIE_NAME, true, null, null, null, null, FALSE);
    }

    /**
     * Remove 'cart not empty' if cart is empty
     *
     * @return void
     */
    public function removeCartNotEmptyCookie()
    {
        if (count(Mage::getSingleton('checkout/session')->getQuote()->getAllItems()) == 0) {
            Mage::getSingleton('core/cookie')->delete(self::CART_NOT_EMPTY_COOKIE_NAME);
        }
    }

    /**
     * Remove 'message added' 
     *
     * @return void
     */
    public function removeMessageAddedCookie()
    {
       // Mage::getSingleton('core/cookie')->delete(self::MESSAGE_ADDED_COOKIE_NAME);
        $this->removeMessagesBlockCache();
    }

    /**
     * Remove 'is logged in' cookie after customer logout
     *
     * @return void
     */
    public function removeIsLoggedInCookie()
    {
        Mage::getSingleton('core/cookie')->delete(self::IS_LOGGED_IN_COOKIE_NAME);
        // Cart will be empty after logout
        Mage::getSingleton('core/cookie')->delete(self::CART_NOT_EMPTY_COOKIE_NAME);
        $this->removeTopLinksBlockCache();
    }
}
