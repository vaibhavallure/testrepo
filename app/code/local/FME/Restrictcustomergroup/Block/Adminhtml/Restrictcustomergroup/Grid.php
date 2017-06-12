<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('restrictcustomergroupGrid');
      $this->setDefaultSort('rule_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _getStore() {
	  
	  $storeId = (int) $this->getRequest()->getParam('store', 0); 
	  return Mage::app()->getStore($storeId);
  }
  
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('restrictcustomergroup/restrictcustomergroup')->getCollection();
      $store = $this->_getStore();
	  
	  if ($store->getId())
	  {
		  $collection->addStoreFilter($store->getId());
	  } 
	  
	  foreach ($collection as $item) {
		if ($item->getCustomerGroups()) {
		  $item->setCustomerGroups(explode(',', $item->getCustomerGroups()));
		}
	  }
	  $this->setCollection($collection);
	  
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('rule_id', array(
          'header'    => Mage::helper('restrictcustomergroup')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rule_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('restrictcustomergroup')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  $this->addColumn('priority', array(
          'header'    => Mage::helper('restrictcustomergroup')->__('Priority'),
          'align'     =>'left',
          'index'     => 'priority',
      ));
	  
	  $this->addColumn('customer_groups', array(
          'header'    => Mage::helper('restrictcustomergroup')->__('Customer Groups'),
          'align'     =>'left',
		  'sortable' => false,
          'index'     => 'customer_groups',
		  'type'  => 'options',
		  'options' => $this->helper('restrictcustomergroup')->getCustomerGroupOptions(),
		  'filter_condition_callback' => array($this, '_filterCustomerGroupCondition'),
      ));
	  
	  if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('restrictcustomergroup')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
				'filter' => false,
                //'filter_condition_callback'
                //                => array($this, '_filterStoreCondition'),
            ));
        }
	  
      $this->addColumn('type', array(
			'header'    => Mage::helper('restrictcustomergroup')->__('Rule Type'),
			'width'     => '150px',
			'index'     => 'form_type',
			'type'      => 'options',
			'options'   => array(
              'basic' => Mage::helper('restrictcustomergroup')->__('Basic'),
              'manual' => Mage::helper('restrictcustomergroup')->__('Manual'),
          ),
      ));
	  

      $this->addColumn('status', array(
          'header'    => Mage::helper('restrictcustomergroup')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('restrictcustomergroup')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('restrictcustomergroup')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('restrictcustomergroup')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('restrictcustomergroup')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('restrictcustomergroup_id');
        $this->getMassactionBlock()->setFormFieldName('restrictcustomergroup');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('restrictcustomergroup')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('restrictcustomergroup')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('restrictcustomergroup/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('restrictcustomergroup')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('restrictcustomergroup')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

	protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
	
	protected function _filterStoreCondition($collection, $column)
	{
        if (!$value = $column->getFilter()->getValue())
		{
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
	
	protected function _filterCustomerGroupCondition($collection, $column) {
	  if (!$value = $column->getFilter()->getValue())
		{
            return;
        }
		
		$this->getCollection()->addCustomerGroupFilter($value);
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}