<?php
/**
 * Product updating model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Item extends Teamwork_Transfer_Model_Transfer
{
    /**
     * CHQ product type SingleItem
     *
     * @const string
     */
    const CHQ_PRODUCT_TYPE_SINGLEITEM  = 'SingleItem';

    /**
     * CHQ product type ServiceItem
     *
     * @const string
     */
    const CHQ_PRODUCT_TYPE_SERVICEITEM = 'ServiceItem';

    /**
     * CHQ product type Style
     *
     * @const string
     */
    const CHQ_PRODUCT_TYPE_STYLE       = 'Style';

    /**
     * Attribute model
     *
     * @var Teamwork_Transfer_Model_Class_Attribute
     */
    protected $_attributeModel;

    /**
     * Data from service_attribute_set table
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Data from service_attribute_value table
     *
     * @var array
     */
    protected $_attributeValues;

    /**
     * Mapping model
     *
     * @var Teamwork_Service_Model_Mapping
     */
    protected $_mapModel;

    /**
     * Styles which is needed to import/update (configurable parrent products' data)
     *
     * @var array
     */
    protected $_styles;

    /**
     * Items which is needed to import/update (configurable children / simple products' data)
     *
     * @var array
     */
    protected $_items;

    /**
     * Media model
     *
     * @var Teamwork_Transfer_Model_Media
     */
    protected $_mediaModel;

    /**
     * Quantity model
     *
     * @var Teamwork_Transfer_Model_Class_Quantity
     */
    protected $_quantityModel;

    /**
     * Enable product custom options import
     *
     * @var bool
     */
    protected $_allowCustomOptionsImport = false;

    /**
     * Custom fields count
     *
     * @var int
     */
    protected $_optionQuantityOfCustomFields = 6;

    /**
     * Prefix of custom text column name in "service_style" table for custom options import process
     *
     * @var string
     */
    protected $_optionNameField = 'customtext';

    /**
     * Prefix of custom price column name in "service_style" table for custom options import process
     *
     * @var string
     */
    protected $_optionValueField = 'customnumber';

    /**
     * Custom options' title
     *
     * @var string
     */
    protected $_optionTitle = 'Additional goods';

    /**
     * Default weight value
     *
     * @var float
     */
    protected $_defaultWeight = 1.0000;

    /**
     * The number or records from service_style table keeped for import proccess
     *
     * @var int
     */
    protected $_stylesPerStep = 10;

    /**
     * Relationships types
     *
     * @var array
     */
    protected $_neededRelatedTypes = array('Related', 'UpSell', 'CrossSell');

    /**
     *  Category model
     *
     * @var Teamwork_Transfer_Model_Class_Category
     */
    protected $_categoryModel = null;

    /**
     *  Model's cache
     *
     * @var array
     */
    protected $_cache = array();

    /**
     *  Delimiter that divides values of multiselect attribute while it is converting to text
     *
     * @const string
     */
    protected $_convertMultiselectToTextDelimiter = ', ';

    /**
     *  CHQ to magento product compliance
     *
     * @var array
     */
    protected $_productTypes = array(
        self::CHQ_PRODUCT_TYPE_SINGLEITEM    => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        self::CHQ_PRODUCT_TYPE_SERVICEITEM   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        self::CHQ_PRODUCT_TYPE_STYLE         => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
    );

    /**
     * Images just added to the product. Used in _addProductImages and _saveImageInternalIds methods. Nedded for saving image internal_ids.
     *
     * @var array (<file> => <media_uri>)
     */
    protected $_recentlyLoadedProductImages = array();

    /**
     * Cache var for keeping media attributes of current product
     *
     * @var array
     */
    protected $_productMediaAttributesCache = array('unique_id' => null, 'media_attributes' => array());

    /**
     * Cache array to keep attribute codes
     *
     * @var array
     */
    protected $_magentoAttributesIdsCache = array();

    const GALLERY_ATTRIBUTE_CODE = 'media_gallery';

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     */
    public function init($globalVars)
    {
        $this->_globalVars = $globalVars;
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');

        $this->_attributeModel = Mage::getModel('teamwork_transfer/class_attribute');
        $this->_attributeModel->init($globalVars, array('class_item_object' => $this));

        $this->_mediaModel = Mage::getModel('teamwork_transfer/media');

        $this->_quantityModel = Mage::getModel('teamwork_transfer/class_quantity');
        $this->_quantityModel->init($globalVars);

        $this->_mapModel = Mage::getModel('teamwork_service/mapping');
        $this->_mapModel->getMappingFields($this->_globalVars['channel_id'], true);

        $this->_categoryModel = Mage::getModel('teamwork_transfer/class_category');
        $this->_categoryModel->init($globalVars);
    }

    /**
     * Entry point
     */
    public function execute()
    {
        try
        {
            Mage::helper('teamwork_transfer/reindex')->registerReindex();
            $this->_getStagingItems();
        }
        catch(Exception $e)
        {
            $this->_getLogger()->addMessage(sprintf("Should not be here: file: %s; line: %s", __FILE__, __LINE__), Zend_Log::DEBUG);
            $this->_getLogger()->addException($e);
            $this->_addErrorMsg('Internal error (exception): ' . $e->getMessage(), false);
        }

        try
        {
            if($this->_canImportProductImages())
            {
                Mage::getModel('catalog/product_image')->clearCache();
                Mage::dispatchEvent('clean_catalog_images_cache_after');
            }
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg('Internal error (exception): ' . $e->getMessage(), true);
            $this->_getLogger()->addException($e);
        }

        $this->updateEcm('Styles');
        return $this;
    }

    /**
     * Get data from staging tables and initiate import/update process.
     */
    protected function _getStagingItems()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $offset = 0;
        $result = array();

        $this->initAttributeData();
        $internal_ids = array();

        /*processing products' data using $this->_stylesPerStep styles per select request*/
        do
        {
            $flag = false;

            $select = $this->_db->select()
                ->from(Mage::getSingleton('core/resource')->getTableName('service_style'))
                ->where('request_id = ?', $this->_globalVars['request_id'])
                ->order('no ASC')
            ->limit($this->_stylesPerStep, $offset);
            $this->_styles = $this->_db->fetchAssoc($select);

            if(!empty($this->_styles))
            {
                $this->_getItems();

                //attribute set field
                $attributeSetMappingField = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_ATTRIBUTE_SET);

                foreach($this->_styles as $style)
                {
                    //update staging DAM images' info
                    if (Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_DAM_ENABLED)) Mage::getSingleton('teamwork_service/dam')->updateRecordsForStyle($style['style_id'], false);

                    //set attribute set
                    $attrSetName = '';
                    if (!empty($attributeSetMappingField))
                    {
                        $vals = $this->_getMapAttributeValue('', $attributeSetMappingField, $style);
                        if (!empty($vals))
                        {
                            $attrSetName = current($vals);
                        }
                    }
                    if (empty($attrSetName))
                    {
                        $attrSetName = $this->_attributeModel->getDefaultAttributeSetName();
                    }
                    $this->_attributeModel->setCurrentAttributeSet($attrSetName);

                    if(empty($style['internal_id']) || !Mage::getModel('catalog/product')->getResource()->getProductsSku(array($style['internal_id'])))
                    {
                        $this->_getInternalIdFromMultiChannel($style);
                    }

                    if(!empty($style['internal_id']))
                    {
                        $internal_ids[] = $style['internal_id'];
                    }

                    $itemsCount = count($this->_items[$style['style_id']]);
                    if( $this->getProductTypeByInventype($style[Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE]) == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                    {
                        if ($itemsCount !== 1)
                        {
                            $this->_addErrorMsg(sprintf("Inven type of style no %s, says to create simple product, but the style have %s items. Please, convert product to style.", $style['no'], $itemsCount));
                        }
                        else
                        {
                            $this->_importSimpleProduct($style);
                        }
                    }
                    else
                    {
                        $this->_importConfigurableProduct($style);
                    }

                    /*recall that ECM on the go due to the fact that product saving process may take some time*/
                    $this->checkLastUpdateTime();
                }

                $this->_styles = array();
                $this->_items = array();
                if (function_exists('gc_collect_cycles')) // NOT exists in php 5.2
                {
                    gc_collect_cycles();
                }

                $flag = true;
            }
            $offset = $offset + $this->_stylesPerStep;
        }while($flag);

        /*set/update products relationships*/
        $this->_setRelatedProducts($internal_ids);
    }

    /**
     * Initialize attribute variables.
     */
    public function initAttributeData()
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_attribute_set'), array('attribute_set_id', 'internal_id', 'code', 'description', 'alias', 'values_mapping', 'is_active'))
        ->where('internal_id is not null');
        $this->_attributes = $this->_db->fetchAssoc($select);

        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_attribute_value'), array('attribute_value_id', 'attribute_set_id', 'internal_id', 'attribute_value', 'attribute_alias', 'attribute_alias2'))
        ->where('internal_id is not null');
        $this->_attributeValues = $this->_db->fetchAssoc($select);

        //modify 'attribute_value' of $this->_attributeValues elements according to 'values_mapping' from $this->_attributes
        if (class_exists('Teamwork_Service_Model_Confattrmapprop'))
        {
            foreach($this->_attributeValues as $attributeValueId => $attributeValueData)
            {
                $this->_attributeValues[$attributeValueId]['attribute_value'] = Teamwork_Service_Model_Confattrmapprop::getMappedAttributeValue($this->_attributes[$attributeValueData['attribute_set_id']]['values_mapping'],
                                                                $attributeValueData['attribute_value'],
                                                                $attributeValueData['attribute_alias'],
                                                                $attributeValueData['attribute_alias2']);
                unset($this->_attributeValues[$attributeValueId]['attribute_alias']);
                unset($this->_attributeValues[$attributeValueId]['attribute_alias2']);
            }

        }

        //todo _getItemIdentifiers
    }

    /**
     * Import/update magento simple product
     *
     * @param array $style
     */
    protected function _importSimpleProduct(&$style)
    {
        try
        {
            $product = $this->_loadProduct($style['internal_id'], Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
            if (!$product->getId())
            {
                $style['internal_id'] = null;
            }

            $item = current($this->_items[$style['style_id']]);
            /*fill magento product object*/
            $this->_addProductDataFromStyle($product, $style, Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $item);
            if($this->_canImportProductImages())
            {
                $this->_addProductImages($product, $style);
            }
            $this->addAttributeFromSets($product, $item, false, $style);

            $product->setStockData($this->_quantityModel->getProductStockData($item['item_id'], $product->getId()));

            $this->_addProductCustomOptions($product, $style);

            $this->_saveProduct($product);
            $this->_afterSave($product,$style);

            if(empty($style['internal_id']))
            {
                $productId = $product->getId();

                /*set internal id value in style and item staging tables*/
                $this->_db->update(Mage::getSingleton('core/resource')->getTableName('service_style'), array('internal_id' => $productId), "style_id = '{$style['style_id']}'");
                $this->_db->update(Mage::getSingleton('core/resource')->getTableName('service_items'), array('internal_id' => $productId), "style_id = '{$style['style_id']}'");
            }
            elseif(empty($item['internal_id']))
            {
                /*set internal id value in the item staging table*/
                $this->_db->update(Mage::getSingleton('core/resource')->getTableName('service_items'), array('internal_id' => $style['internal_id']), "style_id = '{$style['style_id']}'");
            }

            $this->_saveNonDefaultStoreValues($product);

            /*clean working product object to save memory*/
            $product->clearInstance();
            Mage::getSingleton('catalog/product_option')->unsetOptions();
        }
        catch (Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while saving simple product: style %s - %s", $style['no'], $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }
    }

    /**
     * Fill magento product object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $style
     * @param string $typeId
     * @param array $item
     */
    protected function _addProductDataFromStyle($product, &$style, $typeId = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $item = null)
    {
        $baseData = array(
            'attribute_set_id'                   => $this->_attributeModel->_attributeSetId,
            'type_id'                            => $typeId,
            //'store_id'                         => $this->_globalVars['store_id'],
            //'website_ids'                        => $this->_getWebsites($product),
            'msrp_enabled'                       => 2,
            'msrp_display_actual_price_type'     => 4,
            'enable_googlecheckout'              => 1,
            'is_recurring'                       => 0,
            'options_container'                  => 'container2',
            'use_config_gift_message_available'  => 1
        );

        $product->addData($baseData);
        $product->addData($this->_getProductData($style, $typeId, $item));
        $this->removeEqualPriceData($product);

        if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_RELATIONSHIPS))
        {
            foreach($this->_neededRelatedTypes as $type)
            {
                $product->{'set'.$type.'LinkData'}(false);
            }
        }
    }

    /**
     * Get product's data
     *
     * @param array $style
     * @param string $typeId
     * @param array $item
     */
    protected function _getProductData(&$style, $typeId = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $item = null)
    {
        if($typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE || $this->_productTypes[$style[Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE]] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            $entity = &$style;
            $topProduct = true;
        }
        else
        {
            $entity = &$item;
            $topProduct = false;
        }

        $productData = array(
            'tax_class_id' => $style['taxcategory'],
        );

        if (!empty($entity['url_key']))
        {
            $productData['url_key'] = $entity['url_key'];
        }

        if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_CATEGORIES))
        {
            $productData['category_ids'] = ($topProduct ? $this->_categoryModel->getStyleMagentoCategories($style['style_id']) : $this->_categoryModel->getItemMagentoCategories($item['item_id']));
        }

        if(empty($entity['internal_id']) || Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_VISIBILITY))
        {
            $productData['visibility'] = $topProduct ? Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH : Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        }

        if(empty($entity['internal_id']) || Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_STATUSES))
        {
            if(strtolower($entity['ecomerce']) == strtolower('EC Offer'))
            {
                $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            }
            else
            {
                $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            }
        }

        $this->_fillFieldsFromMapping($productData, $style, $item, $topProduct);

        foreach($this->_mapModel->getPrice() as $attributeCode => $mappingPriceLevel)
        {
            if( $attributeCode != 'price' && $productData[$attributeCode] == 0 )
            {
                $productData[$attributeCode] = '';
            }
        }

        if($typeId == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            if( empty($productData['weight']) )
            {
                $productData['weight'] = $this->_defaultWeight;
            }
        }

        $this->_beforeAddData($productData, $style, $typeId, $item, $topProduct);
        
        //mapping rich content
        $this->_mappingRichContent($productData, $style, $this->_globalVars['channel_id'], $item, $typeId);

		// it's important to add name suffix at the end of _getProductData, when product name won't be changed by any function
        if(!$topProduct)
        {
            $suffix = '';
            for($i = 1; $i <= 3; $i++)
            {
                if(!empty($item["attribute{$i}_id"]) && !empty($this->_attributeValues[$item["attribute{$i}_id"]]) && $this->_attributes[$this->_attributeValues[$item["attribute{$i}_id"]]['attribute_set_id']]['is_active'])
                {
                    $suffix .= ' ' . trim($this->_attributeValues[$item["attribute{$i}_id"]]['attribute_value']);
                }
            }
            
            if(!empty($suffix))
            {
                $prefix = !empty($productData['name']) ? $productData['name'] : '';
                $productData['name'] = $prefix . $suffix;
            }
        }
        
        //push once
        $this->_pushOnce($productData, $style, $typeId, $item);

        return $productData;
    }

    /**
     * Import custom product options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $style
     */
    protected function _addProductCustomOptions($product, $style)
    {
        /*if enabled (disabled by default using hardcode)*/
        if($this->_allowCustomOptionsImport)
        {
            $return = $values = $recieved_titles = array();

            for($i = 1; $i <= $this->_optionQuantityOfCustomFields; $i++)
            {
                if(!empty($style[$this->_optionNameField.$i]))
                {
                    $values[$i] = array(
                        'option_type_id'    => '-1',
                        'is_delete'         => '',
                        'title'             => $style[$this->_optionNameField.$i],
                        'price'             => (float)$style[$this->_optionValueField.$i],
                        'price_type'        => 'fixed',
                        'sku'               => '',
                        'sort_order'        => $i
                    );
                    $recieved_titles[$i] = $style[$this->_optionNameField.$i];
                }
            }

            if($product->getOptions() && !empty($values))
            {
                foreach($product->getOptions() as $option)
                {
                    if($option->getValues())
                    {
                        foreach($option->getValues() as $existed)
                        {
                            if(!in_array($existed['title'], $recieved_titles) || empty($existed['title']))
                            {
                                $values[] = array(
                                    'option_type_id'    => $existed['option_type_id'],
                                    'is_delete'         => '1',
                                    'title'             => $existed['title'],
                                    'price'             => $existed['price'],
                                    'price_type'        => $existed['price_type'],
                                    'sku'               => $existed['sku'],
                                    'sort_order'        => $existed['sort_order']
                                );
                            }
                            elseif(in_array($existed['title'], $recieved_titles))
                            {
                                $values[array_search($existed['title'], $recieved_titles)]['option_type_id'] = $existed['option_type_id'];
                            }
                        }
                    }
                    if(!empty($values))
                    {
                        // for multy options comment next code
                        if(!empty($return))
                        {
                            $return[] = array(
                                'is_delete'        => 1,
                                'option_id'        => $option['option_id']
                            );
                            continue;
                        }

                        $return[] = array(
                            'is_delete'         => '',
                            'previous_type'     => '',
                            'previous_group'    => 'select',
                            'id'                => '',
                            'option_id'         => $option['option_id'],
                            'title'             => $this->_optionTitle,
                            'type'              => 'radio',
                            'is_require'        => '1',
                            'sort_order'        => '1',
                            'values'            => $values
                        );
                    }
                }
            }
            elseif(!$product->getOptions() && !empty($values))
            {
                $return[] = array(
                    'is_delete'         => '',
                    'previous_type'     => '',
                    'previous_group'    => 'select',
                    'id'                => '1',
                    'option_id'         => '0',
                    'title'             => $this->_optionTitle,
                    'type'              => 'radio',
                    'is_require'        => '1',
                    'sort_order'        => '1',
                    'values'            => $values
                );
            }
            elseif(empty($values) && $product->getOptions())
            {
                foreach($product->getOptions() as $option)
                {
                    $return[] = array(
                        'is_delete'        => 1,
                        'option_id'        => $option['option_id']
                    );
                }
            }

            if(!empty($return))
            {
                $product->setProductOptions($return);
                $product->setCanSaveCustomOptions(true);
            }
        }
    }

    protected function _getItemIdentifiers(&$itemId)
    {
        $identifiers = array();

        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_identifier'))
        ->where('item_id = ?', $itemId);

        $results = $this->_db->fetchAll($select);

        foreach($results as $res)
        {
            $identifiers[$res['idclass']] = $res['value'];
        }

        return $identifiers;
    }

    /**
     * Set/update magento products' relationships from staging tables for set of products' magento ids
     *
     * @param array $internal_ids
     */
    protected function _setRelatedProducts($internal_ids)
    {
        if (!Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_RELATIONSHIPS)) return;
        /*STEP1: collect relationspip data from staging tables*/
        $select = $this->_db->select()->distinct()
            ->from(array('sty2' => Mage::getSingleton('core/resource')->getTableName('service_style')), array('sty2.internal_id as product_id'))
            ->join(array('rel' => Mage::getSingleton('core/resource')->getTableName('service_style_related')), "rel.style_id = sty2.style_id and sty2.request_id = '{$this->_globalVars['request_id']}'", array('rel.related_style_type', 'rel.relation_kind'))
            ->join(array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), "rel.related_style_id = sty.style_id and sty.channel_id = sty2.channel_id", array('sty.internal_id'))
            ->joinLeft(array('ity' => Mage::getSingleton('core/resource')->getTableName('service_items')), 'rel.related_item_id = ity.item_id and sty2.channel_id = ity.channel_id', array('ity.internal_id as internal_id_item'))
            ->joinLeft(array('ity2' => Mage::getSingleton('core/resource')->getTableName('service_items')), 'rel.style_id = ity2.style_id and sty2.channel_id = ity2.channel_id', array('ity2.internal_id as product_id_item'))
        ->where('rel.related_style_type IN (?)', $this->_neededRelatedTypes);

        if($relatedStyles = $this->_db->fetchAll($select))
        {
            $results = array();
            $relatedProduct = Mage::getModel('catalog/product');
            /*STEP2: check product ids and convert data for convenience*/
            foreach($relatedStyles as $k => $relatedStyle)
            {
                /*Relation type*/
                if ($relatedStyle['relation_kind'] == 'ItemToItem')
                {
                    $relatedStyle['product_id'] = $relatedStyle['product_id_item'];
                    $relatedStyle['internal_id'] = $relatedStyle['internal_id_item'];
                }
                if ($relatedStyle['relation_kind'] == 'ItemToStyle')
                {
                    $relatedStyle['product_id'] = $relatedStyle['product_id_item'];
                }
                if ($relatedStyle['relation_kind'] == 'StyleToItem')
                {
                    $relatedStyle['internal_id'] = $relatedStyle['internal_id_item'];
                }
            
                if($relatedStyle['product_id'] && $relatedStyle['internal_id'])
                {
                    $relatedProduct->load($relatedStyle['internal_id']);

                    if($relatedProduct->getId())
                    {
                        $results[$relatedStyle['product_id']][$relatedStyle['related_style_type']][$relatedStyle['internal_id']] = array('position' => $k);
                    }
                    /*clean working product object to save memory*/
                    $relatedProduct->clearInstance();
                }
            }
            /*STEP3: update magento products' relationships*/
            if(!empty($results))
            {
                foreach($results as $product_id => $result)
                {
                    $product = $this->_loadProduct($product_id);
                    if($product->getId())
                    {
                        if(!empty($result))
                        {
                            foreach($result as $key => $val)
                            {
                                $product->{'set'.$key.'LinkData'}($val);
                            }
                        }

                        try
                        {
                            $product->save();
                        }
                        catch (Exception $e)
                        {
                            $this->_addErrorMsg(sprintf("Error occured while setting related products: sku: %s: relation type: %s: %s", $product->getSku(), $key, $e->getMessage()), true);
                            $this->_getLogger()->addException($e);
                        }
                        /*clean working product object to save memory*/
                        $product->clearInstance();
                    }
                }
            }
        }
    }

    /**
     * Append/update attribute value to/in product data array
     *
     * Firstly, we get values (there may be a few values for, i.g., collections) of map attribute. These values don't depend on mapping settings
     * Secondly, we add these values to product data. We have two special cases at this procedure:
     * 1) for select and multiselect magento attributes we add not values but options ids
     * 2) excepting the case when we map to multiselect attribute, we convert values array to string.
     *    For example, when we map collections to some multiselect magento attribute, option ids will be mapped;
     *            but, when we map collections to some text attribute (i.g., meta_title), we convert array to string. Result will be like 'collection_one, collection_two'
     *
     *
     * @param string $magentoAttrName magento attribute (we do mapping for this attribute)
     * @param string $mapAttrName     map attribute (i.e. cloudhq attribute) name
     * @param array  $objectData      style or item
     * @param array  &$productData    mapped data
     */
    protected function _fillAttributeData($magentoAttrName, $mapAttrName, $objectData, &$productData)
    {
        $values = $this->_getMapAttributeValue($magentoAttrName, $mapAttrName, $objectData);

        $attributeData = $this->_attributeModel->getAttributeData($magentoAttrName,'',true);

        $valuesToAdd = array();
        foreach ($values as $value)
        {
            if( in_array($attributeData['source_model'], array('eav/entity_attribute_source_boolean')) )
            {
                $yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toArray();
                foreach($yesnoSource as $sourceKey => $sourceValue)
                {
                    if( (string)$sourceKey === $value ||
                        strtolower($sourceValue) == strtolower($value)
                    )
                    {
                        $valuesToAdd[] = $sourceKey;
                        break;
                    }
                }
            }
            elseif(in_array($attributeData['type'], array('select', 'multiselect')))
            {
                $this->_attributeModel->populateAttibuteOption($magentoAttrName, $value);
                $valuesToAdd[] = (string) array_search($value, $this->_attributeModel->_attributeOptions[$attributeData['id']], true);
            }
            else
            {
                $valuesToAdd = $values;
                break;
            }
        }

        $productData[$attributeData['code']] = ($attributeData['type'] == 'multiselect') ?
            $valuesToAdd :
        join($this->_convertMultiselectToTextDelimiter, $valuesToAdd);
    }

    /**
     * Returns array of values of attribute (all values for multiselect attribute and single value for another ones)
     *
     * @param  string $magentoAttrName
     * @param  string $mapAttrName
     * @param  array  $objectData style or item
     *
     * @return array
     */
    protected function _getMapAttributeValue($magentoAttrName, $mapAttrName, $objectData)
    {
        if($this->_attributeModel->isSpecialAttribute($mapAttrName))
        {
            $auxiliaryParams = array(
                'magento_attribute_name' => $magentoAttrName,
                'map_model'              => $this->_mapModel
            );
            return $this->_attributeModel->getSpecialAttributeValues($mapAttrName, $objectData, $auxiliaryParams, array('class_item_object' => $this));
        }

        $mapAttrName = $this->_mapModel->cutItemPrefixInAttributeName($mapAttrName); //cut item prefix if exists
        return (empty($objectData[$mapAttrName])) ? array() : array($objectData[$mapAttrName]);
    }

    /**
     * Mapping logic for both custom and default style mapping
     *
     * @param array &$mappedData
     * @param array &$style
     * @param array &$item
     * @param bool  $topProduct is true when we process parent or simple product; is false when we deal with child product
     * @param bool  $customMapping is true when we process a custom style mapping
     */
    protected function _fillStyleFieldsFromMapping(&$mappedData, &$style, &$item, $topProduct, $customMapping = false)
    {
        $mapStyle = $this->_getMapping(Teamwork_Service_Model_Mapping::CONST_STYLE, $customMapping);
        $mapItem  = $this->_getMapping(Teamwork_Service_Model_Mapping::CONST_ITEM,  $customMapping);
        $mapDefaultItem = $this->_mapModel->getMapDefaultItem();

        /*mapping chq property*/
        $mapStyle = $this->_getChqFieldsMapping($mapStyle, Teamwork_Service_Model_Mapping::CONST_STYLE, $style);
        $mapItem = $this->_getChqFieldsMapping($mapItem, Teamwork_Service_Model_Mapping::CONST_ITEM, $style);

        if(!empty($mapStyle))
        {
            foreach ($mapStyle as $magentoAttrName => $mapAttrName)
            {
                $objectData = $style;

                /* Sometimes we use not style but item mapping settings. It happens when:
                 * 1) we process a child product (~item having 'brothers', i.e. not the only item belonging a style). $topProduct == 0 tells that
                 * 2) current magento attribute is used both in item and style mappings
                 *
                 * It means that we prefer item mapping settings when processing a child product.
                 * For configurable products 'child product' ~ 'item'.
                 */
                if(!$topProduct && !empty($item) && isset($mapItem[$magentoAttrName]))
                {
                    $mapAttrName = $mapItem[$magentoAttrName];
                    $objectData  = ($this->_mapModel->isItemAttribute($mapItem[$magentoAttrName])) ? $item : $style;
                }

                /**
                 * For child products, we don't do mapping for custom style attributes if they exist in default item mapping.
                 *
                 * Previously, it could be a situation when wrong (i.e. style instead of item) fields was mapped.
                 * Example:
                 * 1) Default item mapping contains mapping 'A => B' (A is Magento attribute, B is cloudhq one). Customer added 'A => C' to custom style mapping. Other mappings don't mention field 'A'.
                 * 2) While processing default item mapping,        we fill 'A' with value of field 'B'
                 * 3) Later, while processing custom style mapping, we fill 'A' with value of field 'C'.
                 *    For child products, it's abnormally when style mapping have higher priority then item mapping.
                 *
                 */
                if (!($customMapping && !$topProduct && isset($mapDefaultItem[$magentoAttrName])))
                {
                    $this->_fillAttributeData($magentoAttrName, $mapAttrName, $objectData, $mappedData);
                }
            }
        }
    }

    /**
     * Mapping logic for both custom and default style mapping
     *
     * @param array &$mappedData
     * @param array &$style
     * @param array &$item
     * @param bool  $customMapping is true when we process a custom item mapping
     */
    protected function _fillItemFieldsFromMapping(&$mappedData, &$style, &$item, $customMapping = false)
    {
        $mapStyle = $this->_getMapping(Teamwork_Service_Model_Mapping::CONST_STYLE, $customMapping);
        $mapItem  = $this->_getMapping(Teamwork_Service_Model_Mapping::CONST_ITEM,  $customMapping);

        /*mapping chq property*/
        $mapStyle = $this->_getChqFieldsMapping($mapStyle, Teamwork_Service_Model_Mapping::CONST_STYLE, $style);
        $mapItem = $this->_getChqFieldsMapping($mapItem, Teamwork_Service_Model_Mapping::CONST_ITEM, $style);

        if(!empty($mapItem))
        {
            foreach ($mapItem as $magentoAttrName => $mapAttrName)
            {
                /*
                 * We map only fields absent in style mapping settings
                 * When we process 'parent' configurable product (~style with 2 or more items), we take skipped attributes from the first item (the first style's child)
                 * When we process 'child'  product (~item having 'brothers', i.e. not the only item belonging a style), we have already taken all overlapping fields in _fillStyleFieldsFromMapping. And this function will add 'item' fields not overlapped by style mapping.
                 */
                if(!isset($mapStyle[$magentoAttrName]) || $this->getProductTypeByInventype($style[Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE]) == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE )
                {
                    $objectData = ($this->_mapModel->isItemAttribute($mapAttrName)) ? $item : $style;
                    $this->_fillAttributeData($magentoAttrName,  $mapAttrName, $objectData, $mappedData);
                }
            }
        }
    }

    /**
     * Append product data to array using mapping settings (service_settings table, setting_value column data)
     * '$topProduct' is true when we process parent (~style with 2 or more items) or simple (1 style, 1 item) product; is false for child product (~item having 'brothers', i.e. not the only item belonging a style)
     *
     * @param array &$productData
     * @param array &$style
     * @param array $item
     * @param bool  $topProduct is true when for parent and simple products; is false for child
     */
    protected function _fillFieldsFromMapping(&$productData, &$style, $item = null, $topProduct)
    {
        $mappedData = array();

        $this->_fillStyleFieldsFromMapping($mappedData, $style, $item, $topProduct);
        $this->_fillItemFieldsFromMapping ($mappedData, $style, $item);
        $this->_fillStyleFieldsFromMapping($mappedData, $style, $item, $topProduct, true);
        $this->_fillItemFieldsFromMapping ($mappedData, $style, $item, true);

        $productData = array_merge($productData, $mappedData);
    }

    /**
     * Gets mapping from mapping model and replaces magento attribute names by correspnding attribute codes
     *
     * @param  string  $entityType style or item. Valid values: Teamwork_Service_Model_Mapping::CONST_STYLE, Teamwork_Service_Model_Mapping::CONST_ITEM
     * @param  boolean $customMapping if TRUE, custom mapping settings will be returned
     *
     * @return array
     */
    protected function _getMapping($entityType, $customMapping = false)
    {
        $result = null;

        if (in_array($entityType, array(Teamwork_Service_Model_Mapping::CONST_STYLE, Teamwork_Service_Model_Mapping::CONST_ITEM)))
        {
            if ($entityType == Teamwork_Service_Model_Mapping::CONST_STYLE)
            {
                $rawMapping = ($customMapping) ? $this->_mapModel->getMapCustomStyle() : $this->_mapModel->getMapDefaultStyle();
            }
            else
            {
                $rawMapping = ($customMapping) ? $this->_mapModel->getMapCustomItem() : $this->_mapModel->getMapDefaultItem();
            }

            if (!empty($rawMapping))
            {
                $result = array();
                foreach ($rawMapping as $magentoAttrName => $mapAttrName)
                {
                    $attributeCode = $this->_attributeModel->getAttributeCodeByName($magentoAttrName);
                    if (strlen($attributeCode))
                    {
                        $result[$attributeCode] = $mapAttrName;
                    }
                }
                if (!count($result)) $result = null;
            }
        }

        return $result;
    }

    /**
     * Additional product data processing after filling from staging tables
     *
     * @param array  $productData
     * @param array  $style
     * @param string $typeId
     * @param array  $item
     * @param bool   $topProduct
     */
    protected function _beforeAddData(&$productData, &$style, &$typeId, &$item, &$topProduct)
    {
        //todo in reload
    }

    /**
     * Mapping rich content
     *
     * @param array  $productData
     * @param guid $channelId
     */
    protected function _mappingRichContent(&$productData, $style, $channelId, $item, $typeId)
    {
        $txts = $this->_mediaModel->getMediaTexts($style['style_id'], 'style', $channelId, $item);

        $collection = Mage::getModel('teamwork_service/richmedia')->getCollection()
        ->addFieldToFilter('channel_id', $channelId);

        foreach ($collection as $value)
        {
            $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($value['attribute_id']);

            // fill productData with richMedia text attribute ONLY if it exists
            if (isset($txts[$value['media_index']]))
            {
                $productData[$attribute->attribute_code] = $txts[$value['media_index']];
            }
        }
    }
    
    /**
     * Reload mapping field only first push
     *
     * @param array  $productData
     * @param array  $style
     * @param string $typeId
     * @param array  $item
     */
    
    protected function _pushOnce(&$productData, $style, $typeId, $item)
    {
        $updating = ( $typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
        $this->getProductTypeByInventype($style[Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE]) == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE )
            ? !empty($style['internal_id'])
        : !empty($item['internal_id']);
        if ($updating)
        {
            $collection = Mage::getModel('teamwork_service/mappingproperty')->getCollection()
                ->addFieldToFilter('push_once', 1)
                ->load();
                    
            foreach ($collection as $value)
            {
                $attribute = Mage::getModel('eav/entity_attribute')->load($value->getAttributeId());
                $attributeCode = $attribute->getAttributeCode();
                
                if (array_key_exists($attributeCode, $productData)) 
                {
                    unset($productData[$attributeCode]);
                }
            }
        }
    }

    /**
     * Gets extended info about product media gallery. 'Extended' means containing value_ids (simple $product->getData('media_gallery') doesn't return value_ids)
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function _getProductMediaGallery($product)
    {
        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);
        if (isset($attributes[self::GALLERY_ATTRIBUTE_CODE]))
        {

            $mediaGalleryAttributeBackend = $attributes[self::GALLERY_ATTRIBUTE_CODE]->getBackend();
            $result = Mage::getResourceModel('catalog/product_attribute_backend_media')->loadGallery($product, $mediaGalleryAttributeBackend);
        }
        return (isset($result)) ? $result : array();
    }

    public function saveImageInternalIds($product, $entity)
    {
        return $this->_saveImageInternalIds($product, $entity);
    }

    /**
     * Copies value_ids of just added images to 'service_media' table
     * Is called after product saving, 'cause only after saving value_ids appear in 'catalog_product_entity_media_gallery' table
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array $entity cloudHQ style
     */
    protected function _saveImageInternalIds($product, $entity)
    {
        if (!empty($this->_recentlyLoadedProductImages))
        {
            $productImages = $this->_getProductMediaGallery($product);
            if (!empty($productImages))
            {
                $idTable = Mage::getSingleton('core/resource')->getTableName('service_media_value');

                foreach ($this->_recentlyLoadedProductImages as $ecmImage)
                {
                    foreach ($productImages as $key => $productImage)
                    {
                        if (isset($productImage['file']) && $productImage['file'] == $ecmImage['magento_image_file'])
                        {
                            $this->_db->insert($idTable, array(
                                'media_id'         => $ecmImage['media_id'],
                                'internal_id'      => $productImage['value_id'],
                                'saved_media_name' => $ecmImage['media_name']
                                ));
                        }
                    }
                }
            }
			$this->_recentlyLoadedProductImages = array();
        }
    }

    /**
     * Cycles over all ecm images and looks whether there is existing product image with value_id ('catalog_product_entity_media_gallery' table) = internal_id ('service_media' table)
     * Returns ecm images already existing in product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  array $ecmImages images obtained with getMediaImagesInfo function
     *
     * @return array all ecm images existing in the $product
     */
    protected function _getExistingImages($productMediaGalleryData, $ecmImages)
    {
        $result = array('ecm_images' => array(),'product_images' => array());

        if (!empty($productMediaGalleryData['images']))
        {
            foreach ($ecmImages as $ecmImage)
            {
                $currentMediaName  = $ecmImage['media_name'];
                $savedMediaName    = $ecmImage['saved_media_name'];

                if ($currentMediaName == $savedMediaName)
                {
                    foreach ($ecmImage['internal_ids'] as $internalId)
                    {
                        foreach ($productMediaGalleryData['images'] as $key => $productImage)
                        {
                            if ($productImage['value_id'] == $internalId)
                            {
                                $result['product_images'][] = $productImage + array(
                                    'media_gallery_key' => $key,
                                    'is_file_exists'    => file_exists(Mage::getSingleton('catalog/product_media_config')->getMediaPath($productImage['file']))
                                );
                                $result['ecm_images'][]     = $ecmImage;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns true, when entity is a style
     *
     * @param  array  $entity
     *
     * @return boolean
     */
    protected function _isStyle(&$entity)
    {
        return isset($entity['no']);
    }

    /**
     * Gets info about images for given style or item.
     *
     * @param  array $entity style or item from service tables
     *
     * @return array
     */
    protected function _getEcmImagesInfo(&$entity)
    {
        $result = array();

        // if entity is a style or entity is an item and import_item_images_to_item is enabled, look for images
        // otherwise, if entity is an item and import_item_images_to_item is disabled, there's no sense to look for images - we will not import them in any case
//        if ($this->_isStyle($entity) || Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_ITEM_IMAGES_TO_ITEM))
//        {
            // load info about all ecm images for this product from service tables
            $allEcmImagesInfo = $this->_mediaModel->getMediaImagesInfo($entity['style_id'], Teamwork_Service_Model_Mapping::CONST_STYLE, $this->_globalVars['channel_id']);

            foreach ($allEcmImagesInfo as $ecmImageInfo)
            {
                /*check if attribute<n> is set for the image and there is no records in service_attribute_value table to prevent crash*/
                if(
                    (!empty($ecmImageInfo['attribute1']) && empty($this->_attributeValues[$ecmImageInfo['attribute1']])) ||
                    (!empty($ecmImageInfo['attribute2']) && empty($this->_attributeValues[$ecmImageInfo['attribute2']])) ||
                    (!empty($ecmImageInfo['attribute3']) && empty($this->_attributeValues[$ecmImageInfo['attribute3']]))
                ) continue;

                // $allEcmImagesInfo contains ALL images for parent product (style) (i.e., images with either white or black t-shirt)
                // for items (child products), we need to load only images related to item (i.g., image with white t-shirt)
                if (!$this->_isStyle($entity) && !Mage::helper('teamwork_transfer')->canImageBeAddedToItem($entity, $ecmImageInfo))
                {
                    continue;
                }

                // don't import item image (image having at least 1 attribute set) to style if 'import_item_images_to_style' option enabled
//                if ($this->_isStyle($entity) && !Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_ITEM_IMAGES_TO_STYLE))
//                {
//                    if (!empty($ecmImageInfo['attribute1']) || !empty($ecmImageInfo['attribute2']) || !empty($ecmImageInfo['attribute3']))
//                    {
//                        continue;
//                    }
//                }

                $result[] = $ecmImageInfo;
            }
//        }

        return $result;
    }

    /**
     * Physically deletes product image file
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $filename
     */
    protected function _deleteProductImageFile($product, $filename)
    {
        $file = $product->getMediaConfig()->getMediaPath($filename);
        $ioFile = new Varien_Io_File();
        if ($ioFile->fileExists($file))
        {
            $ioFile->rm($file);
        }
    }

    /**
     * Returns all codes of product media attributes (like 'image', 'small_image', 'thumbnail')
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $uniqueId unique product identifier (we don't use product id, because product can be not created yet)
     *
     * @return array
     */
    protected function _getProductMediaAttributeCodes($product, $uniqueId)
    {
        if ($this->_productMediaAttributesCache['unique_id'] != $uniqueId)
        {
            $this->_productMediaAttributesCache['unique_id'] = $uniqueId;
            $this->_productMediaAttributesCache['media_attributes'] = $product->getMediaAttributes();
        }

        return array_keys($this->_productMediaAttributesCache['media_attributes']);
    }

    /**
     * Clear all info about product media attributes (like 'image', 'small_image', 'thumbnail')
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $uniqueId unique product identifier (we don't use product id, because product can be not created yet)
     */
    protected function _clearProductMediaAttributes($product, $uniqueId)
    {
        $mediaAttributeCodes = $this->_getProductMediaAttributeCodes($product, $uniqueId);
        foreach ($mediaAttributeCodes as $mediaAttributeCode)
        {
            $product->setData($mediaAttributeCode, 'no_selection');
        }
    }

    /**
     * Updates product image mapping, i.e. sets, whether image wil be thumbnail, small image etc.
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $uniqueId unique product identifier (we don't use product id, because product can be not created yet)
     * @param  array $attributesToSet attributes ('image', 'small_image', 'thumbnail' etc.) we want to set
     * @param  string $value usually filename
     */
    protected function _addProductMediaAttributes($product, $uniqueId, $attributesToSet, $value)
    {
        $mediaAttributeCodes = $this->_getProductMediaAttributeCodes($product, $uniqueId);
        foreach ($attributesToSet as $attributeToSet)
        {
            // if current attribute is in a list of product media attribute codes, set it
            if (in_array($attributeToSet, $mediaAttributeCodes))
            {
                $product->setData($attributeToSet, $value);
            }
        }
    }

    /**
     * Gets attributes ('image', 'small_image', 'thumbnail' etc.) of ECM image we want to assign to Magento image
     *
     * @param  array $image
     * @param  array $imageMapping
     *
     * @return array
     */
    protected function _getImageMediaAttributes($image, $imageMapping)
    {
        $mediaAttributes = array();
        if (!empty($imageMapping))
        {
            foreach($imageMapping as $type => $field)
            {
                $fieldData = explode('.', strtolower($field));
                if(current($fieldData).'s' == strtolower($image['media_type']) && end($fieldData) == $image['media_index'])
                {
                    $mediaAttributes[] = $type;
                }
            }
        }

        return $mediaAttributes;
    }

    /**
     * Returns TRUE if we can (generally) import rich media (images, descriptions, videos etc.)
     *
     * @return boolean
     */
    protected function _canImportRichMedia()
    {
        $pushRichMediaFrom = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_PUSH_RICH_MEDIA_FROM);
        return (in_array($pushRichMediaFrom, array(Teamwork_Transfer_Helper_Config::RICHMEDIA_PUSH_FROM_ALL_CHANNELS, $this->_globalVars['channel_id'])));
    }

    /**
     * Returns TRUE if we can (generally) import product images to product of the given type (style by default)
     *
     * @param  boolean $isStyle
     *
     * @return boolean
     */
    protected function _canImportProductImages($isStyle = true)
    {
        $result = (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_IMAGES) && $this->_canImportRichMedia());
        $result &= $isStyle ? Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_ITEM_IMAGES_TO_STYLE) : Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_ITEM_IMAGES_TO_ITEM);
        return $result;
    }

    public function addProductImages($product, &$entity)
    {
        return $this->_addProductImages($product, $entity);
    }

    /**
     * Update product images using staging tables
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $entity
     */
    protected function _addProductImages($product, &$entity)
    {
        $this->_recentlyLoadedProductImages = array();
        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);
        $mediaGalleryAttribute = $attributes[self::GALLERY_ATTRIBUTE_CODE];
        $productMediaGalleryData = $product->getData(self::GALLERY_ATTRIBUTE_CODE);
        $mapDefaultImage = $this->_mapModel->getMapDefaultImage();

        if(!isset($productMediaGalleryData['images']) || !is_array($productMediaGalleryData['images']))
        {
            $productMediaGalleryData['images'] = array();
        }

        // get info about images for style or item
        $ecmImagesInfo = $this->_getEcmImagesInfo($entity);

        // get ecm images already existing in $product
        $existingImages = $this->_getExistingImages($productMediaGalleryData, $ecmImagesInfo);

        if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_DELETE_PRODUCT_IMAGES_ABSENT_IN_ECM))
        {
            // remove images existing in product but absent in ECM
            $productImagesToDelete = Mage::helper('teamwork_transfer')->multiArrayDiffByField($productMediaGalleryData['images'], $existingImages['product_images'], 'file', true);
            foreach ($productImagesToDelete as $key => $image)
            {
                // mark image as removed (Magento will delete record about image by itself)
                $productMediaGalleryData['images'][$key]['removed'] = 1;

                // physically removing product image file
                $this->_deleteProductImageFile($product, $image['file']);
            }
        }

        /*unique image identifier*/
        $uniqueId = $this->_isStyle($entity) ? $entity['internal_id'] . $entity['no'] : $entity['internal_id'] . $entity['plu'];

        if(!empty($ecmImagesInfo))
        {
            // before adding media attributes (like 'image', 'small_image', 'thumbnail') we clear all previous info about them
            $this->_clearProductMediaAttributes($product, $uniqueId);

            foreach ($ecmImagesInfo as $index => $image)
            {
                // get media attributes (like 'image', 'small_image', 'thumbnail') we want to assign to current image
                if (array_key_exists('media_attributes', $image)) $mediaAttributes = $image['media_attributes'];
                else $mediaAttributes = $this->_getImageMediaAttributes($image, $mapDefaultImage);

                /*don't display the image in gallery if it is the only image in product*/
                $disabled = (!empty($mediaAttributes) && count($ecmImagesInfo) == 1);
                if (!$disabled && array_key_exists('excluded', $image)) $disabled = $image['excluded'];

                $imageParams = array(
                    'position' => (int) $image['order'],
                    'label'    => !empty($image['label']) ? $image['label'] : $this->_createImageLabel($image), //label may use attached attribute<n> values
                    'disabled' => (int) $disabled
                );

                try
                {
                    // if image already exists on Magento, its params and mapping
                    if (in_array($image, $existingImages['ecm_images']))
                    {
                        $magentoImage = $this->_getMagentoImageCorrespondingToEcmImage($image, $existingImages);
                        if($magentoImage['is_file_exists'])
                        {
                            // update image mapping in product
                            $this->_addProductMediaAttributes($product, $uniqueId, $mediaAttributes, $magentoImage['file']);
                            // update image params
                            $productImage = &$productMediaGalleryData['images'][$magentoImage['media_gallery_key']];
                            $productImage = array_replace($productImage, $imageParams);
                            
                            continue;
                        }
                        else
                        {
                            $productMediaGalleryData['images'][$magentoImage['media_gallery_key']]['removed'] = 1;
                        }
                    }
                    
                    /*download and attach image*/
                    $loadedImages = $this->_mediaModel->loadMediaImages($entity['style_id'], Teamwork_Service_Model_Mapping::CONST_STYLE, $this->_globalVars['channel_id'], $uniqueId, array($image));
                    $loadedImage = reset($loadedImages);
                    $fileName    = $mediaGalleryAttribute->getBackend()->addImage($product, $loadedImage['link'], $mediaAttributes, true, false);

                    $productMediaGalleryData['images'][] = array('file' => $fileName) + $imageParams;

                    $this->_recentlyLoadedProductImages[] = array(
                        'media_id'           => $image['media_id'],
                        'magento_image_file' => $fileName,
                        'media_name'         => $image['media_name']
                    );
                }
                catch (Exception $e)
                {
                    $this->_addErrorMsg(sprintf("Error occured while attaching/updating image (img: %s; sku: %s: %s)", $loadedImage['link'], $product->getSku(), $e->getMessage()), true);
                    $this->_getLogger()->addException($e);
                }
            }
        }
        $product->setData(self::GALLERY_ATTRIBUTE_CODE, $productMediaGalleryData);
    }

    /**
     * In given correspondingsArray (obtained by $this->_getExistingImages function), searches for Magento image that corresponds to given ECM image
     *
     * @param  array $ecmImage
     * @param  array $correspondingsArray
     *
     * @return array|null
     */
    protected function _getMagentoImageCorrespondingToEcmImage($ecmImage, $correspondingsArray)
    {
        $key = array_search($ecmImage, $correspondingsArray['ecm_images']);
        return ($key === false) ? null : $correspondingsArray['product_images'][$key];
    }

    /**
     * Set attribute<n> values to magento product (configurable attributes values for children for configurable product case and dropdown attribute values for simple product case)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $item
     * @param bool $return
     * @param array $style
     */
    public function addAttributeFromSets($product, &$item, $return = false, &$style)
    {
        $attrArray = array();

        for($j = 1; $j <= 3; $j++)
        {
            if(!empty($style['attributeset'.$j]) && !$this->_attributes[$style['attributeset'.$j]]['is_active'])
            {
                continue;
            }

            $attrValueId = $item['attribute' . $j . '_id'];

            if( !empty($attrValueId) && isset($this->_attributeValues[$attrValueId]) )
            {
                $attributeSetId = $this->_attributeValues[$attrValueId]['attribute_set_id'];
                //$code = $this->_attributeModel->getAttributeCodeByName($this->_attributes[$attributeSetId]['code']);
                $code = $this->getMagentoAttributeCode($this->_attributes[$attributeSetId]['internal_id']);
                if(!empty($code))
                {
                    $product->setData($code, $this->_attributeValues[$attrValueId]['internal_id']);
                    $this->_attributeModel->attributeToAttributeSet($this->_attributes[$attributeSetId]['internal_id']);
                    /*needed for configurable's children to set options pricing*/
                    if($return)
                    {
                        //throw object with price data to help parent to set their price data
                        $priceDataObject = new Varien_Object();
                        $this->copyPriceData($product, $priceDataObject);
                        $attrArray[] = array(
                            'attribute_id'          => $this->_attributes[$this->_attributeValues[$attrValueId]['attribute_set_id']]['internal_id'],
                            'attribute_name'        => $this->getAttributeName($attributeSetId, $style, $item),
                            'attribute_code'        => $code,
                            'value_label'           => $this->_attributeValues[$attrValueId]['attribute_value'],
                            'value_index'           => $this->_attributeValues[$attrValueId]['internal_id'],
                            'is_percent'            => 0,
                            'pricing_value'         => $product->getData('price'),
                            'price_data_object'     => $priceDataObject,
                            'product_status'        => $product->getData('status'),
                            'product_stock'         => $product->getData('is_in_stock'),
                            'plu'                   => $item['plu'],
                        );
                    }
                }
            }
        }

        if($return)
        {
            return $attrArray;
        }
    }

    /**
     * Entry point to import/update configurable product
     *
     * @param array $style
     */
    protected function _importConfigurableProduct(&$style)
    {
        $this->_insertConfigurableParent($style, $this->_insertConfigurableChildren($style));
    }

    /**
     * Import/update children of configurable product
     *
     * @param array $style
     *
     * @return array
     */
    protected function _insertConfigurableChildren(&$style)
    {
        $children = array();

        foreach($this->_items[$style['style_id']] as $item)
        {
            try
            {
                $product = $this->_loadProduct($item['internal_id'], Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
                if (!$product->getId())
                {
                    $item['internal_id'] = null;
                }

                $this->_addProductDataFromStyle($product, $style, Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $item);

                $product->setStockData($this->_quantityModel->getProductStockData($item['item_id'], $product->getId()));
                $attrArray = $this->addAttributeFromSets($product, $item, true, $style);

                /*set media image in simple products*/
                if($this->_canImportProductImages(false))
                {
                    $this->_addProductImages($product, $item);
                }

                $this->_saveProduct($product);
                $this->_afterSave($product, $item);

                if(!empty($attrArray))
                {
                    /*attributes info accumulation to set/update configurable attributes including pricing rules*/
                    $children[$product->getId()] = $attrArray;
                }

                if(empty($item['internal_id']))
                {
                    $table = Mage::getSingleton('core/resource')->getTableName('service_items');
                    $this->_db->update($table, array('internal_id' => $product->getId()), "item_id = '{$item['item_id']}' and channel_id='{$this->_globalVars['channel_id']}'");
                }

                /*recall that ECM on the go due to the fact that product saving process may take some time*/
                $this->checkLastUpdateTime();

                $this->_saveNonDefaultStoreValues($product);

                /*clean working product object to save memory*/
                $product->clearInstance();
            }
            catch (Exception $e)
            {
                $this->_addErrorMsg(sprintf("Error occured while inserting/updating child (plu: %s): %s", $item['plu'], $e->getMessage()), true);
                $this->_getLogger()->addException($e);
            }
        }

        return $children;
    }

    /**
     * Import/update configurable product
     *
     * @param array $style
     * @param array $children
     */
    protected function _insertConfigurableParent(&$style, $children)
    {
        try
        {
            $product = $this->_loadProduct($style['internal_id'], Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
            if(!$product->getId())
            {
                $style['internal_id'] = null;
            }

            //TODO: get item depended on price
            reset($this->_items[$style['style_id']]);
            $item = current($this->_items[$style['style_id']]);
            $this->_addProductDataFromStyle($product, $style, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $item);

            if($this->_canImportProductImages())
            {
                $this->_addProductImages($product, $style);
            }

            $product->setStockData($this->_quantityModel->getProductStockData($item['item_id'], $product->getId(), $children));

            $this->_addProductCustomOptions($product, $style);

            $configurableAttributesData = $this->getConfigurableAttributesData($product, $children, $style);
            $product->setConfigurableAttributesData($configurableAttributesData);
            $product->setConfigurableProductsData($children);
            $product->setCanSaveConfigurableAttributes(true);
            $product->setAffectConfigurableProductAttributes(true);

            $this->_saveProduct($product);
            $this->_afterSave($product, $style);
            
            if( empty($style['internal_id']) )
            {
                $table = Mage::getSingleton('core/resource')->getTableName('service_style');
                $this->_db->update($table, array("internal_id" => $product->getId()), "style_id = '{$style["style_id"]}'");
            }

            /*recall that ECM on the go due to the fact that product saving process may take some time*/
            $this->checkLastUpdateTime();

            $this->_saveNonDefaultStoreValues($product);

            $product->clearInstance();
            Mage::getSingleton('catalog/product_option')->unsetOptions();
        }
        catch (Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while saving configurable product: Style %s - %s", $style['no'], $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }
    }

    /**
     * Convert attributes' info to setConfigurableAttributesData including pricing calculation
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $style
     * @param array $children
     *
     * @return array
     */
    public function getConfigurableAttributesData($product, &$children, &$style, $forceSetParentPrices = true)
    {
        $configurableAttributesTempAttributes = array();
        $optionSets = array();
        $pricingTempAttributes = array();

        $minChildPrice = null;
        $minPriceChildProd = null;
        $maxChildPrice = null;
        $maxPriceChildProd = null;

        $disabledAll = true;
        foreach($children as $childId => $childAttributes)
        {
            foreach($childAttributes as $childAttribute)
            {
                if ($childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                {
                    $disabledAll = false;
                    break 2;
                }
                break;
            }
        }
        
        foreach($children as $childId => $childAttributes)
        {
            foreach($childAttributes as $childAttribute)
            {
                $optionSets[$childAttribute['plu']][$childAttribute['attribute_id']] = $childAttribute['value_index'];
                
                $price = floatval($childAttribute['pricing_value']);
                if (($disabledAll || $childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        && (is_null($minChildPrice) || $price < $minChildPrice))
                {
                    $minChildPrice = $price;
                    $minPriceChildProd = $childAttribute['price_data_object'];
                }
                if (($disabledAll || $childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        && (is_null($maxChildPrice) || $price > $maxChildPrice))
                {
                    $maxChildPrice = $price;
                    $maxPriceChildProd = $childAttribute['price_data_object'];
                }

                $configurableAttributesTempAttributes[$childAttribute['attribute_id']][$childAttribute['value_index']] = $childAttribute;
                if(!isset($pricingTempAttributes[$childAttribute['attribute_id']]))
                {
                    $pricingTempAttributes[$childAttribute['attribute_id']] = array('skip me' => false, 'values' => array());
                }

                if($pricingTempAttributes[$childAttribute['attribute_id']]['skip me'])
                {
                    continue;
                }

                if(!isset($pricingTempAttributes[$childAttribute['attribute_id']]['values'][$childAttribute['value_index']]))
                {
                    $pricingTempAttributes[$childAttribute['attribute_id']]['values'][$childAttribute['value_index']] = $price;
                }
                elseif($pricingTempAttributes[$childAttribute['attribute_id']]['values'][$childAttribute['value_index']] != $price)
                {
                    $pricingTempAttributes[$childAttribute['attribute_id']]['skip me'] = true;
                    unset($pricingTempAttributes[$childAttribute['attribute_id']]['values']);
                }
            }
        }
       
        /*get attributes to skip using collected data*/
        $skipPricingAttributeIds = array();
        $foundPricingAttr = 0;
        /*get attributes to skip using collected data*/
        foreach($pricingTempAttributes as $attrId => $data)
        {
            if ($data['skip me'] || $foundPricingAttr) {
                $skipPricingAttributeIds[] = $attrId;
            } else {
                $foundPricingAttr = true;
            }
        }

        unset($pricingTempAttributes);

        $priceStyle = $minChildPrice;

        /*if found no pricing attributes*/
        if (!$foundPricingAttr)
        {
            $priceStyle = $maxChildPrice;

            $styleNo = "";
            if (empty($style['no']))
            {
                if (!empty($style['internal_id']))
                {
                    $select = $this->_db->select()
                        ->from(Mage::getSingleton('core/resource')->getTableName('service_style'), array('no'))
                    ->where('internal_id = ?', $style['internal_id']);
                    $noTemp = $this->_db->fetchOne($select);
                    if (!empty($noTemp))
                    {
                        $styleNo = $noTemp;
                    }
                }
            }
            else
            {
                $styleNo = $style['no'];
            }

            if (strlen($styleNo))
            {
                if (!isset($this->_cache['error']['pricing']['style_no'][$styleNo]))
                {
                    $this->_cache['error']['pricing']['style_no'][$styleNo] = true;
                    if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
                    {
                        $this->_addWarningMsg(sprintf("Pricing error detected for style no %s, %s price will be used for all variants", $styleNo, $priceStyle), false);
                    }
                }
            }
            else if (!empty($style['internal_id']))
            {
                if (!isset($this->_cache['error']['pricing']['internal_id'][$style['internal_id']]))
                {
                    $this->_cache['error']['pricing']['internal_id'][$style['internal_id']] = true;
                    if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
                    {
                        $this->_addWarningMsg(sprintf("Pricing error detected (magento internal id: %s), %s price will be used for all variants", $style['internal_id'], $priceStyle), false);
                    }
                }
            }
            else
            {
                if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
                {
                    $this->_addWarningMsg("Pricing error detected.", false);
                }
            }
            if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
            {
                $this->_getLogger()->addMessage(sprintf("Error occured while detecting pricing attributes: sku: %s: detected the following attribute ids: %s", $product->getSku(), implode(',', $skipPricingAttributeIds)), Zend_Log::ERR);
            }
        }
        
        $superAttributes = array();
        if( !empty($style['internal_id']) )
        {
            $superAttributes = (array)$product->getTypeInstance(true)->getConfigurableAttributes($product)->getData();
            /* $product->getTypeInstance()->setUsedProductAttributeIds(
                array_keys($configurableAttributesTempAttributes)
            ); */
        }

        $configurableAttributesData = array();
        $unnasignedChildren = array();
        $position = 1;
        foreach($configurableAttributesTempAttributes as $attribute_id => $options)
        {
            $superAttributeId = null;
            if( count($superAttributes) > 0 )
            {
                foreach($superAttributes as $superAttribute)
                {
                    if($superAttribute['attribute_id'] == $attribute_id)
                    {
                        $superAttributeId = $superAttribute['product_super_attribute_id'];
                        break;
                    }
                }
            }

            $attribute = current($options);
            $attributeToSave = array(
                'id'                 => $superAttributeId,
                'attribute_id'       => $attribute_id,
                'attribute_code'     => $attribute['attribute_code'],
                'label'              => $attribute['attribute_name'],
                'frontend_label'     => $attribute['attribute_name'],
                'store_label'        => $attribute['attribute_name'],
                'use_default'        => Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_DEFAULT_VALUE_FOR_CONFIGURABLE_ATTRIBUTE) || $product->getStoreId() == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID || !Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES),
                'html_id'            => 'configurable__attribute_' . ($position-1),
                'position'           => $position++,
            );

            foreach($options as $option)
            {
                $pricingValue = in_array($attribute_id, $skipPricingAttributeIds) ? 0 : $option['pricing_value'] - $priceStyle;
                $attributeToSave['values'][$option['value_index']] = array(
                    'product_super_attribute_id'   => $superAttributeId,
                    'pricing_value'                => $pricingValue,
                    'value_index'                  => $option['value_index'],
                    'label'                        => $option['value_label'],
                    'default_label'                => $option['value_label'],
                    'store_label'                  => $option['value_label'],
                    'is_percent'                   => 0,
                    //'use_default_value'            => true,
                    'use_default_value'            => Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES) ? false : true,
                    'can_edit_price'               => true,
                    'can_read_price'               => true,
                );
            }
            $configurableAttributesData[$attribute_id] = $attributeToSave;
            $this->_unassignWrongChildren($children, $attribute_id, $unnasignedChildren);
        }

        if( !empty($unnasignedChildren) )
        {
            $productIds = Mage::getModel('catalog/product')->getResource()->getProductsSku($unnasignedChildren);
            $skus = array();
            foreach($productIds as $productId)
            {
                $skus[] = $productId['sku'];
            }
            $this->_addWarningMsg( sprintf("Product %s has not accepted some items to be assigned: %s", $product->getSku(), implode(', ', $skus)) );
        }
        
        if( !$this->_checkDoubledAttributeUsage($children, $configurableAttributesData, $product) )
        {
            $this->_checkDoubledAttributeOptionUsage($optionSets, $configurableAttributesData);
        }

        if ($forceSetParentPrices)
        {
            $sourceProduct = $foundPricingAttr ? $minPriceChildProd : $maxPriceChildProd;
            if ($sourceProduct instanceof Varien_Object)
            {
                /*copy price attributes from child but skip style mapped to prevent overwriting*/
                $this->copyPriceData($sourceProduct, $product, $this->getMappedStyleAttributes());
            }
            else
            {
                $message =  'Wrong items attributes data';
                if (!empty($style['no']))
                {
                    $message .= ": Style #{$style['no']}";
                }
                $message .= ", sku #{$product->getSku()}. Please check style configuration.";
                $this->_addWarningMsg($message);
            }
        }
        return $configurableAttributesData;
    }

    /**
     * Decline assignment simple produt for configurable product
     *
     * @param array $children
     * @param int $attribute_id
     * @param array $unnasignedChildren
     */
    protected function _unassignWrongChildren(&$children, $attribute_id, &$unnasignedChildren)
    {
        foreach($children as $childId => $child)
        {
            $signProduct = false;
            foreach($child as $option)
            {
                if($attribute_id == $option['attribute_id'])
                {
                    $signProduct = true;
                    break;
                }
            }
            if( $signProduct === false && !in_array($childId, $unnasignedChildren) )
            {
                $unnasignedChildren[] = $childId;
				unset( $children[$childId] );
            }
        }
    }

    /**
     * Get image label using attribute<n> info if needed for customization (absent in base configuration but can be added to frontend)
     *
     * @param array $image
     *
     * @return string|null
     */
    protected function _createImageLabel($image)
    {
        $label = array();
        for($i = 1; $i <= 3; $i++)
        {
            if(!empty($image['attribute'.$i]))
            {
                $label[] = $this->_attributeValues[$image['attribute'.$i]]['attribute_value'];
            }
        }
        if(!empty($label))
        {
            return implode(' / ', $label);
        }
        return null;
    }

    /**
     * Load items to protected property from staging tables
     *
     */
    protected function _getItems()
    {
        $style_ids = array();
        foreach($this->_styles as $style)
        {
            $style_ids[] = $style['style_id'];
        }

        /*price is a required attribute in magento, so it is most possible that mapping is set*/
        if($this->_mapModel->getPrice())
        {
            /*attach prices*/
            $from = array();
            foreach($this->_mapModel->getPrice() as $priceAlias)
            {
                $level = (int)substr($priceAlias, -1);
                $from[] = "IFNULL(GROUP_CONCAT(IF(pr.price_level = {$level}, pr.price, null)), '0.00000000000000000000') AS '{$priceAlias}'";
            }
            $from = implode(', ', $from);

            $select = $this->_db->select()
                ->from(array('pr' => Mage::getSingleton('core/resource')->getTableName('service_price')), array(new Zend_Db_Expr($from)))
                ->joinRight(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), 'pr.item_id=it.item_id AND pr.channel_id=it.channel_id')
                ->where('it.style_id IN (?)', $style_ids)
                ->where('it.channel_id = ?', $this->_globalVars['channel_id'])
            ->group('it.item_id');
        }
        else
        {
            $select = $this->_db->select()
                ->from(Mage::getSingleton('core/resource')->getTableName('service_items'))
                ->where('style_id IN (?)', $style_ids)
            ->where('channel_id = ?', $this->_globalVars['channel_id']);
        }

        foreach($this->_db->query($select) as $item)
        {
            if( empty($item['internal_id']) || !Mage::getModel('catalog/product')->getResource()->getProductsSku(array($item['internal_id'])) )
            {
                $this->_getInternalIdFromMultiChannel($item);
            }

            $this->_items[$item['style_id']][$item['item_id']] = $item;
        }
    }

    /**
     * Get internal_id in multi channel case
     *
     * @param array $entity
     */
    protected function _getInternalIdFromMultiChannel(&$entity)
    {
        if( $this->_isStyle($entity) )
        {
            $table = Mage::getSingleton('core/resource')->getTableName('service_style');
            $coloumn = 'style_id';
        }
        else
        {
            $table = Mage::getSingleton('core/resource')->getTableName('service_items');
            $coloumn = 'item_id';
        }

        $select = $this->_db->select()->distinct()
            ->from($table, array('internal_id'))
            ->where('internal_id is not null')
        ->where("{$coloumn} = ?", $entity[$coloumn]);

        if( !empty($entity['internal_id']) )
        {
            $select->where('internal_id != ?', $entity['internal_id']);
        }

        foreach($this->_db->fetchCol($select) as $id)
        {
            if( Mage::getModel('catalog/product')->getResource()->getProductsSku(array($id)) )
            {
                $internal_id = $id;
                break;
            }
        }

        if(!empty($internal_id))
        {
            $this->_db->update($table, array('internal_id' => $internal_id), "{$coloumn} = '{$entity[$coloumn]}'");
            $entity['internal_id'] = $internal_id;
        }
    }

    /**
     * Processing product object after save data
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $entity
     */
    protected function _afterSave($product = null, $entity = null)
    {
        if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_PRODUCT_POSITIONS))
        {
            $this->_productPosition($product);
        }

        if($this->_canImportProductImages())
        {
            $this->_saveImageInternalIds($product, $entity);
        }
    }

    /**
     * Set/update catalog product position basing on product creation timestamp
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _productPosition($product, $coef = 1)
    {
        if($product->getId())
        {
            $categories = $product->getCategoryIds();
            if(empty($_FILES)) $_FILES = array(0);
            foreach($categories as $categoryId)
            {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                //getUpdatedAt()
                $timestamp = $product->getCreatedAt();

                $order = ceil((pow(2,31) - strtotime($timestamp))/$coef);

                $existed = $category->getProductsPosition();
                $category->setPostedProducts(array($product->getId() => $order) + (array)$existed);

                try
                {
                    $category->save();
                }
                catch (Exception $e)
                {
                    $this->_getLogger()->addMessage(sprintf("Should not be here: file: %s; line: %s", __FILE__, __LINE__), Zend_Log::DEBUG);
                    $this->_getLogger()->addException($e);
                    $this->_addErrorMsg("Internal error (exception): " . $e->getMessage(), false);
                }
            }
        }
    }

    /**
     * Get product website ids
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _getWebsites($product)
    {
        return array_unique(array_merge((array)$product->getWebsiteIds(), $this->_globalVars['websites']));
    }

    /*public function memoryLog($str = '')
    {
        $str .= "\n";
        $str .= "Memory Usage: " . round(memory_get_usage()/1024/1024, 2) . "Mb\n";
        $str .= "Memory Peak:  " . round(memory_get_peak_usage()/1024/1024, 2) . "Mb\n";
        Mage::log($str, null, 'memory.log');

        $this->start = microtime(true);
        Mage::log(printf('Point 1 %.4F sec.', (microtime(true) - $this->start)), null, 'time.log');
    }*/

    /**
     * Get style data (data from sertvice_style staging table)
     *
     * @param string $guid
     * @param false | string $styleNo
     *
     * @return array | NULL
     */
    public function getStyle($guid, $styleNo = false)
    {
        $field = "style_id";
        $val = $guid;
        if ($styleNo !== false && strlen($styleNo))
        {
            $field = "no";
            $val = $styleNo;
        }
        foreach($this->_styles as $style)
        {
            if (array_key_exists($field, $style) && $style[$field] == $val) return $style;
        }
        return false;
    }

    /**
     * Method can help customize attribute name depended on different factors. Flag default_value_for_configurable_attribute should be set as false
     *
     * @param string $attributeSetId
     * @param array $style
     * @param array $item
     *
     * @return string
     */
    public function getAttributeName($attributeSetId, $style, $item)
    {
        return $this->_attributeModel->getAttributeName($this->_attributes[$attributeSetId]);
    }

    /**
     * Get item(s) data (data from sertvice_items staging table)
     *
     * @param string $guid
     * @param false | string $styleId
     *
     * @return array | NULL
     */
    public function getItems($guid = false, $styleId = false)
    {
        if ($styleId !== false)
        {
            if (array_key_exists($styleId, $this->_items))
            {
                if ($guid !== false)
                {
                    if (array_key_exists($guid, $this->_items[$styleId]))
                    {
                        return $this->_items[$styleId][$guid];
                    }
                    return NULL;
                }
                return $this->_items[$styleId];
            }
            return NULL;
        }

        if ($guid === false) return $this->_items;

        foreach($this->_items as $styleId => $items)
        {
            if (array_key_exists($guid, $items))
            {
                return $items[$guid];
            }
        }
        return NULL;
    }

    /**
     * Get product type using style "inventype" field
     *
     * @param string $inventype
     *
     * @return string | NULL
     */
    public function getProductTypeByInventype($inventype)
    {
        if (array_key_exists($inventype, $this->_productTypes))
        {
            return $this->_productTypes[$inventype];
        }
        return NULL;
    }

    /**
     * Get mapped pricing attribute codes
     *
     * @return array
     */
    public function getPriceAttributeCodes()
    {
        return array_merge(array_keys($this->_mapModel->getPrice()), array("special_from_date", "special_to_date"));
    }

    /**
     * Copy pricing values from one object (product) to another one
     *
     * @param Varien_Object $src
     * @param Varien_Object $dest
     * @param array $skip
     *
     * @return array
     */
    public function copyPriceData(Varien_Object $src, Varien_Object $dest, $skip = array())
    {
        if (!is_array($skip))
        {
            $skip = array();
        }
        $priceAttributeCodes = array_diff($this->getPriceAttributeCodes(), $skip);
        foreach($priceAttributeCodes as $priceAttributeCode)
        {
            $hasAttribute = 'has' . $this->_camelize($priceAttributeCode);
            if ($src->$hasAttribute())
            {
                $dest->setData($priceAttributeCode, $src->getData($priceAttributeCode));
            }
        }
    }

    /**
     * Remove data under price attributes (for instance, Special Price and MSRP) in case if its value equals to price amount
     *
     * @param Varien_Object $product
     */
    public function removeEqualPriceData(Varien_Object $product)
    {
        $priceAttributeCodes = array_keys($this->_mapModel->getPrice());
        foreach($priceAttributeCodes as $priceAttributeCode)
        {
            if( $priceAttributeCode != 'price' && $product->getData($priceAttributeCode) == $product->getData('price') )
            {
                $product->setData($priceAttributeCode, '');
            }
        }
    }

    /**
     * Get attribute codes mapped in style
     *
     * @return array
     */
    public function getMappedStyleAttributes()
    {
        $mapCustomStyle = $this->_getMapping(Teamwork_Service_Model_Mapping::CONST_STYLE,  true);
        if (!is_null($mapCustomStyle))
        {
            $mapCustomStyle = array_keys($mapCustomStyle);
        }
        else
        {
            $mapCustomStyle = array();
        }

        $mapDefaultStyle = $this->_getMapping(Teamwork_Service_Model_Mapping::CONST_STYLE, false);
        if (!is_null($mapDefaultStyle))
        {
            $mapDefaultStyle = array_keys($mapDefaultStyle);
        }
        else
        {
            $mapDefaultStyle = array();
        }

        return array_unique(array_merge($mapCustomStyle, $mapDefaultStyle));
    }

    /**
     * Get product type using style "inventype" field
     *
     * @param string $inventype
     *
     * @return string | NULL
     */
    protected function _getEcmItemCounter()
    {
        $select = $this->_db->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('service_items'), array('COUNT(item_id)'))
        ->where('request_id = ?', $this->_globalVars['request_id']);
        return $this->_db->fetchOne($select);
    }

    /**
     * Remember "use_default" product's attributes checkboxes
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Boolean $isResave
     */
    public function keepUseDefaultCheckboxes($product, $isResave = false)
    {
        if (Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID !== (int) $product->getStoreId())
        {
            foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute)
            {
                if (!$attribute->isScopeGlobal()
                    && (($isResave && !$product->hasData($attribute->getAttributeCode()))
                         || (!$isResave && !$product->getExistsStoreValueFlag($attribute->getAttributeCode())))
                )
                {
                    $product->setData($attribute->getAttributeCode(), false);
                }
            }
        }
    }

    /**
     * Create mapping between magento attribute and chq fields
     *
     * @param array $mapArray
     * @param string $type
     *
     * @return array
     */
    protected function _getChqFieldsMapping($mapArray, $type, $style)
    {   
        $collection = Mage::getModel('teamwork_service/mappingproperty')->getCollection()
            ->addFieldToFilter('channel_id', $this->_globalVars['channel_id'])
            ->addFieldToFilter('type_id', $type)
            ->load();

        $arrayAttribute = array();

        foreach ($collection as $val)
        {
            $attribute = Mage::getModel('eav/entity_attribute')->load($val->getAttributeId());

            $field = Mage::getModel('teamwork_service/chqmappingfields')->load($val->getFieldId());

            if ($type == Teamwork_Service_Model_Mapping::CONST_STYLE)
            {
                $value = trim(substr($field->getValue(), strlen($type)+1));
            }
            else
            {
                $value = trim($field->getValue());
            }

            $arrayAttribute[$attribute->getAttributeCode()] = strtolower($value);
        }
        
        if (count($arrayAttribute) > 0 && count($mapArray) > 0 )
        {
            $mapArray = array_merge($mapArray, $arrayAttribute);
        }
        
        return $mapArray;
    }

    /**
     * Loading the product taking into account store id; checking product type
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Boolean $isResave
     */
    public function _loadProduct($entityId = null, $type = null)
    {
        $product = Mage::getModel('catalog/product');
        $entityId = (int)$entityId;
        if ($entityId)
        {
            $nonDefaultStore = false;
            if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES)
                && array_key_exists('store_id', $this->_globalVars)
                && intval($this->_globalVars['store_id']) !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            )
            {
                $product->setStoreId(intval($this->_globalVars['store_id']));
                $nonDefaultStore = true;
            }
            $product->load($entityId);

            if (!$product->getId())
            {
                //product not found (probably has been deleted)
                $this->_getLogger()->addMessage(sprintf('Product with id %s not found. New product will be created instead.', $entityId));
                //store id should be unset or set to null due EE 1.14.1.0 bug related to url_key
                $product->unsetData('store_id');
            }
            else
            {
                //event product loaded
                $params = new Varien_Object();
                $params->setData('type_id', $type);
                $params->setData('product', $product);
                Mage::dispatchEvent('teamwork_transfer_product_loaded', array('params'=>$params));
                $type = $params->getData('type_id');

                $currentType = $product->getTypeId();
                if( !empty($currentType) && !empty($type) && $currentType != $type && !Mage::helper('teamwork_transfer/conversion')->checkConversion($currentType, $type) )
                {
                    Mage::throwException(Mage::helper('core')->__('Fatal error. Wrong product conversion. Product with type %s can not be converted to %s. Id: %s, sku: %s.', $currentType, $type, $product->getId(), $product->getSku()));
                }

                if (!is_null($type) && $product->getTypeId() !== $type)
                {
                    $product = Mage::getModel('catalog/product');
                }
                else if ($nonDefaultStore)
                {
                    //set "use_default" checkboxes
                    $this->keepUseDefaultCheckboxes($product);
                }
            }
        }
        return $product;
    }

    /**
     * Save non-default store product data
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _saveNonDefaultStoreValues($product)
    {
        if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES)
            && array_key_exists('store_id', $this->_globalVars)
            && intval($this->_globalVars['store_id']) !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            && intval($product->getStoreId()) !== intval($this->_globalVars['store_id']))
        {
            $product->setStoreId(intval($this->_globalVars['store_id']));
            //unset url_key and stock_data to assing from default store and to prevent crushing
            $product->unsetData('url_key');
            $product->unsetData('stock_data');
            //unset configurable type specific data which was already set for default store to prevent crushing
            $product->unsetData('configurable_attributes_data');
            $product->unsetData('configurable_products_data');

            $product->unsetData(self::GALLERY_ATTRIBUTE_CODE);


            //set "use_default" checkboxes
            $this->keepUseDefaultCheckboxes($product, true);

            //set websiteids
            $product->setData('website_ids', $this->_getWebsites($product));
            
            try
            {
                $product->save();
            }
            catch(Exception $e)
            {
                $this->_addErrorMsg(sprintf("Error occured while saving default values for product: sku %s - %s", $product->getSku(), $e->getMessage()), true);
                $this->_getLogger()->addException($e);
            }

            /*recall that ECM on the go due to the fact that product saving process may take some time*/
            $this->checkLastUpdateTime();
        }
    }

    /**
     * Save product for appointed store and default store if needed keeping safe url_key
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _saveProduct(Mage_Catalog_Model_Product $product)
    {
        if (intval($product->getStoreId()) !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
           || !Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES))
        {
            //set websiteids
            $product->setData('website_ids', $this->_getWebsites($product));
        }
        else
        {
            //reset websiteids for the first save in 0-store
            $product->unsetData('website_ids');
        }


        $imgAttributes = array();
        if (intval($product->getStoreId()) !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
        {
            $defaultValue = $product->getAttributeDefaultValue('url_key');
            if ($defaultValue === false)
            {
                $defaultValue = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'url_key', 4);
            }
            //if assigned url_key the same as default to prevent crushing return back old value:
            if ($product->getData('url_key') == $defaultValue)
            {
                // if Use Default flag has been set before product update
                if (!$product->getExistsStoreValueFlag('url_key'))
                {
                    // return it back
                    $product->setData('url_key', false);
                }
                else
                {
                    //use value which has been set before update
                    $product->setData('url_key', Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'url_key', $product->getStoreId()));
                }
            }

            if (Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_DAM_ENABLED))
            {
                $mediaAttributes = array_keys($product->getMediaAttributes());
                foreach($mediaAttributes as $attributeCode)
                {

                    if ($product->getData($attributeCode))
                    {
                        $imgAttributes[$attributeCode] = $product->getData($attributeCode);
                        $product->setData($attributeCode, false);
                    }
                }
            }
        }
        
        try
        {
            $product->save();
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while saving product: sku %s - %s", $product->getSku(), $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }

        if (Mage::getStoreConfigFlag(Teamwork_Service_Helper_Config::XML_PATH_DAM_ENABLED) && $imgAttributes)
        {
            $action = Mage::getModel('catalog/resource_product_action');
            $action->updateAttributes(array($product->getId()), $imgAttributes, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        }
    }

    /**
     * Get code of mageto attribute using id and cache variable
     *
     * @param int $id
     *
     * @return string
     */
    public function getMagentoAttributeCode($id)
    {
        if (!array_key_exists($id, $this->_magentoAttributesIdsCache))
        {
            $attributeModel = $this->_attributeModel->getAttributeById($id);
            if ($attributeModel->getId())
            {
                $this->_magentoAttributesIdsCache[$id] = $attributeModel->getData('attribute_code');
            }
            else
            {
                $this->_magentoAttributesIdsCache[$id] = false;
            }
        }
        return $this->_magentoAttributesIdsCache[$id];
    }
    
    /**
     * Check if style build based on same attributes on different levels
     *
     * @param array $style
     */
    public function _checkDoubledAttributeUsage($children, $attribute, $product)
    {
        if( !empty($children) )
        {
            $duplicates = array();
            
            $child = current($children);
            foreach($child as $option)
            {
                if( !in_array($option['attribute_id'], $duplicates) )
                {
                    $duplicates[] = $option['attribute_id'];
                }
                else
                {
                    $this->_addWarningMsg( Mage::helper('core')->__('Product %s configured by same attribute - %s', $product->getSku(), $attribute[$option['attribute_id']]['attribute_code']) );
                    return true;
                }
            }
        }
    }
    
    /**
     * Check if children build based on same attribute option set (actual for merged options)
     *
     * @param array $style
     */
    public function _checkDoubledAttributeOptionUsage($optionSets, $attribute)
    {
        $duplicates = array();
        foreach($optionSets as $key1 => &$optionSet1)
        {
            foreach($optionSets as $key2 => &$optionSet2)
            {
                if($key1 != $key2)
                {
                    if($optionSet1 == $optionSet2)
                    {
                        $duplicates[$key1][] = $key2;
                        if( !in_array($key1,$duplicates[$key1]) )
                        {
                            $duplicates[$key1][] = $key1;
                        }
                        unset($optionSets[$key2]);
                    }
                }
            }
        }
        
        if( !empty($duplicates) )
        {
            foreach($duplicates as $optionSetKey => $duplicateProducts)
            {
                $message = 'PLUs: ' . implode($duplicateProducts, ', ') . ' use same options:';
                foreach($optionSets[$optionSetKey] as $attributeId => $attributeOptionId)
                {
                    $message .= " {$attribute[$attributeId]['attribute_code']} - {$attribute[$attributeId]['values'][$attributeOptionId]['label']};"; 
                }
                $this->_addWarningMsg($message);
            }
            return true;
        }
    }
}
