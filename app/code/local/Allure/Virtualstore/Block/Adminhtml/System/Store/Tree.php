<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Tree extends Mage_Adminhtml_Block_Widget
{
    /**
     * Cell Template
     *
     * @var Mage_Adminhtml_Block_Template
     */
    protected $_cellTemplate;

    /**
     * Internal constructor, that is called from real constructor
     */
    public function _construct()
    {
        $this->setTemplate('system/store/tree.phtml');
        parent::_construct();
    }

    /**
     * Prepare block layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->_cellTemplate = $this->getLayout()
            ->createBlock('adminhtml/template')
            ->setTemplate('system/store/cell.phtml');
        return parent::_prepareLayout();
    }

    /**
     * Get table data
     *
     * @return array
     */
    public function getTableData()
    {
        $data = array();
        foreach (Mage::getModel('allure_virtualstore/website')->getCollection() as $website) {
            /** @var $website Allure_Virtualstore_Model_Website */
            $groupCollection = $website->getGroupCollection();
            $data[$website->getId()] = array(
                'object' => $website,
                'storeGroups' => array(),
                'count' => 0
            );
            $defaultGroupId = $website->getDefaultGroupId();
            foreach ($groupCollection as $storeGroup) {
                /** @var $storeGroup Allure_Virtualstore_Model_Group */
                $storeCollection = $storeGroup->getStoreCollection();
                $storeGroupCount = max(1, $storeCollection->count());
                $data[$website->getId()]['storeGroups'][$storeGroup->getId()] = array(
                    'object' => $storeGroup,
                    'stores' => array(),
                    'count' => $storeGroupCount
                );
                $data[$website->getId()]['count'] += $storeGroupCount;
                if ($storeGroup->getId() == $defaultGroupId) {
                    $storeGroup->setData('is_default', true);
                }
                $defaultStoreId = $storeGroup->getDefaultStoreId();
                foreach ($storeCollection as $store) {
                    /** @var $store Allure_Virtualstore_Model_Store */
                    $data[$website->getId()]['storeGroups'][$storeGroup->getId()]['stores'][$store->getId()] = array(
                        'object' => $store
                    );
                    if ($store->getId() == $defaultStoreId) {
                        $store->setData('is_default', true);
                    }
                }
            }

            $data[$website->getId()]['count'] = max(1, $data[$website->getId()]['count']);
        }
        return $data;
    }

    /**
     * Create new cell template
     *
     * @return Mage_Adminhtml_Block_Template
     */
    protected function _createCellTemplate()
    {
        return clone($this->_cellTemplate);
    }

    /**
     * Render website
     *
     * @param Mage_Core_Model_Website $website
     * @return string
     */
    public function renderWebsite(Allure_Virtualstore_Model_Website $website)
    {
        return $this->_createCellTemplate()
            ->setObject($website)
            ->setLinkUrl($this->getUrl('*/*/editWebsite', array('website_id' => $website->getWebsiteId())))
            ->setInfo($this->__('Code') . ': ' . $this->escapeHtml($website->getCode()))
            ->toHtml();
    }

    /**
     * Render store group
     *
     * @param Mage_Core_Model_Store_Group $storeGroup
     * @return string
     */
    public function renderStoreGroup(Allure_Virtualstore_Model_Group $storeGroup)
    {
        $rootCategory = Mage::getModel('catalog/category')->load($storeGroup->getRootCategoryId());
        return $this->_createCellTemplate()
            ->setObject($storeGroup)
            ->setLinkUrl($this->getUrl('*/*/editGroup', array('group_id' => $storeGroup->getGroupId())))
            ->setInfo($this->__('Root Category') . ': ' . $this->escapeHtml($rootCategory->getName()))
            ->toHtml();
    }

    /**
     * Render store
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function renderStore(Allure_Virtualstore_Model_Store $store)
    {
        $cell = $this->_createCellTemplate()
            ->setObject($store)
            ->setLinkUrl($this->getUrl('*/*/editStore', array('store_id' => $store->getStoreId())))
            ->setInfo($this->__('Code') . ': ' . $this->escapeHtml($store->getCode()));
        if (!$store->getIsActive()) {
            $cell->setClass('strike');
        }
        return $cell->toHtml();
    }

}
