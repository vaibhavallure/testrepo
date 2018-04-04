<?php
class Allure_Appointments_Block_Adminhtml_Piercingtiming_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('allure_piercing_timing_grid');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('appointments/timing')->getCollection();
		
				$this->setCollection($collection);
				parent::_prepareCollection();
				return $this;
	}

	protected function _prepareColumns()
	{
		$helper = Mage::helper('appointments');

		$this->addColumn('qty', array(
				'header' => $helper->__('No of People in Group:'),
				'index'  => 'qty'
		));
		
		$this->addColumn('time', array(
				'header' => $helper->__('Time Required (in min)'),
				'index'  => 'time'
		));
		//if (!Mage::app()->isSingleStoreMode()) {
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
		//}
				
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