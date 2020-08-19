<?php
/**
 * SetCoreFields.php
 *
 * @copyright B7 Interactive, LLC. All Rights Reserved.
 */

/**
 * Class SearchSpring_Manager_Operation_Product_SetCoreFields
 *
 * Set SearchSpring core fields to the feed
 *
 * @author Nate Brunette <nate@b7interactive.com>
 */
class SearchSpring_Manager_Operation_Product_SetCoreFields extends SearchSpring_Manager_Operation_Product
{
    /**#@+
     * Feed constants
     */
    const FEED_SKU = 'sku';
    const FEED_PRODUCT_TYPE = 'product_type';
    const FEED_DESCRIPTION = 'description';
    const FEED_SHORT_DESCRIPTION = 'short_description';
    const FEED_QUANTITY = 'quantity';
    const FEED_IN_STOCK = 'in_stock';
    const FEED_WEIGHT = 'weight';
    const FEED_URL = 'url';
    const FEED_NAME = 'name';
    const FEED_CHILD_QUANTITY = 'child_quantity';
    const FEED_CHILD_SKU = 'child_sku';
    const FEED_CHILD_NAME = 'child_name';
    const FEED_DAYS_OLD = 'days_old';
    const FEED_VISIBILITY_IN_SEARCH = 'visible_in_search';
    const FEED_VISIBILITY_IN_CATALOG = 'visible_in_catalog';
    /**#@-*/
    
    
    protected $_localReservedFields = array(
        self::FEED_SKU,
        self::FEED_PRODUCT_TYPE,
        self::FEED_DESCRIPTION,
        self::FEED_SHORT_DESCRIPTION,
        self::FEED_QUANTITY,
        self::FEED_IN_STOCK,
        self::FEED_WEIGHT,
        self::FEED_URL,
        self::FEED_NAME,
        self::FEED_CHILD_QUANTITY,
        self::FEED_CHILD_SKU,
        self::FEED_CHILD_NAME,
        self::FEED_DAYS_OLD,
        self::FEED_VISIBILITY_IN_SEARCH,
        self::FEED_VISIBILITY_IN_CATALOG
    );
    
    /**
     * Add SearchSpring core fields to the feed
     *	 - sku
     *	 - product_type
     *	 - quantity
     *	 - in_stock
     *	 - weight
     *	 - manufacturer
     *	 - url
     *	 - url
     *	 - image_url
     *	 - thumbnail_url
     *	 - cached_thumbnail_url
     *	 - name
     *	 - description
     *	 - short_description
     *	 - child_quantity
     *	 - child_name
     *	 - child_sku
     *	 - days_old
     *	 - visible_in_search
     *	 - visible_in_catalog
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return $this
     */
    public function perform(Mage_Catalog_Model_Product $product)
    {
        $phlp = Mage::helper('searchspring_manager/product');
        
        $webBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        
        $this->getRecords()->set(self::FEED_SKU, $product->getSku());
        $this->getRecords()->set(self::FEED_PRODUCT_TYPE, $product->getData('type_id'));
        $this->getRecords()->set(self::FEED_QUANTITY, number_format($this->getQuantity($product)));
        $this->getRecords()->set(self::FEED_WEIGHT, number_format((double)$product->getWeight(), 2));
        $this->getRecords()->set(self::FEED_URL, $webBaseUrl . $product->getUrlPath());
        
        $stockItem = $product->getStockItem();
        $this->getRecords()->set(self::FEED_IN_STOCK, $stockItem->getIsInStock());
        
        $productName = $this->getSanitizer()->removeNewlinesAndStripTags($product->getName());
        $description = $this->getSanitizer()->sanitizeForRequest($product->getData('description'));
        $shortDescription = $this->getSanitizer()->sanitizeForRequest($product->getData('short_description'));
        $this->getRecords()->set(self::FEED_NAME, $productName);
        $this->getRecords()->set(self::FEED_DESCRIPTION, $description);
        $this->getRecords()->set(self::FEED_SHORT_DESCRIPTION, $shortDescription);
        
        $this->setChildQuantity($product);
        
        $this->setDaysOld($product);
        
        $this->setVisibility($product);
        
        $optionsValues = $this->getIcons($product->getId());
        
        $newOptions = [];
        
        foreach ($optionsValues['value'] as $option) {
            $simple_id = $option['child_id'];
            $simpleProduct = Mage::getModel('catalog/product')->load($simple_id);
            
            $option['image'] = (string) Mage::helper('catalog/image')->init($simpleProduct, 'small_image')->resize(376, 490);
            $option['hover'] = ($this->getRolloverImage($simpleProduct)) ? (string) $this->getRolloverImage($simpleProduct) : "";
            
            $newOptions[] = $option;
        }
        
        $optionsValues['value'] = $newOptions;
        
        $this->getRecords()->set('swatch_icons', json_encode($optionsValues));
        
        $imageHelper = Mage::helper('searchspring_manager/catalog_image');
        $imageHelper->init($product, 'small_image')->resize(376, 490);
        $imageUrl = $imageHelper->ifCachedGetUrl() ?: (string) $imageHelper;
        
        $this->getRecords()->set('default_image', $imageUrl);
        
        $this->getRecords()->set('rollover_image', $this->getFirstOptionImage($product,true) ?: '');
        
        return $this;
    }
    
    /**
     * Helper method to get the quantity from a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    private function getQuantity(Mage_Catalog_Model_Product $product)
    {
        $stock_item = $product->getData('stock_item');
        $quantity = 0;
        if(is_object($stock_item) && method_exists($stock_item, 'getData')) {
            $quantity = $stock_item->getData('qty');
        }
        return (int)$quantity;
    }
    
    /**
     * If the product is composite, find the child quantities and set that to the field
     */
    private function setChildQuantity(Mage_Catalog_Model_Product $product)
    {
        // default to normal quantity
        $childQuantity = $this->getRecords()->get(self::FEED_QUANTITY);
        
        // find the child quantity if it exists
        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                /** @var Mage_Catalog_Model_Product_Type_Grouped $typeInstance */
                $typeInstance = $product->getTypeInstance(true);
                $associated = $typeInstance->getAssociatedProducts($product);
                $childQuantity = $this->getQuantityForChildren($associated);
                
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
                $typeInstance = $product->getTypeInstance(true);
                
                $attributes = array();
                foreach ($typeInstance->getConfigurableAttributes($product) as $attribute) {
                    $attributes[] = $attribute->getProductAttribute()->getAttributeCode();
                }
                
                $displayOos = $this->getConfig()->isOutOfStockIndexingEnabled($product->getStoreId());
                $children = $typeInstance->getUsedProducts(null, $product);
                
                $attributeValues = array();
                foreach($children as $child) {
                    $stockItem = $child->getStockItem();
                    
                    if(
                        // Check the stockItem even exists, customers are stupid
                        is_object($stockItem) && method_exists($stockItem, 'getIsInStock') &&
                        // If not indexing out of stock products, skip out of stock options
                        !$stockItem->getIsInStock() && !$displayOos
                        ) {
                            continue;
                        }
                        
                        foreach($attributes as $attribute) {
                            $attributeValues[$attribute][] = $child->getAttributeText($attribute);
                        }
                        
                }
                
                foreach($attributeValues as $attribute => $values) {
                    $values = array_unique($values);
                    
                    if(in_array($attribute, $this->_globalReservedFields)) {
                        $attribute = 'ss_mage_attr_' . $attribute;
                    }
                    
                    foreach($values as $value) {
                        $this->getRecords()->add($attribute, $value);
                    }
                }
                
                $childQuantity = $this->getQuantityForChildren($children);
                
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                /** @var Mage_Bundle_Model_Product_Type $typeInstance */
                $typeInstance = $product->getTypeInstance(true);
                $optionsIds = $typeInstance->getOptionsIds($product);
                $selections = $typeInstance->getSelectionsCollection($optionsIds, $product);
                $bundleOptions = $typeInstance->getOptionsByIds($optionsIds, $product);
                $bundleOptions->appendSelections($selections);
                
                /** @var Mage_Bundle_Model_Option $bundleOption */
                foreach ($bundleOptions as $bundleOption) {
                    $products = $bundleOption->getData('selections');
                    if (is_array($products)) {
                        $childQuantity += $this->getQuantityForChildren($products);
                    }
                }
                
                break;
        }
        
        $this->getRecords()->set(self::FEED_CHILD_QUANTITY, $childQuantity);
    }
    
    /**
     * Set the hos many days old the product is
     */
    private function setDaysOld($product) {
        $createdAt = strtotime($product->getCreatedAt());
        $this->getRecords()->set(self::FEED_DAYS_OLD, floor((time() - $createdAt) / 60 / 60 / 24));
    }
    
    /**
     * Loop over an array of products to set field data and calculate total child quantity
     *
     * @param array $products
     * @return int
     */
    private function getQuantityForChildren(array $products)
    {
        $quantity = 0;
        
        /** @var Mage_Catalog_Model_Product $product */
        foreach ($products as $product) {
            $this->getRecords()->add(self::FEED_CHILD_SKU, $product->getSku());
            $this->getRecords()->add(self::FEED_CHILD_NAME, $product->getName());
            
            $quantity += $this->getQuantity($product);
        }
        
        return $quantity;
    }
    
    private function setVisibility($product) {
        
        $vis = Mage::getSingleton('catalog/product_visibility');
        
        $searchFl = in_array($product->getVisibility(), $vis->getVisibleInSearchIds());
        $catalogFl = in_array($product->getVisibility(), $vis->getVisibleInCatalogIds());
        
        $this->getRecords()->set(self::FEED_VISIBILITY_IN_SEARCH, (int) $searchFl);
        $this->getRecords()->set(self::FEED_VISIBILITY_IN_CATALOG, (int) $catalogFl);
    }
    
    /**
     * Get swatch icons for Product
     */
    public function getIcons($product_id){
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $select = $read->select()
        ->from(array('super' => $resource->getTableName('catalog_product_super_attribute')), array('super.product_id','super.attribute_id'))
        ->join(array('eav' => $resource->getTableName('eav_attribute')),
            'eav.attribute_id = super.attribute_id',
            array('eav.attribute_code','eav.frontend_label'))
            ->where('super.product_id = ?', $product_id);
            $result = $read->fetchAll($select);
            $colors = @explode("," , Mage::getStoreconfig('ecp_color/color_attr'));
            $color_attribute = null;
            $label_attribute = null;
            $resultOptions   = array();
            if(count($result)){
                foreach($result as $info){
                    $attribute_code = $info['attribute_code'];
                    if(in_array($attribute_code, $colors)){
                        $color_attribute = $attribute_code;
                        $label_attribute = $info['frontend_label'];
                        break;
                    }
                }
            }
            if($color_attribute){
                $flat_table = 'catalog_product_flat_' . Mage::app()->getStore()->getStoreId();
                $selectOption = $read->select()
                ->from(array('rel' => $resource->getTableName('catalog_product_relation')), array('rel.child_id'))
                ->join(array('flat' => $resource->getTableName($flat_table)),
                    'flat.entity_id = rel.child_id',
                    array("flat.$color_attribute","flat.$color_attribute"."_value"))
                    ->where('rel.parent_id = ?', $product_id)
                    ->group("flat.$color_attribute");
                    $resultOptions['value'] = $read->fetchAll($selectOption);
                    $resultOptions['code']  = $color_attribute;
                    $resultOptions['label'] = $label_attribute;
            }
            
            
            if(isset($resultOptions['value']) && count($resultOptions['value'])){
                $arrayOption = array();
                foreach($resultOptions['value'] as $info){
                    $arrayOption[] = $info[$resultOptions['code']];
                }
                $selectPosition = $read->select()
                ->from(array('pos' => $resource->getTableName('eav_attribute_option')), array('pos.option_id','pos.sort_order'))
                ->where('pos.option_id in (?)',$arrayOption)
                ->order('pos.sort_order', 'ASC')
                ;
                $position = $read->fetchAll($selectPosition);
                if($position){
                    $values = array();
                    foreach($position as $data){
                        $id = $data['option_id'];
                        foreach($resultOptions['value'] as $inf){
                            if($inf[$resultOptions['code']] == $id) {
                                $inf['position'] = $data['sort_order'];
                                $values[] = $inf;
                            }
                        }
                    }
                    $resultOptions['value'] = $values;
                }
                return $resultOptions;
                
            }
            return array();
    }
    
    /**
     * Get first option Image
     */
    private function getFirstOptionImage($_product,$roll_over=false) {
        if($_product->getTypeId() != 'configurable')
            return Mage::helper('catalog/image')->init($_product, 'small_image')->resize(376, 490);
            
            $options=$this->getIcons($_product->getId());
            if(!count($options))
                return Mage::helper('catalog/image')->init($_product, 'small_image')->resize(376, 490);
                
                $child_product_id=$options['value'][0]['child_id'];
                $childProduct = Mage::getModel('catalog/product')->load($child_product_id);
                
                if($roll_over) {
                    if ($childProduct->getRollOver() && $childProduct->getRollOver()!="no_selection")
                        return Mage::helper('catalog/image')->init($childProduct, 'roll_over')->resize(376, 490);
                        else
                            return $this->getProductSecondResizeImage($childProduct);
                }
                
                return Mage::helper('catalog/image')->init($childProduct, 'small_image')->resize(376, 490);
    }
    
    /**
     * Get next image
     */
    private function getProductSecondResizeImage($product) {
        foreach ($product->getMediaGalleryImages() as $image) {
            if($product->getSmallImage() == $image->getFile())
                continue;
                return Mage::helper('catalog/image')->init($product, 'small_image',$image->getFile())->resize(376, 490);
        }
        return false;
    }
    
    /**
     * Get roll_over image type
     */
    private function getRolloverImage($product) {
        if ($product->getRollOver() && $product->getRollOver() != "no_selection")
            return Mage::helper('catalog/image')->init($product, 'roll_over')->resize(376, 490);
            else
                return $this->getProductSecondResizeImage($product);
    }
    
}