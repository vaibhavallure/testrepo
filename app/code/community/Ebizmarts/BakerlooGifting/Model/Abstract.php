<?php

/**
 * Gifting abstraction providing support for any integration.
 *
 * @package Ebizmarts_BakerlooGifting
 */
abstract class Ebizmarts_BakerlooGifting_Model_Abstract extends Varien_Object
{

    /** @var string */
    protected $_model;

    /** @var string */
    protected $_moduleName;

    /** @var string  */
    protected $_expirationField;

    /** @var string  */
    protected $_balanceField;

    /** @var mixed Stores gift card instance. */
    protected $_giftcard;

    /** @var bool Stores if the integration can be used. */
    protected $_canUse = false;

    /**
     * Check if the integration can be used.
     *
     * @return bool
     */
    public function canUse()
    {
        return $this->_canUse;
    }

    /**
     * Check if the integration is enabled in config.
     */
    public function isEnabled()
    {
        return ($this->_getGiftingConfig() == $this->_moduleName);
    }

    /**
     * Return config data from settings.
     *
     * @return string
     */
    protected function _getGiftingConfig()
    {
        return (string)Mage::helper('bakerloo_restful')->config('integrations/gifting');
    }

    public function getImp()
    {
        return $this->_giftcard;
    }

    /**
     * Return WebsiteId for a given StoreId.
     *
     * @param $storeId
     * @return null|int Website Id.
     */
    public function websiteIdByStoreId($storeId)
    {
        return Mage::getModel('core/store')->load($storeId)->getWebsiteId();
    }

    /**
     * Initialize internal gift card model.
     */
    public function init()
    {
        $this->_giftcard = Mage::getModel($this->_model)->loadByCode(trim($this->getCode()));
    }

    public function setGiftcard($giftcard)
    {
        $this->_giftcard = $giftcard;
    }

    abstract public function isValid();

    abstract public function addToCart(Mage_Sales_Model_Quote $quote);

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array array(
     *                  'id'          => <giftcard_id>,
     *                  'code'        => <giftcard_code>,
     *                  'base_amount' => <giftcard_base_amount>,
     *                  'amount'      => <giftcard_amount>,
     *               );
     */
    abstract public function getQuoteGiftCards(Mage_Sales_Model_Quote $quote);

    /**
     * Create a new gift card.
     *
     * @param string|int $storeId
     * @param string|float $amount
     * @param string|null $expirationDate
     * @return null|string Gift card code or null if not created.
     */
    public function create($storeId, $amount, $expirationDate = null)
    {

        $this->_giftcard
            ->setData('status', 1)
            ->setData('is_redeemable', 1)
            ->setData('website_id', $this->websiteIdByStoreId($storeId))
            ->setData($this->_balanceField, $amount);

        if (!is_null($expirationDate)) {
            $this->_giftcard->setData($this->_expirationField, $expirationDate);
        }

        $this->_giftcard->save();

        return $this->_giftcard->getCode();
    }

    /**
     * Add balance to an existing gift card.
     *
     * @param $amount
     * @param null $data
     * @return null|string Giftcard code or null if not created.
     */
    abstract public function addBalance($amount, $data = null);

    /**
     * Get gift card product options.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array();
     */
    abstract public function getOptions(Mage_Catalog_Model_Product $product);

    /**
     * Add type-dependent buy info on gift card product add to cart.
     *
     * @param $data
     * @return mixed
     */
    abstract public function getBuyInfoOptions($data);

    /**
     * Get gift card options form Buy Request.
     *
     * @param Varien_Object $buyRequest
     * @return mixed
     */
    abstract public function getBuyRequestOptions(Varien_Object $buyRequest);
}
