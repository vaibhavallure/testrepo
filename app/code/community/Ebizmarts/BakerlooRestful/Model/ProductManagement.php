<?php

class Ebizmarts_BakerlooRestful_Model_ProductManagement {

    /** @var Ebizmarts_BakerlooRestful_Helper_Data  */
    private $_helper;

    /** @var Mage_Catalog_Model_Product  */
    private $_productModel;

    private $_collection;

    private $_searchAttributes  = array('entity_id', 'sku', 'name');

    public function __construct(
        Mage_Catalog_Model_Product $product = null,
        Ebizmarts_BakerlooRestful_Helper_Data $helper = null
    )
    {
        if (is_null($product)) {
            $this->_productModel = Mage::getModel('catalog/product');
        } else {
            $this->_productModel = $product;
        }

        if (is_null($helper)) {
            $this->_helper = Mage::helper('bakerloo_restful');
        } else {
            $this->_helper = $helper;
        }
    }

    /**
     * @param null $storeId
     * @param bool $withStock
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCollection($storeId = null, $withStock = false)
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_productModel->getCollection();

            $this->_collection
                ->addAttributeToSelect('*')
                ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner')
                ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

            if ($storeId) {
                $this->_collection->addStoreFilter($storeId);
            }
            
            // START Allure Fixes - Add Stock Filter
            $stockId = Mage::getModel('cataloginventory/stock_item')->getStockId();

            if ($withStock) {
                $this->_collection
                    ->getSelect()
                    ->joinLeft(
                        array('cisi' => $this->_collection->getTable('cataloginventory/stock_item')),
                        'cisi.stock_id = '.$stockId.' AND cisi.product_id = e.entity_id',
                        array()
                    );
            }
            // END Allure Fixes
        }

        return $this->_collection;
    }

    public function addSearchAttributesToCollection($storeId)
    {
        $config = (string)$this->_helper->config('catalog/product_code', $storeId);
        $searchAttributes = $this->_helper->getBarcodeConfig($config);

        $searchAttributes = array_diff($searchAttributes, $this->_searchAttributes);
        foreach ($searchAttributes as $attrCode) {
            if (!empty($attrCode)) { //default empty attr is sometimes selected in config
                $this->getCollection()->joinAttribute($attrCode, 'catalog_product/' . $attrCode, 'entity_id', null, 'left');
            }
        }

        $this->getCollection()->addFieldToFilter('status', array('eq' => 1));

        $searchAttributes = array_unique(array_merge($this->_searchAttributes, $searchAttributes));
        return $searchAttributes;
    }

    /**
     * Prepare filters for online searches over product collections.
     * All attributes are matched with `like` except for entity IDs.
     *
     * @param $search
     * @param array $attributes
     * @return array
     */
    public function assembleFiltersForSearch($search, array $attributes)
    {

        $ret = array();

        if (!empty($search) and !empty($attributes)) {
            $search = filter_var($search, FILTER_SANITIZE_STRING);

            $attributesCount = count($attributes);

            for ($i=0; $i < $attributesCount; $i++) {
                if ($attributes[$i] == 'entity_id') {
                    if (is_numeric($search)) {
                        $ret[$attributes[$i]] = $attributes[$i] . ',eq,' . $search;
                    }
                } else {
                    $ret[$attributes[$i]] = $attributes[$i] . ',like,%' . $search . '%';
                }
            }
        }

        return $ret;
    }
}