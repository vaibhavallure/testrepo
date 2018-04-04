<?php
class Allure_Appointments_Block_Adminhtml_Appointmentpiercers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('allure_piercers_grid');
		$this->setDefaultSort('increment_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('appointments/piercers')->getCollection();
		
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
		
		$this->addColumn('firstname', array(
				'header' => $helper->__('Name'),
				'index'  => 'firstname',
				'renderer' => 'appointments/adminhtml_appointmentpiercers_edit_renderer_name'
		));
		
		$this->addColumn('email', array(
				'header' => $helper->__('Email'),
				'index'  => 'email'
		));
		
		$this->addColumn('phone', array(
				'header' => $helper->__('Phone'),
				'index'  => 'phone'
		));
		
		$this->addColumn('working_days', array(
				'header' => $helper->__('Working Days'),
				'index'  => 'working_days',
				//'renderer' => 'appointments/adminhtml_appointmentpiercers_edit_renderer_workingdays'
		));
		
		$this->addColumn('working_hours', array(
				'header' => $helper->__('Working Hours'),
				'index'  => 'working_hours',
				'width'     => '200px',
				'renderer' => 'appointments/adminhtml_appointmentpiercers_edit_renderer_workinghoursgrid'
			
		));
		$this->addColumn('break_hours', array(
				'header' => $helper->__('Break Hours'),
				'index'  => 'working_hours',
				'width'     => '200px',
				'renderer' => 'appointments/adminhtml_appointmentpiercers_edit_renderer_breakhoursgrid'
		
		));
		
		$this->addColumn('is_active', array(
				'header' => $helper->__('Active'),
				'index'  => 'is_active'
		));
		
		$this->addColumn('is_active', array(
				'header' => $helper->__('Active'),
				'type' => 'options',
				'width'     => '80',
				'options' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
				'index' => 'is_active',
				'sortable' => false,
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