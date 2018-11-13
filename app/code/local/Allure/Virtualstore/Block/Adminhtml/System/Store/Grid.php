<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('storeGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('allure_virtualstore/website')
            ->getCollection()
            ->joinGroupAndStore();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('website_title', array(
            'header'        => Mage::helper('allure_virtualstore')->__('Website Name'),
            'align'         =>'left',
            'index'         => 'name',
            'filter_index'  => 'main_table.name',
            'renderer'      => 'allure_virtualstore/adminhtml_system_store_grid_render_website'
        ));

        $this->addColumn('group_title', array(
            'header'        => Mage::helper('allure_virtualstore')->__('Store Name'),
            'align'         =>'left',
            'index'         => 'group_title',
            'filter_index'  => 'group_table.name',
            'renderer'      => 'allure_virtualstore/adminhtml_system_store_grid_render_group'
        ));

        $this->addColumn('store_title', array(
            'header'        => Mage::helper('allure_virtualstore')->__('Store View Name'),
            'align'         =>'left',
            'index'         => 'store_title',
            'filter_index'  => 'store_table.name',
            'renderer'      => 'allure_virtualstore/adminhtml_system_store_grid_render_store'
        ));

        return parent::_prepareColumns();
    }

}
