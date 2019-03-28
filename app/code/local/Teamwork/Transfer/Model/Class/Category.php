<?php
/**
 * Categories updating model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Category extends Teamwork_Transfer_Model_Transfer
{
    /**
     * Path to categories image files directory
     *
     * @var string
     */
    protected $_catalog_category_path;

    /**
     * Categories' path cache
     *
     * @var array
     */
    protected $_categoryPath = array();

    /**
     * Category staging table (service_category, see init method)
     *
     * @var string
     */
    protected $_table;

    /**
     * "isAnchor" magento category attribute value for each imported category (const)
     *
     * @var int
     */
    protected $_isAnchor = 0;

    /**
     * "Use Navigation Menu CMS Block" (use_navigation_block) magento enterprise category attribute value for each imported category (const)
     *
     * @var int
     */
    protected $_useNavigationBlock = 0;

    /**
     * Allow image attaching
     *
     * @var bool
     */
    protected $image = true;

    /**
     * Allow thumbnail attaching
     *
     * @var bool
     */
    protected $thumbnail = false;

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     */
    public function init($globalVars)
    {
        $this->_globalVars = $globalVars;
        $this->_table = Mage::getSingleton('core/resource')->getTableName('service_category');
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_catalog_category_path = Mage::getBaseDir('media') . '/catalog/category/';
    }

    /**
     * Entry point
     */
    public function execute()
    {
        try
        {
            if(Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_CATEGORIES))
            {
                $this->_getStagingCategory();
            }
        }
        catch(Exception $e)
        {
            $this->_getLogger()->addMessage(sprintf("Should not be here: file: %s; line: %s", __FILE__, __LINE__), Zend_Log::DEBUG);
            $this->_getLogger()->addException($e);
            $this->_addErrorMsg("Internal error (exception): " . $e->getMessage(), false);
        }

        $this->updateEcm('Categories');
        return $this;
    }

    /**
     * Get data from staging tables and initiate import/update process.
     */
    protected function _getStagingCategory()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $select = $this->_db->select()
            ->from(array('cat' => $this->_table))
            ->joinLeft(array('med' => Mage::getSingleton('core/resource')->getTableName('service_media')), 'cat.category_id = med.host_id and cat.channel_id = med.channel_id')
            ->where('cat.request_id = ?', $this->_globalVars['request_id'])
        ->order('changed');

        if($categories = $this->_db->fetchAll($select))
        {
            foreach($categories as $category)
            {
                $this->_saveCategory($category);
                $this->checkLastUpdateTime();
            }
        }
    }

    /**
     * Get magento category ids by style guid from staging tables
     *
     * @param string $styleId
     *
     * @return array
     */
    public function getStyleMagentoCategories($styleId)
    {
        $select = $this->_db->select()
            ->from(array('stcat' => Mage::getSingleton('core/resource')->getTableName('service_style_category')), array())
            ->join(array('cat' => $this->_table), 'stcat.category_id=cat.category_id and stcat.channel_id=cat.channel_id', array('cat.internal_id'))
            ->where('stcat.style_id = ?', $styleId)
        ->where('cat.internal_id is not null');
        return $this->_db->fetchCol($select);
    }

    /**
     * Get magento category ids by item guid from staging tables
     *
     * @param string $itemId
     *
     * @return array
     */
    public function getItemMagentoCategories($itemId)
    {
        $select = $this->_db->select()
            ->from(array('itcat' => Mage::getSingleton('core/resource')->getTableName('service_item_category')), array())
            ->join(array('cat' => $this->_table), 'itcat.category_id=cat.category_id and itcat.channel_id=cat.channel_id', array('cat.internal_id'))
            ->where('itcat.item_id = ?', $itemId)
        ->where('cat.internal_id is not null');
        return $this->_db->fetchCol($select);
    }

    /**
     * Create/update magento category object
     *
     * @param array $categoryData
     */
    protected function _saveCategory($categoryData)
    {
        $category = Mage::getModel('catalog/category');
        
        if( !empty($categoryData['internal_id']) )
        {
            $category->load($categoryData['internal_id']);

            if(!$category->getEntityId() && $categoryData['is_deleted'] == 0)
            {
                $categoryData['internal_id'] = null;
                $category = Mage::getModel('catalog/category');
            }
            elseif($categoryData['is_deleted'] == 1)
            {
                Mage::register('isSecureArea', 1);
                try
                {
                    $category->delete();
                }
                catch(Exception $e)
                {
                    $this->_addErrorMsg(sprintf("Error occured while deleting category: %s (%s)", $category->getName(), $e->getMessage()), true);
                    $this->_getLogger()->addException($e);
                    return false;
                }

                Mage::unregister('isSecureArea');
                return true;
            }
        }
        elseif( $categoryData['is_deleted'] )
        {
            return;
        }

        $category->setName($categoryData['category_name']);

        // if category label was changed, we force Magento to regenerate URL key
        if ($category->getData('name') != $category->getOrigData('name'))
        {
            $category->setUrlKey('');
        }
        $category->setIsActive($categoryData['is_active']);
        //$category->setStoreId($this->_globalVars['store_id']);
        $category->setWebsiteId($this->_globalVars['websites']);
        $category->setIsAnchor($this->_isAnchor);
        $category->setUseNavigationBlock($this->_useNavigationBlock);
        $category->setPosition($categoryData['display_order']);
        $category->setDescription($categoryData['description'] ? $categoryData['description'] : '');
        $category->setMetaKeywords($categoryData['keywords'] ? $categoryData['keywords'] : '');

        $path = $this->_getMagentoCategoryPath($categoryData['parent_id'], $categoryData['internal_id']);
        $category->setPath( $path );
        $category->setLevel( count(explode('/', $path)) - 1 );
        
        if(empty($_FILES)) $_FILES = array(0);

        try
        {
          $category->save();
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while saving category: %s (%s)", $category->getName(), $e->getMessage()), true);
            $this->_getLogger()->addException($e);
            return false;
        }

        if(empty($categoryData['internal_id']))
        {
            $categoryData['internal_id'] = $category->getId();
            $this->_db->update($this->_table, array('internal_id' => $categoryData['internal_id']), "category_id = '{$categoryData['category_id']}' and channel_id = '{$this->_globalVars['channel_id']}'");
            $category->setPosition($categoryData['display_order']);
        }
        /*download images*/
        $image = Mage::getModel("teamwork_transfer/media")->getMediaImages($categoryData['category_id'], 'category', $this->_globalVars['channel_id']);
        if(!empty($image))
        {
            /*copy images to media directory*/
            $name = $this->_getCategoryImages($image, $categoryData['internal_id']/*, $this->thumbnail*/);
            /*attach images*/
            if($this->image)
            {
                $category->setImage('image_'.$name);
            }
            if($this->thumbnail)
            {
                $category->setThumbnail('thumbnail_'.$name);
            }
        }
        else
        {
            /*unattach images*/
            if($this->image)
            {
                $category->addData(array('image' => array('delete' => 1)));
            }
            if($this->thumbnail)
            {
                $category->addData(array('thumbnail' => array('delete' => 1)));
            }
        }
        try
        {
          $category->save();
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while saving category: %s (%s)", $category->getName(), $e->getMessage()), true);
            $this->_getLogger()->addException($e);
            return false;
        }
        return true;
    }

    /**
     * Get and magento category path
     *
     * @param array $globalVars
     */
    protected function _getMagentoCategoryPath($parent_id, $internal_id)
    {
        if(empty($this->_categoryPath[$parent_id]))
        {
            if(!$parent_id)
            {
                $p_id = Mage::app()->getStore($this->_globalVars['store_id'])->getRootCategoryId();
                $this->_categoryPath[$parent_id] = Mage::getModel('catalog/category')->load($p_id)->getPath();
            }
            else
            {
                $select = $this->_db->select()
                    ->from($this->_table, array('internal_id'))
                    ->where('category_id = ?', $parent_id)
                ->where('channel_id = ?', $this->_globalVars['channel_id']);
                if(!$p_id = $this->_db->fetchOne($select))
                {
                    return false;
                }

                $this->_categoryPath[$parent_id] =  Mage::getModel('catalog/category')->load($p_id)->getPath();
                $parts = explode('/', $this->_categoryPath[$parent_id]);
                if(end($parts) != $p_id)
                {
                    $this->_categoryPath[$parent_id] = $this->_categoryPath[$parent_id] . '/' . $p_id;
                }
            }
        }
        return $internal_id ? ($this->_categoryPath[$parent_id] . '/' . $internal_id) : $this->_categoryPath[$parent_id];
    }

    /**
     * Move category images from temp to media directory for attaching
     *
     * @param array $image
     * @param int $magento_category_id
     *
     * @return string
     */
    protected function _getCategoryImages($image, $magento_category_id)
    {
        $name = 'category_'. $magento_category_id . '.' . $image[0]['format'];
        if(!is_dir($this->_catalog_category_path))
        {
            mkdir($this->_catalog_category_path, 0777, true);
        }
        file_put_contents($this->_catalog_category_path . 'image_' . $name, file_get_contents($image[0]['link']));
        if($this->thumbnail)
        {
            file_put_contents($this->_catalog_category_path . 'thumbnail_' . $name, file_get_contents($image[0]['link']));
        }
        unlink($image[0]['link']);
        return $name;
    }
}