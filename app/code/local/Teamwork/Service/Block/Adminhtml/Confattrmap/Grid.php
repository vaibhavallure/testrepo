<?php

class Teamwork_Service_Block_Adminhtml_Confattrmap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setDefaultLimit(200);
    }
	
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('teamwork_service/confattrmapprop')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('teamwork_service');
        
        $this->addColumn('chq_code', array(
            'header' => $helper->__('CHQ attribute code'),
            'index' => 'chq_code',
            'type' => 'text',
        ));
        
        $this->addColumn('chq_description', array(
            'header' => $helper->__('CHQ description'),
            'index' => 'chq_description',
            'type' => 'text',
        ));
        
        $this->addColumn('chq_alias', array(
            'header' => $helper->__('CHQ alias'),
            'index' => 'chq_alias',
            'type' => 'text',
        ));
        
        $this->addColumn('magento_attribute_code', array(
            'header' => $helper->__('Magento attribute code'),
            'index' => 'magento_attribute_code',
            'type' => 'text',
        ));
        
        $this->addColumn('magento_frontend_label', array(
            'header' => $helper->__('Magento frontend attribute label'),
            'index' => 'magento_frontend_label',
            'type' => 'text',
        ));

        $this->addColumn('values_mapping', array(
            'header' => $helper->__('Values mapping'),
            'index' => 'values_mapping',
            'options' => Teamwork_Service_Model_Confattrmapprop::getValuesMappingOptions(),
            'type' => 'options',
        ));

        $this->addColumn('is_active', array(
            'header' => $helper->__('Is Active'),
            'index' => 'is_active',
            'options' => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
            'type' => 'options',
        ));
		
		$this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'      =>'*/*/edit/'
                        ),
                        'field'   => 'link_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));
        
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($model)
    {
        return $this->getUrl('*/*/edit', array(
            'link_id' =>   $model->getId(),
        ));
    }
}
