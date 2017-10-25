<?php

class Ebizmarts_BakerlooRestful_Model_Api_Products extends Ebizmarts_BakerlooRestful_Model_Api_Api
{

    private $_useOR             = false;
    private $_productCollection = null;
    private $_searchAttributes  = array('entity_id', 'sku', 'name');

    /** @var Ebizmarts_BakerlooRestful_Model_ProductManagement  */
    private $_manager;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_product';

    protected $_model = "catalog/product";

    const IMAGES_CONF_PATH = 'default/bakerloorestful/product/imagesizes';

    public function __construct($params) {
        parent::__construct($params);

        $this->_manager = new Ebizmarts_BakerlooRestful_Model_ProductManagement();
    }

    public function getPageSize()
    {

        $limit = intval($this->_getQueryParameter('limit'));
        if ((!is_null($this->getFilterByName('category_id')) or $this->isOnlineSearch()) and $limit) {
            return $limit;
        }

        return $this->getSafePageSize();
    }

    public function reassembleRequestUrl()
    {
        $filters = $this->_getQueryParameter('filters');

        if ($this->isOnlineSearch() and is_array($filters)) {
            //unset search filters for next page
            foreach ($filters as $_key => $_filter) {
                if (in_array($_key, $this->_searchAttributes)) {
                    unset($filters[$_key]);
                }
            }

            $this->parameters['filters'] = $filters;
        }

        return parent::reassembleRequestUrl();
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param bool $useOR
     */
    public function applyFilters($filters, $useOR = false)
    {
        /**
         * This is just for online catalog; we remove the filter and apply the top level category
         * filter to the collection and Magento does the rest.
         *
         * In older versions we asked our API consumers to send all the CategoryIds -parent and childs- when
         * the parent category is_anchor, with this new change we just need the top level category.
         * This change solves the sort by position issue as well.
         */
        $filterByNameCategory = $this->getFilterByName('category_id');
        $filterPositionCategory = $this->getFilterByName('category_id', null, true);
        if (!is_null($filterPositionCategory)) {
            unset($filters[$filterPositionCategory]);
        }

        $filterByNameStock = $this->getFilterByName('is_in_stock');
        $filterPositionStock = $this->getFilterByName('is_in_stock', null, true);
        if (!is_null($filterPositionStock)) {
            unset($filters[$filterPositionStock]);
        }

        parent::applyFilters($filters, $this->_useOR);

        if (!is_null($filterByNameCategory)) {
            $categoryId = $this->returnFirstValueForFilter(array($filterByNameCategory), 'category_id');
            $category = $this->getModel('catalog/category')->load($categoryId);

            if ($category->getId()) {
                $this->_sortByPosition($this->_getCollection(), $category);
            }
        }

        if (!is_null($filterByNameStock)) {
            $stockFilters = $this->explodeFilter($filterByNameStock);

            if (array_key_exists(2, $stockFilters) and !empty($stockFilters[2])) {
                $isInStock = implode(',', $stockFilters[2]);
                $globalManageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK, $this->getStoreId());
                $skipTypes = sprintf('\'%s\', \'%s\', \'%s\'', Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, Mage_Catalog_Model_Product_Type::TYPE_GROUPED);
                $typeBundle = Mage_Catalog_Model_Product_Type::TYPE_BUNDLE;

                $this->_getCollection()
                    ->getSelect()
                    ->joinLeft(
                        array('child' => $this->_getCollection()->getTable('catalog/product_relation')),
                        'child.parent_id = e.entity_id',
                        array()
                    );

                /* @var $subquerySelect Varien_Db_Select */
                $childStockSelect = $this->getNewSelect();
                $childStockSelect->from(array('child_stock' => $this->_getCollection()->getTable('cataloginventory/stock_item')), array('item_id'));
                $childStockSelect->where("(child_stock.use_config_manage_stock = 0 AND child_stock.manage_stock = 0) OR (((child_stock.use_config_manage_stock = 1 AND {$globalManageStock} = 1) OR (child_stock.manage_stock = 1)) AND child_stock.is_in_stock in ({$isInStock}))");
                $childStockSelect->where('child_stock.product_id = child.child_id');

                $bundleSelect = new Varien_Db_Select(Mage::getSingleton('core/resource')->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE));
                $bundleSelect->from(array('bundle_option' => $this->_getCollection()->getTable('bundle/option')));
                $bundleSelect->joinInner(
                    array('bundle_selection' => $this->_getCollection()->getTable('bundle/selection')),
                    'bundle_selection.option_id = bundle_option.option_id AND bundle_selection.parent_product_id = bundle_option.parent_id',
                    array()
                );
                $bundleSelect->joinInner(
                    array('bundle_child_stock' => $this->_getCollection()->getTable('cataloginventory/stock_item')),
                    'bundle_child_stock.product_id = bundle_selection.product_id',
                    array()
                );
                $bundleSelect->where('bundle_option.parent_id = e.entity_id AND bundle_child_stock.is_in_stock = 0 AND bundle_option.required = 1');

                if ($this->isOnlineSearch()) {
                    $onlineSearchSubselect = $this->_addOnlineSearchSubselect($filters, true, $isInStock);
                    $subQuery = new Zend_Db_Expr("(((cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0) OR (((cisi.use_config_manage_stock = 1 AND {$globalManageStock} = 1) OR (cisi.manage_stock = 1)) AND cisi.is_in_stock in ({$isInStock}))) AND e.type_id NOT IN ({$skipTypes})) OR (e.type_id != '{$typeBundle}' AND EXISTS ({$childStockSelect}) OR (e.type_id = '{$typeBundle}' AND NOT EXISTS ({$bundleSelect})))");
                    $this->_getCollection()->getSelect()->where($subQuery, null, Varien_Db_Select::TYPE_CONDITION);
                    $this->_getCollection()->getSelect()->orWhere("EXISTS ({$onlineSearchSubselect})", null, Varien_Db_Select::TYPE_CONDITION);
                } else {
                    $subQuery = new Zend_Db_Expr("(((cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0) OR (((cisi.use_config_manage_stock = 1 AND {$globalManageStock} = 1) OR (cisi.manage_stock = 1)) AND cisi.is_in_stock in ({$isInStock}))) AND e.type_id NOT IN ({$skipTypes})) OR (e.type_id != '{$typeBundle}' AND EXISTS ({$childStockSelect}) OR (e.type_id = '{$typeBundle}' AND NOT EXISTS ({$bundleSelect})))");
                    $this->_getCollection()->getSelect()->where($subQuery, null, Varien_Db_Select::TYPE_CONDITION);
                    $this->_getCollection()->groupByAttribute('entity_id');
                }
            }
        } elseif ($this->isOnlineSearch()) {
            $onlineSearchSubselect = $this->_addOnlineSearchSubselect($filters);
            $subQuery = new Zend_Db_Expr("EXISTS ({$onlineSearchSubselect})");
            $this->_getCollection()->getSelect()->orWhere($subQuery, null, Varien_Db_Select::TYPE_CONDITION);
        }
    }

    private function _addOnlineSearchSubselect($filters, $addStockFilter = false, $inStockValues = null)
    {

        /* @var $reader Varien_Db_Adapter_Interface */
        $connection = $this->getModel('core/resource', true);

        /* @var $subquerySelect Varien_Db_Select */
        $subquerySelect = $this->getSubquerySelect($connection);
        $subquerySelect->from(array('child_e' => $connection->getTableName($this->_model)));

        //copy joins
        $from = $subquerySelect->getPart(Zend_Db_Select::FROM);
        $originalFrom = $this->_getCollection()->getSelect()->getPart(Zend_Db_Select::FROM);
        foreach ($originalFrom as $_alias => $_fromSection) {
            if (!isset($_fromSection['joinCondition'])) {
                continue;
            } elseif (preg_match('/catalog_product_relation/', $_fromSection['tableName'])) {
                continue;
            }

            $_fromSection['joinCondition'] = preg_replace('/' . $_alias . '/', 'child_' . $_alias, $_fromSection['joinCondition']);
            $_fromSection['joinCondition'] = preg_replace('/`e`/', '`child_e`', $_fromSection['joinCondition']);

            if (preg_match('/product_website/', $_alias)) { //website filter hardcoded
                $_fromSection['joinCondition'] = preg_replace('/child_product_website.product_id = e.entity_id/', 'child_product_website.product_id = child_e.entity_id', $_fromSection['joinCondition']);
            } elseif (preg_match('/cisi/', $_alias)) { //cisi filter hardcoded
                $_fromSection['joinCondition'] = preg_replace('/child_cisi.product_id = e.entity_id/', 'child_cisi.product_id = child_e.entity_id', $_fromSection['joinCondition']);
            } elseif (preg_match('/cat_index/', $_alias)) {
                $_fromSection['joinCondition'] = preg_replace('/child_cat_index.product_id=e.entity_id/', 'child_cat_index.product_id = child_e.entity_id', $_fromSection['joinCondition']);
            }

            $from['child_' . $_alias] = $_fromSection;
        }

        $subquerySelect->setPart(Zend_Db_Select::FROM, $from);

        //copy columns
        $columns = array(); //save columns for filters
        $originalColumns = $this->_getCollection()->getSelect()->getPart(Zend_Db_Select::COLUMNS);
        foreach ($originalColumns as $_column) {
            list($t, $e, $a) = $_column;

            if ($e == Zend_Db_Select::SQL_WILDCARD) {
                continue;
            }

            $table = preg_replace('/' . $t . '/', 'child_' . $t, $t);
            if ($e instanceof Zend_Db_Expr) {
                $expression = preg_replace('/' . $t . '/', 'child_' . $t, (string)$e);
                $expression = new Zend_Db_Expr($expression);
            } else {
                $expression = $e;
            }

            $subquerySelect->columns(array($a => $expression), $table);
            $columns[$a] = array($table, $expression);
            $originalColumns[$a] = array($t, (string)$e);
        }

        //add order
        $subquerySelect->order(new Zend_Db_Expr('child_e.' . $this->defaultSort . ' ' . $this->defaultDir));

        //add filters
        if (!empty($filters)) {
            $where = array();
            foreach ($filters as $_filter) {
                list($attributeCode, $condition, $value) = $this->explodeFilter($_filter);

                $condition = is_numeric($value) ? $condition = '=' : $condition = strtoupper($condition);
                $value     = is_array($value) ? sprintf('(%s)', implode(',', $value)) : sprintf('\'%s\'', $value);

                if (isset($columns[$attributeCode])) {
                    list($t, $e) = $columns[$attributeCode];

                    if ($e instanceof Zend_Db_Expr) {
                        $c = sprintf("(%s %s %s)", (string)$e, $condition, (string)$value);
                    } else {
                        $c = sprintf("(%s.%s %s %s)", $t, $e, $condition, (string)$value);
                    }
                } else {
                    $c = sprintf("(%s %s %s)", 'child_e.' . (string)$attributeCode, $condition, (string)$value);
                }

                $where[] = $c;
            }

            if (!empty($where)) {
                $where = implode(' ' . Zend_Db_Select::SQL_OR . ' ', $where);
                $subquerySelect->where($where);
            }
        }

        if (isset($columns['status'])) {
            list($t, $e) = $columns['status'];
            $parentStatus = $originalColumns['status'];

            if ($e instanceof Zend_Db_Expr) {
                $statusExpression = sprintf("(%s %s AND %s %s)", (string)$e, '= 1', $parentStatus[1], '= 1');
            } else {
                $statusExpression = sprintf("(%s.%s %s AND %s %s)", $t, (string)$e, '= 1', $parentStatus[1], '= 1');
            }
            
            $subquerySelect->where($statusExpression);
        }

        if (isset($columns['visibility'])) {
            list($t, $e) = $columns['visibility'];

            if ($e instanceof Zend_Db_Expr) {
                $subquerySelect->where(sprintf("(%s %s %d)", (string)$e, '=', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
            } else {
                $subquerySelect->where(sprintf("(%s.%s %s %d)", $t, (string)$e, '=', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
            }

        }

        if ($addStockFilter) {
            if (!is_null($inStockValues)) {
                $subquerySelect->where('child_cisi.is_in_stock IN (' . $inStockValues . ') OR child_cisi.manage_stock = 0', null, Varien_Db_Select::TYPE_CONDITION);
            } else {
                $subquerySelect->where('child_cisi.is_in_stock = 1 OR child_cisi.manage_stock = 0', null, Varien_Db_Select::TYPE_CONDITION);
            }
        } else {
            $this->_getCollection()->joinTable(
                array('child' =>  $this->_getCollection()->getTable('catalog/product_relation')),
                'parent_id=entity_id',
                array('child_id' => 'child_id'),
                null,
                'left'
            );
        }

        $subquerySelect->where('child_e.entity_id = child.child_id');

        $this->_getCollection()
            ->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->groupByAttribute('entity_id');

        return $subquerySelect;
    }

    protected function _getCacheTags(array $result)
    {
        $tags = array('pos_product_cache');

        $products = $result['page_data'];

        foreach ($products as $_prod) {
            $tags[] = $_prod['product_id'];

            if (isset ($_prod['children']) and !empty($_prod['children'])) {
                $tags = array_merge($tags, $_prod['children']);
            }
        }

        return array_unique($tags);
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get()
    {

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        // check response is cached.
        $allowCache = $this->getConfig('catalog/allow_result_caching', $this->getStoreId());
        $key = $this->_getCacheKey();

        if ($allowCache) {
            $cached = $this->getCache($key);

            if ($cached) {
                return unserialize($cached);
            }
        }

        $this->_getCollection();
        $search = $this->_getQueryParameter('search');

        if (!is_null($search)) {
            $this->_searchAttributes = $this->_manager->addSearchAttributesToCollection($this->getStoreId());
            $searchFilters = $this->_manager->assembleFiltersForSearch($search, $this->_searchAttributes);

            if (array_key_exists('filters', $this->parameters)) {
                $this->parameters['filters'] += $searchFilters;
            } else {
                $this->parameters['filters'] = $searchFilters;
            }
        }

        if ($this->isOnlineSearch() or !is_null($search)) {
            $this->_useOR = true;
        }

        $result = parent::get();

        // save cache only for collections
        if ($allowCache && isset($result['page_data'])) {
            $this->saveCache($result, $key, $this->_getCacheTags($result));
        }

        return $result;
    }

    protected function isOnlineSearch()
    {
        return !is_null($this->_getQueryParameter('online_search'));
    }

    /**
     * Use since from external table instead of catalog_product table.
     *
     * @param $collection
     * @param $page
     * @param null $since
     * @return $this
     */
    public function _beforePaginateCollection($collection, $page, $since = null)
    {

        if ("catalog/product" == $this->_model) {
            return parent::_beforePaginateCollection($collection, $page, $since);
        }

        if (!$this->_productCollection) {
            $this->_productCollection = $collection;
        }

        $this->_productCollection->addFieldToFilter(
            'store_id',
            array(
                    array('eq'   => $this->getStoreId()),
                    array('null' => true),
            )
        );

        return $this;
    }

    protected function _getIndexId()
    {
        if ($this->_model == "bakerloo_restful/catalogtrash") {
            return 'product_id';
        }

        return parent::_getIndexId();
    }

    protected function _getCollection()
    {
        if ("catalog/product" != $this->_model) {
            return parent::_getCollection();
        }

        if (is_null($this->_productCollection)) {
            $withStock = ($this->isOnlineSearch() or !is_null($this->getFilterByName('is_in_stock')));
            $this->_productCollection = $this->_manager->getCollection($this->getStoreId(), $withStock);
        }

        return $this->_productCollection;
    }

    protected function _sortByPosition($collection, $category)
    {
        $collection->addCategoryFilter($category);
        $collection->addAttributeToSort('position');
    }

    public function _createDataObject($id = null, $data = null)
    {

        if (is_object($data) and ($data instanceof Ebizmarts_BakerlooRestful_Model_Catalogtrash)) {
            return $data->getData();
        }

        $since = $this->_getQueryParameter('since');

        Varien_Profiler::start('POS::' . __METHOD__);

        $result  = array();
        $product = $this->getModel($this->_model)->setStoreId($this->getStoreId())->load($id);
        if ($product->getId()) {
            //If data is null, no need to go to DB to fetch images
            if (is_null($data)) {
                $gallery = $product->getMediaGalleryImages();
            } else {
                $gallery = Mage::getModel('catalog/product')
                            ->setStoreId($this->getStoreId())
                            ->load($product->getId())
                            ->getMediaGalleryImages();
            }
            //Main image, some customers only have one image and use it as "exclude"
            if (($product->getImage() != 'no_selection') and $product->getImage()) {
                $mainImage = array(
                                   'file'     => $product->getImage(),
                                   'position' => 0,//@ToDo
                                   'label'    => $product->getData('image_label'),
                                   'url'      => Mage::getSingleton('catalog/product_media_config')->getMediaUrl($product->getImage()),
                );
                $gallery->addItem(new Varien_Object($mainImage));
            }

            //Images
            $galleryUrls = $this->getGalleryImages($gallery, $product);
            $result['images']                  = $galleryUrls;
            $result['description']             = (string) $product->getDescription();
            $result['short_description']       = (string) $product->getShortDescription();
            $result['use_description']         = (string) Mage::helper('bakerloo_restful')->config('catalog/description', $this->getStoreId());
            $result['last_update']             = $product->getUpdatedAt();
            $result['name']                    = $product->getName();
            $result['price']                   = $this->_getProductPrice($product);
            $result['minimal_price']           = $result['price'];
            $result['maximal_price']           = $result['price'];
            $result['product_id']              = (int) $product->getId();
            $result['sku']                     = $product->getSku();
            $result['barcode']                 = (string) $this->getHelperRestful()->getProductBarcode($product->getId(), $this->getStoreId());
            $result['special_price']           = (float)$product->getSpecialPrice(); //don't cast to allow null values
            $result['special_price_from_date'] = (string) $product->getSpecialFromDate();
            $result['special_price_to_date']   = (string) $product->getSpecialToDate();
            $result['store_id']                = (int) $product->getStoreId();
            $result['tax_class']               = (int) $product->getTaxClassId();
            $result['visibility']              = (int) $product->getVisibility(); //1- Not visible individually; 2- Catalog; 3- Search; 4-Catalog, Search
            $result['status']                  = (int) $product->getStatus(); //1- Enabled; 2- Disabled
            $result['type']                    = $product->getTypeId();
            $result['categories']              = $this->_getCategories($product);
            $result['tier_pricing']            = $this->_getTierPrice($product);
            $result['group_pricing']           = $this->_getGroupPrice($product);
            //Adding cross sell, up sell and related products
            $this->_addRelatedProductsData($product, $result);
            //configurable details
            $associatedProductsArray = array();
            $attributeOptions        = array();
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $attributeConfig = $this->getAttributesConfig($product);

                //attributes
                $attributesData = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach ($attributesData as $productAttribute) {
                    $attributeValues = array();
                    foreach ($productAttribute['values'] as $attribute) {
                        $attributeValues[] = array(
                                                   'label'         => (string)$attribute['label'],
                                                   'value_index'   => (int)$attribute['value_index'],
                                                   'pricing_value' => (float)$attribute['pricing_value'],
                                                   'is_percent'    => (int)$attribute['is_percent']
                                                  );
                    }

                    //Attribute config for dependencies
                    $config = array();
                    if (isset($attributeConfig[$productAttribute['attribute_code']]['options'])) {
                        $config = $attributeConfig[$productAttribute['attribute_code']]['options'];
                    }

                    if (!empty($config)) { //Avoid attributes without options (Configurables without children)
                        $attributeOptions[] = array(
                                                    'attribute_code'  => $productAttribute['attribute_code'],
                                                    'attribute_label' => $productAttribute['label'],
                                                    'values'          => $attributeValues,
                                                    'config'          => $config
                        );

                        foreach ($config as $_attrConfig) {
                            $associatedProductsArray = array_merge($associatedProductsArray, $_attrConfig['products']);
                        }
                    }
                }

                unset($attributeConfig);
            } elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
                $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);

                foreach ($associatedProducts as $_child) {
                    $associatedProductsArray []= (int)$_child->getId();
                }
            } elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $product->getTypeInstance(true)->setStoreFilter($this->getStoreId(), $product);

                $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

                $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );

                $optionsArray = $optionCollection->appendSelections($selectionCollection, false, false);

                $bundleAttributeOptions = array();
                $selected               = array();

                foreach ($optionsArray as $_option) {
                    if (!$_option->getSelections()) {
                        continue;
                    }

                    $option = array (
                        'id'         => (int)$_option->getOptionId(),
                        'title'      => $_option->getTitle(),
                        'type'       => (string)$_option->getType(),
                        'required'   => (int)$_option->getRequired(),
                        'position'   => (int)$_option->getPosition(),
                        'selections' => array()
                    );

                    $selectionCount = count($_option->getSelections());

                    foreach ($_option->getSelections() as $_selection) {
                        $_qty = !($_selection->getSelectionQty()*1)?'1':$_selection->getSelectionQty()*1;
                        $selection = array (
                            'id'           => (int)$_selection->getSelectionId(),
                            'qty'          => ($_qty * 1),
                            'canChangeQty' => (int)$_selection->getSelectionCanChangeQty(),
                            'price'        => Mage::helper('core')->currency($_selection->getFinalPrice(), false, false),
                            'priceValue'   => Mage::helper('core')->currency($_selection->getSelectionPriceValue(), false, false),
                            'priceType'    => $_selection->getSelectionPriceType(),
                            'tierPrice'    => $_selection->getTierPrice(),
                            'name'         => $_selection->getName(),
                            'product_id'   => (int)$_selection->getId(),
                            'position'     => (int)$_selection->getPosition(),
                            'is_default'   => (int)$_selection->getIsDefault(),
                        );
                        /*$responseObject = new Varien_Object();
                        $args = array('response_object'=>$responseObject, 'selection'=>$_selection);
                        Mage::dispatchEvent('bundle_product_view_config', $args);
                        if (is_array($responseObject->getAdditionalOptions())) {
                            foreach ($responseObject->getAdditionalOptions() as $o=>$v) {
                                $selection[$o] = $v;
                            }
                        }*/
                        $option['selections'][] = $selection;

                        if (($_selection->getIsDefault() || ($selectionCount == 1 && $_option->getRequired())) && $_selection->isSalable()) {
                            $selected[$_option->getId()][] = $_selection->getSelectionId();
                        }

                        // add selections to children attribute
                        $associatedProductsArray[] = (int)$_selection->getId();
                    }
                    $bundleAttributeOptions[] = $option;
                }

                $result['bundle_option'] = $bundleAttributeOptions;
                $priceType = 'dynamic';
                if ($product->getPriceType()) {
                    $priceType = 'fixed';
                }
                $result['price_type'] = $priceType;

                //add minimal and maximal prices
                list($minPrice, $maxPrice)  = $product->getPriceModel()->getTotalPrices($product, null, null, false);
                $result['minimal_price']    = $minPrice;
                $result['maximal_price']    = $maxPrice;
            }
            $result['attributes'] = $attributeOptions;
            $result['children']   = array_unique($associatedProductsArray);

            //Custom Options
            $customOptions = array();
            $options       = $product->getOptions();

            if (count($options)) {
                $customOptions = $this->_getProductCustomOptions($product, $options);
            }

            $result['options'] = $customOptions;

            //Gift card options
            $result['gift_card_options'] = $this->getGiftCardProductOptions($product);

            //Store credit product options
            $result['store_credit_options'] = $this->getStoreCreditProductOptions($product);

            Varien_Profiler::start('POS::' . __METHOD__ . '::additional_attributes');

            //Additional attributes
            $result['additional_attributes'] = $this->getAdditionalAttributes($product);

            Varien_Profiler::stop('POS::' . __METHOD__ . '::additional_attributes');
            if ($since != -1) {
                $result['inventory'] = $this->getModel('bakerloo_restful/api_inventory')->setStoreId($this->getStoreId())->_createDataObject($product->getId());
            }
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $this->returnDataObject($result);
    }

    private function _getProductCustomOptions(Mage_Catalog_Model_Product $product, $options)
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        $customOptions = array();

        $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
        foreach ($options as $option) {
            /* @var $option Mage_Catalog_Model_Product_Option */

            $value = array();

            $value['option_id']  = (int)$option->getOptionId();
            $value['title']      = (string)$option->getTitle();
            $value['type']       = (string)$option->getType();
            $value['is_require'] = (int)$option->getIsRequire();
            $value['sort_order'] = (int)$option->getSortOrder();

            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                $i = 0;
                $itemCount = 0;
                foreach ($option->getValues() as $_value) {
                    /* @var $_value Mage_Catalog_Model_Product_Option_Value */
                    $value['option_values'][$i] = array(
                        'option_type_id' => (int)$_value->getOptionTypeId(),
                        'title'          => (string)$_value->getTitle(),
                        'price'          => (float)$this->getPriceValue($_value->getPrice(), $_value->getPriceType()),
                        'price_type'     => (string)$_value->getPriceType(),
                        'sku'            => (string)$_value->getSku(),
                        'sort_order'     => (int)$_value->getSortOrder(),
                    );

                    $i++;
                }
            } else {
                $value['price']          = (float)$this->getPriceValue($option->getPrice(), $option->getPriceType());
                $value['price_type']     = (string)$option->getPriceType();
                $value['sku']            = (string)$option->getSku();
                $value['max_characters'] = (int)$option->getMaxCharacters();
            }

            $customOptions[] = $value;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $customOptions;
    }

    public function getAttributesConfig($_product)
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        $attributes = array();
        $options    = array();

        $products    = array();
        $allProducts = $_product->getTypeInstance(true)
            ->getUsedProducts(null, $_product);

        foreach ($allProducts as $product) {
            //if ($product->isSaleable()) {
                $products[] = $product;
            //}
        }

        $allowAttributes = $_product->getTypeInstance(true)
            ->getConfigurableAttributes($_product);

        foreach ($products as $product) {
            $productId  = $product->getId();

            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();

                if (!is_object($productAttribute)) {
                    // START Allure Fixes
                    Mage::log("Attribute error: " . $attribute->getLabel() . '-' . $attribute->getProductId(), Zend_Log::DEBUG, 'pos_exception.log', true);
                    continue;
                    // END Allure Fixes
                    //Mage::throwException("Attribute error: " . $attribute->getLabel() . '-' . $attribute->getProductId());
                }

                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttribute->getId()])) {
                    $options[$productAttribute->getId()] = array();
                }

                if (!isset($options[$productAttribute->getId()][$attributeValue])) {
                    $options[$productAttribute->getId()][$attributeValue] = array();
                }
                $options[$productAttribute->getId()][$attributeValue][] = (int)$productId;
            }
        }

        foreach ($allowAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();

            if (!is_object($productAttribute)) {
                // START Allure Fixes
                Mage::log("Attribute error: " . $attribute->getLabel() . '-' . $attribute->getProductId(), Zend_Log::DEBUG, 'pos_exception.log', true);
                continue;
                // END Allure Fixes
                //Mage::throwException("Attribute error: " . $attribute->getLabel() . '-' . $attribute->getProductId());
            }

            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => (int)$productAttribute->getId(),
                'attribute_code' => $productAttribute->getAttributeCode(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!isset($options[$attributeId][$value['value_index']])) {
                        continue;
                    }

                    $info['options'][] = array(
                        'value_index'   => (int)$value['value_index'],
                        'products'      => isset($options[$attributeId][$value['value_index']]) ? $options[$attributeId][$value['value_index']] : array(),
                    );
                }
            }
            $attributes[$productAttribute->getAttributeCode()] = $info;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $attributes;
    }

    public function getPriceValue($value)
    {
        return number_format($value, 2, null, '');
    }

    private function _addRelatedProductsData(Mage_Catalog_Model_Product $product, array &$result)
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        $related = array(
                         'cross_sell' => 'getCrossSellProducts',
                         'related'    => 'getRelatedProducts',
                         'up_sell'    => 'getUpSellProducts'
                        );

        foreach ($related as $key => $method) {
            $products = array();

            $related = $product->{$method}();
            foreach ($related as $prod) {
                $products[] = array('product_id' => (int)$prod->getId());
            }

            $result [$key]= $products;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    private function _getTierPrice($product)
    {
        return $this->_priceStruct($product, 'tier_price');
    }

    private function _getGroupPrice($product)
    {
        if (version_compare(Mage::getVersion(), '1.7.0.0', '<')) {
            return array();
        }

        return $this->_priceStruct($product, 'group_price');
    }

    private function _priceStruct($product, $dataType)
    {
        $dataPrice = array();

        $dataPriceData = $product->getData($dataType);

        if (is_array($dataPriceData) && !empty($dataPriceData)) {
            foreach ($dataPriceData as $_tprice) {
                $_tprice['price_id']          = (int)$_tprice['price_id'];
                $_tprice['website_id']        = (int)$_tprice['website_id'];
                $_tprice['all_groups']        = (int)$_tprice['all_groups'];
                $_tprice['customer_group_id'] = (int)$_tprice['cust_group'];

                unset($_tprice['cust_group']);

                if (isset($_tprice['price_qty'])) {
                    $_tprice['price_qty'] = (float)$_tprice['price_qty'];
                }

                $_tprice['price']             = (float)$_tprice['price'];
                $_tprice['website_price']     = (float)$_tprice['website_price'];

                $dataPrice [] = $_tprice;
            }
        }

        return $dataPrice;
    }

    /**
     * Retrieve the last sold item
     *
     * @return array
     * @throws Exception
     */
    public function getLastSold()
    {
        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a store ID.');
        }

        $model = $this->getModel('bakerloo_restful/api_lastSoldProducts');
        $latest = $model->_getAllItems();
        return $latest;
    }

    /**
     * Retrieve the last sold item
     *
     * @return array
     * @throws Exception
     */
    public function getBestseller()
    {
        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a store ID.');
        }

        $model = $this->getModel('bakerloo_restful/api_bestSellingProducts');
        $bestSellers = $model->_getAllItems();
        return $bestSellers;
    }

    /**
     * Retrieve DELETED or removed from website products.
     *
     * @return Collection data.
     */
    public function trashed()
    {
        $this->checkGetPermissions();

        $this->_model = 'bakerloo_restful/catalogtrash';
        $this->_iterator = false;

        //get page
        $page = $this->_getQueryParameter('page');
        if (!$page) {
            $page = 1;
        }

        $myFilters = array();
        $since     = $this->_getQueryParameter('since');
        if (!is_null($since)) {
            array_push($myFilters, "updated_at,gt,{$since}");
        }

        $filters = $this->_getQueryParameter('filters');
        if (is_null($filters)) {
            $filters = $myFilters;
        } else {
            $filters = array_merge($filters, $myFilters);
        }

        return $this->_getAllItems($page, $filters);
    }

    /**
     * Clear all POS product cache
     */
    public function resetCache()
    {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('pos_product_cache'));

        return 'OK';
    }

    /**
     * Retrieve product price correctly from real object.
     *
     * @param $product
     * @return float
     */
    protected function _getProductPrice($product)
    {

        $price = $product->getPrice();

        //Avoid price tricks from this module, just give me the configurable price.
        if ($product instanceof OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product) {
            $_product = new Mage_Catalog_Model_Product();
            $_product->setPriceCalculation(false);
            $_product->load($product->getId());

            $price = $_product->getPrice();
        }

        return (float)$price;
    }

    /**
     * Return categories with product position data.
     *
     * @param $product
     * @return array
     */
    public function _getCategories($product)
    {

        Varien_Profiler::start('POS::' . __METHOD__);

        $cats = $product->getCategoryIds();

        $categories = array();

        for ($i=0; $i<count($cats); $i++) {
            $categoryId = $cats[$i];

            $myCategoryData = array(
              'category_id' => $categoryId,
              'position'    => 0,
            );

            $positions = $this->categoryProductPositions($categoryId);
            if (!empty($positions)) {
                $exists = array_key_exists(((int)$product->getId()), $positions);
                if ($exists) {
                    $myCategoryData['position'] = (int)$positions[$product->getId()];
                }
            }

            $categories []= $myCategoryData;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $categories;
    }

    private function categoryProductPositions($categoryId)
    {
        $key = 'pos_categories_pos_' . $categoryId;

        $positionsRegistry = Mage::registry($key);
        if (is_null($positionsRegistry)) {
            $category  = new Varien_Object(array('id' => $categoryId));
            $positions = Mage::getResourceModel('catalog/category')->getProductsPosition($category);
            Mage::register($key, $positions);
        }

        return Mage::registry($key);
    }

    /**
     * @param $gallery
     * @param $product
     * @return array
     */
    private function getGalleryImages($gallery, $product)
    {
        $galleryUrls = array();

        if (is_null($gallery) or !$gallery->getSize()) {
            return $galleryUrls;
        }

        $thumbnail = $product->getThumbnail();
        $smallImage = $product->getSmallImage();
        $baseImage = $product->getImage();

        foreach ($gallery as $_image) {
            //If image is disabled do not use
            if ((int)$_image->getDisabled() === 1) {
                continue;
            }

            $_imageData = array();

            $_imageData['position'] = (int)$_image->getPosition();

            $_imageData['is_base'] = ($_image->getFile() == $baseImage ? 1 : 0);
            $_imageData['is_small'] = ($_image->getFile() == $smallImage ? 1 : 0);
            $_imageData['is_thumbnail'] = ($_image->getFile() == $thumbnail ? 1 : 0);

            $_imageData['large'] = $_image->getUrl();
            $_imageData['label'] = $_image->getLabel();

            $imagesConf = Mage::getConfig()->getNode(self::IMAGES_CONF_PATH)->asArray();

            foreach ($imagesConf as $code => $size) {
                $_size = explode('x', $size);
                $width = $_size[0];
                $height = $_size[1];

                $thumb = Mage::helper('bakerloo_restful')->getResizedImageUrl($product->getId(), $this->getStoreId(), $_image->getFile(), (int)$width, (int)$height);
                $_imageData[$code] = (string)$thumb;
            }

            $galleryUrls[] = $_imageData;

            $_imageData = null;
            $thumb = null;

            unset($_imageData);
            unset($thumb);
        }

        return $galleryUrls;
    }

    /**
     * @param $product
     * @return array
     */
    private function getAdditionalAttributes($product)
    {
        $additionalAttributeData = array();

        $additionalAttributesConfig = (string) Mage::helper('bakerloo_restful')->config('catalog/additional_attributes', $this->getStoreId());
        $attributes = !empty($additionalAttributesConfig) ? explode(',', $additionalAttributesConfig) : array();

        foreach ($attributes as $_attributeCode) {
            if (!strlen($_attributeCode)) {
                continue;
            }

            $_attributeValue = $product->getAttributeText($_attributeCode);
            if (!$_attributeValue) {
                $method = 'get' . uc_words($_attributeCode, '');
                if (is_callable(array($product, $method))) {
                    $_attributeValue = $product->$method();
                }

                if (!$_attributeValue) {
                    $_attributeValue = $product->getData($_attributeCode);

                    if (!$_attributeValue) {
                        $_attributeValue = '';
                    }
                }
            }

            //Array values not supported on the app.
            if (is_array($_attributeValue)) {
                continue;
            }

            $_attr = $product->getResource()->getAttribute($_attributeCode);

            if ($_attr->getFrontendInput() == 'boolean') {
                $_attributeValue = $_attributeValue == 'Yes' ? "1" : "0";
            }

            $additionalAttributeData [] = array(
                'name' => $_attributeCode,
                'label' => $_attr->getFrontendLabel(),
                'type' => $_attr->getFrontendInput(),
                'value' => $_attributeValue,
            );
        }

        return $additionalAttributeData;
    }

    /**
     * @param $product
     */
    private function getGiftCardProductOptions($product)
    {
        if (Mage::helper('bakerloo_gifting')->productIsGiftcard($product)) {
            return Mage::helper('bakerloo_gifting')->getGiftcardOptions($product);
        }

        return null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    private function getStoreCreditProductOptions($product)
    {
        if ($product->getTypeId() === 'customercredit') {
            $options = array();
            $aux = $product->getPriceModel()->getCreditAmount($product);
            
            $options['type'] = $aux['type'];
            switch ($options['type']) {
                case 'static':
                    $options['min_amount'] = $aux['value'];
                    $options['max_amount'] = $aux['value'];
                    $options['amounts'] = null;
                    break;
                case 'range':
                    $options['min_amount'] = $aux['from'];
                    $options['max_amount'] = $aux['to'];
                    $options['amounts'] = null;
                    break;
                case 'dropdown':
                    $options['min_amount'] = (string)$aux['min_price'];
                    $options['max_amount'] = (string)$aux['max_price'];
                    $options['amounts'] = $aux['prices'];
                    break;
                default:
                    break;
            }
            $options['credit_rate'] = $product->getCreditRate();

            return $options;
        }

        return null;
    }

    public function shareProduct()
    {
        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());
        $data = $this->getJsonPayload(true);

        if (!isset($data['sender_email'])) {
            Mage::throwException('Please provide a valid sender email address.');
        }

        $senderEmail = filter_var($data['sender_email'], FILTER_VALIDATE_EMAIL);
        if ($senderEmail === false) {
            Mage::throwException('Please provide a valid sender email address.');
        }

        if (!isset($data['recipients']) or empty($data['recipients'])) {
            Mage::throwException('Please provide at least one recipient.');
        }

        if (!isset($data['product_id'])) {
            Mage::throwException('Please provide a Product ID.');
        }

        $product = Mage::getModel('catalog/product')->load($data['product_id']);
        if (!$product->getId()) {
            Mage::throwException('Product could not be found.');
        }

        $dataObject = new Varien_Object($data);

        /** @var Ebizmarts_BakerlooRestful_Helper_Email $helper */
        $helper = $this->getHelper('bakerloo_restful/email');
        $helper->sendProduct($dataObject, $product, $this->getStoreId());

        return array('sent' => $helper->getEmailSent());
    }

    public function getNewSelect()
    {
        return new Varien_Db_Select(Mage::getSingleton('core/resource')->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE));
    }

    public function getSubquerySelect($connection)
    {
        return new Varien_Db_Select($connection->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE));
    }
}
