<?php
class Allure_Appointments_Block_Adminhtml_Hidedates_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		//$this->setId('allure_hidedates_grid');
		//$this->setDefaultSort('increment_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('appointments/dates')->getCollection();
		
				$this->setCollection($collection);
				parent::_prepareCollection();
				return $this;
	}

	protected function _prepareColumns()
	{
		$helper = Mage::helper('appointments');

		$this->addColumn('id', array(
				'header' => $helper->__('Piercer Id'),
				'width'     => '50px',
				'index'  => 'id'
		));
		$this->addColumn('date', array(
		    
		    'header'    => $helper->__('Date'),
		    'align'     => 'left',
		    'type'      => 'date', // specify this column is date type
		    'index'     => 'date',
		));
		if (!Mage::app()->isSingleStoreMode()) {
		    if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
		        $storeOptions = Mage::getSingleton('allure_virtualstore/adminhtml_store')->getStoreOptionHash();
		    }else{
		        $storeOptions = Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash();
		    }
			$this->addColumn('store_id', array(
					'header' => $helper->__('Store'),
					'type' => 'options',
			        'options' => $storeOptions,//Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
					'index' => 'store_id',
					'sortable' => false,
			));
		}
		
	
		
		$this->addColumn('is_available', array(
				'header' => $helper->__('Is Availbale'),
				'index'  => 'is_available',
		        'type'=>'options',
                'options' => array('1' => 'Yes', '0' => 'No')
		));
		$this->addColumn('exclude', array(
		    'header' => $helper->__('Exclude'),
		    'index'  => 'exclude',
		    'type'=>'options',
		    'options' => array('1' => 'Yes', '0' => 'No')
		));
		
		
		$this->addColumn(
				'action',
				array(
						'header'    => $helper->__('Action'),
						'width'     => '100',
						'type'      => 'action',
						'getter'    => 'getId',
						'actions'   => array(
								array(
										'caption' => $helper->__('Edit'),
										'url'     => array('base' => '*/*/edit'),
										'field'   => 'id',
								)
						),
						'filter'    => false,
						'sortable'  => false,
						'index'     => 'stores',
						'is_system' => true,
				)
				);
		
		$this->addExportType('*/*/exportCsv', $helper->__('CSV'));
		$this->addExportType('*/*/exportExcel', $helper->__('Excel XML'));

		return parent::_prepareColumns();
	}

	public function filterCallback($collection, $column)
	{
		$value = $column->getFilter()->getValue();
		if (is_null(@$value))
			return;
			else
				$collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true,'_secure' => true));
	}
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id'=>$row->getId(),'_secure' => true));
	}
}