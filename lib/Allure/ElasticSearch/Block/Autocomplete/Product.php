<?php

/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Block_Autocomplete_Product extends Allure_ElasticSearch_Block_Autocomplete_Abstract
{

    /**
     * @var string
     */
    protected $_title = 'Products';

    /**
     * @var string
     */
    protected $_template = 'allure/elasticsearch/autocomplete/product.phtml';

    /**
     * @param Varien_Object $product
     * @return string
     */
    public function getProductUrl(Varien_Object $product)
    {
        return $this->cleanUrl($product->getData('_url'));
    }

    /**
     * @return string
     */
    public function getImageSrc()
    {
        return $this->cleanUrl($this->getEntity()->getData('image'));
    }

    /**
     * @return int
     */
    public function getImageSize()
    {
        return $this->_config ? $this->_config->getConfig('product/image_size') : 50;
    }

    public function getDisplayImage()
    {
        return $this->_config ? $this->_config->getConfig('product/image') : 1;
    }

    /**
     * Returns price HTML of given product
     *
     * @param Varien_Object $product
     * @return string
     */
    public function getPriceHtml(Varien_Object $product)
    {
        $block = new Allure_ElasticSearch_Block_Autocomplete_Product_Price();
        $block->setCurrency($this->_currency);
        $block->setCustomerGroup($this->_customerGroup);
        $block->setEntity($product);
        $block->setConfig($this->_config);

        return $block->toHtml();
    }

    /**
     * @see Mage_Catalog_Model_Product_Visibility
     * @param array $data
     * @return bool
     */
    public function validate($data)
    {
        return isset($data['_prices_'.$this->_customerGroup]) && isset($data['visibility']) && $data['visibility'] >= 3;
    }

}
