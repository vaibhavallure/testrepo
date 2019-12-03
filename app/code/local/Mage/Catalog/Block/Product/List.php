<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2019 Magento, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product list
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;


    protected $_priceArray = array();
    protected $_userType;
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

            if (Mage::registry('product')) {
                /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                if ($categories->count()) {
                    $this->setCategoryId($categories->getFirstItem()->getId());
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                    $this->addModelTags($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        if($layer=$this->getLayer()) {
            if($sortOption=$layer->getCurrentCategory()->getForcefullySortingBy()) {
                $this->_productCollection->getSelect()->reset( Zend_Db_Select::ORDER );
                $this->_productCollection->getSelect()->order('e.'.$sortOption.' desc');
            }
        }


        return $this->_productCollection;
    }

    /**
     * Get catalog layer model
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }
        return Mage::getSingleton('catalog/layer');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection' => $this->_getProductCollection()
        ));

        $this->_getProductCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    public function setCollection($collection)
    {
        $this->_productCollection = $collection;
        return $this;
    }

    public function addAttribute($code)
    {
        $this->_getProductCollection()->addAttributeToSelect($code);
        return $this;
    }

    public function getPriceBlockTemplate()
    {
        return $this->_getData('price_block_template');
    }

    /**
     * Returns product price block html
     *
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $displayMinimalPrice
     * @param string $idSuffix
     * @return string
     */
    public function getPriceHtmls($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $type_id = $product->getTypeId();
        if (Mage::helper('catalog')->canApplyMsrp($product)) {
            $realPriceHtml = $this->_preparePriceRenderer($type_id)
                ->setProduct($product)
                ->setDisplayMinimalPrice($displayMinimalPrice)
                ->setIdSuffix($idSuffix)
                ->toHtml();
            $product->setAddToCartUrl($this->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }

        return $this->_preparePriceRenderer($type_id)
            ->setProduct($product)
            ->setDisplayMinimalPrice($displayMinimalPrice)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return Mage_Catalog_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('catalog/config');
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Block_Product_List
     */
    public function prepareSortableFieldsByCategory($category) {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($categorySortBy = $category->getDefaultSortBy()) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve block cache tags based on product collection
     *
     * @return array
     */
    public function getCacheTags()
    {
        return array_merge(
            parent::getCacheTags(),
            $this->getItemsTags($this->_getProductCollection())
        );
    }

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

    public function getCustomOptions($productId){
        $product = Mage::getModel("catalog/product")->load($productId);
        $options = $product->getOptions();
        if(count($options) > 0){
            return $options;
        }
        return null;
    }
    public function getFirstOptionImage($_product,$roll_over=false)
    {
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


    public function getProductSecondResizeImage($product)
    {

        foreach ($product->getMediaGalleryImages() as $image)
        {
            if($product->getSmallImage() == $image->getFile())
                continue;

            return Mage::helper('catalog/image')->init($product, 'small_image',$image->getFile())->resize(376, 490);

        }
        return false;
    }


    public function getRolloverImage($product)
    {
        if ($product->getRollOver() && $product->getRollOver()!="no_selection")
            return Mage::helper('catalog/image')->init($product, 'roll_over')->resize(376, 490);
        else
            return $this->getProductSecondResizeImage($product);
    }




    /*for custom search filter*/
    public function getFilters(){


//        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product','style_of_jewelry');
//        if($attributeModel->getId()){
//            var_dump($attributeModel->getId());
//        }

//        $_filter_collection->getSelect()->joinLeft(
//            array("cp_allowed_group" => "catalog_product_entity_text"),
//            'e.entity_id = cp_allowed_group.entity_id AND cp_allowed_group.attribute_id = '.$attributeModel->getId()
//        );

        $_filter_collection = clone $this->_productCollection;
        $filter_helper =  Mage::helper('catalogsearch/filter');


        $_filter_collection->getSelect()->reset(Zend_Db_Select::LIMIT_COUNT);
        $_filter_collection->getSelect()->reset(Zend_Db_Select::LIMIT_OFFSET);

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = $_filter_collection->getSelect()->__toString();

        $results = $readConnection->fetchAll($query);

        $category_ids = array();
        $categoryArra = array();
        $colorArra = array();
        $gemstoneArra = array();
        $gemstoneMap = array();
        $priceArray = array();
        $metalCnt = 0;

        foreach ($results as $product):


            $temp_sku = $product['sku'];

            if(!empty($temp_sku)) {
                $temp_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $temp_sku);
                $currentProductId = $temp_product->getId();
                $product = $temp_product;
            }
            $priceArray[] = $this->getPriceArray($product);

            $category_ids = array_merge($category_ids,$product->getCategoryIds());

            $gems = $product->getGemstone();

            if(!empty($gems)):
                $gemsAr = explode(',',$gems);

                if(count($gemsAr) > 1):
                    foreach ($gemsAr as $gem):
                        array_push($gemstoneArra,$gem);
                    endforeach;
                else:
                    array_push($gemstoneArra,$gems);
                endif;
            endif;
            /*DISABLE METAL*/

//            $optionsValues = $this->getIcons($currentProductId);
//            if(!empty($optionsValues)):
//                $attribute_code = $optionsValues['code'];
//                foreach ($optionsValues['value'] as $option):
//                    $optionLb = $option[$attribute_code . "_value"];
//                    $colorArra[$metalCnt]['label']=$optionLb;
//                    $colorArra[$metalCnt]['code']=$option[$attribute_code];
//                    $metalCnt++;
//                endforeach;
//            endif;
        endforeach;


        $category_ids = array_unique($category_ids);
        $colorArra = array_unique($colorArra ,SORT_REGULAR);
        $gemstoneArra = array_unique($gemstoneArra);
        $priceArray = array_unique($priceArray);

        $categories = Mage::getResourceModel('catalog/category_collection');
        $categories->addAttributeToSelect('*');
        $categories->addAttributeToFilter('entity_id', ['in' => $category_ids]);
        $categories->addAttributeToFilter('is_active', 1);

        $cnt = 0;
        foreach ($categories as $category):
            $label = $category->getName();
            if (strpos(strtolower($label), 'style') !== false):
            else:
                $categoryArra[$cnt]['code'] = $category->getId();
                $categoryArra[$cnt]['label'] = $label;//.' ('.$category->getProductCount().')';
            endif;
            $cnt++;
        endforeach;

        $cnt = 0;

        foreach ($gemstoneArra as $gems_id):
            $gemstoneMap[$cnt]['code'] = $gems_id;
            $gemstoneMap[$cnt]['label'] = $this->getOptionLabel('gemstone',$gems_id);
            $cnt++;
        endforeach;

        sort($priceArray);

        $priceArray = $filter_helper->createGroups($priceArray);

        return array('category' => $categoryArra,
            'metal' => $colorArra,
            'gemstone' => $gemstoneMap,
            'price'=> $priceArray
        );

    }

    public function getPriceArray($_product){
        $_taxHelper  = $this->helper('allure_taxconfig');

        $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());

        $_regularPrice = $_taxHelper->getPrice($_product, $_product->getPrice(), $_simplePricesTax);
        $_finalPrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice(), $_simplePricesTax);

        return $_finalPrice;
    }

    public function getOptionLabel($attributeCode,$attributeValue){
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option){
            if($option["value"] == $attributeValue){
                return $option["label"];
            }
        }
        return null;
    }
    public function getFilterHelper(){
        return Mage::helper('catalogsearch/filter');
    }
    public function getBaseSearchUrl(){
        return $this->helper('catalogsearch')->getResultUrl().'?q='.$this->helper('catalogsearch')->getQueryText();
    }
}
