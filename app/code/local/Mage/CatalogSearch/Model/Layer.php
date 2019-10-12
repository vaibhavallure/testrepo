<?php
/**
 * Searchanise update based on group
 */

class Mage_CatalogSearch_Model_Layer extends Mage_Catalog_Model_Layer
{
    const XML_PATH_DISPLAY_LAYER_COUNT = 'catalog/search/use_layered_navigation_count';

    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection
     */
    public function getProductCollection()
    {

        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
//        Mage::log("-----------------------------------------------------------------------------------------------",Zend_Log::DEBUG,'search.log',true);
        
return $collection;


    }

    /**
     * Prepare product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
    public function prepareProductCollection($collection)
    {

        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
            ->setStore(Mage::app()->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();

        $search_text = Mage::helper('catalogsearch')->getQuery()->getQueryText();
        $collection->getSelect()->where("e.sku like '%".$search_text."%' or e.name like '%".$search_text."%'");
        
        /*Filter By Group Code Starts Here*/

            /*Find Group of Customer Start*/
//                Mage::log('Catalog Search Model Layer',Zend_Log::DEBUG,'search.log',true);


        $groupCollection = Mage::getModel('customer/group')->getCollection()->addFieldToFilter('customer_group_code', array('eq'=>'NOT LOGGED IN'));
        $groupCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns('customer_group_id');
        $allowed_group = 'all';
        if($groupCollection->getSize()>0){
            foreach ($groupCollection as $value)
            {
                $allowed_group = $value['customer_group_id']+1;
                break;
            }
        }

                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $customerData = Mage::getSingleton('customer/session')->getCustomer();
                    $group_id = $customerData->getGroupId()+1;
                    $allowed_group = $group_id;
                }
            /*Find Group of Customer End*/

            $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product','allowed_group');

            if($attributeModel->getId())
            {
                $collection->getSelect()->joinLeft(
                    array("cp_allowed_group" => "catalog_product_entity_text"),
                    'e.entity_id = cp_allowed_group.entity_id AND cp_allowed_group.attribute_id = '.$attributeModel->getId()
                );

                $collection->getSelect()->where("FIND_IN_SET('all',cp_allowed_group.value) OR FIND_IN_SET(".$allowed_group.",cp_allowed_group.value) OR cp_allowed_group.value IS NULL");

            }

        /*Filter By Group Code Starts Here*/

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
//       Mage::log($collection->getSelect()->__toString(),Zend_Log::DEBUG,'abc2.log',true);
        return $this;
    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if ($this->_stateKey === null) {
            $this->_stateKey = 'Q_' . Mage::helper('catalogsearch')->getQuery()->getId()
                . '_'. parent::getStateKey();
        }
        return $this->_stateKey;
    }

    /**
     * Get default tags for current layer state
     *
     * @param   array $additionalTags
     * @return  array
     */
    public function getStateTags(array $additionalTags = array())
    {
        $additionalTags = parent::getStateTags($additionalTags);
        $additionalTags[] = Mage_CatalogSearch_Model_Query::CACHE_TAG;
        return $additionalTags;
    }

    /**
     * Add filters to attribute collection
     *
     * @param   Mage_Catalog_Model_Resource_Eav_Resource_Product_Attribute_Collection $collection
     * @return  Mage_Catalog_Model_Resource_Eav_Resource_Product_Attribute_Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addIsFilterableInSearchFilter()
            ->addVisibleFilter();
        return $collection;
    }

    /**
     * Prepare attribute for use in layered navigation
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @return  Mage_Eav_Model_Entity_Attribute
     */
    protected function _prepareAttribute($attribute)
    {
        $attribute = parent::_prepareAttribute($attribute);
        $attribute->setIsFilterable(Mage_Catalog_Model_Layer_Filter_Attribute::OPTIONS_ONLY_WITH_RESULTS);
        return $attribute;
    }

}
