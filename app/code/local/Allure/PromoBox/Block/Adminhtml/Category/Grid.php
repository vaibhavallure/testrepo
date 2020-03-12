<?php

class Allure_PromoBox_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('promocategoryId');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('promobox/category')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('promobox')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('category_id', array(
            'header'    => Mage::helper('promobox')->__('Category'),
            'index'     => 'category_id',
            'align'     => 'center',
            'renderer'  => 'Allure_PromoBox_Block_Adminhtml_Category_Renderer_Category'
        ));

        $this->addColumn('start_date', array(
            'header'    => Mage::helper('promobox')->__('Start Date'),
            'align'     =>'left',
            'index'     => 'start_date',
            'type'      => 'datetime',
        ));

        $this->addColumn('end_date', array(
            'header'    => Mage::helper('promobox')->__('End Date'),
            'align'     =>'left',
            'index'     => 'end_date',
            'type'      => 'datetime',

        ));
        $this->addColumn('size', array(
            'header'    => Mage::helper('promobox')->__('size'),
            'align'     =>'left',
            'index'     => 'size',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('promobox')->__('status'),
            'align'     =>'left',
            'index'     => 'status',
            'renderer'  => 'Allure_PromoBox_Block_Adminhtml_Category_Renderer_Status'

        ));


        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('promobox')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('promobox')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('category');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('promobox')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('promobox')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}