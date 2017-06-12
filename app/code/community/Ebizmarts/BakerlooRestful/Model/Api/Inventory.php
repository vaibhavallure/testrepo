<?php

class Ebizmarts_BakerlooRestful_Model_Api_Inventory extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const SEARCH_PARAM = 'search';

    /** @var Ebizmarts_BakerlooRestful_Model_ProductManagement  */
    private $_manager;

    private $_isOnlineSearch;
    private $_useOR = false;

    protected $_model   = 'cataloginventory/stock_item';
    public $defaultSort = "main_table.updated_at";

    public function __construct($params, $manager = null)
    {
        parent::__construct($params);

        if (is_null($manager)) {
            $this->_manager = new Ebizmarts_BakerlooRestful_Model_ProductManagement();
        } else {
            $this->_manager = $manager;
        }
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get()
    {

        $this->checkGetPermissions();

        $identifier = $this->_getIdentifier();

        if ($identifier) { //get item by id
            $helper = $this->getHelper('bakerloo_restful');

            if (is_numeric($identifier)) {
                $product = $this->getModel('catalog/product')->load($identifier);
                if (false === $helper->productIsInStore($product, $this->getStoreId())) {
                    Mage::throwException($helper->__('Product does not exist in store.'));
                }

                return $this->_createDataObject((int)$identifier, $product);
            } else {
                throw new Exception($helper->__('Incorrect request.'));
            }
        } elseif ($this->_getQueryParameter(self::SEARCH_PARAM)) {
            return $this->onlineSearch();
        } else {
            //get page
            $page = $this->_getQueryParameter('page');
            if (!$page) {
                $page = 1;
            }

            $filters     = $this->_getQueryParameter('filters');
            $resultArray = $this->_getAllItems($page, $filters);

            return $resultArray;
        }
    }

    /**
     * Retrieve inventory data for a given array of product ids.
     */
    public function multiple()
    {
        $ids = explode(",", $this->_getQueryParameter('products'));

        $result = array();

        if (is_array($ids) && !empty($ids)) {
            $nrOfIds = count($ids);
            for ($i = 0; $i < $nrOfIds; $i++) {
                $data = $this->_createDataObject($ids[$i]);

                if (is_array($data) && !empty($data)) {
                    $result[] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * Update inventory for a given product.
     *
     * @return array
     */
    public function put()
    {
        parent::put();

        if (!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload(true);

        $productId = (isset($data['product_id']) ? ((int)$data['product_id']) : null);

        $product = $this->getModel('catalog/product')->setStoreId($this->getStoreId())->load($productId);

        $oldData = clone $product->getStockItem();

        if ($product->getId()) {
            $product->getStockItem()
                ->setQty($data['qty'])
                ->setIsInStock($data['is_in_stock'])
                ->setManageStock($data['manage_stock'])
                ->save();

            Mage::dispatchEvent("pos_update_inventory", array("product" => $product, "old_stock_item" => $oldData, "new_stock_item" => $product->getStockItem()));
            $this->getModel('bakerloo_restful/api_products')->resetCache();
        } else {
            Mage::throwException("Product does not exist. {$productId}");
        }

        return $this->_createDataObject($product->getId());
    }

    /**
     * Return stock data min qty.
     *
     * @param $stockData object CatalogInventory object
     * @return array|float
     */
    public function getMinSaleQtyAllCustomerGroups($stockData)
    {

        $allGroups = array('customer_group_id' => Mage_Customer_Model_Group::CUST_GROUP_ALL, 'customer_group_code' => 'ALL GROUPS');

        if ((int)$stockData->getUseConfigMinSaleQty() === 0) {
            $allGroups['min_sale_qty'] = (float)$stockData->getMinSaleQty();

            return array($allGroups);
        }

        $allCustomerGroupsArray = $this->getModel('customer/group')
            ->getCollection()
            ->toArray();
        $allCustomerGroupsArray = $allCustomerGroupsArray['items'];

        array_push($allCustomerGroupsArray, $allGroups);

        $allGroupsCount = count($allCustomerGroupsArray);
        $helper = $this->getHelper('cataloginventory/minsaleqty');

        for ($i=0; $i < $allGroupsCount; $i++) {
            $_configQty = $helper->getConfigValue($allCustomerGroupsArray[$i]['customer_group_id']);

            $allCustomerGroupsArray[$i]['customer_group_id'] = (int)$allCustomerGroupsArray[$i]['customer_group_id'];

            if (isset($allCustomerGroupsArray[$i]['tax_class_id'])) {
                unset($allCustomerGroupsArray[$i]['tax_class_id']);
            }

            if (isset($allCustomerGroupsArray[$i]['bakerloo_payment_methods'])) {
                unset($allCustomerGroupsArray[$i]['bakerloo_payment_methods']);
            }

            if (empty($_configQty)) {
                unset($allCustomerGroupsArray[$i]);
            } else {
                $allCustomerGroupsArray[$i]['min_sale_qty'] = (float)$_configQty;
            }
        }

        return array_values($allCustomerGroupsArray);
    }

    /**
     * Process online inventory searches by product fields.
     */
    public function onlineSearch()
    {
        $search = $this->_getQueryParameter(self::SEARCH_PARAM);

        $this->_getCollection();
        $searchAttributes = $this->_manager->addSearchAttributesToCollection($this->getStoreId());
        $searchFilters = $this->_manager->assembleFiltersForSearch($search, $searchAttributes);

        if (array_key_exists('filters', $this->parameters)) {
            $this->parameters['filters'] += $searchFilters;
        } else {
            $this->parameters['filters'] = $searchFilters;
        }

        $this->_useOR = true;

        //get page
        $page = $this->_getQueryParameter('page');
        if (!$page) {
            $page = 1;
        }

        return $this->_getAllItems($page, $this->parameters['filters']);
    }

    public function applyFilters($filters, $useOR = false)
    {
        parent::applyFilters($filters, $this->_useOR);
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->getSafePageSize();
    }

    /**
     * @return bool
     */
    private function isOnlineSearch()
    {
        if (is_null($this->_isOnlineSearch)) {
            $this->_isOnlineSearch = !is_null($this->_getQueryParameter(self::SEARCH_PARAM));
        }

        return $this->_isOnlineSearch;
    }

    protected function _getCollection()
    {
        if (is_null($this->_collection)) {
            if ($this->isOnlineSearch()) {
                $this->_collection = $this->_manager->getCollection($this->getStoreId(), true);
                $this->_model = 'catalog/product';
                $this->defaultSort = 'e.updated_at';
            } else {
                $this->_collection = parent::_getCollection();
            }
        }

        return $this->_collection;
    }

    /**
     * @return string
     */
    protected function _getIndexId()
    {
        if ($this->isOnlineSearch()) {
            return parent::_getIndexId();
        }
        else {
            return 'product_id';
        }
    }

    public function _createDataObject($id = null, $data = null)
    {
        if ($this->isOnlineSearch()) {
            return $this->getModel('bakerloo_restful/api_products')->_createDataObject($id, $data);
        } else {
            return $this->getInventoryDataObject($id, $data);
        }
    }

    public function getInventoryDataObject($id = null, $data = null)
    {
        Mage::app()->setCurrentStore($this->getStoreId());

        $result = new Varien_Object;

        $stockItem = $this->getModel($this->_model)->load($id, 'product_id');
        $product   = is_null($data) ? $this->getModel('catalog/product')->load($stockItem->getProductId()) : $data;

        if ($stockItem->getId()) {
            $stockData = clone $stockItem;

            $stockData->setMinimumSaleQty($this->getMinSaleQtyAllCustomerGroups($stockData));

            if (((int)$this->getHelperRestful()->config('catalog/allow_backorders'))) {
                $stockData->setBackorders(1);
                if (!Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES)) {
                    Mage::register(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES, true);
                }
            }

            Mage::dispatchEvent("pos_get_inventory", array("product" => $product, "stock_item" => $stockData));

            $updatedAt = $stockData->getUpdatedAt();
            if ($stockData->getUpdatedAt() == '0000-00-00 00:00:00' or is_null($updatedAt)) {
                $updatedAt = '0001-01-01 00:00:00';
            }


            $result = array(
                'backorders'              => (int)$stockData->getBackorders(),
                'enable_qty_increments'   => (int)$stockData->getEnableQtyIncrements(),
                'is_qty_decimal'          => (int)$stockData->getIsQtyDecimal(),
                'is_in_stock'             => (int)$stockData->getIsInStock(),
                'manage_stock'            => (int)$stockData->getManageStock(),
                'manage_stock_use_config' => (int)$stockData->getUseConfigManageStock(),
                'product_id'              => (int)$stockData->getProductId(),
                'qty'                     => (is_null($stockData->getQty()) ? 0.0000 : $stockData->getQty()),
                'qty_increments'          => ($stockData->getQtyIncrements() === false ? 0.0000 : $stockData->getQtyIncrements()),
                'store_id'                => $stockData->getStoreId(),
                'updated_at'              => $updatedAt,
                'min_sale_qty'            => $stockData->getMinimumSaleQty(),
                'max_sale_qty'            => $stockData->getMaxSaleQty()
            );
        }

        return $result;
    }
}
