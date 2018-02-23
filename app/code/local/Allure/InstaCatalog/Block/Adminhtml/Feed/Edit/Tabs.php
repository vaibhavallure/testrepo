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
 * Feed admin edit tabs
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('feed_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('allure_instacatalog')->__('Instagram Feed'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_feed',
            array(
                'label'   => Mage::helper('allure_instacatalog')->__('Feed'),
                'title'   => Mage::helper('allure_instacatalog')->__('Feed'),
                'content' => $this->getLayout()->createBlock(
                    'allure_instacatalog/adminhtml_feed_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        /* if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_feed',
                array(
                    'label'   => Mage::helper('allure_instacatalog')->__('Store views'),
                    'title'   => Mage::helper('allure_instacatalog')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'allure_instacatalog/adminhtml_feed_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        } */
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve feed entity
     *
     * @access public
     * @return Allure_InstaCatalog_Model_Feed
     */
    public function getFeed()
    {
        return Mage::registry('current_feed');
    }
}
