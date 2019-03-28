<?php
/**
 * Boundle products import/updating model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Package extends Teamwork_Transfer_Model_Transfer
{
    /**
     * Attribute model
     *
     * @var Teamwork_Transfer_Model_Class_Attribute
     */
    protected $_attributeModel;

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     */
    public function init($globalVars)
    {
        $this->_globalVars = $globalVars;
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_attributeModel = Mage::getModel("teamwork_transfer/class_attribute");

        $records = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_collection'), array('collection_id', 'internal_id'))
            ->where('internal_id is not null');
        $records = $this->_db->fetchAll($records);

        if(!empty($records))
        {
            foreach($records as $rec)
            {
                $this->_collections[$rec['collection_id']] = $rec['internal_id'];
            }
        }
    }

    /**
     * Entry point
     */
    public function execute()
    {
        try
        {
            $this->_getStagingItems();
        }
        catch(Exception $e)
        {
            $this->_getLogger()->addMessage(sprintf("Should not be here: file: %s; line: %s", __FILE__, __LINE__), Zend_Log::DEBUG);
            $this->_getLogger()->addException($e);
            $this->_addErrorMsg("Internal error (exception): " . $e->getMessage(), false);
        }
        $this->updateEcm('Package');

        return $this;
    }

    /**
     * Get data from staging tables and initiate import/update process.
     */
    protected function _getStagingItems()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_package');

        $select = $this->_db->select()->from($table)->where('request_id = ?', $this->_globalVars['request_id']);
        $packages = $this->_db->fetchAll($select);
        if(is_array($packages))
        {
            foreach($packages as $package)
            {
                $this->_insertBundle($package);
            }
            //Mage::getModel('catalogrule/rule')->applyAll();
        }
    }

    /**
     * Create/update boundle product
     *
     * @param array $package
     */
    protected function _insertBundle($package)
    {
        $product = Mage::getModel('catalog/product');
        $table_package_collection = Mage::getSingleton('core/resource')->getTableName('service_package_collection');
        $table_collection = Mage::getSingleton('core/resource')->getTableName('service_collection');
        $table = Mage::getSingleton('core/resource')->getTableName('service_package');
        $table_pack_cat = Mage::getSingleton('core/resource')->getTableName('service_package_category');
        $table_cat = Mage::getSingleton('core/resource')->getTableName('service_category');

        /*$select = $this->_db->select()
            ->from(array($table),array('internal_id'))
        ->where('package_id = ?', $package['package_id']);

        $debugMsgWord = "inserting";
        if($internal_id = $this->_db->fetchOne($select))*/
        $debugMsgWord = "inserting";
        if($internal_id = $package['internal_id'])
        {
            $product->load($internal_id);
            if ($product->getId()) {
                $debugMsgWord = "updating";
            } else {
                $this->_getLogger()->addMessage(sprintf("Error: boundle product updating: there was %s internal id in %s table for %s package product for which are absent", $internal_id, $table, $package['package_id']), Zend_Log::ERR);
            }
        }

        /*get magento category ids for boundle product*/
        $select = $this->_db->select()
            ->from(array('pc' => $table_pack_cat), array())
            ->joinLeft(array('cat' => $table_cat), 'pc.category_id = cat.category_id', array('internal_id'))
        ->where('pc.package_id = ?', $package['package_id']);
        $categories = $this->_db->fetchAll($select);

        /*general data of boundle parent*/
        $array = array(
            'sku_type'                  => 0,
            'sku'                       => $package['package_id'],
            'name'                      => $package['description'],
            'description'               => $package['notes'],
            'short_description'         => $package['description'],
            'type_id'                   => Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            'attribute_set_id'          => Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId(),
            'weight_type'               => 0,
            'visibility'                => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'price_type'                => Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED,
            'price'                     => 0,
            'tax_class_id'              => 0,
            'price_view'                => 0,
            'status'                    => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
            'created_at'                => strtotime('now'),
            'category_ids'              => $categories,
            'store_id'                  => $this->_globalVars['store_id'],
            'website_ids'               => $this->_globalVars['websites']
        );

        $collections = $this->_getPackageCollections($package['package_id']);
        if(!empty($collections))
        {
            $array[$this->_attributeModel->getAttributeCodeByName('Collection')] = implode(',', $collections);
        }
        $product->setData($array);
        Mage::register('product', $product);

        $optionRawData = array();

        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_package_component'))
            ->where('package_id = ?', $package['package_id'])
        ->order(array('comp_no ASC'));

        $options = $this->_db->fetchAll($select);
        $selectionRawData = array();

        /*add groups*/
        foreach($options as $key => $option)
        {
            $optionRawData[$key] = array(
                'required'         => !$option['allow_none'],
                'option_id'        => '',
                'position'         => $key,
                'type'             => $option['allow_multiple'] ? 'checkbox' : 'radio',
                'title'            => $option['description'],
                'default_title'    => $option['description'],
                'delete'           => ''
            );

            /*get children qty and price data belongs to group*/
            $select = $this->_db->select()
                ->from(Mage::getSingleton('core/resource')->getTableName('service_package_component_element'))
                ->where('package_id = ?', $package['package_id'])
            ->where('no = ?', $option['comp_no']);

            $selections = $this->_db->fetchAll($select);
            $selectionRawData[$key] = array();

            /*attach children to group*/
            foreach ($selections as $k => $selection)
            {
                /*get magento product id*/
                $select = $this->_db->select()
                    ->from($table = Mage::getSingleton('core/resource')->getTableName('service_items'),array('internal_id'))
                    ->where('item_id = ?', $selection['item_id'])
                ->where('channel_id = ?', $this->_globalVars['channel_id']);

                if(!$internal_id = $this->_db->fetchOne($select))
                {
                    //the all children should be cereated before
                    Mage::unregister('product');
                    $this->_addErrorMsg(sprintf("Error occured while boundle product {$debugMsgWord}: sku: %s", $product->getSku()), true);
                    return;
                }

                $selectionRawData[$key][$k] = array(
                    'product_id'                => $internal_id,
                    'selection_qty'             => $selection['quantity'],
                    'selection_can_change_qty'  => 0,
                    'position'                  => $k,
                    'is_default'                => $selection['is_component_default'],
                    'selection_id'              => '',
                    'selection_price_type'      => 0,
                    'selection_price_value'     => $selection['price'],
                    'option_id'                 => '',
                    'delete'                    => ''
                );
            }
        }
        $product->setStockData(array(
            'use_config_manage_stock'           => 1,
            'qty'                               => 1,
            'use_config_min_qty'                => 1,
            'use_config_min_sale_qty'           => 1,
            'use_config_max_sale_qty'           => 1,
            'is_qty_decimal'                    => 0,
            'is_decimal_divided'                => 0,
            'use_config_backorders'             => 1,
            'use_config_notify_stock_qty'       => 1,
            'use_config_enable_qty_increments'  => 1,
            'use_config_qty_increments'         => 1,
            'is_in_stock'                       => 1
        ));
        $product->setBundleOptionsData($optionRawData);
        $product->setBundleSelectionsData($selectionRawData);
        $product->setCanSaveBundleSelections(true);
        $product->setAffectBundleProductSelections(true);
        try
        {
            $product->save();
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while boundle product {$debugMsgWord}: sku: %s: %s", $product->getSku(), $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }

        /* $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($product->getId());
        if (!$stockItem->getId()) {
            $stockItem->setProductId($product->getId())->setStockId(1);
        }
        $stockItem->setData('is_in_stock', 1);
        $stockItem->save();
        $pi = Mage::getSingleton('bundle/price_index');
        $pi->addPriceIndexToProduct($product);
        $pi->save(); */

        $table = Mage::getSingleton('core/resource')->getTableName('service_package');
        $this->_db->update($table,array('internal_id' => $product->getId()), "package_id = '".$package['package_id']."'");
        Mage::unregister('product');
    }

    /**
     * Get "collection" attribute values from staging tables
     *
     * @param string $package
     *
     * @return array
     */
    protected function _getPackageCollections($package_id)
    {
        $collections = array();
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_package_collection'), array('collection_id'))
        ->where('package_id = ?', $package_id);

        if($results = $this->_db->fetchAll($select))
        {
            foreach($results as $res)
            {
                $collections[] = $this->_collections[$res['collection_id']];
            }
        }
        return $collections;
    }
}