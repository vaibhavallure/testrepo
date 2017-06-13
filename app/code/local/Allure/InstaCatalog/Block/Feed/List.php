<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * Feed list block
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Feed_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     */
    public function _construct()
    {
        parent::_construct();
        $helper = Mage::helper('allure_instacatalog');
        $limit = $helper->getLimit();
       // if(empty($limit))
        $limit = 12;
        $feeds = Mage::getResourceModel('allure_instacatalog/feed_collection')
                         //->addStoreFilter(Mage::app()->getStore())
                         ->addFieldToFilter('status', 1)
                         ->setPageSize($limit)
                         ->setCurPage(1)
        				->addFieldToFilter('lookbook_mode',array('neq'=>1))
                         ->setOrder('created_timestamp', 'desc'); 
        //$feeds->setOrder('media_id', 'asc');
        $this->setFeeds($feeds);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Feed_List
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getFeeds()->load();
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
