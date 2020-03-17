<?php

class Allure_PromoBox_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('promobannerId');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('promobox/banner')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('image', array(
            'header'    => Mage::helper('promobox')->__('Image'),
            'width'     => '150px',
            'index'     => 'image',
            'align'     => 'center',
            'type'      => 'image',
            'renderer'  => 'Allure_PromoBox_Block_Adminhtml_Banner_Renderer_Image'
        ));

        $this->addColumn('id', array(
            'header'    => Mage::helper('promobox')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('promobox')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

        $this->addColumn('html_block', array(
            'header'    => Mage::helper('promobox')->__('Html Content'),
            'align'     =>'left',
            'index'     => 'html_block',
        ));
        $this->addColumn('size', array(
            'header'    => Mage::helper('promobox')->__('size'),
            'align'     =>'left',
            'index'     => 'size',
            'renderer'  => 'Allure_PromoBox_Block_Adminhtml_Banner_Renderer_Size'
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
        $this->getMassactionBlock()->setFormFieldName('banners');

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