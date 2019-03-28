<?php
class Teamwork_Service_Block_Adminhtml_Service_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultLimit(100);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('teamwork_service/adminhtml_stagingtable');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('teamwork_service');

        $this->addColumn('table', array(
            'header'    => $helper->__('table name'),
            'index'     => 'table',
            'type'      => 'text',
        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getTable',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'      =>'*/*/view/'
                        ),
                        'field'   => 'table'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($model)
    {
        return $this->getUrl('*/*/view', array(
            'table' => $model->getTable(),
        ));
    }
}