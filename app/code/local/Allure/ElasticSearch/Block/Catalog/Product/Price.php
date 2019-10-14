<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function setUseLinkForAsLowAs($bool = true)
    {
        $this->_useLinkForAsLowAs = $bool;

        return $this;
    }
}