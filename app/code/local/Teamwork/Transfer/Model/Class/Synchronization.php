<?php
/**
 * Products and categories synchronization model (magento object ids -> staging tables internal_ids)
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Synchronization extends Teamwork_Transfer_Model_Transfer
{
    /**
     * Categories data from staging tables
     *
     * @var array
     */
    protected $_stagingCats;

    /**
     * Categories data from magento tables
     *
     * @var array
     */
    protected $_magentoCats;


    //public $stagingEmptyCats;
    protected $isUse = false;

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     */
    public function init($globalVars)
    {
        $this->_globalVars = $globalVars;
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Entry point
     */
    public function execute()
    {
        if($this->isUse)
        {
            $this->_syncCategories();
            //$this->_syncStyles();
            //$this->_syncItems();
        }
    }

    /**
     * Get magento product ids and set as internal_id values to 'service_style' table
     */
    protected function _syncStyles()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_style');
        $select = $this->_db->select()->from($table)->where('request_id = ?', $this->_globalVars["request_id"])->where('channel_id = ?', $this->_globalVars['channel_id'])->where('internal_id is null');
        $styles = $this->_db->fetchAll($select);

        if(!empty($styles))
        {
            $_product = Mage::getModel('catalog/product');

            foreach ($styles as $style)
            {
                if ((string)intval($style['no']) == $style['no'])
                {
                    $sku = 'style-' . $style['no'];
                }
                else
                {
                    $sku = $style['no'];
                }

                $id = $_product->getIdBySku($sku);

                if(!empty($id))
                {
                    $this->_db->update($table, array("internal_id" => $id), "style_id = '{$style['style_id']}'");
                }
            }
        }
    }

    /**
     * Get magento product ids and set as internal_id values to 'service_items' table
     */
    protected function _syncItems()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_items');
        //$table_identifier = Mage::getSingleton('core/resource')->getTableName('service_identifier');
        $_product = Mage::getModel('catalog/product');
        $select = $this->_db->select()->from($table)->where('request_id = ?', $this->_globalVars["request_id"])->where('channel_id = ?', $this->_globalVars['channel_id'])->where('internal_id is null');
        $items = $this->_db->fetchAll($select);

        if(!empty($items))
        {
            foreach ($items as $item)
            {
                $id = $_product->getIdBySku($item['plu']);
                if(!empty($id))
                {
                    $this->_db->update($table, array("internal_id" => $id), "item_id = '{$item['item_id']}'");
                }
                /*$select = $this->_db->select()->from($table_identifier)->where('item_id = ?', $item['item_id']);
                $identifiers = $this->_db->fetchAll($select);

                foreach($identifiers as $identifier)
                {
                    $id = $_product->getIdBySku($identifier['value']);

                    if(!empty($id))
                    {
                        $this->_db->update($table, array("internal_id" => $id), "item_id = '{$item['item_id']}'");
                        break;
                    }
                }*/
            }
        }
    }

    /**
     * Get magento categories and add magento categories' ids as internal_id values to 'service_category' staging tables
     */
    protected function _syncCategories()
    {
        $this->_prepareMagentoCategories();
        $this->checkLastUpdateTime();
        $this->_prepareStagingCategories();

        $table = Mage::getSingleton('core/resource')->getTableName('service_category');

        foreach($this->_stagingCats as $key => $val)
        {
            $id = array_search($val, $this->_magentoCats);

            if($id !== false)
            {
                $this->_db->update($table, array("internal_id" => $id), "category_id = '{$key}' and channel_id = '{$this->_globalVars['channel_id']}'");
            }
        }

        $this->checkLastUpdateTime();
    }

    /**
     * Get magento root category id related to selected $this->_globalVars['store_id'] and initiate $this->_magentoCats array filling
     */
    protected function _prepareMagentoCategories()
    {
        $parentId = Mage::app()->getStore($this->_globalVars['store_id'])->getRootCategoryId();
        $this->_prepareMagentoCategoryChildren(new Varien_Object(array('id' => $parentId)), false);
    }

    /**
     * Retrieve magento categories data
     *
     * @param Varien_Object|Mage_Catalog_Model_Category $category
     * @param bool $useParrentPath
     */
    protected function _prepareMagentoCategoryChildren($category, $useParrentPath = true)
    {
        $tree = Mage::getResourceModel('catalog/category_tree');
        $tree->loadNode($category->getId())
            ->loadChildren(1)
            ->getChildren();
        $tree->addCollectionData(null, true, $category->getId(), true, false);
        $children = $tree->getCollection();

        if($children)
        {
            foreach ($children as $child)
            {
                $this->_magentoCats[$child->getId()] = $useParrentPath ? $this->_magentoCats[$category->getId()] . '->' . $child->getName() : $child->getName();
                $this->_prepareMagentoCategoryChildren($child);
            }
        }
    }

    /**
     * Initiate $this->_stagingCats and  array filling starting from channel root category
     */
    protected function _prepareStagingCategories()
    {
        $this->_prepareStagingCategoryChildren(0, false);
    }

    /**
     * Retrieve categories data from 'service_category' staging table
     *
     * @param Varien_Object|Mage_Catalog_Model_Category $category
     * @param bool $useParrentPath
     */
    protected function _prepareStagingCategoryChildren($parentId, $useParrentPath = true)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_category');
        $select = $this->_db->select()
            ->from($table)
            ->where("parent_id = ?", (string)$parentId);
            //->where("internal_id is null");

        $categories = $this->_db->fetchAll($select);

        foreach ($categories as $category)
        {
            /*if(empty($category['internal_id']))
            {
                 $this->stagingEmptyCats[$category['category_id']] = $category['category_id'];
            }*/

            $this->_stagingCats[$category['category_id']] = $useParrentPath ? $this->_stagingCats[$parentId] . '->' . $category['category_name'] : $category['category_name'];
            $this->_prepareStagingCategoryChildren($category['category_id']);
        }
    }
}