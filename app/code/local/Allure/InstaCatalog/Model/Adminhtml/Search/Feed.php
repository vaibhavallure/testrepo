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
 * Admin search model
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Model_Adminhtml_Search_Feed extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return Allure_InstaCatalog_Model_Adminhtml_Search_Feed
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('allure_instacatalog/feed_collection')
            ->addFieldToFilter('media_id', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $feed) {
            $arr[] = array(
                'id'          => 'feed/1/'.$feed->getId(),
                'type'        => Mage::helper('allure_instacatalog')->__('Feed'),
                'name'        => $feed->getMediaId(),
                'description' => $feed->getMediaId(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/instacatalog_feed/edit',
                    array('id'=>$feed->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
