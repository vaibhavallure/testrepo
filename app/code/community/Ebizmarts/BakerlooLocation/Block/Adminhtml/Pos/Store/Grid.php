<?php

class Ebizmarts_BakerlooLocation_Block_Adminhtml_Pos_Store_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        $this->setId('bakerloo_restful_stores');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('created_at');
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('bakerloo_location/store')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
            'header' => Mage::helper('bakerloo_location')->__('ID'),
            'index'  => 'id',
            'type'   => 'int',
            )
        );
        $this->addColumn(
            'title',
            array(
            'header' => Mage::helper('bakerloo_location')->__('Name'),
            'index'  => 'title',
            )
        );
        $this->addColumn(
            'street',
            array(
            'header' => Mage::helper('bakerloo_location')->__('Address'),
            'index'  => 'street',
            )
        );
        $this->addColumn(
            'telephone',
            array(
            'header' => Mage::helper('bakerloo_location')->__('Telephone'),
            'index'  => 'telephone',
            )
        );
        $this->addColumn(
            'active',
            array(
            'header' => Mage::helper('bakerloo_location')->__('Active'),
            'index'  => 'active',
            'type'  => 'options',
            'options' => array(
                0 => Mage::helper('bakerloo_location')->__('No'),
                1 => Mage::helper('bakerloo_location')->__('Yes'),
            )
            )
        );
        $this->addColumn(
            'notes',
            array(
            'header' => Mage::helper('bakerloo_location')->__('Notes'),
            'index'  => 'notes',
            )
        );

        $this->addColumn(
            'action',
            array(
            'header' => Mage::helper('bakerloo_location')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('Edit'),
                    'url'     => array('base' => 'adminhtml/pos_store/edit'),
                    'field'   => 'id',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object
     * @return string
     */
    public function getRowUrl($log)
    {
        return false;
    }
}
